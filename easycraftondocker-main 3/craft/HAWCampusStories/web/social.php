<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

$pdo = new PDO(
    'mysql:host=db;dbname=tiktok;charset=utf8mb4',
    'craft',
    'craft'
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$userFilter = $_GET['user'] ?? null;
$tagFilter = $_GET['tag'] ?? null;

$where = array();
$params = array();

if ($userFilter !== null) {
    $where[] = 'u.username = :username';
    $params['username'] = $userFilter;
}

if ($tagFilter !== null) {
    $where[] = 't.name = :tagname';
    $params['tagname'] = $tagFilter;
}

$sql = 'SELECT p.id AS post_id, u.username, u.user_realname, u.user_image,
               p.post_video_file, p.post_text,
               GROUP_CONCAT(t.name SEPARATOR \',\') AS tags
        FROM post p
        JOIN user u ON p.fk_user = u.id
        LEFT JOIN post_tag pt ON pt.fk_post = p.id
        LEFT JOIN tag t ON pt.fk_tag = t.id';

if (count($where) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' GROUP BY p.id, u.id';

$postStatement = $pdo->prepare($sql);
$postStatement->execute($params);
$posts = $postStatement->fetchAll(PDO::FETCH_ASSOC);

$tagStatement = $pdo->query(
    'SELECT t.name, COUNT(pt.fk_post) AS post_count
     FROM tag t
     LEFT JOIN post_tag pt ON pt.fk_tag = t.id
     GROUP BY t.id, t.name'
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

$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new \Twig\Environment($loader, array('autoescape' => 'html'));

echo $twig->render('social.twig', array(
    'posts' => $posts,
    'allTags' => $allTags,
    'thumbnailByPostId' => $thumbnailByPostId,
));
