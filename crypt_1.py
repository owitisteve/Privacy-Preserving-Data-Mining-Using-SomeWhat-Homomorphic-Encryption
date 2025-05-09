import sys
import json
import base64
import mysql.connector
import tenseal as ts

import tenseal as ts

# Create encryption context
def create_encryption_context():
    context = ts.context(
        ts.SCHEME_TYPE.CKKS,
        poly_modulus_degree=8192,
        coeff_mod_bit_sizes=[60, 40, 60]
    )
    context.generate_galois_keys()
    context.global_scale = 1099511627776
    return context

# Save the encryption context
def save_encryption_context(context, filename):
    with open(filename, "wb") as f:
        f.write(context.serialize(save_galois_keys=True))
    print(f"Encryption context saved to {filename}")

# Create and save context
context = create_encryption_context()
save_encryption_context(context, "/home/vostive/Desktop/project/encryption_context.ckks")


# Encrypt data and return as Base64
def encrypt_data(context, text):
    # Convert the entire string into a list of ASCII values
    ascii_values = [float(ord(c)) for c in text]
    # Encrypt the vector of ASCII values
    encrypted_vector = ts.ckks_vector(context, ascii_values)
    # Serialize and encode as Base64
    return base64.b64encode(encrypted_vector.serialize()).decode()


# Save encrypted data to MySQL
def save_to_database(data):
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="D_vine@245",
            database="ppdm"
        )
        cursor = connection.cursor()

        # SQL query to insert encrypted data
        sql = """
        INSERT INTO counseling (
            family_structure, emotional_issues, parenting_style, conflict_type, crisis,
            mental_condition, stress_level, coping_mechanism, academic, finance,
            skills, physical, sexual, drug_abuse, counselor_comments,
            school, department, year_of_study, counseling_type
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        values = (
            data["encrypted_family_structure"], data["encrypted_emotional_issues"], data["encrypted_parenting_style"],
            data["encrypted_conflict_type"], data["encrypted_crisis"], data["encrypted_mental_condition"],
            data["encrypted_stress_level"], data["encrypted_coping_mechanism"], data["encrypted_academic"],
            data["encrypted_finance"], data["encrypted_skills"], data["encrypted_physical"],
            data["encrypted_sexual"], data["encrypted_drug_abuse"], data["encrypted_counselor_comments"],
            data["school"], data["department"], data["year_of_study"], data["counseling_type"]
        )
        cursor.execute(sql, values)
        connection.commit()
        print("Data saved to database successfully.")
    except mysql.connector.Error as e:
        print(f"Database error: {e}")
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

# Read JSON data from standard input
data = json.loads(sys.stdin.read())

# Encrypt data
context = create_encryption_context()
encrypted_data = {
    "encrypted_family_structure": encrypt_data(context, data["family_structure"]),
    "encrypted_emotional_issues": encrypt_data(context, data["emotional_issues"]),
    "encrypted_parenting_style": encrypt_data(context, data["parenting_style"]),
    "encrypted_conflict_type": encrypt_data(context, data["conflict_type"]),
    "encrypted_crisis": encrypt_data(context, data["crisis"]),
    "encrypted_mental_condition": encrypt_data(context, data["mental_condition"]),
    "encrypted_stress_level": encrypt_data(context, data["stress_level"]),
    "encrypted_coping_mechanism": encrypt_data(context, data["coping_mechanism"]),
    "encrypted_academic": encrypt_data(context, data["academic"]),
    "encrypted_finance": encrypt_data(context, data["finance"]),
    "encrypted_skills": encrypt_data(context, data["skills"]),
    "encrypted_physical": encrypt_data(context, data["physical"]),
    "encrypted_sexual": encrypt_data(context, data["sexual"]),
    "encrypted_drug_abuse": encrypt_data(context, data["drug_abuse"]),
    "encrypted_counselor_comments": encrypt_data(context, data["counselor_comments"]),
    "school": data["school"],
    "department": data["department"],
    "year_of_study": data["year_of_study"],
    "counseling_type": data["counseling_type"]
}

# Store encrypted data in the database
save_to_database(encrypted_data)
