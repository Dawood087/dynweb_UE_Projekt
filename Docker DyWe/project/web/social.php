<?php
require_once __DIR__ . '/../model/post_data.php';
?>
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Campus Feed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/hawblue.css" rel="stylesheet" />
  </head>
  <body class="bg-light" style="--bs-primary: var(--hawblue); --bs-primary-rgb: 0, 48, 93;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
      <div class="container">
        <a class="navbar-brand fw-bold" href="/">HAW Campus</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#feedNav" aria-controls="feedNav" aria-expanded="false" aria-label="Navigation öffnen">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="feedNav">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-lg-3">
            <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="/profile.php">Profil</a></li>
            <li class="nav-item"><a class="nav-link active" href="/social.php">Feed</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="container py-4">
      <section class="col-lg-9 mx-auto">
        <h1 class="h2 fw-bold text-primary mb-4">Campus Feed</h1>

        <div class="row g-4">
          <?php foreach ($posts as $post): ?>
            <div class="col-12">
              <article class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-3 p-md-4">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="/assets/img/<?php echo htmlspecialchars($post['user_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Profilbild von <?php echo htmlspecialchars($post['user_realname'], ENT_QUOTES, 'UTF-8'); ?>" class="rounded-circle" width="48" height="48" />
                    <div class="flex-grow-1">
                      <a href="?user=<?php echo urlencode($post['username']); ?>" class="text-decoration-none fw-bold text-primary">@<?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></a>
                      <div class="small text-muted"><?php echo htmlspecialchars($post['user_realname'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                  </div>

                  <?php if ($post['post_video_file'] !== ''): ?>
                    <video class="w-100 rounded" controls playsinline preload="metadata">
                      <source src="<?php echo htmlspecialchars($post['post_video_file'], ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4" />
                      Dein Browser unterstützt das Video-Tag nicht.
                    </video>
                  <?php else: ?>
                    <img src="/assets/img/Karussel1.jpeg" alt="Campus-Moment von <?php echo htmlspecialchars($post['user_realname'], ENT_QUOTES, 'UTF-8'); ?>" class="w-100 rounded" />
                  <?php endif; ?>

                  <p class="mt-3 mb-2"><?php echo htmlspecialchars($post['post_text'], ENT_QUOTES, 'UTF-8'); ?></p>
                  <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($post['post_tags'] as $tag): ?>
                      <span class="badge text-bg-light">#<?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
      <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <span>© 2026 HAW Campus Stories</span>
        <div class="d-flex flex-wrap gap-3">
          <a href="/" class="text-white-50 text-decoration-none">Start</a>
          <a href="/social.php" class="text-white-50 text-decoration-none">Feed</a>
          <a href="/impressum.php" class="text-white-50 text-decoration-none">Impressum</a>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
