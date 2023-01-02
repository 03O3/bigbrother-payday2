<?php


$router->get('/webhook/{suspect}/{author}/', 'Brain\CheaterDetection@SendNotify');
