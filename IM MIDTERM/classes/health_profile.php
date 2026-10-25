<?php

class HealthProfile
{
    private PDO $conn;

    public function __construct(PDO $database)
    {
        $this->conn = $database;
    }

    // Get all health profiles
    public function getHealthProfiles(): array
    {
        try {
            $sql = "
                SELECT
                    profiles.profile_id,
                    profiles.student_id,
                    profiles.medical_history,
                    profiles.allergies,
                    profiles.emergency_contact,
                    students.first_name,
                    students.last_name,
                    students.grade_level,
                    students.section

                FROM health_profiles_table AS profiles

                INNER JOIN students_table AS students
                    ON profiles.student_id = students.student_id

                ORDER BY students.last_name, students.first_name
            ";

            $statement = $this->conn->prepare($sql);
            $statement->execute();

            return $statement->fetchAll();
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to retrieve health profiles.",
                0,
                $error
            );
        }
    }

    // Add a health profile
    public function addHealthProfile(
        int $studentId,
        string $medicalHistory,
        string $allergies,
        string $emergencyContact
    ): bool {
        try {
            $sql = "
                INSERT INTO health_profiles_table
                    (
                        student_id,
                        medical_history,
                        allergies,
                        emergency_contact
                    )
                VALUES (?, ?, ?, ?)
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([
                $studentId,
                $medicalHistory,
                $allergies,
                $emergencyContact
            ]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to add the health profile.",
                0,
                $error
            );
        }
    }

    // Update a health profile
    public function updateHealthProfile(
        int $profileId,
        string $medicalHistory,
        string $allergies,
        string $emergencyContact
    ): bool {
        try {
            $sql = "
                UPDATE health_profiles_table
                SET
                    medical_history = ?,
                    allergies = ?,
                    emergency_contact = ?
                WHERE profile_id = ?
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([
                $medicalHistory,
                $allergies,
                $emergencyContact,
                $profileId
            ]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to update the health profile.",
                0,
                $error
            );
        }
    }

    // Delete a health profile
    public function deleteHealthProfile(int $profileId): bool
    {
        try {
            $sql = "
                DELETE FROM health_profiles_table
                WHERE profile_id = ?
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([$profileId]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to delete the health profile.",
                0,
                $error
            );
        }
    }
}