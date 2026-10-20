<?php
$profile = [
  'name' => 'David Piekarski',
  'handle' => 'davidpiekarski',
  'role' => 'Student',
  'location' => 'Kiel, Deutschland',
  'bio' => 'Studierender im Semester 3 des Studiengangs Multimedia Production an der HAW Kiel.',
  'followers' => 0,
  'following' => 0,
  'posts' => 5,
  'avatar' => '/assets/img/PictureOfMe.jpg',
  'stats' => [
    ['label' => 'Studiengang', 'value' => 'Multimedia Production'],
    ['label' => 'Semester', 'value' => '3. Semester'],
    ['label' => 'Interessen', 'value' => 'Design, Video Editing, Sport, Social Media'],
  ]
];
require_once dirname(__DIR__) . '/vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new \Twig\Environment($loader, array('autoescape' => 'html'));

echo $twig->render('profile.twig', array('profile' => $profile));
