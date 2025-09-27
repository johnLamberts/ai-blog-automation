<?php
// src/PageBuilder.php - Updated with proper Radix template
require_once __DIR__ . '/../config/config.php';

class PageBuilder {
    private $outputDir;
    
    public function __construct() {
        $this->outputDir = Config::OUTPUT_DIR;
        
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }
    
    public function buildPage($blogData, $images) {
        // Get the Radix template
        $template = $this->getRadixTemplate();
        
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
                'filesize' => $this->formatFileSize(filesize($filepath)),
                'url' => Config::BASE_URL . 'output/' . $filename,
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return null;
    }
    
    private function getRadixTemplate() {
        return '<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{BLOG_TITLE}} | Daily Tech Insights</title>
    <meta name="description" content="{{META_DESCRIPTION}}">
    <meta name="keywords" content="{{KEYWORDS}}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{BLOG_TITLE}}">
    <meta property="og:description" content="{{META_DESCRIPTION}}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{CURRENT_URL}}">
    
    <style>
        :root {
            --gray-1: #fcfcfd;
            --gray-2: #f9f9fb;
            --gray-3: #f0f0f3;
            --gray-4: #e8e8ec;
            --gray-5: #e1e1e6;
            --gray-6: #d9d9e0;
            --gray-7: #cecfd6;
            --gray-8: #b8bac7;
            --gray-9: #8b8d98;
            --gray-10: #80838d;
            --gray-11: #62656d;
            --gray-12: #1e1f24;
            
            --blue-9: #0090ff;
            --blue-10: #0588f0;
            --blue-11: #0d74ce;
            
            --green-9: #46a758;
            --green-10: #3e9b4f;
            
            --font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
        }
        
        [data-theme="dark"] {
            --gray-1: #0d0e11;
            --gray-2: #12131a;
            --gray-3: #1c1d26;
            --gray-4: #24252e;
            --gray-5: #2b2d37;
            --gray-6: #34363f;
            --gray-7: #3e4049;
            --gray-8: #504f57;
            --gray-9: #706f78;
            --gray-10: #7c7b84;
            --gray-11: #b5b4bd;
            --gray-12: #eeeef0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family);
            background-color: var(--gray-1);
            color: var(--gray-12);
            line-height: 1.6;
            font-size: 15px;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background-color: var(--gray-2);
            border-right: 1px solid var(--gray-6);
            padding: 24px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            min-width: 0;
        }
        
        .header {
            background-color: var(--gray-1);
            border-bottom: 1px solid var(--gray-6);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .logo {
            font-weight: 600;
            font-size: 16px;
            color: var(--gray-12);
            text-decoration: none;
        }
        
        .header-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .theme-toggle, .menu-toggle {
            background: var(--gray-3);
            border: 1px solid var(--gray-6);
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            color: var(--gray-11);
            transition: all 0.2s ease;
            font-size: 16px;
        }
        
        .theme-toggle:hover, .menu-toggle:hover {
            background: var(--gray-4);
            color: var(--gray-12);
        }
        
        .menu-toggle {
            display: none;
        }
        
        .generation-info {
            background: var(--gray-3);
            border: 1px solid var(--gray-6);
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 12px;
            color: var(--gray-10);
            text-align: center;
        }
        
        .nav-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-menu li {
            margin: 2px 0;
        }
        
        .nav-menu a {
            color: var(--gray-11);
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 6px;
            display: block;
            font-size: 14px;
            transition: all 0.2s ease;
            line-height: 1.4;
        }
        
        .nav-menu a:hover {
            background: var(--gray-4);
            color: var(--gray-12);
        }
        
        .nav-menu a.active {
            background: var(--blue-9);
            color: white;
        }
        
        .nav-menu .nav-level-2 {
            padding-left: 24px;
        }
        
        .nav-menu .nav-level-3 {
            padding-left: 36px;
        }
        
        .article-header {
            padding: 48px 32px 32px;
            border-bottom: 1px solid var(--gray-6);
            background: var(--gray-1);
        }
        
        .article-meta {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
            font-size: 14px;
            color: var(--gray-11);
            flex-wrap: wrap;
        }
        
        .article-title {
            font-size: 36px;
            font-weight: 700;
            color: var(--gray-12);
            margin-bottom: 16px;
            line-height: 1.2;
        }
        
        .article-description {
            font-size: 18px;
            color: var(--gray-11);
            max-width: 600px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        
        .tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .tag {
            background: var(--gray-3);
            color: var(--gray-11);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid var(--gray-6);
            transition: all 0.2s ease;
        }
        
        .tag:hover {
            background: var(--gray-4);
            color: var(--gray-12);
        }
        
        .article-content {
            padding: 48px 32px;
            max-width: 768px;
        }
        
        .featured-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 32px;
            border: 1px solid var(--gray-6);
        }
        
        .article-content h1,
        .article-content h2,
        .article-content h3 {
            margin-top: 48px;
            margin-bottom: 16px;
            font-weight: 600;
            color: var(--gray-12);
            scroll-margin-top: 100px;
            position: relative;
        }
        
        .article-content h1:first-child,
        .article-content h2:first-child,
        .article-content h3:first-child {
            margin-top: 0;
        }
        
        .article-content h1 {
            font-size: 28px;
            border-bottom: 1px solid var(--gray-6);
            padding-bottom: 12px;
        }
        
        .article-content h2 {
            font-size: 24px;
        }
        
        .article-content h3 {
            font-size: 20px;
        }
        
        .article-content p {
            margin-bottom: 24px;
            color: var(--gray-11);
            font-size: 16px;
            line-height: 1.7;
        }
        
        .article-content ul,
        .article-content ol {
            margin-bottom: 24px;
            padding-left: 24px;
            color: var(--gray-11);
        }
        
        .article-content li {
            margin-bottom: 8px;
            line-height: 1.6;
        }
        
        .article-content pre {
            background: var(--gray-3);
            border: 1px solid var(--gray-6);
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
            overflow-x: auto;
            font-family: "SF Mono", Monaco, "Inconsolata", "Roboto Mono", monospace;
            font-size: 14px;
        }
        
        .article-content code {
            background: var(--gray-3);
            border: 1px solid var(--gray-6);
            border-radius: 4px;
            padding: 2px 6px;
            font-family: "SF Mono", Monaco, "Inconsolata", "Roboto Mono", monospace;
            font-size: 13px;
            color: var(--gray-12);
        }
        
        .article-content pre code {
            background: none;
            border: none;
            padding: 0;
        }
        
        .article-content blockquote {
            border-left: 3px solid var(--blue-9);
            margin: 24px 0;
            padding: 16px 24px;
            background: var(--gray-2);
            border-radius: 0 6px 6px 0;
            font-style: italic;
            color: var(--gray-11);
        }
        
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            margin: 24px 0;
            border: 1px solid var(--gray-6);
        }
        
        .toc {
            background: var(--gray-2);
            border: 1px solid var(--gray-6);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 32px;
        }
        
        .toc h3 {
            margin: 0 0 12px 0 !important;
            font-size: 16px;
            font-weight: 600;
        }
        
 
    }: 600;
        }
        
        .toc ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .toc li {
            margin: 8px 0;
        }
        
        .toc a {
            color: var(--gray-11);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s ease;
        }
        
        .toc a:hover {
            color: var(--blue-9);
        }
        
        .nav-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-menu li {
            margin: 4px 0;
        }
        
        .nav-menu a {
            color: var(--gray-11);
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            display: block;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .nav-menu a:hover {
            background: var(--gray-4);
            color: var(--gray-12);
        }
        
        .nav-menu a.active {
            background: var(--blue-9);
            color: white;
        }
        
        .footer {
            border-top: 1px solid var(--gray-6);
            padding: 32px;
            text-align: center;
            color: var(--gray-10);
            font-size: 14px;
            margin-top: 64px;
        }
        
        .tag {
            background: var(--gray-3);
            color: var(--gray-11);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid var(--gray-6);
            transition: all 0.2s ease;
            display: inline-block;
            margin-right: 8px;
            margin-bottom: 8px;
        }
        
        .tag:hover {
            background: var(--gray-4);
            color: var(--gray-12);
        }
        
        .tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        
        .supporting-image {
            width: 100%;
            max-width: 500px;
            height: auto;
            margin: 32px auto;
            display: block;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1000;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .article-header {
                padding: 32px 20px 24px;
            }
            
            .article-content {
                padding: 32px 20px;
            }
            
            .article-title {
                font-size: 28px;
            }
            
            .header {
                padding: 16px 20px;
            }
            
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                transform: none;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .menu-toggle {
            display: none;
            background: var(--gray-3);
            border: 1px solid var(--gray-6);
            border-radius: 6px;
            padding: 8px;
            cursor: pointer;
            color: var(--gray-11);
        }
        
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
        }
        
        .generation-info {
            background: var(--gray-2);
            border: 1px solid var(--gray-6);
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 13px;
            color: var(--gray-10);
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="generation-info">
                🤖 Generated by AI • {{GENERATION_DATE}}
            </div>
            
            <nav class="nav-menu">
                <ul>
                    <li><a href="#introduction" class="nav-link active">Introduction</a></li>
                    {{NAVIGATION_ITEMS}}
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <header class="header">
                <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
                <a href="#" class="logo">Daily Tech Insights</a>
                <button class="theme-toggle" onclick="toggleTheme()">
                    🌙
                </button>
            </header>
            
            <div class="article-header fade-in">
                <div class="article-meta">
                    <span>{{PUBLISH_DATE}}</span>
                    <span>•</span>
                    <span>{{READ_TIME}} min read</span>
                    <span>•</span>
                    <span>{{CATEGORY}}</span>
                    <span>•</span>
                    <span>{{WORD_COUNT}} words</span>
                </div>
                
                <h1 class="article-title">{{BLOG_TITLE}}</h1>
                <p class="article-description">{{BLOG_DESCRIPTION}}</p>
                
                <div class="tags">
                    {{TAGS}}
                </div>
            </div>
            
            <article class="article-content fade-in">
                {{FEATURED_IMAGE_HTML}}
                
                {{TABLE_OF_CONTENTS_HTML}}
                
                {{BLOG_CONTENT}}
            </article>
            
            <footer class="footer">
                <p>Generated with AI • Source: {{SOURCE_NAME}} • {{GENERATION_DATE}}</p>
            </footer>
        </main>
    </div>
    
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute("data-theme");
            const newTheme = currentTheme === "dark" ? "light" : "dark";
            html.setAttribute("data-theme", newTheme);
            localStorage.setItem("theme", newTheme);
            
            const themeBtn = document.querySelector(".theme-toggle");
            themeBtn.textContent = newTheme === "dark" ? "☀️" : "🌙";
        }
        
        function toggleSidebar() {
            document.querySelector(".sidebar").classList.toggle("open");
        }
        
        // Load saved theme
        const savedTheme = localStorage.getItem("theme") || "light";
        document.documentElement.setAttribute("data-theme", savedTheme);
        document.querySelector(".theme-toggle").textContent = savedTheme === "dark" ? "☀️" : "🌙";
        
        // Smooth scrolling for navigation
        document.querySelectorAll("a[href^=\\"#\\"]").forEach(anchor => {
            anchor.addEventListener("click", function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute("href"));
                if (target) {
                    target.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                    
                    // Update active navigation
                    document.querySelectorAll(".nav-menu a").forEach(link => {
                        link.classList.remove("active");
                    });
                    this.classList.add("active");
                }
            });
        });
        
        // Auto-generate table of contents and navigation
        document.addEventListener("DOMContentLoaded", function() {
            const headings = document.querySelectorAll(".article-content h1, .article-content h2, .article-content h3");
            const tocList = document.querySelector(".toc ul");
            const navList = document.querySelector(".nav-menu ul");
            
            if (headings.length > 0 && tocList) {
                headings.forEach((heading, index) => {
                    const id = `heading-${index}`;
                    heading.id = id;
                    
                    const tocLi = document.createElement("li");
                    const tocA = document.createElement("a");
                    tocA.href = `#${id}`;
                    tocA.textContent = heading.textContent;
                    tocA.style.paddingLeft = heading.tagName === "H2" ? "16px" : heading.tagName === "H3" ? "32px" : "0";
                    
                    tocLi.appendChild(tocA);
                    tocList.appendChild(tocLi);
                    
                    // Add to sidebar navigation
                    if (heading.tagName === "H2" || heading.tagName === "H3") {
                        const navLi = document.createElement("li");
                        const navA = document.createElement("a");
                        navA.href = `#${id}`;
                        navA.textContent = heading.textContent;
                        navA.className = "nav-link";
                        
                        navLi.appendChild(navA);
                        navList.appendChild(navLi);
                    }
                });
            }
        });
        
        // Intersection Observer for active navigation
        const observerOptions = {
            rootMargin: "-20% 0px -35% 0px",
            threshold: 0
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    document.querySelectorAll(".nav-menu a").forEach(link => {
                        link.classList.remove("active");
                    });
                    const activeLink = document.querySelector(`.nav-menu a[href="#${id}"]`);
                    if (activeLink) {
                        activeLink.classList.add("active");
                    }
                }
            });
        }, observerOptions);
        
        // Observe all headings
        document.querySelectorAll(".article-content h1, .article-content h2, .article-content h3").forEach(heading => {
            observer.observe(heading);
        });
    </script>
</body>
</html>';
    }
    
    private function prepareTemplateData($blogData, $images) {
        // Prepare featured image HTML
        $featuredImageHtml = '';
        if ($images['featured']) {
            if ($images['featured']['source'] === 'generated') {
                $imageContent = file_get_contents($images['featured']['path']);
                $featuredImageHtml = '<div class="featured-image" style="background: var(--gray-2); padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 32px;">' . $imageContent . '</div>';
            } else {
                $featuredImageHtml = '<img src="' . htmlspecialchars($images['featured']['url']) . '" alt="' . htmlspecialchars($images['featured']['alt']) . '" class="featured-image">';
            }
        }
        
        // Prepare supporting images HTML - insert them between sections
        $supportingImagesHtml = '';
        foreach ($images['supporting'] as $index => $image) {
            if ($image['source'] === 'generated') {
                $imageContent = file_get_contents($image['path']);
                $supportingImagesHtml .= '<div class="supporting-image" style="background: var(--gray-2); padding: 20px; text-align: center; border-radius: 8px; margin: 32px 0;">' . $imageContent . '</div>';
            } else {
                $supportingImagesHtml .= '<img src="' . htmlspecialchars($image['url']) . '" alt="' . htmlspecialchars($image['alt']) . '" class="supporting-image">';
            }
        }
        
        // Generate table of contents HTML
        $tocHtml = '';
        if (!empty($blogData['table_of_contents'])) {
            $tocHtml = '<div class="toc"><h3>Table of Contents</h3><ul>';
            foreach ($blogData['table_of_contents'] as $item) {
                $indent = ($item['level'] - 1) * 16; // 16px per level
                $tocHtml .= '<li><a href="#heading-' . $item['id'] . '" style="padding-left: ' . $indent . 'px;">' . htmlspecialchars($item['heading']) . '</a></li>';
            }
            $tocHtml .= '</ul></div>';
        }
        
        // Generate tags HTML
        $tagsHtml = '';
        if (!empty($blogData['tags'])) {
            foreach ($blogData['tags'] as $tag) {
                $tagsHtml .= '<span class="tag">' . htmlspecialchars($tag) . '</span>';
            }
        }
        
        // Prepare content with proper IDs and insert supporting images
        $contentWithIds = $this->addIdsToContent($blogData['content'], $blogData['table_of_contents']);
        $contentWithImages = $this->insertSupportingImages($contentWithIds, $supportingImagesHtml);
        
        return [
            'BLOG_TITLE' => htmlspecialchars($blogData['title']),
            'META_DESCRIPTION' => htmlspecialchars($blogData['meta_description']),
            'KEYWORDS' => htmlspecialchars(implode(', ', $blogData['seo_keywords'] ?? [])),
            'BLOG_DESCRIPTION' => htmlspecialchars($blogData['meta_description']),
            'BLOG_CONTENT' => $contentWithImages,
            'FEATURED_IMAGE_HTML' => $featuredImageHtml,
            'TABLE_OF_CONTENTS_HTML' => $tocHtml,
            'TAGS' => $tagsHtml,
            'PUBLISH_DATE' => date('F j, Y'),
            'READ_TIME' => $blogData['read_time'] ?? 5,
            'CATEGORY' => ucfirst(Config::NICHE),
            'WORD_COUNT' => number_format($blogData['word_count'] ?? 0),
            'GENERATION_DATE' => date('M j, Y \a\t g:i A', strtotime($blogData['generated_at'] ?? 'now')),
            'SOURCE_NAME' => $blogData['source_topic']['source'] ?? 'Unknown',
            'CURRENT_URL' => Config::BASE_URL . 'output/' . $this->generateFilename($blogData['title'])
        ];
    }
    
    private function addIdsToContent($content, $toc) {
        if (empty($toc)) {
            // If no TOC provided, generate IDs for all headings
            $content = preg_replace_callback(
                '/<(h[1-6])[^>]*>(.*?)<\/h[1-6]>/i',
                function($matches) {
                    static $counter = 0;
                    $headingTag = $matches[1];
                    $headingText = $matches[2];
                    $id = 'heading-' . $counter++;
                    return '<' . $headingTag . ' id="' . $id . '">' . $headingText . '</' . $headingTag . '>';
                },
                $content
            );
            return $content;
        }
        
        // Use provided TOC to add IDs
        $counter = 0;
        foreach ($toc as $item) {
            $headingText = preg_quote($item['heading'], '/');
            $level = $item['level'];
            $id = 'heading-' . $counter++;
            
            $pattern = '/(<h' . $level . '[^>]*>)(' . $headingText . ')(<\/h' . $level . '>)/i';
            $replacement = '$1<span id="' . $id . '"></span>$2$3';
            $content = preg_replace($pattern, $replacement, $content, 1);
        }
        
        return $content;
    }
    
    private function insertSupportingImages($content, $supportingImagesHtml) {
        if (empty($supportingImagesHtml)) {
            return $content;
        }
        
        // Split content into sections (by h2 tags)
        $sections = preg_split('/(<h2[^>]*>.*?<\/h2>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        // Insert images between sections
        $result = '';
        $imageInserted = false;
        
        foreach ($sections as $index => $section) {
            $result .= $section;
            
            // Insert image after every 2-3 sections (but not at the very beginning or end)
            if (!$imageInserted && $index > 2 && preg_match('/<h2[^>]*>/i', $section)) {
                $result .= $supportingImagesHtml;
                $imageInserted = true;
            }
        }
        
        // If no image was inserted, add it at the end
        if (!$imageInserted) {
            $result .= $supportingImagesHtml;
        }
        
        return $result;
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
    
    private function formatFileSize($size) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
}
