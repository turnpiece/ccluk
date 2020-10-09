<?php

namespace Beehive;

// Don't redefine the functions if included multiple times.
if (!\function_exists('Beehive\\GuzzleHttp\\Psr7\\str')) {
    require __DIR__ . '/functions.php';
}