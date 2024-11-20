import tempfile
import cv2
import os
import numpy as np
from datetime import datetime
from mysql.connector import Error
from flask import Blueprint, request, jsonify
from app.services.headpose_service import classify_headpose
from app.services.audio_service import initialize_audio_service, detect_anomaly_in_audio
from app.services.object_detection_service import analyze_image
from app.db import Database

# Initialize Blueprint and Database
routes = Blueprint('routes', __name__)

db = Database(
    host="localhost",
    user="root",
    password="",
    database="proctor_on"
)

# Initialize audio service components
config, voice_filter, silero_model, speaker_embedding = initialize_audio_service()

# Helper function to create metadata dictionary
def create_metadata(request):
    return {
        "exam_id": request.form.get("exam_id"),
        "user_id": request.form.get("user_id"),
        "timestamp": datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        "email": request.form.get("email")
    }

# Helper function to validate metadata
def validate_metadata(metadata):
    if not all(metadata.values()):
        return {"status": "error", "message": "Missing exam_id, user_id, or email"}, 400
    return None

# Helper function to validate and decode files
def validate_and_decode_file(file_key, file_type):
    if file_key not in request.files:
        return None, {"status": "error", "message": f"No {file_type} file provided"}, 400

    file = request.files[file_key]

    if file_type == "image":
        # Decode image
        np_img = np.frombuffer(file.read(), np.uint8)
        image = cv2.imdecode(np_img, cv2.IMREAD_COLOR)
        if image is None:
            return None, {"status": "error", "message": "Invalid image file"}, 400
        return image, None

    elif file_type == "audio":
        # Save audio file temporarily
        temp_file = tempfile.NamedTemporaryFile(delete=False)
        file.save(temp_file.name)
        return temp_file.name, None

    return None, {"status": "error", "message": "Unsupported file type"}, 400

def save_abnormal_file(file_type, file_content, metadata):
    # Define folder paths
    base_folder = "storage"
    sub_folder = os.path.join(base_folder, file_type)
    os.makedirs(sub_folder, exist_ok=True)  # Ensure the folder exists

    # Create the filename
    filename = f"{metadata['user_id']}_{metadata['exam_id']}_{metadata['timestamp'].replace(':', '-')}.{'jpeg' if file_type == 'webcam' or file_type == 'mobilecam' else 'wav'}"
    file_path = os.path.join(sub_folder, filename)

    print(file_path)

    # Save the file
    if file_type in ['webcam', 'mobilecam']:  # Save image
        cv2.imwrite(file_path, file_content)
    elif file_type == 'audio':  # Save audio
        os.rename(file_content, file_path)  # Move the temp file to the storage folder

    return file_path

def process_and_save_result(result_type, result_value, metadata, file_type, file_content):
    try:
        # Save the abnormal file if applicable
        if result_value == 'abnormal':
            save_abnormal_file(file_type, file_content, metadata)

        # Save the result to the database
        db_response = db.save_result_to_db(
            result_type=result_type,
            result_value=result_value,
            metadata=metadata
        )
        return {
            "status": db_response['status'],
            "message": db_response['message'],
            "result": {"label": result_value}
        }
    except Error as e:
        return {"status": "error", "message": f"Error saving model results to DB: {e}"}, 400

@routes.route('/predict_webcam', methods=['POST'])
def predict_webcam():
    # Extract metadata
    metadata = create_metadata(request)
    validation_error = validate_metadata(metadata)
    if validation_error:
        return jsonify(validation_error[0]), validation_error[1]

    # Validate and decode image
    image, error_response = validate_and_decode_file('image', 'image')
    if error_response:
        return jsonify(error_response[0]), error_response[1]

    # Process the image for headpose classification
    result = classify_headpose(image)

    return jsonify(process_and_save_result("headpose_model_result", result['label'], metadata, "webcam", image))

@routes.route('/predict_audio', methods=['POST'])
def predict_audio():
    # Extract metadata
    metadata = create_metadata(request)
    validation_error = validate_metadata(metadata)
    if validation_error:
        return jsonify(validation_error[0]), validation_error[1]

    # Validate and process audio file
    audio_path, error_response = validate_and_decode_file('audio', 'audio')
    if error_response:
        return jsonify(error_response[0]), error_response[1]

    # Analyze the audio
    result = detect_anomaly_in_audio(audio_path, silero_model, voice_filter, speaker_embedding, config)

    return jsonify(process_and_save_result("audio_model_result", result['label'], metadata, "audio", audio_path))

@routes.route('/predict_mobilecam', methods=['POST'])
def predict_mobilecam():
    # Extract metadata
    metadata = create_metadata(request)
    validation_error = validate_metadata(metadata)
    if validation_error:
        return jsonify(validation_error[0]), validation_error[1]

    # Validate and decode image
    image, error_response = validate_and_decode_file('image', 'image')
    if error_response:
        return jsonify(error_response[0]), error_response[1]

    # Process the image for object detection
    result = analyze_image(image)
    
    return jsonify(process_and_save_result("object_model_result", result['label'], metadata, "mobilecam", image))
