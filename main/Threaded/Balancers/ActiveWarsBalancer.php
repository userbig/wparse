<?php

namespace Main\Threaded\Balancers;

use GuzzleHttp\Client;
use PDO;
use Worker;

class ActiveWarsBalancer extends Worker
{
    protected $warIds;

    protected $maxWarId;

    protected $processed = 0;

    protected $totalPages;

    protected $chunks = 500;

    public function __construct()
    {
        pecho('Balancer initialized');

        $this->warIds;

        $host = getenv('DB_HOST');

        $dbname = getenv('DB_DATABASE');

        $dbuser = getenv('DB_USERNAME');

        $dbpass = getenv('DB_PASSWORD');

        $client = new Client();

        $response = $client->request('GET', 'https://esi.evetech.net/latest/wars/?datasource=tranquility');

        $this->maxWarId = json_decode($response->getBody())[0];

        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $dbuser, $dbpass);
        $stmt = $pdo->prepare('select war_id from wars where (finished is null or finished > NOW()) order by war_id');
        $stmt->execute();
        $dbArray = (array) $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $stmt->closeCursor();

        $this->warIds = (array) array_unique(array_merge($dbArray, range($this->maxWarId, $this->maxWarId - 4000, -1)), SORT_NUMERIC);

        $this->totalPages = (int) ceil(count($this->warIds) / $this->chunks);

        pecho('Wars need to be checked: '.count($this->warIds)."; This is {$this->totalPages} iterations, {$this->chunks} per worker");
    }

    public function getNext()
    {
        if ($this->processed == $this->totalPages) {
            return null;
        }

        $p = $this->processed;

        $this->processed++;

        $warList = array_slice($this->warIds, $p * 200, $this->chunks);

        return [
            'warList'    => $warList,
            'processed'  => $p,
            'totalPages' => $this->totalPages,
        ];
    }
}
