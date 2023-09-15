<?php

class DB {
    private $mysqli;
    private $host;
    private $username;
    private $password;
    private $database;

    /**
     * Constructs the class and connects to the MySQL database with mysqli driver.
     *
     * @param string $host     The database host (e.g., "localhost")
     * @param string $username The database username
     * @param string $password The database password
     * @param string $database The name of the database
     */
    public function __construct($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->connect();
    }

    /**
     * Establishes a database connection.
     */
    private function connect() {
        try {
            $this->mysqli = new mysqli($this->host, $this->username, $this->password, $this->database);
            if ($this->mysqli->connect_error) {
                throw new Exception("Failed to connect to MySQL: " . $this->mysqli->connect_error);
            }
            // Set character set if needed
            $this->mysqli->set_charset("utf8");
        } catch (Exception $e) {
            echo "Service unavailable\n<br>Failed to connect to MySQL: " . $e->getMessage();
            exit;
        }
    }

    /**
     * Prepares and executes a parameterized query and returns the result.
     */
    public function query($query, $params = []) {
        $stmt = $this->mysqli->prepare($query);

        if ($stmt) {
            if (!empty($params)) {
                // Binding parameters
                $types = str_repeat('s', count($params)); // Assumes all parameters are strings
                $stmt->bind_param($types, ...$params);
            }

            // Executing the prepared statement
            $stmt->execute();

            // Getting result
            $result = $stmt->get_result();

            // Fetching data into an array
            $data = $result->fetch_all(MYSQLI_ASSOC);

            // Closing the statement
            $stmt->close();

            return $data;
        } else {
            // Handling query preparation error
            throw new Exception("Query preparation error: " . $this->mysqli->error);
        }
    }

    /**
     * Executes SQL UPDATE / INSERT / DELETE queries and returns the affected rows count.
     */
    public function update($query, $params = []) {
        $stmt = $this->mysqli->prepare($query);

        if ($stmt) {
            if (!empty($params)) {
                // Binding parameters
                $types = str_repeat('s', count($params)); // Assumes all parameters are strings; adjust as needed
                $stmt->bind_param($types, ...$params);
            }

            // Executing the prepared statement
            $stmt->execute();

            // Getting the number of affected rows
            $affected_rows = $stmt->affected_rows;

            // Closing the statement
            $stmt->close();

            return $affected_rows;
        } else {
            // Handling query preparation error
            throw new Exception("Query preparation error: " . $this->mysqli->error);
        }
    }

    /**
     * Close the database connection when the object is destroyed.
     */
    public function __destruct() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }
}
