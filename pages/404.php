<?php
$isLoggedIn = Auth::check();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - MyWebsite</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <header class="app-header">
        <div class="container">
            <nav>
                <ul>
                    <li><a href="<?= url('/') ?>" class="logo">MyWebsite</a></li>
                </ul>
                <ul>
                    <?php if ($isLoggedIn): ?>
                    <li><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
                    <?php else: ?>
                    <li><a href="<?= url('/login') ?>">Log in</a></li>
                    <li><a href="<?= url('/register') ?>" role="button">Get Started</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="error-page">
            <div class="error-code">404</div>
            <h1>Page Not Found</h1>
            <p>The page you're looking for doesn't exist or has been moved.</p>
            <a href="<?= $isLoggedIn ? url('/dashboard') : url('/') ?>" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
                Go Back Home
            </a>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>Made with ✦ for creatives who'd rather be creating</p>
        </div>
    </footer>
</body>
</html>

