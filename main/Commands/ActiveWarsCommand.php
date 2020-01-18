<?php

namespace Main\Commands;

use Main\Threaded\ActiveWars;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActiveWarsCommand extends Command
{
    protected static $defaultName = 'wars:active';

    protected function configure()
    {
        $this->setDescription('Parse all active wars what stored in DB and 4000 latest from ESI API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        new ActiveWars();

        return 0;
    }
}
