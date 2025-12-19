<?php
require_auth();
$user = Auth::user();
$projects = Project::getByUser($user['id']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MyWebsite</title>
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
                    <li><a href="<?= url('/dashboard') ?>" class="logo">MyWebsite</a></li>
                </ul>
                <ul>
                    <li><span class="user-name">Hi, <?= e($user['name']) ?></span></li>
                    <li><a href="<?= url('/logout') ?>" role="button" class="outline">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="dashboard-header">
            <div>
                <h1>Your Portfolios</h1>
                <p>Create and manage your portfolio websites</p>
            </div>
            <button class="btn-primary" onclick="showNewProjectModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Portfolio
            </button>
        </div>

        <?php if (empty($projects)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            </div>
            <h3>No portfolios yet</h3>
            <p>Create your first portfolio to get started</p>
            <button class="btn-primary" onclick="showNewProjectModal()">Create Your First Portfolio</button>
        </div>
        <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
            <article class="project-card" data-id="<?= $project['id'] ?>">
                <div class="project-card-header">
                    <h3><?= e($project['name']) ?></h3>
                    <?php if ($project['published']): ?>
                    <span class="badge badge-success">Published</span>
                    <?php else: ?>
                    <span class="badge badge-draft">Draft</span>
                    <?php endif; ?>
                </div>
                
                <div class="project-card-stats">
                    <span><strong><?= $project['page_count'] ?></strong> pages</span>
                    <span><strong><?= $project['file_count'] ?></strong> files</span>
                </div>
                
                <?php if ($project['description']): ?>
                <p class="project-description"><?= e($project['description']) ?></p>
                <?php endif; ?>
                
                <div class="project-card-actions">
                    <a href="<?= url('/project?id=' . $project['id']) ?>" class="btn-secondary">Edit</a>
                    <?php if ($project['published']): ?>
                    <a href="<?= url('/' . $project['slug']) ?>" target="_blank" class="btn-outline">View Site</a>
                    <?php endif; ?>
                    <button class="btn-icon btn-danger" onclick="deleteProject(<?= $project['id'] ?>, '<?= e($project['name']) ?>')" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <!-- New Project Modal -->
    <dialog id="new-project-modal">
        <article>
            <header>
                <button aria-label="Close" rel="prev" onclick="closeNewProjectModal()"></button>
                <h3>Create New Portfolio</h3>
            </header>
            <form id="new-project-form">
                <label for="project-name">
                    Portfolio Name
                    <input type="text" id="project-name" name="name" placeholder="My Photography Portfolio" required autofocus>
                </label>
                <label for="project-description">
                    Description (optional)
                    <textarea id="project-description" name="description" placeholder="A brief description of your portfolio..." rows="3"></textarea>
                </label>
                <footer>
                    <button type="button" class="secondary" onclick="closeNewProjectModal()">Cancel</button>
                    <button type="submit">
                        <span class="btn-text">Create Portfolio</span>
                        <span class="btn-loading" style="display: none;">
                            <span class="loading"></span>
                        </span>
                    </button>
                </footer>
            </form>
        </article>
    </dialog>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
        const modal = document.getElementById('new-project-modal');
        
        function showNewProjectModal() {
            modal.showModal();
            document.getElementById('project-name').focus();
        }
        
        function closeNewProjectModal() {
            modal.close();
            document.getElementById('new-project-form').reset();
        }
        
        document.getElementById('new-project-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            
            setLoading(btn, true);
            
            try {
                const response = await fetch('<?= url('/api/projects') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: form.name.value,
                        description: form.description.value
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Portfolio created!', 'success');
                    setTimeout(() => window.location.href = data.redirect, 300);
                } else {
                    showToast(data.error || 'Creation failed', 'error');
                }
            } catch (err) {
                showToast('Connection error', 'error');
            } finally {
                setLoading(btn, false);
            }
        });
        
        async function deleteProject(id, name) {
            if (!confirm(`Delete "${name}"? This cannot be undone.`)) {
                return;
            }
            
            try {
                const response = await fetch('<?= url('/api/projects') ?>?id=' + id, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Portfolio deleted', 'success');
                    document.querySelector(`.project-card[data-id="${id}"]`).remove();
                    
                    // Check if empty
                    if (document.querySelectorAll('.project-card').length === 0) {
                        location.reload();
                    }
                } else {
                    showToast(data.error || 'Delete failed', 'error');
                }
            } catch (err) {
                showToast('Connection error', 'error');
            }
        }
        
        // Close modal on outside click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeNewProjectModal();
            }
        });
    </script>
</body>
</html>

