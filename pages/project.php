<?php
require_auth();

$user = Auth::user();
$projectId = (int)($_GET['id'] ?? 0);

if (!$projectId || !Project::belongsToUser($projectId, $user['id'])) {
    redirect('/dashboard');
}

$project = Project::getById($projectId);
$uploads = Upload::getByProject($projectId);

// Get existing designs
$db = Database::get();
$stmt = $db->prepare("SELECT * FROM designs WHERE project_id = ? ORDER BY created_at DESC");
$stmt->execute([$projectId]);
$designs = $stmt->fetchAll();

// Get pages
$stmt = $db->prepare("SELECT * FROM pages WHERE project_id = ? ORDER BY sort_order");
$stmt->execute([$projectId]);
$pages = $stmt->fetchAll();

// Get template
$stmt = $db->prepare("SELECT * FROM project_templates WHERE project_id = ?");
$stmt->execute([$projectId]);
$template = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($project['name']) ?> - MyWebsite</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css">
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
                    <li><a href="<?= url('/dashboard') ?>">← Back to Dashboard</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container project-editor">
        <div class="project-header">
            <div>
                <h1><?= e($project['name']) ?></h1>
                <?php if ($project['published']): ?>
                <a href="<?= url('/' . $project['slug']) ?>" target="_blank" class="project-url">
                    <?= url('/' . $project['slug']) ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </a>
                <?php endif; ?>
            </div>
            <div class="project-actions">
                <?php if (!empty($pages)): ?>
                <button onclick="publishProject()" class="btn-primary" id="publish-btn">
                    <?= $project['published'] ? 'Update Published Site' : 'Publish Site' ?>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Step 1: Upload Files -->
        <section class="editor-section">
            <div class="section-header">
                <h2>
                    <span class="step-badge">1</span>
                    Upload Your Files
                </h2>
                <p>Drag and drop your images and text files here</p>
            </div>
            
            <form action="<?= url('/api/upload?project_id=' . $projectId) ?>" 
                  class="dropzone" 
                  id="file-dropzone">
            </form>
            
            <div class="files-grid" id="files-grid">
                <?php foreach ($uploads as $upload): ?>
                <div class="file-item <?= $upload['file_type'] ?>" data-id="<?= $upload['id'] ?>">
                    <?php if ($upload['file_type'] === 'image'): ?>
                    <img src="<?= e($upload['url']) ?>" alt="<?= e($upload['original_name']) ?>">
                    <?php else: ?>
                    <div class="file-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <?php endif; ?>
                    <span class="file-name"><?= e($upload['original_name']) ?></span>
                    <button class="file-delete" onclick="deleteFile(<?= $upload['id'] ?>)" title="Delete">×</button>
                    <div class="file-select-overlay">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Step 2: Generate Design -->
        <section class="editor-section">
            <div class="section-header">
                <h2>
                    <span class="step-badge">2</span>
                    Generate Your Page
                </h2>
                <p>Select files and describe what you want</p>
            </div>
            
            <div class="generate-form">
                <div class="selected-files-preview" id="selected-preview">
                    <p class="placeholder">Click on files above to select them for your page</p>
                </div>
                
                <label for="style-prompt">
                    Describe your style
                    <textarea id="style-prompt" rows="3" placeholder="I'm a wedding photographer. I want something elegant and romantic with soft colors. The hero image should be prominent."><?= e($project['style_prompt'] ?? '') ?></textarea>
                </label>
                
                <label for="reference-url">
                    Reference website (optional)
                    <input type="url" id="reference-url" placeholder="https://example-portfolio.com" value="<?= e($project['reference_url'] ?? '') ?>">
                    <small>We'll use this site's style as inspiration</small>
                </label>
                
                <label for="page-title">
                    Page title
                    <input type="text" id="page-title" placeholder="Home" value="Home">
                </label>
                
                <button onclick="generateDesigns()" class="btn-primary btn-large" id="generate-btn">
                    <span class="btn-text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"></path></svg>
                        Generate 3 Designs
                    </span>
                    <span class="btn-loading" style="display: none;">
                        <span class="loading"></span>
                        Generating...
                    </span>
                </button>
            </div>
        </section>

        <!-- Step 3: Pick Design -->
        <section class="editor-section" id="designs-section" style="<?= empty($designs) ? 'display:none' : '' ?>">
            <div class="section-header">
                <h2>
                    <span class="step-badge">3</span>
                    Pick Your Favorite
                </h2>
                <p>Star the ones you like, then select your winner</p>
            </div>
            
            <div class="designs-grid" id="designs-grid">
                <?php foreach ($designs as $design): ?>
                <div class="design-card <?= $design['is_starred'] ? 'starred' : '' ?> <?= $design['is_selected'] ? 'selected' : '' ?>" data-id="<?= $design['id'] ?>">
                    <div class="design-preview">
                        <iframe srcdoc="<?= e($design['html_content']) ?>" sandbox="allow-same-origin"></iframe>
                    </div>
                    <div class="design-actions">
                        <button class="btn-star <?= $design['is_starred'] ? 'active' : '' ?>" onclick="toggleStar(<?= $design['id'] ?>)" title="Star">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="<?= $design['is_starred'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </button>
                        <button class="btn-preview" onclick="previewDesign(<?= $design['id'] ?>)" title="Preview">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                        <button class="btn-select" onclick="selectDesign(<?= $design['id'] ?>)" title="Use this design">
                            Use This
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="regenerate-section">
                <label for="feedback">
                    Not quite right? Give feedback:
                    <input type="text" id="feedback" placeholder="Make it darker, more minimalist...">
                </label>
                <button onclick="regenerateDesigns()" class="btn-secondary" id="regenerate-btn">
                    <span class="btn-text">Regenerate with Feedback</span>
                    <span class="btn-loading" style="display: none;">
                        <span class="loading"></span>
                    </span>
                </button>
            </div>
        </section>

        <!-- Pages List -->
        <?php if (!empty($pages)): ?>
        <section class="editor-section">
            <div class="section-header">
                <h2>Your Pages</h2>
            </div>
            
            <div class="pages-list">
                <?php foreach ($pages as $page): ?>
                <div class="page-item">
                    <span class="page-title"><?= e($page['title']) ?></span>
                    <span class="page-slug">/<?= e($page['slug']) ?></span>
                    <a href="<?= url('/' . $project['slug'] . ($page['slug'] === 'home' ? '' : '/' . $page['slug'])) ?>" target="_blank" class="btn-small">Preview</a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Preview Modal -->
    <dialog id="preview-modal">
        <article class="preview-article">
            <header>
                <button aria-label="Close" rel="prev" onclick="closePreview()"></button>
                <h3>Design Preview</h3>
            </header>
            <div class="preview-frame-container">
                <iframe id="preview-frame" sandbox="allow-same-origin"></iframe>
            </div>
        </article>
    </dialog>

    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
        const projectId = <?= $projectId ?>;
        const selectedFiles = new Set();
        let designs = <?= json_encode($designs) ?>;
        
        // Initialize Dropzone
        Dropzone.autoDiscover = false;
        const dropzone = new Dropzone('#file-dropzone', {
            paramName: 'file',
            maxFilesize: 10,
            acceptedFiles: 'image/*,.txt,.md',
            addRemoveLinks: false,
            dictDefaultMessage: `
                <div class="dropzone-message">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    <p>Drop files here or click to upload</p>
                    <small>Images (JPG, PNG, GIF, WebP) and text files</small>
                </div>
            `
        });
        
        dropzone.on('success', function(file, response) {
            if (response.success) {
                addFileToGrid(response.upload);
                showToast('File uploaded!', 'success');
            }
            dropzone.removeFile(file);
        });
        
        dropzone.on('error', function(file, message) {
            showToast(typeof message === 'string' ? message : message.error || 'Upload failed', 'error');
            dropzone.removeFile(file);
        });
        
        function addFileToGrid(upload) {
            const grid = document.getElementById('files-grid');
            const div = document.createElement('div');
            div.className = `file-item ${upload.file_type}`;
            div.dataset.id = upload.id;
            
            if (upload.file_type === 'image') {
                div.innerHTML = `
                    <img src="${upload.url}" alt="${upload.original_name}">
                    <span class="file-name">${upload.original_name}</span>
                    <button class="file-delete" onclick="deleteFile(${upload.id})" title="Delete">×</button>
                    <div class="file-select-overlay">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                `;
            } else {
                div.innerHTML = `
                    <div class="file-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <span class="file-name">${upload.original_name}</span>
                    <button class="file-delete" onclick="deleteFile(${upload.id})" title="Delete">×</button>
                    <div class="file-select-overlay">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                `;
            }
            
            grid.appendChild(div);
        }
        
        async function deleteFile(id) {
            if (!confirm('Delete this file?')) return;
            
            try {
                const response = await fetch(`<?= url('/api/upload') ?>?id=${id}&project_id=${projectId}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    document.querySelector(`.file-item[data-id="${id}"]`).remove();
                    selectedFiles.delete(id);
                    updateSelectedPreview();
                    showToast('File deleted', 'success');
                } else {
                    showToast(data.error || 'Delete failed', 'error');
                }
            } catch (err) {
                showToast('Connection error', 'error');
            }
        }
        
        // File selection
        document.getElementById('files-grid').addEventListener('click', (e) => {
            const fileItem = e.target.closest('.file-item');
            if (!fileItem || e.target.closest('.file-delete')) return;
            
            const id = parseInt(fileItem.dataset.id);
            
            if (selectedFiles.has(id)) {
                selectedFiles.delete(id);
                fileItem.classList.remove('selected');
            } else {
                selectedFiles.add(id);
                fileItem.classList.add('selected');
            }
            
            updateSelectedPreview();
        });
        
        function updateSelectedPreview() {
            const preview = document.getElementById('selected-preview');
            
            if (selectedFiles.size === 0) {
                preview.innerHTML = '<p class="placeholder">Click on files above to select them for your page</p>';
                return;
            }
            
            const items = [];
            selectedFiles.forEach(id => {
                const el = document.querySelector(`.file-item[data-id="${id}"]`);
                if (el) {
                    const name = el.querySelector('.file-name').textContent;
                    const isImage = el.classList.contains('image');
                    items.push(`<span class="selected-file ${isImage ? 'image' : 'text'}">${name}</span>`);
                }
            });
            
            preview.innerHTML = `<strong>${selectedFiles.size} files selected:</strong> ${items.join(' ')}`;
        }
        
        async function generateDesigns() {
            if (selectedFiles.size === 0) {
                showToast('Please select at least one file', 'error');
                return;
            }
            
            const btn = document.getElementById('generate-btn');
            setLoading(btn, true);
            
            try {
                const response = await fetch('<?= url('/api/generate') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        project_id: projectId,
                        upload_ids: Array.from(selectedFiles),
                        prompt: document.getElementById('style-prompt').value,
                        reference_url: document.getElementById('reference-url').value,
                        page_title: document.getElementById('page-title').value || 'Home'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Designs generated!', 'success');
                    designs = data.designs;
                    renderDesigns(data.designs);
                    document.getElementById('designs-section').style.display = '';
                    document.getElementById('designs-section').scrollIntoView({ behavior: 'smooth' });
                } else {
                    showToast(data.error || 'Generation failed', 'error');
                }
            } catch (err) {
                showToast('Connection error', 'error');
            } finally {
                setLoading(btn, false);
            }
        }
        
        async function regenerateDesigns() {
            const feedback = document.getElementById('feedback').value;
            if (!feedback) {
                showToast('Please provide feedback', 'error');
                return;
            }
            
            const btn = document.getElementById('regenerate-btn');
            setLoading(btn, true);
            
            try {
                const response = await fetch('<?= url('/api/generate') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        project_id: projectId,
                        upload_ids: Array.from(selectedFiles),
                        prompt: document.getElementById('style-prompt').value,
                        reference_url: document.getElementById('reference-url').value,
                        page_title: document.getElementById('page-title').value || 'Home',
                        feedback: feedback
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('New designs generated!', 'success');
                    designs = [...designs, ...data.designs];
                    renderDesigns(designs);
                    document.getElementById('feedback').value = '';
                } else {
                    showToast(data.error || 'Generation failed', 'error');
                }
            } catch (err) {
                showToast('Connection error', 'error');
            } finally {
                setLoading(btn, false);
            }
        }
        
        function renderDesigns(designList) {
            const grid = document.getElementById('designs-grid');
            grid.innerHTML = '';
            
            designList.forEach(design => {
                const div = document.createElement('div');
                div.className = `design-card ${design.is_starred ? 'starred' : ''} ${design.is_selected ? 'selected' : ''}`;
                div.dataset.id = design.id;
                div.innerHTML = `
                    <div class="design-preview">
                        <iframe srcdoc="${escapeHtml(design.html_content)}" sandbox="allow-same-origin"></iframe>
                    </div>
                    <div class="design-actions">
                        <button class="btn-star ${design.is_starred ? 'active' : ''}" onclick="toggleStar(${design.id})" title="Star">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="${design.is_starred ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </button>
                        <button class="btn-preview" onclick="previewDesign(${design.id})" title="Preview">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                        <button class="btn-select" onclick="selectDesign(${design.id})" title="Use this design">
                            Use This
                        </button>
                    </div>
                `;
                grid.appendChild(div);
            });
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        async function toggleStar(id) {
            try {
                const response = await fetch('<?= url('/api/designs') ?>', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        project_id: projectId,
                        action: 'toggle_star'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const card = document.querySelector(`.design-card[data-id="${id}"]`);
                    const btn = card.querySelector('.btn-star');
                    const svg = btn.querySelector('svg');
                    
                    if (data.design.is_starred) {
                        card.classList.add('starred');
                        btn.classList.add('active');
                        svg.setAttribute('fill', 'currentColor');
                    } else {
                        card.classList.remove('starred');
                        btn.classList.remove('active');
                        svg.setAttribute('fill', 'none');
                    }
                    
                    // Update local data
                    const design = designs.find(d => d.id === id);
                    if (design) design.is_starred = data.design.is_starred;
                }
            } catch (err) {
                showToast('Error updating star', 'error');
            }
        }
        
        function previewDesign(id) {
            const design = designs.find(d => d.id === id);
            if (!design) return;
            
            const modal = document.getElementById('preview-modal');
            const frame = document.getElementById('preview-frame');
            frame.srcdoc = design.html_content;
            modal.showModal();
        }
        
        function closePreview() {
            document.getElementById('preview-modal').close();
        }
        
        async function selectDesign(id) {
            const pageTitle = document.getElementById('page-title').value || 'Home';
            
            if (!confirm(`Use this design for "${pageTitle}"?`)) return;
            
            try {
                const response = await fetch('<?= url('/api/designs') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        project_id: projectId,
                        page_title: pageTitle
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Page created! Reloading...', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || 'Selection failed', 'error');
                }
            } catch (err) {
                showToast('Connection error', 'error');
            }
        }
        
        async function publishProject() {
            const btn = document.getElementById('publish-btn');
            
            try {
                const response = await fetch('<?= url('/api/publish') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ project_id: projectId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Site published!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || 'Publish failed', 'error');
                }
            } catch (err) {
                showToast('Connection error', 'error');
            }
        }
        
        // Close preview modal on outside click
        document.getElementById('preview-modal').addEventListener('click', (e) => {
            if (e.target.id === 'preview-modal') {
                closePreview();
            }
        });
    </script>
</body>
</html>

