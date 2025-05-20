import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report
import joblib

# Load your cleaned data
df = pd.read_csv("seeded_computers.csv")

# Use your final consistent features
features = ["usage_hours", "temperature", "rpm", "torque", "error_count", "age_months"]
X = df[features]
y = df["needs_maintenance"]

# Train/test split
X_train, X_test, y_train, y_test = train_test_split(X, y, stratify=y, random_state=42)

# Train
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# Evaluate
print(classification_report(y_test, model.predict(X_test)))

# Save
joblib.dump(model, "random_forest_model.pkl")
print("✅ Model saved using cleaned feature names.")
