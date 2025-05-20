import pandas as pd

# Load the AI4I dataset
input_path = "ai4i2020.csv"
df = pd.read_csv(input_path)

# Take only the first 50 rows to simulate your 50 lab PCs
df = df.head(50).copy()

# Rename and clean columns to match your system's style
df_cleaned = pd.DataFrame()
df_cleaned["usage_hours"] = df["Tool wear [min]"] // 60  # Convert minutes to hours

df_cleaned["temperature"] = df["Air temperature [K]"]
df_cleaned["rpm"] = df["Rotational speed [rpm]"]
df_cleaned["torque"] = df["Torque [Nm]"]
df_cleaned["error_count"] = (df["Torque [Nm]"] > 50).astype(int)  # Simulated error condition

df_cleaned["age_months"] = 12 + (df.index % 5) * 6  # Simulate age in months

# Target column (simulated maintenance need)
df_cleaned["needs_maintenance"] = df["Machine failure"]

# Assign PC labels
df_cleaned["label"] = [f"PC-{str(i+1).zfill(3)}" for i in range(50)]

# Reorder columns
columns = ["label", "usage_hours", "temperature", "rpm", "torque", "error_count", "age_months", "needs_maintenance"]
df_cleaned = df_cleaned[columns]

# Save cleaned dataset
output_path = "seeded_computers.csv"
df_cleaned.to_csv(output_path, index=False)

print("✅ Cleaned dataset saved as seeded_computers.csv")
