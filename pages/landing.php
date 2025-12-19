<?php
// Redirect logged in users to dashboard
if (Auth::check()) {
    redirect('/dashboard');
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyWebsite - Create Your Portfolio Without Code</title>
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
                    <li><a href="<?= url('/login') ?>">Log in</a></li>
                    <li><a href="<?= url('/register') ?>" role="button">Get Started</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Your Portfolio,<br>Without the Hassle</h1>
            <p>Drop your photos. Describe your style. Get a beautiful website in minutes.<br>No coding required. Perfect for photographers, stylists, and creatives.</p>
            <a href="<?= url('/register') ?>" class="btn-cta">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"></path></svg>
                Create Your Portfolio
            </a>
        </div>
        <div class="hero-decoration"></div>
    </section>

    <main class="container">
        <section class="how-it-works">
            <h2>How It Works</h2>
            <p class="section-subtitle">Three simple steps to your professional portfolio</p>
            
            <div class="steps-grid">
                <article class="step-card animate-in">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    </div>
                    <h3>Upload Your Files</h3>
                    <p>Drag and drop your photos and text files. Just like Google Drive or Dropbox.</p>
                </article>
                
                <article class="step-card animate-in">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"></path></svg>
                    </div>
                    <h3>Describe Your Style</h3>
                    <p>Tell us what you want. AI generates 3 beautiful designs. Pick your favorite.</p>
                </article>
                
                <article class="step-card animate-in">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                    </div>
                    <h3>Publish & Share</h3>
                    <p>One click to publish. Share your portfolio with a simple, clean URL.</p>
                </article>
            </div>
        </section>

        <section class="features">
            <div class="feature-row">
                <div class="feature-content">
                    <h2>Built for Creatives,<br>Not Coders</h2>
                    <p>You're a photographer, stylist, artist, or designer. You want to showcase your work, not learn HTML. MyWebsite understands that.</p>
                    <ul class="feature-list">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            No coding knowledge needed
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Professional designs in minutes
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Mobile-friendly automatically
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Consistent header & footer across pages
                        </li>
                    </ul>
                </div>
                <div class="feature-visual">
                    <div class="mock-browser">
                        <div class="mock-browser-header">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="mock-browser-content">
                            <div class="mock-header"></div>
                            <div class="mock-hero"></div>
                            <div class="mock-grid">
                                <div></div><div></div><div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <h2>Ready to Showcase Your Work?</h2>
            <p>Join creatives who've already built their portfolios with MyWebsite</p>
            <a href="<?= url('/register') ?>" class="btn-cta-large">
                Start Building - It's Free
            </a>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>Made with ✦ for creatives who'd rather be creating</p>
        </div>
    </footer>

    <script>
        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.animate-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>

