import pandas as pd
from sklearn.model_selection import train_test_split, GridSearchCV
from sklearn.ensemble import RandomForestClassifier

# Load the dataset
file_path = 'new_results/train_headpose_SSD.csv'
df = pd.read_csv(file_path)

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

# Define the parameter grid
param_grid = {
    'n_estimators': [100, 200],
    'max_depth': [10, 20, None],
    'min_samples_split': [2, 5, 10],
    'min_samples_leaf': [1, 2, 4],
    'max_features': ['auto', 'sqrt', 'log2']
}

# Initialize the RandomForestClassifier
rf = RandomForestClassifier(random_state=42)

# Initialize GridSearchCV with 5-fold cross-validation
grid_search = GridSearchCV(estimator=rf, param_grid=param_grid, cv=5, n_jobs=-1, verbose=2)

# Fit the model
grid_search.fit(X_train, y_train)

# Get the best parameters
best_params = grid_search.best_params_
print("Best parameters found: ", best_params)
