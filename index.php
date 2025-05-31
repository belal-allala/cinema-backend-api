<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Facades\Router;
use Core\Database;
use Core\DataSources\PostgreDataSource;

$ds = new PostgreDataSource(
    'localhost', 
    5432,      
    'fil_rouge_rattrapage', 
    'postgres',   
    'belal' 
);
Database::init($ds);

$router = new Router();
$router->dispatch();
