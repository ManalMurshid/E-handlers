import cv2
import joblib
import numpy as np
import pandas as pd
from app.models.headpose.fsanet_wrapper import FsanetWrapper
from app.models.headpose.config import Config

# Load pre-trained SSD model
SSD_PROTO = 'app/models/headpose/SSD Model/deploy.prototxt'
SSD_MODEL = 'app/models/headpose/SSD Model/res10_300x300_ssd_iter_140000.caffemodel'
ssd_model = cv2.dnn.readNetFromCaffe(SSD_PROTO, SSD_MODEL)

# Load pre-trained Random Forest model
RANDOM_FOREST_MODEL = "app/models/headpose/random_forest_model_with_cross_val.joblib"
clf = joblib.load(RANDOM_FOREST_MODEL)

# Initialize FSANet
config = Config()
fsanet = FsanetWrapper(graph=config.graph_fsanet)

def detect_faces_ssd(image):
    input_size = (300, 300)
    img_resized = cv2.resize(image, input_size)
    blob = cv2.dnn.blobFromImage(img_resized, 1.0, input_size, (104.0, 177.0, 123.0))
    ssd_model.setInput(blob)
    detections = ssd_model.forward()

    faces = []
    h, w = image.shape[:2]
    for i in range(detections.shape[2]):
        confidence = detections[0, 0, i, 2]
        if confidence > 0.5:  # Confidence threshold
            box = detections[0, 0, i, 3:7] * np.array([w, h, w, h])
            (startX, startY, endX, endY) = box.astype("int")
            faces.append((startX, startY, endX - startX, endY - startY))
    return faces

def take_faces(detected, input_img, im_size, ratio):
    faces = np.empty((len(detected), im_size, im_size, 3))
    img_h, img_w, _ = np.shape(input_img)

    if len(detected) > 0:
        for i, bb in enumerate(detected):
            (xmin, ymin, w, h) = bb
            xmax, ymax = xmin + w, ymin + h
            xw1 = max(int(xmin - ratio * w), 0)
            yw1 = max(int(ymin - ratio * h), 0)
            xw2 = min(int(xmax + ratio * w), img_w - 1)
            yw2 = min(int(ymax + ratio * h), img_h - 1)
            face = input_img[yw1:yw2 + 1, xw1:xw2 + 1, :]
            face_resized = cv2.resize(face, (im_size, im_size))
            faces[i, :, :, :] = cv2.normalize(face_resized, None, alpha=0, beta=255, norm_type=cv2.NORM_MINMAX)
    return faces

def classify_headpose(image):
    detected_faces = detect_faces_ssd(image)
    
    # If no faces are detected
    if not detected_faces:
        return {"status": "error", "message": "No face detected", "label": None}
    
    # If more than one face is detected
    if len(detected_faces) > 1:
        return {"status": "success", "label": "Abnormal", "message": "Multiple faces detected"}

    # Process the single detected face
    face = detected_faces[0]
    faces = take_faces([face], image, config.image_size, config.ratio)
    params = fsanet.predict(images=faces)
    yaw, pitch, roll = params[0], params[1], params[2]
    features = pd.DataFrame([[yaw, pitch, roll]], columns=['yaw', 'pitch', 'roll'])
    prediction = clf.predict(features)
    label = 'normal' if prediction[0] == 0 else 'abnormal'

    return {
        "status": "success",
        "label": label,
        "yaw": yaw.item(),
        "pitch": pitch.item(),
        "roll": roll.item()
    }
