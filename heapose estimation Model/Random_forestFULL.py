import os
import cv2
import joblib
import numpy as np
import pandas as pd
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score
from src.fsanet_wrapper import FsanetWrapper
from src.config import Config
from termcolor import colored

# Load the pre-trained Random Forest model
joblib_file = "Random forest/random_forest_model.joblib"
clf = joblib.load(joblib_file)

# Function to detect faces using SSD model
def detect_faces_ssd(image, ssd_model):
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

# Function to extract faces from images
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

# Main function
def main():
    # Load pre-trained SSD model
    ssd_model = cv2.dnn.readNetFromCaffe('SSD Model/deploy.prototxt', 'SSD Model/res10_300x300_ssd_iter_140000.caffemodel')
    
    config = Config()
    fsanet = FsanetWrapper(graph=config.graph_fsanet)

    image_dir = "dataset_Students_Behavior_Online_Exam_org/train/image2"
    result_dir = "new_results"
    os.makedirs(result_dir, exist_ok=True)

    results = []

    for image_name in os.listdir(image_dir):
        if image_name.endswith(".jpg") or image_name.endswith(".png"):
            input_img = cv2.imread(os.path.join(image_dir, image_name))
            detected = detect_faces_ssd(input_img, ssd_model)
            
            ground_truth = 'Abnormal' if image_name.startswith('t_5') else 'Normal'
            
            if detected:  # If faces are detected
                for i, face in enumerate(detected):
                    faces = take_faces([face], input_img, config.image_size, config.ratio)
                    params = fsanet.predict(images=faces)
                    yaw, pitch, roll = params[0], params[1], params[2]
                    features = pd.DataFrame([[yaw, pitch, roll]], columns=['yaw', 'pitch', 'roll'])
                    prediction = clf.predict(features)
                    label = 'Normal' if prediction[0] == 0 else 'Abnormal'
                    results.append([image_name, i+1, yaw, pitch, roll, label, ground_truth])
                    print(f"Image: {image_name}, Face: {i+1}, Yaw: {yaw}, Pitch: {pitch}, Roll: {roll}, Classification: {label}")
                    print(colored(f"[INFO] Processed and saved result for: {image_name}", "green", attrs=['bold']))
            else:
                results.append([image_name, '', '', '', '', 'No face detected', ground_truth])
                print(colored(f"[WARNING] No faces detected in image: {image_name}", "yellow", attrs=['bold']))
    
    df_results = pd.DataFrame(results, columns=["image_name", "face_id", "yaw", "pitch", "roll", "label", "ground_truth"])
    df_results.to_csv(os.path.join(result_dir, "results.csv"), index=False)
    
    # Evaluate the results
    df_results = df_results[df_results['label'] != 'No face detected']
    y_true = df_results['ground_truth'].apply(lambda x: 1 if x == 'Abnormal' else 0)
    y_pred = df_results['label'].apply(lambda x: 1 if x == 'Abnormal' else 0)

    accuracy = accuracy_score(y_true, y_pred)
    precision = precision_score(y_true, y_pred, zero_division=1)
    recall = recall_score(y_true, y_pred, zero_division=1)
    f1 = f1_score(y_true, y_pred, zero_division=1)

    print(f"Accuracy: {accuracy}")
    print(f"Precision: {precision}")
    print(f"Recall: {recall}")
    print(f"F1-Score: {f1}")

if __name__ == '__main__':
    main()


# import os
# import cv2
# import joblib
# import numpy as np
# import pandas as pd 
# from src.fsanet_wrapper import FsanetWrapper
# from src.config import Config
# from termcolor import colored

# # Load the pre-trained Random Forest model
# joblib_file = "Test_models/random_forest_model.pkl"
# clf = joblib.load(joblib_file)

# # Function to detect faces using SSD model
# def detect_faces_ssd(image, ssd_model):
#     input_size = (300, 300)
#     img_resized = cv2.resize(image, input_size)
#     blob = cv2.dnn.blobFromImage(img_resized, 1.0, input_size, (104.0, 177.0, 123.0))
#     ssd_model.setInput(blob)
#     detections = ssd_model.forward()

#     faces = []
#     h, w = image.shape[:2]
#     for i in range(detections.shape[2]):
#         confidence = detections[0, 0, i, 2]
#         if confidence > 0.5:  # Confidence threshold
#             box = detections[0, 0, i, 3:7] * np.array([w, h, w, h])
#             (startX, startY, endX, endY) = box.astype("int")
#             faces.append((startX, startY, endX - startX, endY - startY))
#     return faces

# # Function to extract faces from images
# def take_faces(detected, input_img, im_size, ratio):
#     faces = np.empty((len(detected), im_size, im_size, 3))
#     img_h, img_w, _ = np.shape(input_img)

#     if len(detected) > 0:
#         for i, bb in enumerate(detected):
#             (xmin, ymin, w, h) = bb
#             xmax, ymax = xmin + w, ymin + h
#             xw1 = max(int(xmin - ratio * w), 0)
#             yw1 = max(int(ymin - ratio * h), 0)
#             xw2 = min(int(xmax + ratio * w), img_w - 1)
#             yw2 = min(int(ymax + ratio * h), img_h - 1)
#             face = input_img[yw1:yw2 + 1, xw1:xw2 + 1, :]
#             face_resized = cv2.resize(face, (im_size, im_size))
#             faces[i, :, :, :] = cv2.normalize(face_resized, None, alpha=0, beta=255, norm_type=cv2.NORM_MINMAX)
#     return faces

# # Main function
# def main():
#     # Load pre-trained SSD model
#     ssd_model = cv2.dnn.readNetFromCaffe('SSD Model/deploy.prototxt', 'SSD Model/res10_300x300_ssd_iter_140000.caffemodel')
    
#     config = Config()
#     fsanet = FsanetWrapper(graph=config.graph_fsanet)

#     image_dir = "dataset_Students_Behavior_Online_Exam_org/train/image2"
#     result_dir = "new_results"
#     os.makedirs(result_dir, exist_ok=True)

#     with open(os.path.join(result_dir, "results.csv"), "w") as f:
#         f.write("image_name,face_id,yaw,pitch,roll,label\n")
    
#         for image_name in os.listdir(image_dir):
#             if image_name.endswith(".jpg") or image_name.endswith(".png"):
#                 input_img = cv2.imread(os.path.join(image_dir, image_name))
#                 detected = detect_faces_ssd(input_img, ssd_model)
                
#                 if detected:  # If faces are detected
#                     for i, face in enumerate(detected):
#                         faces = take_faces([face], input_img, config.image_size, config.ratio)
#                         params = fsanet.predict(images=faces)
#                         # for param in params:
#                         yaw, pitch, roll = params[0], params[1], params[2]
#                         # features = np.array([[yaw, pitch, roll]])
#                         features = pd.DataFrame([[yaw, pitch, roll]], columns=['yaw', 'pitch', 'roll'])
#                         prediction = clf.predict(features)
#                         # print(prediction)
#                         # print('%s ---- value %s'%(type(prediction[0]),prediction[0]))
#                         label = 'Normal' if prediction[0] == 0 else 'Abnormal'
#                         print(f"Image: {image_name}, Face: {i+1}, Yaw: {yaw}, Pitch: {pitch}, Roll: {roll}, Classification: {label}")
#                         f.write(f"{image_name},face_{i+1},{yaw},{pitch},{roll},{label}\n")
#                         print(colored(f"[INFO] Processed and saved result for: {image_name}", "green", attrs=['bold']))
#                 else:
#                     f.write(f"{image_name},,,,'No face detected'\n")  # If no faces are detected
#                     print(colored(f"[WARNING] No faces detected in image: {image_name}", "yellow", attrs=['bold']))
                
# if __name__ == '__main__':
#     main()
