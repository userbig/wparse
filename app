#!/usr/bin/env php
<?php

require_once 'init.php';



$app = new \Symfony\Component\Console\Application();

$app->add(new \Main\Commands\ActiveWarsCommand());
$app->add(new \Main\AllWarsCommand());

$app->run();