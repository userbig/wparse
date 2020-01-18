<?php


namespace Main\Threaded;


use Main\Threaded\Balancers\AllWarsBalancer;
use Main\Threaded\Workers\AllWarsWorker;

class AllWars
{
    protected $threads;

    protected $provider;

    protected $pool;

    protected $progressBar;

    public function __construct()
    {

        $this->threads = 300;
        $this->provider = new AllWarsBalancer();
        $this->pool = new \Pool($this->threads, BaseWorker::class, [$this->provider, 'init.php']);
        $this->run();
    }

    public function run()
    {
        pecho('Fetch stated; ALL WARS');
        $start = microtime(true);

        $workers = $this->threads;

        for($i = 0; $i < $workers; $i++) {
            $this->pool->submit(new AllWarsWorker($i));
        }

        $this->pool->shutdown();
        printf("Done for %.2f seconds" . PHP_EOL, microtime(true) - $start);

    }
}