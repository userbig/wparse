<?php


namespace Main\Commands;


use Main\Threaded\AllWars;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AllWarsCommand extends Command
{
    protected static $defaultName = 'wars:all';

    protected function configure()
    {
        $this->setDescription('Fetch all wars. Use it only to fill database');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        new AllWars();
        return 0;

    }

}