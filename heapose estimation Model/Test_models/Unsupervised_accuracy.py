import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.cluster import DBSCAN
from sklearn.ensemble import IsolationForest
from sklearn.metrics import classification_report, confusion_matrix

# Load the data
data = pd.read_csv("new_results/train_headpose_SSD.csv")  # Replace with your actual dataset path

# Filter out rows with no face detected
data = data[data['face_detected'] == 'Detected']

# Convert columns to numeric
data[['yaw', 'pitch', 'roll']] = data[['yaw', 'pitch', 'roll']].apply(pd.to_numeric, errors='coerce')

# Remove rows with NaN values
data = data.dropna(subset=['yaw', 'pitch', 'roll'])

# Features
X = data[['yaw', 'pitch', 'roll']].values

# Scale the features
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)



# Fit the DBSCAN model
dbscan = DBSCAN(eps=1.0, min_samples=7).fit(X_scaled)

# Predict anomalies
data['dbscan_classification'] = dbscan.labels_

# Map DBSCAN output to 'Normal' and 'Abnormal'
data['dbscan_classification'] = data['dbscan_classification'].map(lambda x: 'Abnormal' if x == -1 else 'Normal')



# # Fit the Isolation Forest model
# iso_forest = IsolationForest(contamination=0.05, random_state=42)  # Adjust contamination according to your needs
# iso_forest.fit(X_scaled)

# # Predict anomalies
# data['iso_forest_classification'] = iso_forest.predict(X_scaled)

# # Map Isolation Forest output to 'Normal' and 'Abnormal'
# data['iso_forest_classification'] = data['iso_forest_classification'].map({1: 'Normal', -1: 'Abnormal'})

# Fit the Isolation Forest model with specified parameters
iso_forest = IsolationForest(contamination=0.05, n_estimators=100, max_samples=200, random_state=42)
iso_forest.fit(X_scaled)

# Predict anomalies
data['iso_forest_classification'] = iso_forest.predict(X_scaled)

# Map Isolation Forest output to 'Normal' and 'Abnormal'
data['iso_forest_classification'] = data['iso_forest_classification'].map({1: 'Normal', -1: 'Abnormal'})





# Convert 'Label' column to binary labels
data['Truth'] = data['Label'].apply(lambda x: 0 if x == 'Normal' else 1)

# Convert predictions to binary labels for evaluation
data['dbscan_classification'] = data['dbscan_classification'].apply(lambda x: 0 if x == 'Normal' else 1)
data['iso_forest_classification'] = data['iso_forest_classification'].apply(lambda x: 0 if x == 'Normal' else 1)

# Evaluation for DBSCAN
print("DBSCAN Classification Report")
print(classification_report(data['Truth'], data['dbscan_classification']))
print("DBSCAN Confusion Matrix")
print(confusion_matrix(data['Truth'], data['dbscan_classification'], labels=[0, 1]))

# Evaluation for Isolation Forest
print("Isolation Forest Classification Report")
print(classification_report(data['Truth'], data['iso_forest_classification']))
print("Isolation Forest Confusion Matrix")
print(confusion_matrix(data['Truth'], data['iso_forest_classification'], labels=[0, 1]))





