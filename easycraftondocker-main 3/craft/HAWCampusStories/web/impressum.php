<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new \Twig\Environment($loader, array('autoescape' => 'html'));

echo $twig->render('impressum.twig');
