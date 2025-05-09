import sys
import json
import tenseal as ts
import base64

# Create encryption context
def create_encryption_context():
    context = ts.context(ts.SCHEME_TYPE.CKKS, poly_modulus_degree=8192, coeff_mod_bit_sizes=[60, 40, 60])
    context.generate_galois_keys()
    context.global_scale = 1099511627776
    return context

# Encrypt data and return as Base64 (to safely pass to PHP)
def encrypt_data(context, name, age, year_of_study, gender):
    encrypted_name = ts.ckks_vector(context, [ord(c) for c in name])
    encrypted_age = ts.ckks_vector(context, [age])
    encrypted_year_of_study = ts.ckks_vector(context, [year_of_study])
    encrypted_gender = ts.ckks_vector(context, [ord(c) for c in gender])

    # Serialize and encode in Base64 (makes it safe for PHP to handle)
    return {
        "encrypted_name": base64.b64encode(encrypted_name.serialize()).decode(),
        "encrypted_age": base64.b64encode(encrypted_age.serialize()).decode(),
        "encrypted_year_of_study": base64.b64encode(encrypted_year_of_study.serialize()).decode(),
        "encrypted_gender": base64.b64encode(encrypted_gender.serialize()).decode()
    }

# Read JSON input from PHP
input_data = sys.stdin.read().strip()
data = json.loads(input_data)

# Encrypt data
context = create_encryption_context()
encrypted_data = encrypt_data(context, data["name"], data["age"], data["year_of_study"], data["gender"])

# Output encrypted data as JSON (PHP will use this)
print(json.dumps(encrypted_data))
