import sys
import mysql.connector
import tenseal as ts

# Load encryption context from file
try:
    with open("/home/vostive/Desktop/project/context.tenseal", "rb") as f:
        context = ts.context_from(f.read())
except Exception as e:
    print(f"Error loading encryption context: {e}")
    sys.exit(1)

# Decrypt data using the context
def decrypt_data(context, encrypted_data):
    try:
        # Deserialize the encrypted data to get the encrypted vector
        encrypted_vector = ts.ckks_vector_from(context, encrypted_data)
        decrypted_values = encrypted_vector.decrypt()

        # Convert decrypted floats to characters
        decrypted_chars = []
        for v in decrypted_values:
            try:
                # Round the float and convert to int, then to character
                char = chr(int(round(v)))
                decrypted_chars.append(char)
            except (OverflowError, ValueError):
                # Handle cases where conversion fails
                print(f"Warning: Decryption yielded an invalid value: {v}")
                return "N/A"

        # Join the decrypted characters into a string
        return "".join(decrypted_chars).strip()
    except Exception as e:
        print(f"Decryption error: {e}")
        return "N/A"

# Connect to MySQL database
try:
    connection = mysql.connector.connect(
        host="localhost",
        user="root",
        password="D_vine@245",
        database="ppdm"
    )
    cursor = connection.cursor()

    # Retrieve encrypted family structures from the database
    query = "SELECT family_structure FROM counseling"
    cursor.execute(query)
    records = cursor.fetchall()

    # Variable to count nuclear families
    nuclear_count = 0

    # Iterate through the records and decrypt each family structure
    for record in records:
        encrypted_value = record[0]  # Get the binary data from the tuple
        decrypted_family_structure = decrypt_data(context, encrypted_value)

        if decrypted_family_structure.lower() == "nuclear":
            nuclear_count += 1

    print(f"Number of families with structure 'nuclear': {nuclear_count}")

except mysql.connector.Error as e:
    print(f"Database error: {e}")
finally:
    if connection.is_connected():
        cursor.close()
        connection.close()
