<?php
/**
 * Třída Database - Singleton pattern pro připojení k databázi
 * 
 * Zajišťuje jediné připojení k MySQL databázi v celé aplikaci.
 * Implementuje návrhový vzor Singleton.
 * 
 * @author Student V3I-ICT
 * @version 1.0
 */
class Database
{
    /** @var Database|null Jediná instance třídy */
    private static ?Database $instance = null;
    
    /** @var mysqli Připojení k databázi */
    private mysqli $connection;

    /**
     * Privátní konstruktor - nelze vytvořit instanci zvenčí
     * 
     * @param string $host Hostitel databáze
     * @param string $user Uživatelské jméno
     * @param string $password Heslo
     * @param string $database Název databáze
     * @throws Exception Pokud se nepodaří připojit
     */
    private function __construct(string $host, string $user, string $password, string $database)
    {
        $this->connection = new mysqli($host, $user, $password, $database);
        if ($this->connection->connect_error) {
            throw new Exception("Chyba připojení k databázi: " . $this->connection->connect_error);
        }
        $this->connection->set_charset("utf8mb4");
    }

    /**
     * Získá instanci databázového připojení (Singleton)
     * 
     * @param array $config Konfigurace připojení
     * @return Database Instance třídy
     */
    public static function getInstance(array $config): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database(
                $config['db_host'],
                $config['db_user'],
                $config['db_password'],
                $config['db_name']
            );
        }
        return self::$instance;
    }

    /**
     * Vrátí mysqli připojení
     * 
     * @return mysqli Připojení k databázi
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    /**
     * Provede SQL dotaz
     * 
     * @param string $query SQL dotaz
     * @return mysqli_result|bool Výsledek dotazu
     */
    public function query(string $query): mysqli_result|bool
    {
        return $this->connection->query($query);
    }

    /**
     * Escapuje string pro bezpečné použití v SQL
     * 
     * @param string $value Hodnota k escapování
     * @return string Escapovaná hodnota
     */
    public function escape(string $value): string
    {
        return $this->connection->real_escape_string($value);
    }

    /**
     * Vrátí ID posledního vloženého záznamu
     * 
     * @return int|string ID záznamu
     */
    public function lastInsertId(): int|string
    {
        return $this->connection->insert_id;
    }
}
