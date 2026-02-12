<?php
/**
 * PDO-based database connection for the Student Management API.
 *
 * This class reads its configuration from config/config.php and exposes
 * a single method, getConnection(), returning a configured PDO instance.
 */

class Database
{
    /**
     * @var PDO|null
     */
    private $conn = null;

    /**
     * Get (or create) the PDO connection.
     *
     * @return PDO
     * @throws PDOException on connection error
     */
    public function getConnection(): PDO
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        $configPath = __DIR__ . '/config.php';
        if (!file_exists($configPath)) {
            throw new RuntimeException('Database configuration file not found: ' . $configPath);
        }

        $config = require $configPath;
        $db     = $config['db'] ?? [];

        $host    = $db['host'] ?? 'localhost';
        $name    = $db['name'] ?? 'sms_api';
        $user    = $db['user'] ?? 'root';
        $pass    = $db['pass'] ?? '';
        $charset = $db['charset'] ?? 'utf8mb4';

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $name, $charset);

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->conn = new PDO($dsn, $user, $pass, $options);

        return $this->conn;
    }
}

