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
('10785', '70741', 'cissensorhe03', 'M2 / Automotive - 1. regál od schodiště'),
('10786', '70743', 'cissensorhe04', 'M1 / Normal - stůl mistra'),
('10787', '70746', 'cissensorhe05', 'M1 / Koax stůl'),
('10787', '70747', 'cissensorhe05', 'M1 / Krimpovačky 2.sloup od vchodu'),
('10788', '70750', 'cissensorhe06', 'M3 / Sklad - A u mot. výtahu'),
('10788', '70751', 'cissensorhe06', 'M3 / Sklad - B podesta pod stolem'),
('10789', '70753', 'cissensorhe07', 'U3 / Prkna - na stěně u skladu prken'),
('10790', '70756', 'cissensorhe08', 'U1 / Nordex A - u stropu'),
('10790', '70757', 'cissensorhe08', 'U1 / Nordex B - stěna u schodiště'),
('10791', '70758', 'cissensorhe09', 'C1 / kanc. G. Malorny - pod stolem'),
('10792', '70759', 'cissensorhe10', 'M2 / Testery - stůl QW'),
('10793', '70762', 'cissensorhe11', 'M3 / FCT A - VÝROBA 1.stůl u vchodu'),
('10793', '70763', 'cissensorhe11', 'M3 / FCT B SKLAD u stolu'),
('11228', '129695', 'cissensorhe12', 'NH / Musterbau'),
('10794', '70766', 'cissensorhe14', 'M4 / Výroba A - stěna u šatny'),
('10794', '70767', 'cissensorhe14', 'M4 / Výroba B - 2.L sloup od vrat'),
('10795', '70770', 'cissensorhe15', 'M3 / Miwe A - 1.sloup od mot. výtahu'),
('10795', '70771', 'cissensorhe15', 'M3 / Miwe B - 1.sloup od HY výtahu'),
('10796', '70774', 'cissensorhe16', 'M3 / Stříhárna A - 1.sloup od mot. výtahu'),
('10796', '70775', 'cissensorhe16', 'M3 / Stříhárna B - 1.sloup od HY výtahu');
