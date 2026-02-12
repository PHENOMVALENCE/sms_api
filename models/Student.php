<?php
/**
 * Student Model
 *
 * Encapsulates all persistence logic for the `students` table and provides
 * reusable validation helpers that can be used by API endpoints before
 * attempting database operations.
 */

class Student
{
    /**
     * @var PDO
     */
    private $conn;

    /**
     * @var string
     */
    private $table_name = 'students';

    // Student properties (mapped to table columns)
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $date_of_birth;
    public $gender;
    public $address;
    public $enrollment_date;
    public $created_at;
    public $updated_at;

    /**
     * Allowed gender values for validation.
     */
    private const ALLOWED_GENDERS = ['Male', 'Female', 'Other'];

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Validate and sanitize input data for create/update operations.
     *
     * @param array $input
     * @param bool  $isUpdate When true, applies update-specific rules.
     *
     * @return array ['valid' => bool, 'errors' => array, 'data' => array]
     */
    public function validate(array $input, bool $isUpdate = false): array
    {
        $errors = [];
        $data   = [];

        // ID for update
        if ($isUpdate) {
            if (empty($input['id'])) {
                $errors['id'] = 'ID is required for update.';
            } elseif (!filter_var($input['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                $errors['id'] = 'ID must be a positive integer.';
            } else {
                $data['id'] = (int) $input['id'];
            }
        }

        // Required fields
        $required = ['first_name', 'last_name', 'email'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            } else {
                $value = trim((string) $input[$field]);
                if (strlen($value) > 100) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is too long.';
                } else {
                    $data[$field] = $value;
                }
            }
        }

        // Email format
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email format is invalid.';
        }

        // Optional fields
        $optionalStringFields = [
            'phone'   => 20,
            'gender'  => 10,
        ];

        foreach ($optionalStringFields as $field => $maxLen) {
            if (isset($input[$field]) && $input[$field] !== null && $input[$field] !== '') {
                $value = trim((string) $input[$field]);
                if (strlen($value) > $maxLen) {
                    $errors[$field] = ucfirst($field) . " is too long (max {$maxLen} characters).";
                } else {
                    $data[$field] = $value;
                }
            } else {
                $data[$field] = null;
            }
        }

        // Address (TEXT, but still enforce a reasonable limit)
        if (isset($input['address']) && $input['address'] !== null && $input['address'] !== '') {
            $address = trim((string) $input['address']);
            if (strlen($address) > 1000) {
                $errors['address'] = 'Address is too long.';
            } else {
                $data['address'] = $address;
            }
        } else {
            $data['address'] = null;
        }

        // Date fields
        foreach (['date_of_birth', 'enrollment_date'] as $dateField) {
            if (isset($input[$dateField]) && $input[$dateField] !== null && $input[$dateField] !== '') {
                $date = (string) $input[$dateField];
                $dt   = \DateTime::createFromFormat('Y-m-d', $date);
                $errorsInDate = \DateTime::getLastErrors();
                if (!$dt || $errorsInDate['warning_count'] > 0 || $errorsInDate['error_count'] > 0) {
                    $errors[$dateField] = ucfirst(str_replace('_', ' ', $dateField)) . ' must be in YYYY-MM-DD format.';
                } else {
                    $data[$dateField] = $dt->format('Y-m-d');
                }
            } elseif ($dateField === 'enrollment_date' && !$isUpdate) {
                // For create, default enrollment_date to today if not provided.
                $data[$dateField] = (new \DateTime())->format('Y-m-d');
            } else {
                $data[$dateField] = null;
            }
        }

        // Gender value validation
        if (!empty($data['gender']) && !in_array($data['gender'], self::ALLOWED_GENDERS, true)) {
            $errors['gender'] = 'Gender must be one of: ' . implode(', ', self::ALLOWED_GENDERS) . '.';
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
            'data'   => $data,
        ];
    }

    /**
     * Populate the model properties from sanitized data.
     *
     * @param array $data
     */
    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Create a new student record.
     */
    public function create(): bool
    {
        $query = "INSERT INTO {$this->table_name}
            (first_name, last_name, email, phone, date_of_birth, gender, address, enrollment_date, created_at)
            VALUES (:first_name, :last_name, :email, :phone, :date_of_birth, :gender, :address, :enrollment_date, NOW())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':first_name', $this->first_name);
        $stmt->bindValue(':last_name', $this->last_name);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':phone', $this->phone);
        $stmt->bindValue(':date_of_birth', $this->date_of_birth);
        $stmt->bindValue(':gender', $this->gender);
        $stmt->bindValue(':address', $this->address);
        $stmt->bindValue(':enrollment_date', $this->enrollment_date);

        if ($stmt->execute()) {
            $this->id = (int) $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Read all students (optionally with simple pagination).
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return PDOStatement
     */
    public function readAll(?int $limit = null, ?int $offset = null): PDOStatement
    {
        $sql = "SELECT * FROM {$this->table_name} ORDER BY created_at DESC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($sql);

        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt;
    }

    /**
     * Read a single student by ID.
     */
    public function readOne(): bool
    {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->fill($row);
            return true;
        }

        return false;
    }

    /**
     * Update an existing student.
     */
    public function update(): bool
    {
        $query = "UPDATE {$this->table_name}
            SET first_name = :first_name,
                last_name = :last_name,
                email = :email,
                phone = :phone,
                date_of_birth = :date_of_birth,
                gender = :gender,
                address = :address,
                enrollment_date = :enrollment_date,
                updated_at = NOW()
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':first_name', $this->first_name);
        $stmt->bindValue(':last_name', $this->last_name);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':phone', $this->phone);
        $stmt->bindValue(':date_of_birth', $this->date_of_birth);
        $stmt->bindValue(':gender', $this->gender);
        $stmt->bindValue(':address', $this->address);
        $stmt->bindValue(':enrollment_date', $this->enrollment_date);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a student by ID.
     */
    public function delete(): bool
    {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Search students by keyword (name or email).
     *
     * @param string   $keywords
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return PDOStatement
     */
    public function search(string $keywords, ?int $limit = null, ?int $offset = null): PDOStatement
    {
        $query = "SELECT * FROM {$this->table_name}
            WHERE first_name LIKE :keywords
               OR last_name LIKE :keywords
               OR email LIKE :keywords
            ORDER BY created_at DESC";

        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);

        $like = '%' . $keywords . '%';
        $stmt->bindValue(':keywords', $like, PDO::PARAM_STR);

        if ($limit !== null && $offset !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt;
    }

    /**
     * Check if an email already exists (optionally excluding a given ID).
     *
     * @param string      $email
     * @param int|null    $excludeId
     *
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM {$this->table_name} WHERE email = :email";

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email);

        if ($excludeId !== null) {
            $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}

