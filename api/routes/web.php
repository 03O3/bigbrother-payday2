<?php


$router->get('/', function () {
    return 'Hello World';
});

$router->get('/webhook/{suspect}/{author}/', 'Brain\CheaterDetection@SendNotify');
