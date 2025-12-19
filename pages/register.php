<?php
require_guest();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - MyWebsite</title>
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
                    <li><a href="<?= url('/login') ?>">Sign In</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="auth-container">
            <article class="card">
                <header>
                    <h2>Create Your Account</h2>
                    <p>Start building your portfolio in minutes</p>
                </header>
                
                <form id="register-form">
                    <?= csrf_field() ?>
                    
                    <label for="name">
                        Your Name
                        <input type="text" id="name" name="name" placeholder="Maria Schmidt" required autofocus>
                    </label>
                    
                    <label for="email">
                        Email
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </label>
                    
                    <label for="password">
                        Password
                        <input type="password" id="password" name="password" placeholder="At least 6 characters" minlength="6" required>
                    </label>
                    
                    <button type="submit" class="btn-full">
                        <span class="btn-text">Create Account</span>
                        <span class="btn-loading" style="display: none;">
                            <span class="loading"></span>
                        </span>
                    </button>
                </form>
                
                <footer>
                    <p>Already have an account? <a href="<?= url('/login') ?>">Sign in</a></p>
                </footer>
            </article>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            
            setLoading(btn, true);
            
            try {
                const response = await fetch('<?= url('/api/auth?action=register') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: form.name.value,
                        email: form.email.value,
                        password: form.password.value
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Account created! Redirecting...', 'success');
                    setTimeout(() => window.location.href = data.redirect, 500);
                } else {
                    showToast(data.error || 'Registration failed', 'error');
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

