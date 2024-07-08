import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.svm import SVC
from sklearn.metrics import classification_report, accuracy_score

# Load the dataset
file_path = 'new_results/train_headpose_SSD.csv'
df = pd.read_csv(file_path)

# Drop rows with NaN values
df.dropna(inplace=True)

# Check if there are enough samples
if len(df) > 1:
    # Extract features and labels
    X = df[['yaw', 'pitch', 'roll']]
    y = df['Label']

    # Split the data into training and testing sets
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    # Train an SVM classifier
    svm_model = SVC(kernel='linear', C=1, random_state=42)
    svm_model.fit(X_train, y_train)

    # Predict on the test set
    y_pred = svm_model.predict(X_test)

    # Evaluate the model
    accuracy = accuracy_score(y_test, y_pred)
    report = classification_report(y_test, y_pred)

    print(f'Accuracy: {accuracy}')
    print('Classification Report:')
    print(report)
else:
    print("Not enough samples to split the dataset after dropping rows with NaN values.")

