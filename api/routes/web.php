<?php


$router->get('/', function () {
    return 'Hello World';
});

$router->get('/webhook/{suspect}/{reason}', 'Brain\CheaterDetection@App');
