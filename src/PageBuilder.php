<?php
/**
 * Page Builder - Extract and assemble page parts
 */
class PageBuilder {
    
    public static function extractParts(string $html): array {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $header = '';
        $main = '';
        $footer = '';
        $css = '';
        
        // Extract CSS from style tags
        $styles = $dom->getElementsByTagName('style');
        foreach ($styles as $style) {
            $css .= $style->textContent . "\n";
        }
        
        // Extract header
        $headers = $dom->getElementsByTagName('header');
        if ($headers->length > 0) {
            $header = $dom->saveHTML($headers->item(0));
        }
        
        // Extract main
        $mains = $dom->getElementsByTagName('main');
        if ($mains->length > 0) {
            $main = $dom->saveHTML($mains->item(0));
        } else {
            // Fallback: try to get body content minus header/footer
            $bodies = $dom->getElementsByTagName('body');
            if ($bodies->length > 0) {
                $body = $bodies->item(0);
                $main = '<main>';
                foreach ($body->childNodes as $child) {
                    $nodeName = strtolower($child->nodeName);
                    if ($nodeName !== 'header' && $nodeName !== 'footer' && $nodeName !== '#text') {
                        $main .= $dom->saveHTML($child);
                    }
                }
                $main .= '</main>';
            }
        }
        
        // Extract footer
        $footers = $dom->getElementsByTagName('footer');
        if ($footers->length > 0) {
            $footer = $dom->saveHTML($footers->item(0));
        }
        
        return [
            'header' => $header,
            'main' => $main,
            'footer' => $footer,
            'css' => $css
        ];
    }
    
    public static function assemblePage(array $template, array $page, array $project, array $allPages): string {
        $title = htmlspecialchars($page['title']) . ' - ' . htmlspecialchars($project['name']);
        
        // Build navigation
        $nav = self::buildNavigation($allPages, $page['slug'], $project['slug']);
        
        // Inject navigation into header
        $header = $template['header_html'];
        if (str_contains($header, '</header>')) {
            // Try to inject nav before closing header
            if (!str_contains($header, '<nav')) {
                $header = str_replace('</header>', $nav . '</header>', $header);
            }
        }
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
{$template['global_css']}
    </style>
</head>
<body>
    {$header}
    {$page['content_html']}
    {$template['footer_html']}
</body>
</html>
HTML;
        
        return $html;
    }
    
    public static function buildNavigation(array $pages, string $currentSlug, string $projectSlug): string {
        if (count($pages) <= 1) {
            return '';
        }
        
        $baseUrl = url('/' . $projectSlug);
        $items = [];
        
        foreach ($pages as $page) {
            $href = $page['slug'] === 'home' ? $baseUrl : $baseUrl . '/' . $page['slug'];
            $active = $page['slug'] === $currentSlug ? ' class="active"' : '';
            $title = htmlspecialchars($page['title']);
            $items[] = "<li{$active}><a href=\"{$href}\">{$title}</a></li>";
        }
        
        return '<nav class="portfolio-nav"><ul>' . implode('', $items) . '</ul></nav>';
    }
    
    public static function generatePageSlug(string $title): string {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        if (empty($slug)) {
            $slug = 'page';
        }
        
        return $slug;
    }
}

