import pandas as pd
import joblib
import mysql.connector

# Load the trained model
model = joblib.load("random_forest_model.pkl")

# Load your cleaned seeded computers dataset
df = pd.read_csv("seeded_computers.csv")

# Use the features your model was trained on
features = ["usage_hours", "temperature", "rpm", "torque", "error_count", "age_months"]
df["Predicted Failure"] = model.predict(df[features])

# Connect to the MySQL database
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",  # Use your password if set
    database="secure_lab_db"
)
cursor = conn.cursor()

# Update the maintenance_risk column using label
for _, row in df.iterrows():
    label = row["label"]
    risk = int(row["Predicted Failure"])
    cursor.execute("UPDATE computers SET maintenance_risk = %s WHERE label = %s", (risk, label))

conn.commit()
conn.close()

print("✅ Computers table updated with predicted maintenance risks.")
