# AI Blog Automation

## System Overview

This projects combiens two powerful components:
1. **n8n Automation Workflow** - Backend automation for content generation
2. **Php Web Interface** - Modern dashboard for workflow management and content viewing

## Features 

**Modern Dark Theme**: Radix UI-inspired design with sophisticated dark aesthetics
**Workflow Management**: Trigger n8n workflows directly from the web interface
**Real-time Status**: MOnitor workflow progress with live updates
**Blog Gallery**: View and download generated blog posts
**Responsive Design**: Works perfectly on desktop and mobile devices
**Database Integration**: Track all generated content and workflow history

## 🔧 Configuration Options

### Content Sources by Niche
- **Tech**: Hacker News, Reddit r/programming, Dev.to
- **AI**: Reddit r/MachineLearning, ArXiv papers
- **Design**: Dribbble, Reddit r/web_design
- **Marketing**: Configurable sources


## 📊 Daily Output
Each day you'll receive an email with:

1. **Beautiful HTML Blog Post**
   - Radix UI-inspired design
   - Dark/light mode toggle
   - Responsive layout
   - SEO optimized

2. **AI-Generated Images**
   - Featured hero image
   - Supporting visuals
   - Fallback to beautiful CSS/SVG placeholders

3. **Complete Analytics**
   - Word count and reading time
   - SEO keyword analysis
   - Topic relevance score
   - Source attribution

## 🎨 Design Features

### Radix UI Inspired Styling
- Clean typography with Inter/system fonts
- Sophisticated color palette
- Elegant spacing and shadows
- Professional code syntax highlighting
- Smooth animations and transitions

### Responsive Design
- Mobile-first approach
- Collapsible sidebar navigation
- Touch-friendly interactions
- Optimized for all screen sizes

## 🔄 Workflow Steps

### 1. Topic Research (8:59 AM)
- Scrapes multiple sources
- Analyzes trending topics
- Scores relevance to your niche
- Selects highest-scoring topic

### 2. AI Enhancement (9:00 AM)
- Expands basic topic into full article
- Adds structure with proper headings
- Optimizes for SEO
- Ensures 1500-2000 word count

### 3. Image Generation (9:02 AM)
- Creates AI prompts for visuals
- Attempts Unsplash search
- Falls back to generated SVG placeholders
- Ensures all images have alt text

### 4. Page Building (9:03 AM)
- Compiles everything into Radix-style HTML
- Embeds images and styles
- Generates table of contents
- Adds proper meta tags

### 5. Email Delivery (9:04 AM)
- Creates beautiful email template
- Attaches all files
- Includes preview and analytics
- Delivers to your inbox


## 📁 File Structure
```
blog-automation/
├── config/
│   ├── config.php          # Main configuration
│   └── sources.php         # Content source definitions
├── src/
│   ├── ContentScraper.php  # Topic research & scraping
│   ├── AIEnhancer.php      # AI content enhancement
│   ├── ImageGenerator.php  # Image generation & handling
│   ├── PageBuilder.php     # HTML page assembly
│   └── EmailSender.php     # Email delivery system
├── templates/
│   └── radix-template.php  # Base HTML template
├── assets/
│   ├── css/               # Additional stylesheets
│   ├── js/                # JavaScript files
│   └── images/            # Static images
├── output/                # Generated blog posts
│   ├── images/            # Generated images
│   └── *.html             # Daily blog files
├── logs/                  # System logs
│   ├── automation_*.log   # Daily execution logs
│   ├── success_metrics.json # Success tracking
│   └── cron.log          # Cron job logs
└── index.php             # Main execution script
```
