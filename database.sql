-- Vytvoření databáze
CREATE DATABASE IF NOT EXISTS temperaturen
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_czech_ci;

USE temperaturen;

-- konfigurace teplotních senzorů
CREATE TABLE IF NOT EXISTS sensors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    host_id VARCHAR(20) NOT NULL COMMENT 'ID hosta v Zabbixu',
    item_id VARCHAR(20) NOT NULL COMMENT 'ID položky v Zabbixu',
    name VARCHAR(50) NOT NULL COMMENT 'Název senzoru',
    location VARCHAR(100) NOT NULL COMMENT 'Lokace senzoru',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_host_item (host_id, item_id)
) ENGINE=InnoDB COMMENT='Konfigurace teplotních senzorů';

-- Uchovává historii naměřených teplot
CREATE TABLE IF NOT EXISTS temperature_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NOT NULL,
    temperature DECIMAL(5,2) NOT NULL COMMENT 'Naměřená teplota ve °C',
    measured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Čas měření',
    
    FOREIGN KEY (sensor_id) REFERENCES sensors(id) ON DELETE CASCADE,
    INDEX idx_sensor_time (sensor_id, measured_at)
) ENGINE=InnoDB COMMENT='Historie naměřených teplot';


-- vložení testovacích dat senzorů dle realnych dat ze Zabbixu
INSERT INTO sensors (host_id, item_id, name, location) VALUES
    ('10785', '70741', 'sensor-he-03', 'Building M2 / Area A - Rack 1 near stairs'),
    ('10786', '70743', 'sensor-he-04', 'Building M1 / Area N - supervisor desk'),
    ('10787', '70746', 'sensor-he-05', 'Building M1 / Workstation C'),
    ('10787', '70747', 'sensor-he-05', 'Building M1 / Equipment area - column 2 from entrance'),
    ('10788', '70750', 'sensor-he-06', 'Building M3 / Storage - Section A near elevator'),
    ('10788', '70751', 'sensor-he-06', 'Building M3 / Storage - Section B platform under table'),
    ('10789', '70753', 'sensor-he-07', 'Building U3 / Material storage - wall near warehouse'),
    ('10790', '70756', 'sensor-he-08', 'Building U1 / Production Area A - ceiling mount'),
    ('10790', '70757', 'sensor-he-08', 'Building U1 / Production Area B - wall near stairs'),
    ('10791', '70758', 'sensor-he-09', 'Building C1 / Office area - under desk'),
    ('10792', '70759', 'sensor-he-10', 'Building M2 / Testing area - workstation QW'),
    ('10793', '70762', 'sensor-he-11', 'Building M3 / Test Station A - 1st table from entrance'),
    ('10793', '70763', 'sensor-he-11', 'Building M3 / Test Station B - near storage table'),
    ('11228', '129695', 'sensor-he-12', 'Building NH / Prototype area'),
    ('10794', '70766', 'sensor-he-14', 'Building M4 / Production A - wall near locker room'),
    ('10794', '70767', 'sensor-he-14', 'Building M4 / Production B - column 2L from gate'),
    ('10795', '70770', 'sensor-he-15', 'Building M3 / Area MW A - column 1 from elevator A'),
    ('10795', '70771', 'sensor-he-15', 'Building M3 / Area MW B - column 1 from elevator B'),
    ('10796', '70774', 'sensor-he-16', 'Building M3 / Workshop A - column 1 from elevator A'),
    ('10796', '70775', 'sensor-he-16', 'Building M3 / Workshop B - column 1 from elevator B'),


