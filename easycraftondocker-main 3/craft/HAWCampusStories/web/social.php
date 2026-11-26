<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

// DB Connection
$pdo = new PDO('mysql:host=craft_db;dbname=tiktok;charset=utf8mb4', 'root', 'cr4ftd4t4b4s3');
$userFilter = $_GET['user'] ?? null;
$tagFilter  = $_GET['tag']  ?? null;

$sql = "SELECT p.id AS post_id, u.username, u.user_realname, u.user_image,
               p.post_video_file, p.post_text,
               GROUP_CONCAT(t.name SEPARATOR ',') AS tags
        FROM post p
        JOIN user u ON p.fk_user = u.id
        LEFT JOIN post_tag pt ON pt.fk_post = p.id
        LEFT JOIN tag t ON pt.fk_tag = t.id";

$params = array();

// Filter 
if ($userFilter !== null && $userFilter !== '') {
    $sql .= " WHERE u.username = :user";
    $params[':user'] = $userFilter;
} elseif ($tagFilter !== null && $tagFilter !== '') {
    $sql .= " WHERE p.id IN (
        SELECT pt_sub.fk_post 
        FROM post_tag pt_sub 
        JOIN tag t_sub ON pt_sub.fk_tag = t_sub.id 
        WHERE t_sub.name = :tag
    )";
    $params[':tag'] = $tagFilter;
}

$sql .= " GROUP BY p.id, u.id";

$postStatement = $pdo->prepare($sql);
$postStatement->execute($params);
$posts = $postStatement->fetchAll(PDO::FETCH_ASSOC);

$tagStatement = $pdo->query(
    "SELECT t.name, COUNT(pt.fk_post) AS post_count
     FROM tag t
     LEFT JOIN post_tag pt ON pt.fk_tag = t.id
     GROUP BY t.id, t.name
     ORDER BY post_count DESC, t.name ASC, t.id ASC"
);

$allTags = array();
foreach ($tagStatement->fetchAll(PDO::FETCH_ASSOC) as $tag) {
    $allTags[$tag['name']] = $tag['post_count'];
}

$topTags = array_slice(array_filter($allTags), 0, 5, true);

$recentPosters = $pdo->query(
    "SELECT u.username, u.user_realname, u.user_image
     FROM post p
     JOIN user u ON u.id = p.fk_user
     GROUP BY u.id, u.username, u.user_realname, u.user_image
     ORDER BY MAX(p.post_date) DESC, MAX(p.id) DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

// twig load and render
$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new \Twig\Environment($loader, array('autoescape' => 'html'));

// prepare for twig render
echo $twig->render('social.twig', array(
    'posts' => $posts,
    'allTags' => $allTags,
    'topTags' => $topTags,
    'recentPosters' => $recentPosters,
    'activeUser' => $userFilter,
    'activeTag' => ($userFilter === null || $userFilter === '') ? $tagFilter : null,
));