<?php

class VisitRecord
{
    private PDO $conn;

    public function __construct(PDO $database)
    {
        $this->conn = $database;
    }

    // Show all visit records
    public function getVisitRecords(): array
    {
        try {
            $sql = "
                SELECT
                    visits.visit_id,
                    visits.appointment_id,
                    visits.diagnosis,
                    visits.treatment,
                    visits.notes,

                    appointments.reason,
                    appointments.status,

                    students.first_name,
                    students.last_name,

                    slots.slot_date,
                    slots.start_time

                FROM visit_records_table AS visits

                INNER JOIN appointments_table AS appointments
                    ON visits.appointment_id =
                       appointments.appointment_id

                INNER JOIN students_table AS students
                    ON appointments.student_id =
                       students.student_id

                INNER JOIN time_slots_table AS slots
                    ON appointments.slot_id =
                       slots.slot_id

                ORDER BY
                    slots.slot_date DESC,
                    slots.start_time DESC
            ";

            $statement = $this->conn->prepare($sql);
            $statement->execute();

            return $statement->fetchAll();
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to retrieve visit records.",
                0,
                $error
            );
        }
    }

    // Add a visit record
    public function addVisitRecord(
        int $appointmentId,
        string $diagnosis,
        string $treatment,
        string $notes
    ): bool {
        try {
            $sql = "
                INSERT INTO visit_records_table
                    (
                        appointment_id,
                        diagnosis,
                        treatment,
                        notes
                    )
                VALUES (?, ?, ?, ?)
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([
                $appointmentId,
                $diagnosis,
                $treatment,
                $notes
            ]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to add the visit record.",
                0,
                $error
            );
        }
    }

    // Update a visit record
    public function updateVisitRecord(
        int $visitId,
        string $diagnosis,
        string $treatment,
        string $notes
    ): bool {
        try {
            $sql = "
                UPDATE visit_records_table
                SET
                    diagnosis = ?,
                    treatment = ?,
                    notes = ?
                WHERE visit_id = ?
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([
                $diagnosis,
                $treatment,
                $notes,
                $visitId
            ]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to update the visit record.",
                0,
                $error
            );
        }
    }

    // Delete a visit record
    public function deleteVisitRecord(int $visitId): bool
    {
        try {
            $sql = "
                DELETE FROM visit_records_table
                WHERE visit_id = ?
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([$visitId]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to delete the visit record.",
                0,
                $error
            );
        }
    }
}