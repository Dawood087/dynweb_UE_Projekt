<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

// DB Connetion
$pdo = new PDO('mysql:host=db;dbname=tiktok;charset=utf8mb4', 'craft', 'craft');

$userFilter = $_GET['user'] ?? null;
$tagFilter  = $_GET['tag']  ?? null;

$sql = "SELECT p.id AS post_id, u.username, u.user_realname, u.user_image,
               p.post_video_file, p.post_text,
               GROUP_CONCAT(t.name SEPARATOR ',') AS tags
        FROM post p
        JOIN user u ON p.fk_user = u.id
        LEFT JOIN post_tag pt ON pt.fk_post = p.id
        LEFT JOIN tag t ON pt.fk_tag = t.id";

// Filter-Logik
if ($userFilter !== null && $userFilter !== '') {
    $sql .= " WHERE u.username = '$userFilter'";
} elseif ($tagFilter !== null && $tagFilter !== '') {
    // Unterabfrage (Subquery) nutzen, um den Post über seine ID nach dem Tag zu filtern,
    // damit GROUP_CONCAT danach trotzdem noch ALLE Tags des Posts anzeigen kann!
    $sql .= " WHERE p.id IN (
        SELECT pt_sub.fk_post 
        FROM post_tag pt_sub 
        JOIN tag t_sub ON pt_sub.fk_tag = t_sub.id 
        WHERE t_sub.name = '$tagFilter'
    )";
}

$sql .= " GROUP BY p.id, u.id";

$postStatement = $pdo->query($sql);
$posts = $postStatement->fetchAll(PDO::FETCH_ASSOC);

$tagStatement = $pdo->query(
    "SELECT t.name, COUNT(pt.fk_post) AS post_count
     FROM tag t
     LEFT JOIN post_tag pt ON pt.fk_tag = t.id
     GROUP BY t.id, t.name"
);

$allTags = array();
foreach ($tagStatement->fetchAll(PDO::FETCH_ASSOC) as $tag) {
    $allTags[$tag['name']] = $tag['post_count'];
}

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

// twig load and render
$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new \Twig\Environment($loader, array('autoescape' => 'html'));

// prepare for twig render
echo $twig->render('social.twig', array(
    'posts' => $posts,
    'allTags' => $allTags,
    'thumbnailByPostId' => $thumbnailByPostId,
));