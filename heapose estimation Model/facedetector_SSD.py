import cv2
import os
from src.fsanet_wrapper import FsanetWrapper
from src.config import Config
from termcolor import colored
import numpy as np

os.environ["CUDA_VISIBLE_DEVICES"] = "-1"

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



def main():
    # Load pre-trained SSD model
    ssd_model = cv2.dnn.readNetFromCaffe('SSD Model/deploy.prototxt', 'SSD Model/res10_300x300_ssd_iter_140000.caffemodel')
    
    config = Config()
    fsanet = FsanetWrapper(graph=config.graph_fsanet)

    image_dir = "dataset_Students_Behavior_Online_Exam_org/train/images"
    result_dir = "new_results"
    os.makedirs(result_dir, exist_ok=True)
    
    with open(os.path.join(result_dir, "train_headpose_SSD.csv"), "w") as f:
        f.write("image_name,face_id,yaw,pitch,roll,face_detected\n")
        
        for image_name in os.listdir(image_dir):
            if image_name.endswith(".jpg") or image_name.endswith(".png"):
                input_img = cv2.imread(os.path.join(image_dir, image_name))
                detected = detect_faces_ssd(input_img, ssd_model)
                
                if detected:  # If faces are detected
                    for i, face in enumerate(detected):
                        if i > 0:
                            f.write(f"{image_name},face_{i+1},,,,,Detected\n")
                        else:
                            faces = take_faces([face], input_img, config.image_size, config.ratio)
                            params = fsanet.predict(images=faces)
                            for param in params:
                                f.write(f"{image_name},face_{i+1},{param[0]},{param[1]},{param[2]},Detected\n")
                            print(colored(f"[INFO] Processed and saved result for: {image_name}", "green", attrs=['bold']))
                else:  # If no faces are detected
                    f.write(f"{image_name},,,,,No faces detected\n")
                    print(colored(f"[WARNING] No faces detected in image: {image_name}", "yellow", attrs=['bold']))

if __name__ == '__main__':
    main()
