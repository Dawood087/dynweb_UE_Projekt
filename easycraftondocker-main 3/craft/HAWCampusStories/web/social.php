<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/model/model.php';

$dbPassword = $_ENV['HAW_KIEL_DB_PASSWORD'] ?? getenv('HAW_KIEL_DB_PASSWORD');
if (!is_string($dbPassword) || $dbPassword === '') {
    throw new RuntimeException('Set HAW_KIEL_DB_PASSWORD in the project .env file.');
}

$pdo = new PDO(
    'mysql:host=jtrt.your-database.de;dbname=haw_kiel;charset=utf8mb4',
    'haw_kiel_r',
    $dbPassword,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function socialInput($value): string
{
    return is_string($value) ? trim($value) : '';
}

$userFilter = socialInput($_GET['user'] ?? '');
$tagFilter = $userFilter === '' ? socialInput($_GET['tag'] ?? '') : '';
$search = socialInput($_GET['q'] ?? '');
$filters = array_filter(['user' => $userFilter, 'tag' => $tagFilter, 'q' => $search], 'strlen');
$feedUrl = '/social.php' . ($filters ? '?' . http_build_query($filters) : '');
$likesAvailable = socialLikesAvailable($pdo);

session_start();
$_SESSION['campus_like_token'] ??= bin2hex(random_bytes(32));
$likeToken = $_SESSION['campus_like_token'];
$likedPosts = $_SESSION['campus_liked_posts'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['like_token'] ?? null;
    if (!$likesAvailable || !is_string($token) || !hash_equals($likeToken, $token)) {
        http_response_code(403);
        exit('Die Like-Anfrage konnte nicht bestätigt werden.');
    }

    $postId = filter_var($_POST['post_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $desiredState = $_POST['liked'] ?? null;
    if ($postId === false || !in_array($desiredState, ['0', '1'], true)) {
        http_response_code(400);
        exit('Ungültige Like-Anfrage.');
    }

    $isLiked = isset($likedPosts[$postId]);
    if ($desiredState === '1' && !$isLiked && changeSocialLike($pdo, $postId, 1)) {
        $likedPosts[$postId] = true;
    } elseif ($desiredState === '0' && $isLiked) {
        changeSocialLike($pdo, $postId, -1);
        unset($likedPosts[$postId]);
    }

    $_SESSION['campus_liked_posts'] = $likedPosts;
    session_write_close();
    header('Location: ' . $feedUrl . '#post-' . $postId, true, 303);
    exit;
}
session_write_close();

$posts = loadSocialPosts($pdo, $userFilter, $tagFilter, $search, $likesAvailable);
$visibleTags = [];
foreach ($posts as $post) {
    if (!empty($post['tags'])) {
        $visibleTags = array_merge($visibleTags, explode(',', $post['tags']));
    }
}

$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new \Twig\Environment($loader, ['autoescape' => 'html']);

echo $twig->render('social.twig', [
    'posts' => $posts,
    'allTags' => loadSocialTagCounts($pdo, array_values(array_unique($visibleTags))),
    'topTags' => loadSocialTagCounts($pdo),
    'recentPosters' => loadRecentSocialPosters($pdo),
    'activeUser' => $userFilter,
    'activeTag' => $tagFilter,
    'search' => $search,
    'feedUrl' => $feedUrl,
    'likesAvailable' => $likesAvailable,
    'likedPostIds' => array_keys($likedPosts),
    'likeToken' => $likeToken,
]);

// 2 Posts haben kein Video, daher werden sie nicht angezeigt. Das ist korrekt, da die Aufgabe nur Posts mit Videos anzeigt.