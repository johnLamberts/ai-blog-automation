<!DOCTYPE html>
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
    <meta property="og:image" content="{{FEATURED_IMAGE}}">
    <meta property="og:type" content="article">
    
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
            
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
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
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background-color: var(--gray-2);
            border-right: 1px solid var(--gray-6);
            padding: 24px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 0;
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
            z-index: 100;
        }
        
        .logo {
            font-weight: 600;
            font-size: 16px;
            color: var(--gray-12);
            text-decoration: none;
        }
        
        .theme-toggle {
            background: var(--gray-3);
            border: 1px solid var(--gray-6);
            border-radius: 6px;
            padding: 8px;
            cursor: pointer;
            color: var(--gray-11);
            transition: all 0.2s ease;
        }
        
        .theme-toggle:hover {
            background: var(--gray-4);
            color: var(--gray-12);
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
        }
        
        .article-content {
            padding: 48px 32px;
            max-width: 768px;
        }
        
        .featured-image {
            width: 100%;
            height: 300px;
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
            scroll-margin-top: 80px;
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
            font-family: 'SF Mono', Monaco, 'Inconsolata', 'Roboto Mono', 'Source Code Pro', monospace;
            font-size: 14px;
        }
        
        .article-content code {
            background: var(--gray-3);
            border: 1px solid var(--gray-6);
            border-radius: 4px;
            padding: 2px 6px;
            font-family: 'SF Mono', Monaco, 'Inconsolata', 'Roboto Mono', 'Source Code Pro', monospace;
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
            margin: 0 0 12px 0;
            font-size: 16px;
            font-weight: 600;
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
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
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
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <nav class="nav-menu">
                <ul>
                    <li><a href="#introduction" class="active">Introduction</a></li>
                    <li><a href="#overview">Overview</a></li>
                    <li><a href="#implementation">Implementation</a></li>
                    <li><a href="#examples">Examples</a></li>
                    <li><a href="#best-practices">Best Practices</a></li>
                    <li><a href="#conclusion">Conclusion</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <header class="header">
                <a href="#" class="logo">Daily Tech Insights</a>
                <button class="theme-toggle" onclick="toggleTheme()">
                    <svg width="16" height="16" viewBox="0 0 15 15" fill="none">
                        <path d="M2.89998 0.499976C2.89998 0.279062 2.72089 0.0999756 2.49998 0.0999756C2.27906 0.0999756 2.09998 0.279062 2.09998 0.499976V1.09998H1.49998C1.27906 1.09998 1.09998 1.27906 1.09998 1.49998C1.09998 1.72089 1.27906 1.89998 1.49998 1.89998H2.09998V2.49998C2.09998 2.72089 2.27906 2.89998 2.49998 2.89998C2.72089 2.89998 2.89998 2.72089 2.89998 2.49998V1.89998H3.49998C3.72089 1.89998 3.89998 1.72089 3.89998 1.49998C3.89998 1.27906 3.72089 1.09998 3.49998 1.09998H2.89998V0.499976Z" fill="currentColor"/>
                    </svg>
                </button>
            </header>
            
            <div class="article-header fade-in">
                <div class="article-meta">
                    <span>{{PUBLISH_DATE}}</span>
                    <span>•</span>
                    <span>{{READ_TIME}} min read</span>
                    <span>•</span>
                    <span>{{CATEGORY}}</span>
                </div>
                
                <h1 class="article-title">{{BLOG_TITLE}}</h1>
                <p class="article-description">{{BLOG_DESCRIPTION}}</p>
                
                <div class="tags">
                    {{TAGS}}
                </div>
            </div>
            
            <article class="article-content fade-in">
                {{FEATURED_IMAGE_HTML}}
                
                <div class="toc">
                    <h3>Table of Contents</h3>
                    <ul>
                        {{TABLE_OF_CONTENTS}}
                    </ul>
                </div>
                
                {{BLOG_CONTENT}}
            </article>
            
            <footer class="footer">
                <p>Generated with AI • {{GENERATION_DATE}}</p>
            </footer>
        </main>
    </div>
    
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Update active navigation
                    document.querySelectorAll('.nav-menu a').forEach(link => {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');
                }
            });
        });
        
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }
        
        // Auto-generate table of contents
        function generateTOC() {
            const headings = document.querySelectorAll('.article-content h1, .article-content h2, .article-content h3');
            const tocList = document.querySelector('.toc ul');
            
            if (tocList && headings.length === 0) {
                headings.forEach((heading, index) => {
                    const id = `heading-${index}`;
                    heading.id = id;
                    
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.href = `#${id}`;
                    a.textContent = heading.textContent;
                    a.style.paddingLeft = heading.tagName === 'H2' ? '16px' : heading.tagName === 'H3' ? '32px' : '0';
                    
                    li.appendChild(a);
                    tocList.appendChild(li);
                });
            }
        }
        
        // Initialize TOC if content is dynamic
        document.addEventListener('DOMContentLoaded', generateTOC);
    </script>
</body>
</html>
