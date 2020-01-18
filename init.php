<?php

//composer vendor autoload
require_once 'vendor/autoload.php';

require_once 'main/helpers/helpers.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);

$dotenv->load();
