<?php

namespace Main\Threaded;



use Main\Threaded\Balancers\ActiveWarsBalancer;
use Main\Threaded\Workers\ActiveWarsWorker;

class ActiveWars
{
    protected $threads;

    protected $provider;

    protected $pool;

    protected $progressBar;

    public function __construct()
    {

        $this->threads = 5;
        $this->provider = new ActiveWarsBalancer();
        $this->pool = new \Pool($this->threads, BaseWorker::class, [$this->provider, 'init.php']);
        $this->run();
    }

    public function run()
    {
        $start = microtime(true);

        $workers = $this->threads;

        for($i = 0; $i < $workers; $i++) {
            $this->pool->submit(new ActiveWarsWorker($i));
        }

        $this->pool->shutdown();
        printf("Done for %.2f seconds" . PHP_EOL, microtime(true) - $start);

    }

}