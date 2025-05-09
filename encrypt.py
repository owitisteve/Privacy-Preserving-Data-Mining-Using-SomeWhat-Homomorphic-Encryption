import sys
import json
import mysql.connector
import tenseal as ts

# Create encryption context (with saving to file for reuse)
context = ts.context(
    ts.SCHEME_TYPE.CKKS,
    poly_modulus_degree=8192,
    coeff_mod_bit_sizes=[60, 40, 60]
)
context.generate_galois_keys()
context.global_scale = 2**40

# Encrypt data and return as binary
def encrypt_data(context, value):
    if not value:
        return b"N/A"
    ascii_values = [float(ord(c)) for c in value]
    encrypted_vector = ts.ckks_vector(context, ascii_values)
    return encrypted_vector.serialize()
from db_config import get_connection

def save_to_database1(data):
    try:
        connection = get_connection()
        if connection is None:
            print("Failed to establish a database connection.")
            return

        cursor = connection.cursor()

        sql = """
        INSERT INTO counseling (
            family_structure, emotional_issues, parenting_style, conflict_type, crisis,
            mental_condition, stress_level, coping_mechanism, academic, finance,
            skills, physical, sexual, drug_abuse, counselor_comments,
            school, department, year_of_study, counseling_type
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        values = (
            mysql.connector.Binary(data["encrypted_family_structure"]),
            mysql.connector.Binary(data["encrypted_emotional_issues"]),
            mysql.connector.Binary(data["encrypted_parenting_style"]),
            mysql.connector.Binary(data["encrypted_conflict_type"]),
            mysql.connector.Binary(data["encrypted_crisis"]),
            mysql.connector.Binary(data["encrypted_mental_condition"]),
            mysql.connector.Binary(data["encrypted_stress_level"]),
            mysql.connector.Binary(data["encrypted_coping_mechanism"]),
            mysql.connector.Binary(data["encrypted_academic"]),
            mysql.connector.Binary(data["encrypted_finance"]),
            mysql.connector.Binary(data["encrypted_skills"]),
            mysql.connector.Binary(data["encrypted_physical"]),
            mysql.connector.Binary(data["encrypted_sexual"]),
            mysql.connector.Binary(data["encrypted_drug_abuse"]),
            mysql.connector.Binary(data["encrypted_counselor_comments"]),
            data["school"], data["department"], data["year_of_study"], data["counseling_type"]
        )
        
        cursor.execute(sql, values)
        connection.commit()
        print("Data encryption and upload successfull.")
    except mysql.connector.Error as e:
        print(f"Database error: {e}")
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def save_to_database2(data):
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="D_vine@245",
            database="ppddm"
        )
        cursor = connection.cursor()

        sql = """
        INSERT INTO counseling(
            family_structure, emotional_issues, parenting_style, conflict_type, crisis,
            mental_condition, stress_level, coping_mechanism, academic, finance,
            skills, physical, sexual, drug_abuse, counselor_comments,
            school, department, year_of_study, counseling_type
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        values = (
            data.get("family_structure", ""),
            data.get("emotional_issues", ""),
            data.get("parenting_style", ""),
            data.get("conflict_type", ""),
            data.get("crisis", ""),
            data.get("mental_condition", ""),
            data.get("stress_level", ""),
            data.get("coping_mechanism", ""),
            data.get("academic", ""),
            data.get("finance", ""),
            data.get("skills", ""),
            data.get("physical", ""),
            data.get("sexual", ""),
            data.get("drug_abuse", ""),
            data.get("counselor_comments", ""),
            data.get("school", ""),
            data.get("department", ""),
            data.get("year_of_study", ""),
            data.get("counseling_type", "")
        )
        
        cursor.execute(sql, values)
        connection.commit()
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

# Read JSON data from standard input
data = json.loads(sys.stdin.read())

# Encrypt the data fields
encrypted_data = {
    "encrypted_family_structure": encrypt_data(context, data.get("family_structure", "")),
    "encrypted_emotional_issues": encrypt_data(context, data.get("emotional_issues", "")),
    "encrypted_parenting_style": encrypt_data(context, data.get("parenting_style", "")),
    "encrypted_conflict_type": encrypt_data(context, data.get("conflict_type", "")),
    "encrypted_crisis": encrypt_data(context, data.get("crisis", "")),
    "encrypted_mental_condition": encrypt_data(context, data.get("mental_condition", "")),
    "encrypted_stress_level": encrypt_data(context, data.get("stress_level", "")),
    "encrypted_coping_mechanism": encrypt_data(context, data.get("coping_mechanism", "")),
    "encrypted_academic": encrypt_data(context, data.get("academic", "")),
    "encrypted_finance": encrypt_data(context, data.get("finance", "")),
    "encrypted_skills": encrypt_data(context, data.get("skills", "")),
    "encrypted_physical": encrypt_data(context, data.get("physical", "")),
    "encrypted_sexual": encrypt_data(context, data.get("sexual", "")),
    "encrypted_drug_abuse": encrypt_data(context, data.get("drug_abuse", "")),
    "encrypted_counselor_comments": encrypt_data(context, data.get("counselor_comments", "")),
    "school": data.get("school", ""),
    "department": data.get("department", ""),
    "year_of_study": data.get("year_of_study", ""),
    "counseling_type": data.get("counseling_type", "")
}

save_to_database1(encrypted_data)
save_to_database2(data)
