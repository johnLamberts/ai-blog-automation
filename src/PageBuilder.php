<?php
// src/PageBuilder.php
require_once __DIR__ . '/../config/config.php';

class PageBuilder {
    private $templatePath;
    private $outputDir;
    
    public function __construct() {
        $this->templatePath = __DIR__ . '/../templates/radix-template.php';
        $this->outputDir = Config::OUTPUT_DIR;
        
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }
    
    public function buildPage($blogData, $images) {
        // Load the template
        $template = $this->loadTemplate();
        
        // Prepare all data for template replacement
        $templateData = $this->prepareTemplateData($blogData, $images);
        
        // Replace placeholders in template
        $finalHtml = $this->replacePlaceholders($template, $templateData);
        
        // Generate filename
        $filename = $this->generateFilename($blogData['title']);
        $filepath = $this->outputDir . $filename;
        
        // Save the file
        if (file_put_contents($filepath, $finalHtml)) {
            return [
                'filepath' => $filepath,
                'filename' => $filename,
                'filesize' => filesize($filepath),
                'url' => Config::BASE_URL . 'output/' . $filename,
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return null;
    }
    
    private function loadTemplate() {
        if (file_exists($this->templatePath)) {
            return file_get_contents($this->templatePath);
        }
        
        // Fallback: use the embedded template from our artifact
        return $this->getEmbeddedTemplate();
    }
    
    private function getEmbeddedTemplate() {
        // This would be the full HTML template from our artifact
        // For brevity, I'll show the structure - you'd copy the full template here
        return '<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{BLOG_TITLE}} | Daily Tech Insights</title>
    <meta name="description" content="{{META_DESCRIPTION}}">
    <!-- ... rest of the template from the artifact ... -->
</head>
<body>
    <!-- ... full template content ... -->
</body>
</html>';
    }
    
    private function prepareTemplateData($blogData, $images) {
        // Prepare images with base64 encoding for embedded HTML
        $featuredImageData = '';
        if ($images['featured']) {
            if ($images['featured']['source'] === 'generated') {
                // For SVG, embed directly
                $featuredImageData = file_get_contents($images['featured']['path']);
            } else {
                // For external images, use URL or convert to base64
                $featuredImageData = $images['featured']['url'];
            }
        }
        
        // Prepare supporting images
        $supportingImagesHtml = '';
        foreach ($images['supporting'] as $index => $image) {
            if ($image['source'] === 'generated') {
                $imageData = file_get_contents($image['path']);
                $supportingImagesHtml .= '<img src="data:image/svg+xml;base64,' . base64_encode($imageData) . '" alt="' . htmlspecialchars($image['alt']) . '" class="supporting-image">' . "\n";
            } else {
                $supportingImagesHtml .= '<img src="' . htmlspecialchars($image['url']) . '" alt="' . htmlspecialchars($image['alt']) . '" class="supporting-image">' . "\n";
            }
        }
        
        // Generate table of contents HTML
        $tocHtml = '';
        foreach ($blogData['table_of_contents'] as $item) {
            $indent = str_repeat('    ', ($item['level'] - 1) * 2);
            $tocHtml .= $indent . '<li><a href="#' . $item['id'] . '">' . htmlspecialchars($item['heading']) . '</a></li>' . "\n";
        }
        
        // Generate tags HTML
        $tagsHtml = '';
        foreach ($blogData['tags'] as $tag) {
            $tagsHtml .= '<a href="#" class="tag">' . htmlspecialchars($tag) . '</a>' . "\n";
        }
        
        // Prepare content with proper IDs for table of contents
        $contentWithIds = $this->addIdsToContent($blogData['content'], $blogData['table_of_contents']);
        
        return [
            'BLOG_TITLE' => htmlspecialchars($blogData['title']),
            'META_DESCRIPTION' => htmlspecialchars($blogData['meta_description']),
            'KEYWORDS' => htmlspecialchars(implode(', ', $blogData['seo_keywords'])),
            'FEATURED_IMAGE' => $featuredImageData,
            'BLOG_DESCRIPTION' => htmlspecialchars($blogData['meta_description']),
            'BLOG_CONTENT' => $contentWithIds . $supportingImagesHtml,
            'TABLE_OF_CONTENTS' => $tocHtml,
            'TAGS' => $tagsHtml,
            'PUBLISH_DATE' => date('F j, Y'),
            'READ_TIME' => $blogData['read_time'],
            'CATEGORY' => ucfirst(Config::NICHE),
            'GENERATION_DATE' => $blogData['generated_at'],
            'WORD_COUNT' => number_format($blogData['word_count'])
        ];
    }
    
    private function addIdsToContent($content, $toc) {
        foreach ($toc as $item) {
            $pattern = '/(<h' . $item['level'] . '[^>]*>)(' . preg_quote($item['heading'], '/') . ')(<\/h' . $item['level'] . '>)/i';
            $replacement = '$1<span id="' . $item['id'] . '"></span>$2$3';
            $content = preg_replace($pattern, $replacement, $content, 1);
        }
        
        return $content;
    }
    
    private function replacePlaceholders($template, $data) {
        foreach ($data as $placeholder => $value) {
            $template = str_replace('{{' . $placeholder . '}}', $value, $template);
        }
        
        // Clean up any remaining placeholders
        $template = preg_replace('/\{\{[^}]+\}\}/', '', $template);
        
        return $template;
    }
    
    private function generateFilename($title) {
        $slug = $this->slugify($title);
        $date = date('Y-m-d');
        return $date . '-' . $slug . '.html';
    }
    
    private function slugify($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
    
    public function optimizeForEmail($filepath) {
        $content = file_get_contents($filepath);
        
        // Inline CSS for better email compatibility
        $content = $this->inlineCSS($content);
        
        // Optimize images for email
        $content = $this->optimizeImagesForEmail($content);
        
        // Create email-optimized version
        $emailFilepath = str_replace('.html', '_email.html', $filepath);
        file_put_contents($emailFilepath, $content);
        
        return $emailFilepath;
    }
    
    private function inlineCSS($html) {
        // Extract CSS from <style> tags
        if (preg_match('/<style[^>]*>(.*?)<\/style>/s', $html, $matches)) {
            $css = $matches[1];
            
            // This is a simplified CSS inlining - in production you might want to use a library
            // For now, we'll just keep the styles in the head since most email clients support it
            return $html;
        }
        
        return $html;
    }
    
    private function optimizeImagesForEmail($html) {
        // Convert large images to smaller versions or compress them
        // Add proper alt tags and dimensions
        
        $html = preg_replace(
            '/<img([^>]*?)src="([^"]*)"([^>]*?)>/i',
            '<img$1src="$2"$3 style="max-width:100%;height:auto;display:block;">',
            $html
        );
        
        return $html;
    }
    
    public function generatePreview($filepath) {
        // Generate a preview/screenshot of the page
        // This is a placeholder - you might want to use a service like Puppeteer or similar
        
        $previewData = [
            'title' => $this->extractTitleFromFile($filepath),
            'url' => Config::BASE_URL . 'output/' . basename($filepath),
            'filesize' => $this->formatFileSize(filesize($filepath)),
            'preview_text' => $this->extractPreviewText($filepath)
        ];
        
        return $previewData;
    }
    
    private function extractTitleFromFile($filepath) {
        $content = file_get_contents($filepath);
        if (preg_match('/<title[^>]*>(.*?)<\/title>/i', $content, $matches)) {
            return html_entity_decode(strip_tags($matches[1]));
        }
        return 'Blog Post';
    }
    
    private function extractPreviewText($filepath) {
        $content = file_get_contents($filepath);
        
        // Find the first paragraph in the article content
        if (preg_match('/<p[^>]*>(.*?)<\/p>/i', $content, $matches)) {
            $text = html_entity_decode(strip_tags($matches[1]));
            return substr($text, 0, 200) . (strlen($text) > 200 ? '...' : '');
        }
        
        return 'Generated blog post content';
    }
    
    private function formatFileSize($size) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
    
    public function createPackage($filepath, $images) {
        // Create a ZIP package with the HTML file and images
        $zip = new ZipArchive();
        $packagePath = str_replace('.html', '_package.zip', $filepath);
        
        if ($zip->open($packagePath, ZipArchive::CREATE) === TRUE) {
            // Add HTML file
            $zip->addFile($filepath, basename($filepath));
            
            // Add images
            foreach (array_merge([$images['featured']], $images['supporting']) as $image) {
                if ($image && file_exists($image['path'])) {
                    $zip->addFile($image['path'], 'images/' . $image['filename']);
                }
            }
            
            $zip->close();
            
            return $packagePath;
        }
        
        return null;
    }
}
