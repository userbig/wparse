<?php

namespace Main\Threaded\Balancers;

use GuzzleHttp\Client;

class AllWarsBalancer extends \Worker
{
    private $totalPages;

    private $maxWarId;

    private $processed = 0;

    public function __construct($maxWarId = 999999999)
    {
        pecho('Balancer initialized');

        $this->maxWarId = $maxWarId;

        $client = new Client();

        $response = $client->request('GET', 'https://esi.evetech.net/latest/wars/?datasource=tranquility&max_war_id='.$maxWarId);

        $this->maxWarId = json_decode($response->getBody())[0];

        $this->totalPages = (int) ceil($this->maxWarId / 2000);

        echo PHP_EOL."MAX WAR ID $this->maxWarId".PHP_EOL;

        pecho('Max war_id: '.$this->maxWarId."; This is {$this->totalPages} iterations, 2000 per worker");
    }

    public function getNext()
    {
        if ($this->processed === $this->totalPages) {
            return null;
        }

        $p = $this->processed;

        $this->processed++;

        $w = $this->maxWarId;

        $this->maxWarId -= 2000;

        return [
            'maxWarId'   => $w,
            'processed'  => $p,
            'totalPages' => $this->totalPages,
        ];
    }
}
