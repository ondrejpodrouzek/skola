<?php
/**
 * Třída ZabbixApi - komunikace se Zabbix API
 */
class ZabbixApi
{
    /** @var string URL Zabbix API */
    private string $url;

    /** @var string API token pro autentizaci */
    private string $apiToken;

    /**
     * Konstruktor API klienta
     * 
     * @param string $url URL Zabbix API
     * @param string $apiToken Bearer token pro autentizaci
     */
    public function __construct(string $url, string $apiToken)
    {
        $this->url = $url;
        $this->apiToken = $apiToken;
    }

    /**
     * Odešle JSON-RPC požadavek na Zabbix API
     * 
     * @param array $payload Data požadavku
     * @return array|null Odpověď API nebo null při chybě
     */
    private function sendRequest(array $payload): ?array
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->apiToken
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Načte aktuální teplotu pro senzor
     * 
     * @param Sensor $sensor Senzor pro načtení teploty
     * @return float|null Teplota nebo null při chybě
     */
    public function fetchTemperature(Sensor $sensor): ?float
    {
        $payload = [
            "jsonrpc" => "2.0",
            "method" => "item.get",
            "params" => [
                "output" => ["itemid", "lastvalue"],
                "itemids" => $sensor->getItemId(),
                "hostids" => $sensor->getHostId()
            ],
            "id" => 1
        ];

        $response = $this->sendRequest($payload);

        if (isset($response['result'][0]['lastvalue'])) {
            return (float) $response['result'][0]['lastvalue'];
        }
        return null;
    }

    /**
     * Načte teploty pro všechny senzory
     * 
     * @param array $sensors Pole senzorů
     * @return array Pole senzorů s načtenými teplotami
     */
    public function fetchAllTemperatures(array $sensors): array
    {
        foreach ($sensors as $sensor) {
            $temperature = $this->fetchTemperature($sensor);
            if ($temperature !== null) {
                $sensor->setTemperature($temperature);
            }
        }
        return $sensors;
    }
}
