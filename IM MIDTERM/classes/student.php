<?php

class Student
{
    private PDO $conn;

    public function __construct(PDO $database)
    {
        $this->conn = $database;
    }

    // Get all students
    public function getAllStudents(): array
    {
        $sql = "
            SELECT *
            FROM students_table
            ORDER BY student_id DESC
        ";

        $statement = $this->conn->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    // Count all students
    public function getStudentCount(): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM students_table
        ";

        $statement = $this->conn->prepare($sql);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    // Search students
    public function searchStudents(string $keyword): array
    {
        $search = "%" . $keyword . "%";

        $sql = "
            SELECT *
            FROM students_table
            WHERE
                CAST(student_id AS CHAR) LIKE ?
                OR first_name LIKE ?
                OR last_name LIKE ?
                OR grade_level LIKE ?
                OR section LIKE ?
                OR contact_number LIKE ?
            ORDER BY student_id DESC
        ";

        $statement = $this->conn->prepare($sql);

        $statement->execute([
            $search,
            $search,
            $search,
            $search,
            $search,
            $search
        ]);

        return $statement->fetchAll();
    }

    // Add a student
    public function addStudent(
        string $firstName,
        string $lastName,
        string $gradeLevel,
        string $section,
        string $contactNumber
    ): bool {
        $sql = "
            INSERT INTO students_table
                (
                    first_name,
                    last_name,
                    grade_level,
                    section,
                    contact_number
                )
            VALUES (?, ?, ?, ?, ?)
        ";

        $statement = $this->conn->prepare($sql);

        return $statement->execute([
            $firstName,
            $lastName,
            $gradeLevel,
            $section,
            $contactNumber
        ]);
    }

    // Update a student
    public function updateStudent(
        int $studentId,
        string $firstName,
        string $lastName,
        string $gradeLevel,
        string $section,
        string $contactNumber
    ): bool {
        $sql = "
            UPDATE students_table
            SET
                first_name = ?,
                last_name = ?,
                grade_level = ?,
                section = ?,
                contact_number = ?
            WHERE student_id = ?
        ";

        $statement = $this->conn->prepare($sql);

        return $statement->execute([
            $firstName,
            $lastName,
            $gradeLevel,
            $section,
            $contactNumber,
            $studentId
        ]);
    }

    // Delete a student
    public function deleteStudent(int $studentId): bool
    {
        $sql = "
            DELETE FROM students_table
            WHERE student_id = ?
        ";

        $statement = $this->conn->prepare($sql);

        return $statement->execute([$studentId]);
    }
}