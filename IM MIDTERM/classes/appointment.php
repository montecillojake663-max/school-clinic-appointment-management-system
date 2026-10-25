<?php

class Appointment
{
    private PDO $conn;

    public function __construct(PDO $database)
    {
        $this->conn = $database;
    }

    // CREATE
    public function addAppointment(
        int $studentId,
        int $userId,
        int $slotId,
        string $reason,
        string $status = "Scheduled"
    ): bool {
        try {
            $sql = "
                INSERT INTO appointments_table
                    (student_id, user_id, slot_id, reason, status)
                VALUES
                    (?, ?, ?, ?, ?)
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([
                $studentId,
                $userId,
                $slotId,
                $reason,
                $status
            ]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to add the appointment.",
                0,
                $error
            );
        }
    }

    // READ
    public function getAppointments(): array
    {
        try {
            $sql = "
                SELECT
                    appointments.appointment_id,
                    appointments.student_id,
                    appointments.user_id,
                    appointments.slot_id,
                    appointments.reason,
                    appointments.status,

                    students.first_name,
                    students.last_name,
                    students.grade_level,
                    students.section,

                    users.full_name AS staff_name,

                    slots.slot_date,
                    slots.start_time,
                    slots.end_time

                FROM appointments_table AS appointments

                INNER JOIN students_table AS students
                    ON appointments.student_id = students.student_id

                INNER JOIN users_table AS users
                    ON appointments.user_id = users.user_id

                INNER JOIN time_slots_table AS slots
                    ON appointments.slot_id = slots.slot_id

                ORDER BY
                    slots.slot_date DESC,
                    slots.start_time DESC
            ";

            $statement = $this->conn->prepare($sql);
            $statement->execute();

            return $statement->fetchAll();
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to retrieve appointments.",
                0,
                $error
            );
        }
    }

    // UPDATE
    public function updateAppointment(
        int $appointmentId,
        int $studentId,
        int $userId,
        int $slotId,
        string $reason,
        string $status
    ): bool {
        try {
            $sql = "
                UPDATE appointments_table
                SET
                    student_id = ?,
                    user_id = ?,
                    slot_id = ?,
                    reason = ?,
                    status = ?
                WHERE appointment_id = ?
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([
                $studentId,
                $userId,
                $slotId,
                $reason,
                $status,
                $appointmentId
            ]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to update the appointment.",
                0,
                $error
            );
        }
    }

    // DELETE
    public function deleteAppointment(int $appointmentId): bool
    {
        try {
            $sql = "
                DELETE FROM appointments_table
                WHERE appointment_id = ?
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([
                $appointmentId
            ]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to delete the appointment.",
                0,
                $error
            );
        }
    }

    // SEARCH
    public function searchAppointments(string $keyword): array
    {
        try {
            $searchKeyword = "%" . $keyword . "%";

            $sql = "
                SELECT
                    appointments.appointment_id,
                    appointments.student_id,
                    appointments.user_id,
                    appointments.slot_id,
                    appointments.reason,
                    appointments.status,

                    students.first_name,
                    students.last_name,
                    students.grade_level,
                    students.section,

                    users.full_name AS staff_name,

                    slots.slot_date,
                    slots.start_time,
                    slots.end_time

                FROM appointments_table AS appointments

                INNER JOIN students_table AS students
                    ON appointments.student_id = students.student_id

                INNER JOIN users_table AS users
                    ON appointments.user_id = users.user_id

                INNER JOIN time_slots_table AS slots
                    ON appointments.slot_id = slots.slot_id

                WHERE
                    CAST(appointments.appointment_id AS CHAR) LIKE ?
                    OR students.first_name LIKE ?
                    OR students.last_name LIKE ?
                    OR students.grade_level LIKE ?
                    OR students.section LIKE ?
                    OR appointments.reason LIKE ?
                    OR appointments.status LIKE ?
                    OR CAST(slots.slot_date AS CHAR) LIKE ?

                ORDER BY
                    slots.slot_date DESC,
                    slots.start_time DESC
            ";

            $statement = $this->conn->prepare($sql);

            $statement->execute([
                $searchKeyword,
                $searchKeyword,
                $searchKeyword,
                $searchKeyword,
                $searchKeyword,
                $searchKeyword,
                $searchKeyword,
                $searchKeyword
            ]);

            return $statement->fetchAll();
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to search appointments.",
                0,
                $error
            );
        }
    }
}