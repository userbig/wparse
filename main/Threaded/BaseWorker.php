<?php


namespace Main\Threaded;


class BaseWorker extends \Worker
{
    public $provider;

    public $autoloader;

    public function __construct(\Worker $provider, $autoloader)
    {
        $this->provider = $provider;
        $this->autoloader= $autoloader;
    }

    public function run()
    {
        //autoloader
        require_once $this->autoloader;

    }


    //override
    public function start(int $options = PTHREADS_INHERIT_ALL) { return parent::start(PTHREADS_INHERIT_NONE); }

    public function getProvider()
    {
        return $this->provider;
    }



}