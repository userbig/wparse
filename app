#!/usr/bin/env php
<?php

require_once 'init.php';


pecho('Console command initialized');

$app = new \Symfony\Component\Console\Application();

$app->add(new \Main\Commands\ActiveWarsCommand());
$app->add(new \Main\Commands\AllWarsCommand());

$app->run();