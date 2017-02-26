<?php
require_once '../vendor/autoload.php';
require_once '../src/autoload.php';

//Объявляем класс
$agent = new \SteeinAgent\Agent();

$languages = $agent->languages();

echo '<pre>';
    print_r($agent->device());
echo '</pre>';