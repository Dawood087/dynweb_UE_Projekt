<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/../model/post_data.php';

$thumbnailByPostId = array(
    "1" => "/assets/videos/thumb/thumb_spaetsommerfest.jpeg",
    "2" => "/assets/videos/thumb/thumb_videocut.jpeg",
    "3" => "/assets/videos/thumb/thumb_drehbuchschreiben.jpeg",
    "4" => "/assets/videos/thumb/thumb_maclabore.jpeg",
    "5" => "/assets/videos/thumb/thumb_unitag.jpeg",
    "6" => "/assets/videos/thumb/placeholder.jpg",
    "7" => "/assets/videos/thumb/placeholder.jpg",
    "8" => "/assets/videos/thumb/placeholder.jpg",
    "9" => "/assets/videos/thumb/placeholder.jpg",
    "10" => "/assets/videos/thumb/placeholder.jpg",
);

$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new \Twig\Environment($loader, array('autoescape' => 'html'));

echo $twig->render('social.twig', array(
    'posts' => $posts,
    'thumbnailByPostId' => $thumbnailByPostId,
));
