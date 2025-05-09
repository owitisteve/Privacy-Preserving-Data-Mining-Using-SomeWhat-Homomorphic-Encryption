import mysql.connector

def get_connection():
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="D_vine@245",
            database="ppdm"
        )
        return connection
    except mysql.connector.Error as e:
        print(f"Database connection error: {e}")
        return None
