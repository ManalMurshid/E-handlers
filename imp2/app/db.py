import mysql.connector
from mysql.connector import Error

class Database:
    def __init__(self, host, user, password, database):
        self.host = host
        self.user = user
        self.password = password
        self.database = database

    def connect(self):
        """Establish a connection to the MySQL database."""
        try:
            connection = mysql.connector.connect(
                host=self.host,
                user=self.user,
                password=self.password,
                database=self.database
            )
            if connection.is_connected():
                return connection
        except Error as e:
            print(f"Error connecting to MySQL database: {e}")
            raise

    def close(self, connection):
        """Closes the database connection."""
        if connection.is_connected():
            connection.close()

    def save_result_to_db(self, result_type, result_value, metadata):
        """
        Inserts the result into the `report_summary` table.

        Parameters:
            result_type (str): The column name for the result (e.g., 'headpose_model_result', 'audio_model_result').
            result_value (str): The value to be inserted into the result column.
            metadata (dict): Dictionary containing common fields:
                             {
                                 "exam_id": int,
                                 "user_id": int,
                                 "timestamp": str,
                                 "email": str
                             }

        Returns:
            dict: A status message indicating success or failure.
        """
        # Establish a database connection
        db_connection = self.connect()
        if db_connection is None:
            return {"status": "error", "message": "Database connection failed"}

        cursor = db_connection.cursor()

        # Dynamically construct the query based on the result type
        query = f"""
        INSERT INTO report_summary ({result_type}, exam_id, user_id, timestamp, email)
        VALUES (%s, %s, %s, %s, %s)
        """
        try:
            # Execute the query with provided values
            cursor.execute(query, (result_value, metadata["exam_id"], metadata["user_id"], metadata["timestamp"], metadata["email"]))
            db_connection.commit()
            return {"status": "success", "message": f"{result_type} saved to database"}
        except Error as e:
            print(f"Database Error: {e}")
            db_connection.rollback()
            return {"status": "error", "message": f"Database error: {e}"}
        finally:
            cursor.close()
            self.close(db_connection)
