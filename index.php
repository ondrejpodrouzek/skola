<?php
/**
 * Temperaturen OOP - Hlavní aplikační soubor
 * 
 * Webová aplikace pro zobrazení aktuálních teplot ze senzorů.
 * Využívá OOP přístup s načítáním dat ze Zabbix API a ukládáním do MySQL.
 * 
 * @author Student V3I-ICT
 * @version 2.0
 */

// Načtení konfigurace
$config = require_once __DIR__ . '/config.php';

// Načtení tříd
require_once __DIR__ . '/class/Database.php';
require_once __DIR__ . '/class/Sensor.php';
require_once __DIR__ . '/class/SensorRepository.php';
require_once __DIR__ . '/class/ZabbixApi.php';

// Inicializace komponent
try {
    $db = Database::getInstance($config);
    $repository = new SensorRepository($db);
    $zabbixApi = new ZabbixApi($config['zabbix_url'], $config['zabbix_token']);

    // Načtení senzorů z databáze
    $sensors = $repository->findAll();

    // Načtení aktuálních teplot ze Zabbixu
    $sensors = $zabbixApi->fetchAllTemperatures($sensors);

    // Uložení teplot do historie
    foreach ($sensors as $sensor) {
        if ($sensor->getTemperature() !== null) {
            $repository->logTemperature($sensor);
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktuální teploty | CiS systems s.r.o.</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f3f3f3;
            margin: 0;
            padding: 20px;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            margin-top: 20px;
        }

        .logo {
            width: 150px;
            height: auto;
        }

        h1 {
            font-size: 2em;
            margin: 0;
        }

        .tiles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .tile {
            background-color: white;
            color: #333;
            padding: 20px;
            height: 250px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .tile:hover {
            transform: scale(1.05);
        }

        .temperature-value {
            font-size: 2em;
            font-weight: bold;
            color: #d9534f;
            margin: 10px 0;
        }

        .cold {
            color: blue !important;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin: 20px;
            border-radius: 5px;
        }
    </style>
    <script>
        // Automatická obnova stránky každých 10 sekund
        setTimeout(function () { window.location.reload(); }, 10000);
    </script>
</head>

<body>
    <header>
        <img src="cis.svg" alt="CiS Logo" class="logo">
        <h1>Aktuální teploty | Hejnice</h1>
    </header>

    <nav style="text-align: center; margin: 20px 0;">
        <a href="index.php"
            style="display: inline-block; padding: 10px 25px; margin: 0 5px; background: #333; color: white; text-decoration: none; border-radius: 5px;">Aktuální
            teploty</a>
        <a href="historie.php"
            style="display: inline-block; padding: 10px 25px; margin: 0 5px; background: white; color: #333; text-decoration: none; border-radius: 5px; border: 1px solid #333;">Historie</a>
    </nav>

    <?php if (isset($error)): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php else: ?>
        <div class="tiles">
            <?php foreach ($sensors as $sensor): ?>
                <div class="tile">
                    <strong>LOKACE:</strong>
                    <?= htmlspecialchars($sensor->getLocation()) ?><br>
                    <strong>TEPLOTA:</strong>
                    <span class="temperature-value <?= $sensor->isCold() ? 'cold' : '' ?>">
                        <?= $sensor->getFormattedTemperature() ?>
                    </span><br>
                    <strong>SENSOR:</strong>
                    <?= htmlspecialchars($sensor->getName()) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>

</html>