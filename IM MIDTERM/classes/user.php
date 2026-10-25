<?php

class User
{
    private PDO $conn;

    public function __construct(PDO $database)
    {
        $this->conn = $database;
    }

    // Find one user using their username
    public function findByUsername(string $username): array|false
    {
        $sql = "
            SELECT *
            FROM users_table
            WHERE username = ?
            LIMIT 1
        ";

        $statement = $this->conn->prepare($sql);
        $statement->execute([$username]);

        return $statement->fetch();
    }

    // Create a new system user
    public function createUser(
        string $fullName,
        string $role,
        string $username,
        string $password
    ): bool {
        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "
            INSERT INTO users_table
                (full_name, role, username, password)
            VALUES
                (?, ?, ?, ?)
        ";

        $statement = $this->conn->prepare($sql);

        return $statement->execute([
            $fullName,
            $role,
            $username,
            $hashedPassword
        ]);
    }
}