<?php
/**
 * Historie teplot - zobrazení záznamů z databáze
 * 
 */

$config = require_once __DIR__ . '/config.php';
require_once __DIR__ . '/class/Database.php';
require_once __DIR__ . '/class/Sensor.php';
require_once __DIR__ . '/class/SensorRepository.php';

try {
    $db = Database::getInstance($config);
    $repository = new SensorRepository($db);

    // Načtení všech senzorů
    $sensors = $repository->findAll();

    // Vybraný senzor (z GET parametru)
    $selectedSensorId = isset($_GET['sensor']) ? (int) $_GET['sensor'] : null;

    // Historie pro vybraný senzor
    $history = [];
    $stats = null;
    if ($selectedSensorId) {
        $history = $repository->getTemperatureHistory($selectedSensorId, 50);

        // Statistiky - agregační funkce
        $query = sprintf(
            "SELECT 
                COUNT(*) as pocet,
                AVG(temperature) as prumer,
                MIN(temperature) as minimum,
                MAX(temperature) as maximum
            FROM temperature_logs 
            WHERE sensor_id = %d",
            $selectedSensorId
        );
        $result = $db->query($query);
        $stats = $result->fetch_object();
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
    <title>Historie teplot | CiS systems s.r.o.</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            margin: 0;
            padding: 20px;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            margin-bottom: 20px;
        }

        .logo {
            width: 150px;
            height: auto;
        }

        h1 {
            font-size: 1.8em;
            margin: 0;
        }

        nav {
            margin-bottom: 20px;
            text-align: center;
        }

        nav a {
            display: inline-block;
            padding: 10px 25px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 5px;
        }

        nav a.active {
            background: #333;
            color: white;
        }

        nav a:not(.active) {
            background: white;
            color: #333;
            border: 1px solid #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #d9534f;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        select {
            padding: 10px;
            font-size: 1em;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #333;
            color: white;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .cold {
            color: blue;
        }

        .hot {
            color: #d9534f;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <img src="cis.svg" alt="CiS Logo" class="logo">
            <h1>Historie teplot</h1>
        </header>

        <nav>
            <a href="index.php">Aktuální teploty</a>
            <a href="historie.php" class="active">Historie</a>
        </nav>

        <?php if (isset($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>

            <form method="get">
                <select name="sensor" onchange="this.form.submit()">
                    <option value="">-- Vyberte senzor --</option>
                    <?php foreach ($sensors as $sensor): ?>
                        <option value="<?= $sensor->getId() ?>" <?= $selectedSensorId == $sensor->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sensor->getLocation()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($selectedSensorId && $stats): ?>
                <div class="stats">
                    <div class="stat-box">
                        <div class="stat-value">
                            <?= $stats->pocet ?>
                        </div>
                        <div class="stat-label">Počet měření</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">
                            <?= $stats->prumer ? number_format($stats->prumer, 1) : '-' ?>°C
                        </div>
                        <div class="stat-label">Průměr</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">
                            <?= $stats->minimum ? number_format($stats->minimum, 1) : '-' ?>°C
                        </div>
                        <div class="stat-label">Minimum</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">
                            <?= $stats->maximum ? number_format($stats->maximum, 1) : '-' ?>°C
                        </div>
                        <div class="stat-label">Maximum</div>
                    </div>
                </div>

                <?php if (count($history) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Datum a čas</th>
                                <th>Teplota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $record): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($record['measured_at']) ?>
                                    </td>
                                    <td class="<?= $record['temperature'] < 21 ? 'cold' : 'hot' ?>">
                                        <?= number_format($record['temperature'], 1) ?>°C
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>nic zde není - historie se nenačetla</p>
                <?php endif; ?>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>

</html>