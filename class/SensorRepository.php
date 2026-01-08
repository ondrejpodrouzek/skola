<?php
/**
 * Třída SensorRepository - práce se senzory v databázi
 */
class SensorRepository
{
    /** @var Database Instance databáze */
    private Database $db;

    /**
     * Konstruktor repository
     * 
     * @param Database $db Instance databázového připojení
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Načte všechny senzory z databáze
     * 
     * @return array Pole objektů Sensor
     */
    public function findAll(): array
    {
        $sensors = [];
        $query = "SELECT id, host_id, item_id, name, location FROM sensors ORDER BY location";
        $result = $this->db->query($query);

        while ($row = $result->fetch_object()) {
            $sensors[] = new Sensor(
                $row->host_id,
                $row->item_id,
                $row->name,
                $row->location,
                (int) $row->id
            );
        }
        return $sensors;
    }

    /**
     * Najde senzor podle ID
     * 
     * @param int $id ID senzoru
     * @return Sensor|null Senzor nebo null
     */
    public function findById(int $id): ?Sensor
    {
        $query = "SELECT id, host_id, item_id, name, location FROM sensors WHERE id = " . (int) $id;
        $result = $this->db->query($query);

        if ($row = $result->fetch_object()) {
            return new Sensor(
                $row->host_id,
                $row->item_id,
                $row->name,
                $row->location,
                (int) $row->id
            );
        }
        return null;
    }

    /**
     * Uloží záznam o teplotě do historie
     * 
     * @param Sensor $sensor Senzor s naměřenou teplotou
     * @return bool Úspěch operace
     */
    public function logTemperature(Sensor $sensor): bool
    {
        if ($sensor->getId() === null || $sensor->getTemperature() === null) {
            return false;
        }

        $query = sprintf(
            "INSERT INTO temperature_logs (sensor_id, temperature, measured_at) VALUES (%d, %.2f, NOW())",
            $sensor->getId(),
            $sensor->getTemperature()
        );

        return $this->db->query($query) !== false;
    }

    /**
     * Načte historii teplot pro senzor
     * 
     * @param int $sensorId ID senzoru
     * @param int $limit Maximální počet záznamů
     * @return array Pole záznamů
     */
    public function getTemperatureHistory(int $sensorId, int $limit = 100): array
    {
        $history = [];
        $query = sprintf(
            "SELECT temperature, measured_at FROM temperature_logs WHERE sensor_id = %d ORDER BY measured_at DESC LIMIT %d",
            $sensorId,
            $limit
        );
        $result = $this->db->query($query);

        while ($row = $result->fetch_object()) {
            $history[] = [
                'temperature' => (float) $row->temperature,
                'measured_at' => $row->measured_at
            ];
        }
        return $history;
    }

    /**
     * Vrátí průměrnou teplotu za poslední hodinu
     * 
     * @param int $sensorId ID senzoru
     * @return float|null Průměrná teplota
     */
    public function getAverageTemperature(int $sensorId): ?float
    {
        $query = sprintf(
            "SELECT AVG(temperature) as avg_temp FROM temperature_logs WHERE sensor_id = %d AND measured_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $sensorId
        );
        $result = $this->db->query($query);

        if ($row = $result->fetch_object()) {
            return $row->avg_temp !== null ? (float) $row->avg_temp : null;
        }
        return null;
    }
}
