<?php
require_guest();
$error = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyWebsite</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
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
                    <li><a href="<?= url('/register') ?>">Create Account</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="auth-container">
            <article class="card">
                <header>
                    <h2>Welcome Back</h2>
                    <p>Sign in to manage your portfolios</p>
                </header>
                
                <form id="login-form">
                    <?= csrf_field() ?>
                    
                    <label for="email">
                        Email
                        <input type="email" id="email" name="email" placeholder="you@example.com" required autofocus>
                    </label>
                    
                    <label for="password">
                        Password
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </label>
                    
                    <button type="submit" class="btn-full">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loading" style="display: none;">
                            <span class="loading"></span>
                        </span>
                    </button>
                </form>
                
                <footer>
                    <p>Don't have an account? <a href="<?= url('/register') ?>">Create one</a></p>
                </footer>
            </article>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            
            setLoading(btn, true);
            
            try {
                const response = await fetch('<?= url('/api/auth?action=login') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: form.email.value,
                        password: form.password.value
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Welcome back!', 'success');
                    setTimeout(() => window.location.href = data.redirect, 500);
                } else {
                    showToast(data.error || 'Login failed', 'error');
                }
            } catch (err) {
                showToast('Connection error', 'error');
            } finally {
                setLoading(btn, false);
            }
        });
    </script>
</body>
</html>

