<?php
/**
 * xAI API Integration
 */
class AI {
    
    public static function generate(array $params): array {
        $prompt = self::buildPrompt($params);
        
        $designs = [];
        
        // Generate 3 variations
        for ($i = 0; $i < 3; $i++) {
            $variation = self::callAPI($prompt, $i);
            if ($variation) {
                $designs[] = $variation;
            }
        }
        
        if (empty($designs)) {
            throw new Exception('Failed to generate designs');
        }
        
        return $designs;
    }
    
    private static function callAPI(string $prompt, int $variation): ?string {
        $variationHints = [
            'Create a clean, minimal design with lots of whitespace.',
            'Create a bold, dramatic design with strong visual hierarchy.',
            'Create an elegant, sophisticated design with refined typography.'
        ];
        
        $fullPrompt = $prompt . "\n\nStyle variation: " . $variationHints[$variation];
        
        $payload = [
            'model' => XAI_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => self::getSystemPrompt()
                ],
                [
                    'role' => 'user',
                    'content' => $fullPrompt
                ]
            ],
            'max_tokens' => 4000,
            'temperature' => 0.8
        ];
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . XAI_API_KEY
                ],
                'content' => json_encode($payload),
                'timeout' => 60
            ]
        ]);
        
        $response = @file_get_contents(XAI_API_URL, false, $context);
        
        if ($response === false) {
            error_log('xAI API request failed');
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            error_log('xAI API unexpected response: ' . $response);
            return null;
        }
        
        $html = $data['choices'][0]['message']['content'];
        
        // Extract HTML from response (in case it's wrapped in markdown)
        $html = self::extractHTML($html);
        
        return $html;
    }
    
    private static function getSystemPrompt(): string {
        return <<<PROMPT
You are an expert web designer creating beautiful, professional portfolio websites.

CRITICAL REQUIREMENTS:
1. Return ONLY valid HTML code - no markdown, no explanations, no code fences
2. Include all CSS in a <style> tag in the <head>
3. Structure the page with semantic HTML: <header>, <main>, <footer>
4. Make it fully responsive using CSS Grid and Flexbox
5. Use modern, professional design principles
6. Include smooth transitions and subtle animations
7. Ensure excellent typography with proper hierarchy
8. Use the exact image paths provided by the user

STRUCTURE TEMPLATE:
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio</title>
    <style>
        /* All CSS here */
    </style>
</head>
<body>
    <header>
        <!-- Logo, navigation -->
    </header>
    <main>
        <!-- Page content -->
    </main>
    <footer>
        <!-- Footer content -->
    </footer>
</body>
</html>

The header and footer will be extracted and reused across all pages, so make them generic enough to work on any page while maintaining the design aesthetic.
PROMPT;
    }
    
    public static function buildPrompt(array $params): string {
        $prompt = "Create a beautiful portfolio website page.\n\n";
        
        // Page info
        $pageTitle = $params['page_title'] ?? 'Home';
        $prompt .= "PAGE: {$pageTitle}\n\n";
        
        // User's description
        if (!empty($params['prompt'])) {
            $prompt .= "CLIENT'S VISION:\n{$params['prompt']}\n\n";
        }
        
        // Feedback for regeneration
        if (!empty($params['feedback'])) {
            $prompt .= "ADJUSTMENT REQUESTED:\n{$params['feedback']}\n\n";
        }
        
        // Reference site
        if (!empty($params['reference_url'])) {
            $prompt .= "STYLE REFERENCE: {$params['reference_url']}\n";
            $prompt .= "Take inspiration from this website's visual style, layout, and aesthetic.\n\n";
        }
        
        // Images
        if (!empty($params['images'])) {
            $prompt .= "IMAGES TO USE (use these exact paths):\n";
            foreach ($params['images'] as $img) {
                $prompt .= "- {$img['url']}\n";
            }
            $prompt .= "\n";
        }
        
        // Text content
        if (!empty($params['text_content'])) {
            $prompt .= "TEXT CONTENT TO INCORPORATE:\n";
            $prompt .= $params['text_content'] . "\n\n";
        }
        
        // If no text provided, add placeholder guidance
        if (empty($params['text_content'])) {
            $prompt .= "Use placeholder text that fits a creative portfolio (photographer, designer, artist, stylist).\n\n";
        }
        
        return $prompt;
    }
    
    private static function extractHTML(string $response): string {
        // Remove markdown code fences if present
        $response = preg_replace('/^```html?\s*/i', '', $response);
        $response = preg_replace('/```\s*$/', '', $response);
        
        // Try to find DOCTYPE or html tag
        if (preg_match('/<!DOCTYPE html>.*$/is', $response, $matches)) {
            return trim($matches[0]);
        }
        
        if (preg_match('/<html[^>]*>.*<\/html>/is', $response, $matches)) {
            return '<!DOCTYPE html>' . "\n" . trim($matches[0]);
        }
        
        // If it looks like HTML, return as-is
        if (str_contains($response, '<') && str_contains($response, '>')) {
            return trim($response);
        }
        
        // Fallback: wrap in basic HTML structure
        return self::wrapInHTML($response);
    }
    
    private static function wrapInHTML(string $content): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; line-height: 1.6; }
    </style>
</head>
<body>
    <main>
        {$content}
    </main>
</body>
</html>
HTML;
    }
}

