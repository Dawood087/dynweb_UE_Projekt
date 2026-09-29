<?php
$profile = [
  'name' => 'David Piekarski',
  'handle' => 'davidpiekarski',
  'role' => 'Student MMP',
  'location' => 'Kiel, Deutschland',
  'bio' => 'Studierender im Semester 3 des Studiengangs Multimedia Production an der HAW Kiel.',
  'followers' => 0,
  'following' => 0,
  'posts' => 5,
  'avatar' => '/assets/img/PictureOfMe.jpg',
  'stats' => [
    ['label' => 'Studiengang', 'value' => 'Multimedia Production'],
    ['label' => 'Semester', 'value' => '3. Semester'],
    ['label' => 'Interessen', 'value' => 'Design, Video Editing, Campus Life'],
  ]
];
?>
<!doctype html>
<html lang="de">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $profile['name'] ?> – Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/custom_styling.css" rel="stylesheet" />
  </head>
  <body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
      <div class="container">
        <a class="navbar-brand fw-bold" href="/">HAW Campus</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Navigation öffnen">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-lg-3">
            <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
            <li class="nav-item"><a class="nav-link active" href="/profile.php">Profil</a></li>
            <li class="nav-item"><a class="nav-link" href="/social.php">Feed</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main>
      <section class="container" style="margin-top:">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
              <div class="col-md-auto">
                <img src="<?= $profile['avatar'] ?>" alt="Profilbild von <?= $profile['name'] ?>" class="rounded-circle border border-4 border-white shadow" width="150" height="150" style="object-fit: cover;" />
              </div>
              <div class="col-md">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                  <div>
                    <h1 class="fw-bold text-primary mb-1"><?= $profile['name'] ?></h1>
                    <div class="text-muted mb-2">@<?= $profile['handle'] ?></div>
                    <div class="text-muted"><?= $profile['role'] ?> · <?= $profile['location'] ?></div>
                  </div>
                  <button class="btn btn-primary btn-lg" type="button">Folgen</button>
                </div>
                <p class="mt-3 mb-3 text-secondary"><?= $profile['bio'] ?></p>

                <div class="d-flex flex-wrap gap-4 small text-muted">
                  <span><strong class="text-dark"><?= $profile['followers'] ?></strong> Follower</span>
                  <span><strong class="text-dark"><?= $profile['following'] ?></strong> Following</span>
                  <span><strong class="text-dark"><?= $profile['posts'] ?></strong> Posts</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="container py-5">
        <div class="row g-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body">
                <h2 class="h5 fw-bold text-primary mb-3">Über mich</h2>
                <div class="list-group list-group-flush">
                  <?php foreach ($profile['stats'] as $stat): ?>
                    <div class="list-group-item px-0">
                      <small class="text-muted d-block"><?= $stat['label'] ?></small>
                      <strong><?= $stat['value'] ?></strong>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="bg-dark text-white py-4 mt-4">
      <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <span>© 2026 HAW Campus Stories</span>
        <div class="d-flex flex-wrap gap-3">
          <a href="/" class="text-white-50 text-decoration-none">Start</a>
          <a href="/social.php" class="text-white-50 text-decoration-none">Feed</a>
          <a href="/profile.php" class="text-white-50 text-decoration-none">Profil</a>
          <a href="/impressum.php" class="text-white-50 text-decoration-none">Impressum</a>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
