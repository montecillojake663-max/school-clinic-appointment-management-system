<?php

class TimeSlot
{
    private PDO $conn;

    public function __construct(PDO $database)
    {
        $this->conn = $database;
    }

    public function getTimeSlots(): array
    {
        try {
            $sql = "
                SELECT *
                FROM time_slots_table
                ORDER BY slot_date DESC, start_time ASC
            ";

            $statement = $this->conn->prepare($sql);
            $statement->execute();

            return $statement->fetchAll();
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to retrieve time slots.",
                0,
                $error
            );
        }
    }

    public function addTimeSlot(
        string $slotDate,
        string $startTime,
        string $endTime
    ): bool {
        try {
            $sql = "
                INSERT INTO time_slots_table
                    (slot_date, start_time, end_time, is_available)
                VALUES
                    (?, ?, ?, 1)
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([
                $slotDate,
                $startTime,
                $endTime
            ]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to add the time slot.",
                0,
                $error
            );
        }
    }

    public function updateTimeSlot(
        int $slotId,
        string $slotDate,
        string $startTime,
        string $endTime,
        int $isAvailable
    ): bool {
        try {
            $sql = "
                UPDATE time_slots_table
                SET
                    slot_date = ?,
                    start_time = ?,
                    end_time = ?,
                    is_available = ?
                WHERE slot_id = ?
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([
                $slotDate,
                $startTime,
                $endTime,
                $isAvailable,
                $slotId
            ]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to update the time slot.",
                0,
                $error
            );
        }
    }

    public function deleteTimeSlot(int $slotId): bool
    {
        try {
            $sql = "
                DELETE FROM time_slots_table
                WHERE slot_id = ?
            ";

            $statement = $this->conn->prepare($sql);

            return $statement->execute([$slotId]);
        } catch (PDOException $error) {
            throw new RuntimeException(
                "Unable to delete the time slot.",
                0,
                $error
            );
        }
    }
}