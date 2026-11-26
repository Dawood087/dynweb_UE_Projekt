<?php

function socialLikesAvailable(PDO $pdo): bool
{
    return $pdo->query("SHOW COLUMNS FROM post LIKE 'likes'")->fetch(PDO::FETCH_ASSOC) !== false;
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

    $conditions = [];
    $params = [];
    if ($user !== '') {
        $conditions[] = 'u.username = :user';
        $params['user'] = $user;
    } elseif ($tag !== '') {
        $conditions[] = "EXISTS (
            SELECT 1 FROM post_tag filter_pt
            JOIN tag filter_t ON filter_t.id = filter_pt.fk_tag
            WHERE filter_pt.fk_post = p.id AND filter_t.name = :tag
        )";
        $params['tag'] = $tag;
    }

    if ($search !== '') {
        $term = ltrim($search, '@#');
        $pattern = '%' . strtr($term === '' ? $search : $term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $conditions[] = "(
            p.post_text LIKE :text_search ESCAPE '!'
            OR u.username LIKE :username_search ESCAPE '!'
            OR u.user_realname LIKE :name_search ESCAPE '!'
            OR EXISTS (
                SELECT 1 FROM post_tag search_pt
                JOIN tag search_t ON search_t.id = search_pt.fk_tag
                WHERE search_pt.fk_post = p.id AND search_t.name LIKE :tag_search ESCAPE '!'
            )
        )";
        $params['text_search'] = $pattern;
        $params['username_search'] = $pattern;
        $params['name_search'] = $pattern;
        $params['tag_search'] = $pattern;
    }

    if ($conditions !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= " GROUP BY p.id, u.id, u.username, u.user_realname, u.user_image, p.post_video_file, p.post_text
              ORDER BY p.id
              LIMIT 20";
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
    return $pdo->query(
        "SELECT u.username, u.user_realname, u.user_image
         FROM post p
         JOIN user u ON u.id = p.fk_user
         GROUP BY u.id, u.username, u.user_realname, u.user_image
         ORDER BY MAX(p.post_date) DESC, MAX(p.id) DESC
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);
}

function changeSocialLike(PDO $pdo, int $postId, int $change): bool
{
    // A like must not be counted as a new post for the sidebar.
    $statement = $pdo->prepare(
        "UPDATE post SET likes = GREATEST(CAST(COALESCE(likes, 0) AS SIGNED) + :change, 0),
                         post_date = post_date
         WHERE id = :id"
    );
    $statement->execute(['change' => $change, 'id' => $postId]);
    return $statement->rowCount() === 1;
}
