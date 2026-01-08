<?php
/**
 * Třída Sensor - reprezentuje teplotní senzor
 * 
 * Uchovává informace o senzoru a jeho aktuální teplotě.
 * Umožňuje načítání dat ze Zabbix API a ukládání do databáze.
 * 
 * @author Student V3I-ICT
 * @version 1.0
 */
class Sensor
{
    /** @var int|null ID senzoru v databázi */
    private ?int $id;

    /** @var string ID hosta v Zabbixu */
    private string $hostId;

    /** @var string ID položky v Zabbixu */
    private string $itemId;

    /** @var string Název senzoru */
    private string $name;

    /** @var string Lokace senzoru */
    private string $location;

    /** @var float|null Aktuální teplota */
    private ?float $temperature = null;

    /**
     * Konstruktor senzoru
     * 
     * @param string $hostId ID hosta v Zabbixu
     * @param string $itemId ID položky v Zabbixu
     * @param string $name Název senzoru
     * @param string $location Lokace senzoru
     * @param int|null $id ID v databázi (volitelné)
     */
    public function __construct(
        string $hostId,
        string $itemId,
        string $name,
        string $location,
        ?int $id = null
    ) {
        $this->hostId = $hostId;
        $this->itemId = $itemId;
        $this->name = $name;
        $this->location = $location;
        $this->id = $id;
    }

    /**
     * Getter pro ID
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter pro host ID
     * @return string
     */
    public function getHostId(): string
    {
        return $this->hostId;
    }

    /**
     * Getter pro item ID
     * @return string
     */
    public function getItemId(): string
    {
        return $this->itemId;
    }

    /**
     * Getter pro název
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Getter pro lokaci
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Getter pro teplotu
     * @return float|null
     */
    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    /**
     * Setter pro teplotu
     * @param float $temperature Naměřená teplota
     * @return static
     */
    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    /**
     * Vrátí formátovanou teplotu
     * @return string
     */
    public function getFormattedTemperature(): string
    {
        if ($this->temperature === null) {
            return "N/A";
        }
        return number_format($this->temperature, 1) . "°C";
    }

    /**
     * Zjistí, zda je teplota studená (pod 21°C)
     * @return bool
     */
    public function isCold(): bool
    {
        return $this->temperature !== null && $this->temperature < 21;
    }
}
