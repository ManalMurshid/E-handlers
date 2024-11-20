from ultralytics import YOLO

# Load the fine-tuned YOLOv8 model (ensure the path is correct)
model = YOLO("yolov8s.pt")

# Define category IDs for persons, laptops, and phones
CUSTOM_PERSON_ID = 0
CUSTOM_LAPTOP_ID = 63
CUSTOM_PHONE_ID = 67

# Confidence threshold for detection
CONFIDENCE_THRESHOLD = 0.5

def analyze_image(image):
    """
    Analyzes an input image to detect objects (person, laptop, phone) and determines if it's normal or abnormal.

    Parameters:
        image (numpy.ndarray): The input image as a NumPy array (read using cv2 or from a Flask request).

    Returns:
        str: "normal" if no abnormal behavior is detected, otherwise "abnormal."
    """
    # Perform object detection
    results = model(image)

    # Initialize counters for detected objects
    person_count = 0
    laptop_count = 0
    phone_count = 0

    # Analyze detections and count relevant objects
    for detection in results[0].boxes:
        class_id = int(detection.cls)
        confidence = detection.conf  # Get the confidence score

        if confidence >= CONFIDENCE_THRESHOLD:  # Only consider detections above the threshold
            if class_id == CUSTOM_PERSON_ID:
                person_count += 1
            elif class_id == CUSTOM_LAPTOP_ID:
                laptop_count += 1
            elif class_id == CUSTOM_PHONE_ID:
                phone_count += 1

    # Determine if the image is abnormal based on detection counts
    if person_count > 1 or laptop_count > 1 or phone_count >= 1:
        return {
            'status': 'success',
            'label': "abnormal",
        }
    else:
        return {
            'status': 'success',
            'label': "normal",
        }
