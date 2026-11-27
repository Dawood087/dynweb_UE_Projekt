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
                   p.post_video_file, p.post_text, p.lowresvideo_exists, $likes AS likes,
                   GROUP_CONCAT(DISTINCT t.name SEPARATOR ',') AS tags
            FROM post p
            JOIN user u ON p.fk_user = u.id
            LEFT JOIN post_tag pt ON pt.fk_post = p.id
            LEFT JOIN tag t ON pt.fk_tag = t.id
            WHERE p.post_video_file LIKE :group_prefix ESCAPE '!'";
    $filters = [];
    $params = ['group_prefix' => '19!_%'];

    if ($user !== '') {
        $filters[] = 'u.username = :username';
        $params['username'] = $user;
    } elseif ($tag !== '') {
        $filters[] = 'MAX(t.name = :tag) = 1';
        $params['tag'] = $tag;
    }

    $sql .= ' GROUP BY p.id, u.id, u.username, u.user_realname, u.user_image, p.post_video_file, p.post_text, p.lowresvideo_exists';

    if ($search !== '') {
        $term = ltrim($search, '@#');
        $term = $term === '' ? $search : $term;

        // Prozentzeichen und Unterstriche sind Teil des Suchtexts.
        $pattern = '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $filters[] = "(p.post_text LIKE :search_text ESCAPE '!'
                    OR u.username LIKE :search_username ESCAPE '!'
                    OR u.user_realname LIKE :search_name ESCAPE '!'
                    OR MAX(t.name LIKE :search_tag ESCAPE '!') = 1)";
        $params['search_text'] = $pattern;
        $params['search_username'] = $pattern;
        $params['search_name'] = $pattern;
        $params['search_tag'] = $pattern;
    }

    // HAVING prüft den fertigen Post mit allen seinen Hashtags.
    if ($filters !== []) {
        $sql .= ' HAVING ' . implode(' AND ', $filters);
    }

    $sql .= ' ORDER BY p.id DESC LIMIT 20';
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
            JOIN post p ON p.id = pt.fk_post
            JOIN user u ON u.id = p.fk_user
            WHERE p.post_video_file LIKE :group_prefix ESCAPE '!'";
    $params = ['group_prefix' => '19!_%'];
    if ($tags !== null) {
        $placeholders = [];
        foreach (array_values($tags) as $index => $tag) {
            $placeholder = 'tag_' . $index;
            $placeholders[] = ':' . $placeholder;
            $params[$placeholder] = $tag;
        }
        $sql .= ' AND t.name IN (' . implode(', ', $placeholders) . ')';
    }

    // Ohne Tagliste laden wir die fünf häufigsten Hashtags.
    $sql .= ' GROUP BY t.id, t.name ORDER BY post_count DESC, t.name ASC, t.id ASC';
    if ($tags === null) {
        $sql .= ' LIMIT 5';
    }

    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
}

function loadRecentSocialPosters(PDO $pdo): array
{
    $sql = "SELECT u.username, u.user_realname, u.user_image
            FROM post p
            JOIN user u ON u.id = p.fk_user
            WHERE p.post_video_file LIKE :group_prefix ESCAPE '!'
            GROUP BY u.id, u.username, u.user_realname, u.user_image
            ORDER BY MAX(p.post_date) DESC, MAX(p.id) DESC
            LIMIT 5";
    $statement = $pdo->prepare($sql);
    $statement->execute(['group_prefix' => '19!_%']);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
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

// : Sicherheitsmechanismus gegen SQL-Injection, der die Eingabe auf erlaubte Zeichen prüft. muss rein