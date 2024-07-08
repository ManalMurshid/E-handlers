import pandas as pd
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split
from sklearn.tree import DecisionTreeClassifier
from sklearn.metrics import accuracy_score, f1_score, precision_score, confusion_matrix
import joblib

# Load the dataset
file_path = 'new_results/train_headpose_SSD.csv'
df = pd.read_csv(file_path)

# Display the first few rows of the dataset
print(df.head())

# Remove unnecessary columns
df = df.drop(columns=['image_name', 'face_id', 'face_detected'])

# Drop rows with missing values
df = df.dropna()

# Encode labels as numeric values
label_encoder = LabelEncoder()
df['Label'] = label_encoder.fit_transform(df['Label'])

# Split the dataset into features and labels
X = df.drop(columns=['Label'])
y = df['Label']

# Split the dataset into training and testing sets
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Train the Decision Tree classifier
clf = DecisionTreeClassifier(random_state=42)
clf.fit(X_train, y_train)

# Predict on the test set
y_pred = clf.predict(X_test)

# Calculate accuracy, F1 score, precision, and confusion matrix
accuracy = accuracy_score(y_test, y_pred)
f1 = f1_score(y_test, y_pred)
precision = precision_score(y_test, y_pred)
conf_matrix = confusion_matrix(y_test, y_pred)

# Display the metrics
print("Accuracy:", accuracy)
print("F1 Score:", f1)
print("Precision:", precision)
print("Confusion Matrix:\n", conf_matrix)

# Save the trained model
joblib_file = "decision_tree_model.pkl"
joblib.dump(clf, joblib_file)
print(f"Model saved as {joblib_file}")
