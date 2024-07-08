import cv2
import os

# Check OpenCV version
print("OpenCV version:", cv2.__version__)

# Print path to OpenCV data directory
print("OpenCV data directory:", cv2.data.haarcascades)

# Verify existence of XML file
xml_file_path = os.path.join(cv2.data.haarcascades, 'lbpcascade_frontalface.xml')
if os.path.exists(xml_file_path):
    print("XML file exists:", xml_file_path)
else:
    print("XML file does not exist at:", xml_file_path)
