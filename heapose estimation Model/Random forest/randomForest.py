import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report
import joblib

# Load the dataset
file_path = 'new_results/train_headpose_SSD.csv'
df = pd.read_csv(file_path)

# Display the first few rows of the dataset
print(df.head())

# Remove unnecessary columns (modify this line to match your actual column names)
df = df.drop(columns=['image_name', 'face_id', 'face_detected'])

# Drop rows with missing values
df = df.dropna()

# Encode labels as numeric values
df['Label'] = df['Label'].map({'Abnormal': 1, 'Normal': 0})

# Split the dataset into features and labels
X = df[['yaw', 'pitch', 'roll']]
y = df['Label']

# Split the dataset into training and testing sets (80-20 split)
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Best parameters found
best_params = {
    'max_depth': 10,
    'max_features': 'sqrt',
    'min_samples_leaf': 1,
    'min_samples_split': 2,
    'n_estimators': 200
}

# Initialize the RandomForestClassifier with the best parameters
best_rf = RandomForestClassifier(**best_params)

# Train the model
best_rf.fit(X_train, y_train)

# Make predictions
y_pred = best_rf.predict(X_test)

# Evaluate the model
accuracy = accuracy_score(y_test, y_pred)
report = classification_report(y_test, y_pred)

print("Accuracy: ", accuracy)
print("Classification Report:\n", report)

# Save the trained model to a file
model_filename = 'Random forest/random_forest_model.joblib'
joblib.dump(best_rf, model_filename)
print(f"Model saved to {model_filename}")
