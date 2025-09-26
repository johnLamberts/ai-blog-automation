<?php
// src/ImageGenerator.php
require_once __DIR__ . '/../config/config.php';

class ImageGenerator {
    private $unsplashKey;
    private $outputDir;
    
    public function __construct() {
        $this->unsplashKey = Config::UNSPLASH_ACCESS_KEY;
        $this->outputDir = Config::OUTPUT_DIR . 'images/';
        
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }
    
    public function generateImages($imagePrompts, $blogData) {
        $images = [
            'featured' => null,
            'supporting' => []
        ];
        
        try {
            // Try to get featured image
            $images['featured'] = $this->getImage(
                $imagePrompts['featured_image'],
                'featured_' . $this->slugify($blogData['title'])
            );
            
            // Get supporting images
            foreach ($imagePrompts['supporting_images'] as $index => $prompt) {
                $image = $this->getImage(
                    $prompt,
                    'support_' . ($index + 1) . '_' . $this->slugify($blogData['title'])
                );
                
                if ($image) {
                    $images['supporting'][] = $image;
                }
            }
            
            // If we don't have enough images, generate placeholders
            $images = $this->ensureMinimumImages($images, $blogData);
            
        } catch (Exception $e) {
            error_log("Image generation failed: " . $e->getMessage());
            $images = $this->createPlaceholderImages($blogData);
        }
        
        return $images;
    }
    
    private function getImage($prompt, $filename) {
        // Try multiple strategies for getting images
        
        // Strategy 1: Unsplash search based on prompt keywords
        $image = $this->searchUnsplash($prompt, $filename);
        if ($image) return $image;
        
        // Strategy 2: Generate CSS-based placeholder
        return $this->createCSSPlaceholder($prompt, $filename);
    }
    
    private function searchUnsplash($prompt, $filename) {
        if (empty($this->unsplashKey)) {
            return null;
        }
        
        // Extract keywords from prompt for search
        $keywords = $this->extractKeywordsFromPrompt($prompt);
        $query = implode(' ', array_slice($keywords, 0, 3)); // Use first 3 keywords
        
        $url = "https://api.unsplash.com/search/photos?" . http_build_query([
            'query' => $query,
            'per_page' => 1,
            'orientation' => 'landscape',
            'content_filter' => 'high'
        ]);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Client-ID ' . $this->unsplashKey,
                'Accept-Version: v1'
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("Unsplash API error: HTTP $httpCode");
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['results']) || empty($data['results'])) {
            return null;
        }
        
        $photo = $data['results'][0];
        $imageUrl = $photo['urls']['regular']; // 1080px width
        
        // Download and save the image
        $imageData = file_get_contents($imageUrl);
        if ($imageData === false) {
            return null;
        }
        
        $extension = 'jpg';
        $localPath = $this->outputDir . $filename . '.' . $extension;
        
        if (file_put_contents($localPath, $imageData)) {
            return [
                'path' => $localPath,
                'filename' => $filename . '.' . $extension,
                'url' => $imageUrl,
                'alt' => $photo['alt_description'] ?? $filename,
                'source' => 'unsplash',
                'attribution' => [
                    'photographer' => $photo['user']['name'],
                    'photographer_url' => $photo['user']['links']['html'],
                    'unsplash_url' => $photo['links']['html']
                ]
            ];
        }
        
        return null;
    }
    
    private function createCSSPlaceholder($prompt, $filename) {
        // Extract colors and themes from prompt
        $colors = $this->extractColorsFromPrompt($prompt);
        $theme = $this->extractThemeFromPrompt($prompt);
        
        // Generate SVG placeholder
        $svg = $this->generateSVGPlaceholder($colors, $theme, $prompt);
        
        $svgPath = $this->outputDir . $filename . '.svg';
        
        if (file_put_contents($svgPath, $svg)) {
            return [
                'path' => $svgPath,
                'filename' => $filename . '.svg',
                'url' => $svgPath,
                'alt' => $this->generateAltText($prompt),
                'source' => 'generated',
                'type' => 'svg'
            ];
        }
        
        return null;
    }
    
    private function generateSVGPlaceholder($colors, $theme, $prompt) {
        $width = 1200;
        $height = 600;
        
        // Base colors
        $primary = $colors['primary'] ?? '#0090ff';
        $secondary = $colors['secondary'] ?? '#f0f0f3';
        $accent = $colors['accent'] ?? '#46a758';
        
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:' . $primary . ';stop-opacity:0.1"/>
            <stop offset="100%" style="stop-color:' . $accent . ';stop-opacity:0.05"/>
        </linearGradient>
        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="' . $secondary . '" stroke-width="1" opacity="0.3"/>
        </pattern>
    </defs>
    
    <rect width="100%" height="100%" fill="url(#bg)"/>
    <rect width="100%" height="100%" fill="url(#grid)"/>
    ';
    
        // Add theme-specific elements
        switch ($theme) {
            case 'tech':
                $svg .= $this->addTechElements($width, $height, $colors);
                break;
            case 'ai':
                $svg .= $this->addAIElements($width, $height, $colors);
                break;
            case 'design':
                $svg .= $this->addDesignElements($width, $height, $colors);
                break;
            default:
                $svg .= $this->addGenericElements($width, $height, $colors);
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }
    
    private function addTechElements($width, $height, $colors) {
        return '
        <!-- Code brackets -->
        <g transform="translate(' . ($width/2 - 100) . ',' . ($height/2 - 50) . ')">
            <path d="M0,0 L-30,50 L0,100" stroke="' . $colors['primary'] . '" stroke-width="4" fill="none" opacity="0.6"/>
            <path d="M200,0 L230,50 L200,100" stroke="' . $colors['primary'] . '" stroke-width="4" fill="none" opacity="0.6"/>
            <circle cx="100" cy="25" r="3" fill="' . $colors['accent'] . '"/>
            <circle cx="100" cy="50" r="3" fill="' . $colors['accent'] . '"/>
            <circle cx="100" cy="75" r="3" fill="' . $colors['accent'] . '"/>
        </g>
        
        <!-- Circuit pattern -->
        <g opacity="0.3">
            <line x1="100" y1="100" x2="300" y2="100" stroke="' . $colors['primary'] . '" stroke-width="2"/>
            <line x1="300" y1="100" x2="300" y2="200" stroke="' . $colors['primary'] . '" stroke-width="2"/>
            <circle cx="300" cy="100" r="4" fill="' . $colors['accent'] . '"/>
        </g>';
    }
    
    private function addAIElements($width, $height, $colors) {
        return '
        <!-- Neural network nodes -->
        <g transform="translate(' . ($width/2 - 150) . ',' . ($height/2 - 100) . ')">
            <circle cx="0" cy="50" r="8" fill="' . $colors['primary'] . '" opacity="0.7"/>
            <circle cx="0" cy="150" r="8" fill="' . $colors['primary'] . '" opacity="0.7"/>
            <circle cx="150" cy="25" r="8" fill="' . $colors['accent'] . '" opacity="0.7"/>
            <circle cx="150" cy="100" r="8" fill="' . $colors['accent'] . '" opacity="0.7"/>
            <circle cx="150" cy="175" r="8" fill="' . $colors['accent'] . '" opacity="0.7"/>
            <circle cx="300" cy="100" r="8" fill="' . $colors['primary'] . '" opacity="0.7"/>
            
            <!-- Connections -->
            <line x1="0" y1="50" x2="150" y2="25" stroke="' . $colors['primary'] . '" stroke-width="2" opacity="0.4"/>
            <line x1="0" y1="50" x2="150" y2="100" stroke="' . $colors['primary'] . '" stroke-width="2" opacity="0.4"/>
            <line x1="0" y1="150" x2="150" y2="100" stroke="' . $colors['primary'] . '" stroke-width="2" opacity="0.4"/>
            <line x1="0" y1="150" x2="150" y2="175" stroke="' . $colors['primary'] . '" stroke-width="2" opacity="0.4"/>
            <line x1="150" y1="100" x2="300" y2="100" stroke="' . $colors['accent'] . '" stroke-width="3" opacity="0.6"/>
        </g>';
    }
    
    private function addDesignElements($width, $height, $colors) {
        return '
        <!-- Design shapes -->
        <g transform="translate(' . ($width/2 - 100) . ',' . ($height/2 - 100) . ')">
            <rect x="0" y="0" width="80" height="80" fill="' . $colors['primary'] . '" opacity="0.6" rx="8"/>
            <rect x="120" y="40" width="80" height="80" fill="' . $colors['accent'] . '" opacity="0.6" rx="40"/>
            <polygon points="50,120 100,200 0,200" fill="' . $colors['secondary'] . '" opacity="0.8"/>
        </g>';
    }
    
    private function addGenericElements($width, $height, $colors) {
        return '
        <!-- Generic abstract shapes -->
        <g transform="translate(' . ($width/2) . ',' . ($height/2) . ')">
            <circle cx="0" cy="0" r="50" fill="' . $colors['primary'] . '" opacity="0.2"/>
            <rect x="-25" y="-25" width="50" height="50" fill="' . $colors['accent'] . '" opacity="0.3" rx="5"/>
        </g>';
    }
    
    private function extractKeywordsFromPrompt($prompt) {
        // Remove common words and extract meaningful keywords
        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should', 'could', 'can', 'may', 'might', 'must'];
        
        $words = str_word_count(strtolower($prompt), 1);
        $keywords = array_diff($words, $commonWords);
        
        return array_values($keywords);
    }
    
    private function extractColorsFromPrompt($prompt) {
        $colors = [
            'primary' => '#0090ff',
            'secondary' => '#f0f0f3',
            'accent' => '#46a758'
        ];
        
        // Look for color mentions in prompt
        $colorMap = [
            'blue' => '#0090ff',
            'green' => '#46a758',
            'red' => '#e54d2e',
            'purple' => '#8b5cf6',
            'orange' => '#f59e0b',
            'gray' => '#8b8d98',
            'black' => '#1e1f24',
            'white' => '#ffffff'
        ];
        
        $prompt = strtolower($prompt);
        foreach ($colorMap as $color => $hex) {
            if (strpos($prompt, $color) !== false) {
                $colors['primary'] = $hex;
                break;
            }
        }
        
        return $colors;
    }
    
    private function extractThemeFromPrompt($prompt) {
        $prompt = strtolower($prompt);
        
        if (strpos($prompt, 'tech') !== false || strpos($prompt, 'code') !== false || strpos($prompt, 'programming') !== false) {
            return 'tech';
        }
        
        if (strpos($prompt, 'ai') !== false || strpos($prompt, 'neural') !== false || strpos($prompt, 'machine learning') !== false) {
            return 'ai';
        }
        
        if (strpos($prompt, 'design') !== false || strpos($prompt, 'ui') !== false || strpos($prompt, 'ux') !== false) {
            return 'design';
        }
        
        return 'generic';
    }
    
    private function generateAltText($prompt) {
        $keywords = $this->extractKeywordsFromPrompt($prompt);
        $firstFew = array_slice($keywords, 0, 4);
        return 'Illustration featuring ' . implode(', ', $firstFew);
    }
    
    private function ensureMinimumImages($images, $blogData) {
        // Ensure we have at least a featured image
        if (!$images['featured']) {
            $images['featured'] = $this->createCSSPlaceholder(
                'Modern illustration for ' . $blogData['title'],
                'featured_' . $this->slugify($blogData['title'])
            );
        }
        
        // Ensure we have at least 1 supporting image
        if (empty($images['supporting'])) {
            $images['supporting'][] = $this->createCSSPlaceholder(
                'Supporting visual for ' . implode(' ', array_slice($blogData['tags'], 0, 2)),
                'support_1_' . $this->slugify($blogData['title'])
            );
        }
        
        return $images;
    }
    
    private function createPlaceholderImages($blogData) {
        return [
            'featured' => $this->createCSSPlaceholder(
                'Featured image for ' . $blogData['title'],
                'featured_' . $this->slugify($blogData['title'])
            ),
            'supporting' => [
                $this->createCSSPlaceholder(
                    'Supporting image for ' . $blogData['title'],
                    'support_1_' . $this->slugify($blogData['title'])
                )
            ]
        ];
    }
    
    private function slugify($text) {
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($text)));
    }
    
    public function convertToBase64($imagePath) {
        if (!file_exists($imagePath)) {
            return null;
        }
        
        $imageData = file_get_contents($imagePath);
        $mimeType = mime_content_type($imagePath);
        
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }
}
