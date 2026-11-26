<?php

function socialLikesAvailable(PDO $pdo): bool
{
    $statement = $pdo->query("SHOW COLUMNS FROM post LIKE 'likes'");
    return $statement->fetch(PDO::FETCH_ASSOC) !== false;
}

function loadSocialPosts(PDO $pdo, string $user, string $tag, string $search, bool $likesAvailable): array
{
    $likes = $likesAvailable ? 'MAX(p.likes)' : '0';
    $sql = "SELECT p.id AS post_id, u.username, u.user_realname, u.user_image,
                   p.post_video_file, p.post_text, $likes AS likes,
                   GROUP_CONCAT(DISTINCT t.name SEPARATOR ',') AS tags
            FROM post p
            JOIN user u ON p.fk_user = u.id
            LEFT JOIN post_tag pt ON pt.fk_post = p.id
            LEFT JOIN tag t ON pt.fk_tag = t.id";
    $filters = [];
    $params = [];

    if ($user !== '') {
        $sql .= ' WHERE u.username = ?';
        $params[] = $user;
    } elseif ($tag !== '') {
        $filters[] = 'MAX(t.name = ?) = 1';
        $params[] = $tag;
    }

    $sql .= ' GROUP BY p.id, u.id, u.username, u.user_realname, u.user_image, p.post_video_file, p.post_text';

    if ($search !== '') {
        $term = ltrim($search, '@#');
        $term = $term === '' ? $search : $term;

        // Prozentzeichen und Unterstriche sind Teil des Suchtexts.
        $pattern = '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $filters[] = "(p.post_text LIKE ? ESCAPE '!'
                    OR u.username LIKE ? ESCAPE '!'
                    OR u.user_realname LIKE ? ESCAPE '!'
                    OR MAX(t.name LIKE ? ESCAPE '!') = 1)";
        array_push($params, $pattern, $pattern, $pattern, $pattern);
    }

    // HAVING prüft den fertigen Post mit allen seinen Hashtags.
    if ($filters !== []) {
        $sql .= ' HAVING ' . implode(' AND ', $filters);
    }

    $sql .= ' ORDER BY p.id LIMIT 20';
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function loadSocialTagCounts(PDO $pdo, ?array $tags = null): array
{
    if ($tags === []) {
        return [];
    }

    $sql = "SELECT t.name, COUNT(DISTINCT p.id) AS post_count
            FROM tag t
            JOIN post_tag pt ON pt.fk_tag = t.id
            JOIN post p ON p.id = pt.fk_post";
    if ($tags !== null) {
        $placeholders = implode(', ', array_fill(0, count($tags), '?'));
        $sql .= " WHERE t.name IN ($placeholders)";
    }

    // Ohne Tagliste laden wir die fünf häufigsten Hashtags.
    $sql .= ' GROUP BY t.id, t.name ORDER BY post_count DESC, t.name ASC, t.id ASC';
    if ($tags === null) {
        $sql .= ' LIMIT 5';
    }

    $statement = $pdo->prepare($sql);
    $statement->execute($tags ?? []);
    return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
}

function loadRecentSocialPosters(PDO $pdo): array
{
    $sql = "SELECT u.username, u.user_realname, u.user_image
            FROM post p
            JOIN user u ON u.id = p.fk_user
            GROUP BY u.id, u.username, u.user_realname, u.user_image
            ORDER BY MAX(p.post_date) DESC, MAX(p.id) DESC
            LIMIT 5";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function changeSocialLike(PDO $pdo, int $postId, int $change): bool
{
    // GREATEST hält die Like-Zahl bei mindestens 0.
    // Das bisherige Datum hält die Reihenfolge der Beiträge stabil.
    $sql = "UPDATE post
            SET likes = GREATEST(CAST(COALESCE(likes, 0) AS SIGNED) + ?, 0),
                post_date = post_date
            WHERE id = ?";
    $statement = $pdo->prepare($sql);
    $statement->execute([$change, $postId]);
    return $statement->rowCount() === 1;
}
