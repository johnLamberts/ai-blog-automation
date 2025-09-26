<?php
// src/FreeAIEnhancer.php - Completely free AI alternatives
require_once __DIR__ . '/../config/config.php';

class AIContentEnhancer {
    private $strategy;
    
    public function __construct($strategy = 'huggingface') {
        $this->strategy = $strategy;
    }
    
    public function enhanceContent($topic, $originalContent = '') {
        switch ($this->strategy) {
            case 'huggingface':
                return $this->enhanceWithHuggingFace($topic, $originalContent);
            case 'ollama':
                return $this->enhanceWithOllama($topic, $originalContent);
            case 'groq':
                return $this->enhanceWithGroq($topic, $originalContent);
            case 'template':
                return $this->enhanceWithTemplate($topic, $originalContent);
            default:
                return $this->enhanceWithTemplate($topic, $originalContent);
        }
    }
    
    // Option 1: Hugging Face Inference API (FREE)
    private function enhanceWithHuggingFace($topic, $originalContent) {
        $apiKey = Config::HUGGINGFACE_API_KEY; // Free at huggingface.co
        
        // Using free models like microsoft/DialoGPT-medium or google/flan-t5-large
        $models = [
            'microsoft/DialoGPT-large',
            'google/flan-t5-large',
            'EleutherAI/gpt-neo-2.7B'
        ];
        
        $prompt = $this->buildPrompt($topic, $originalContent);
        
        foreach ($models as $model) {
            try {
                $response = $this->callHuggingFace($model, $prompt, $apiKey);
                if ($response) {
                    return $this->parseResponse($response, $topic);
                }
            } catch (Exception $e) {
                error_log("HuggingFace model $model failed: " . $e->getMessage());
                continue; // Try next model
            }
        }
        
        // Fallback to template method
        return $this->enhanceWithTemplate($topic, $originalContent);
    }
    
    private function callHuggingFace($model, $prompt, $apiKey) {
        $url = "https://api-inference.huggingface.co/models/$model";
        
        $data = [
            'inputs' => $prompt,
            'parameters' => [
                'max_length' => 2000,
                'temperature' => 0.7,
                'do_sample' => true
            ]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 60
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            return $decoded[0]['generated_text'] ?? null;
        }
        
        return null;
    }
    
    // Option 2: Ollama (100% FREE - Run locally)
    private function enhanceWithOllama($topic, $originalContent) {
        // Requires Ollama installed locally (ollama.ai)
        // Models: llama2, mistral, codellama, etc.
        
        $prompt = $this->buildPrompt($topic, $originalContent);
        
        $data = [
            'model' => 'llama2', // or 'mistral', 'codellama'
            'prompt' => $prompt,
            'stream' => false
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'http://localhost:11434/api/generate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 120 // Local models can be slower
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            return $this->parseResponse($decoded['response'], $topic);
        }
        
        // Fallback if Ollama not available
        return $this->enhanceWithTemplate($topic, $originalContent);
    }
    
    // Option 3: Groq (FREE tier - Very fast)
    private function enhanceWithGroq($topic, $originalContent) {
        $apiKey = Config::GROQ_API_KEY; // Free at console.groq.com
        
        $prompt = $this->buildPrompt($topic, $originalContent);
        
        $data = [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'model' => 'mixtral-8x7b-32768', // Free model
            'max_tokens' => 4000,
            'temperature' => 0.7
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.groq.com/openai/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            $content = $decoded['choices'][0]['message']['content'] ?? null;
            return $this->parseResponse($content, $topic);
        }
        
        return $this->enhanceWithTemplate($topic, $originalContent);
    }
    
    // Option 4: Template-Based Enhancement (100% FREE - No API)
    private function enhanceWithTemplate($topic, $originalContent) {
        $niche = Config::NICHE;
        $keywords = ContentSources::getKeywords($niche);
        
        // Intelligent template-based content generation
        $sections = $this->generateSections($topic, $originalContent, $keywords);
        
        $content = '';
        foreach ($sections as $section) {
            $content .= "<h2>{$section['heading']}</h2>\n";
            $content .= "<p>{$section['content']}</p>\n\n";
        }
        
        return [
            'title' => $this->enhanceTitle($topic['title']),
            'meta_description' => $this->generateMetaDescription($topic, $keywords),
            'content' => $content,
            'tags' => $this->extractTags($topic, $keywords),
            'read_time' => $this->calculateReadTime($content),
            'seo_keywords' => array_slice($keywords, 0, 5),
            'table_of_contents' => $this->generateTOC($sections),
            'source_topic' => $topic,
            'generated_at' => date('Y-m-d H:i:s'),
            'word_count' => str_word_count(strip_tags($content))
        ];
    }
    
    private function buildPrompt($topic, $originalContent) {
        $niche = Config::NICHE;
        $keywords = implode(', ', ContentSources::getKeywords($niche));
        $targetWords = Config::TARGET_WORD_COUNT;
        
        return "Write a comprehensive {$targetWords}-word blog post about: {$topic['title']}

Topic source: {$topic['source']}
Niche: {$niche}
Keywords to include: {$keywords}

Structure the content with:
- Introduction
- Main sections with H2 headings  
- Practical examples
- Conclusion with actionable takeaways

Make it professional, informative, and engaging for {$niche} professionals.

" . (!empty($originalContent) ? "Reference content: " . substr($originalContent, 0, 500) . "..." : "");
    }
    
    private function generateSections($topic, $originalContent, $keywords) {
        $title = $topic['title'];
        $niche = Config::NICHE;
        
        // Intelligent section generation based on topic analysis
        $sections = [];
        
        // Introduction
        $sections[] = [
            'heading' => 'Introduction',
            'content' => $this->generateIntroduction($title, $niche)
        ];
        
        // Main sections based on keywords and topic
        $mainKeywords = array_slice($keywords, 0, 3);
        foreach ($mainKeywords as $keyword) {
            $sections[] = [
                'heading' => $this->generateSectionHeading($keyword, $title),
                'content' => $this->generateSectionContent($keyword, $title, $niche)
            ];
        }
        
        // Practical section
        $sections[] = [
            'heading' => 'Practical Implementation',
            'content' => $this->generatePracticalSection($title, $niche)
        ];
        
        // Best practices
        $sections[] = [
            'heading' => 'Best Practices and Tips',
            'content' => $this->generateBestPractices($title, $niche)
        ];
        
        // Conclusion
        $sections[] = [
            'heading' => 'Conclusion',
            'content' => $this->generateConclusion($title, $niche)
        ];
        
        return $sections;
    }
    
    private function generateIntroduction($title, $niche) {
        $templates = [
            'tech' => [
                "In the rapidly evolving world of technology, {topic} has emerged as a crucial consideration for developers and tech professionals.",
                "As {niche} continues to advance, understanding {topic} becomes increasingly important for staying competitive.",
                "The landscape of {niche} is constantly changing, and {topic} represents a significant development worth exploring."
            ],
            'ai' => [
                "Artificial Intelligence continues to revolutionize how we approach {topic}, offering unprecedented opportunities.",
                "The intersection of AI and {topic} opens new possibilities for innovation and efficiency.",
                "As machine learning technologies mature, {topic} has become a focal point for AI researchers and practitioners."
            ],
            'design' => [
                "Modern design principles are evolving, and {topic} represents a significant shift in how we approach user experience.",
                "The design community has been buzzing about {topic}, and for good reason.",
                "User experience design continues to evolve, with {topic} playing an increasingly important role."
            ]
        ];
        
        $nicheTemplates = $templates[$niche] ?? $templates['tech'];
        $template = $nicheTemplates[array_rand($nicheTemplates)];
        
        return str_replace(['{topic}', '{niche}'], [$title, $niche], $template) . 
               " In this comprehensive guide, we'll explore the key concepts, practical applications, and best practices you need to know.";
    }
    
    private function generateSectionHeading($keyword, $title) {
        $patterns = [
            "Understanding {keyword} in {context}",
            "The Role of {keyword}",
            "How {keyword} Impacts {context}",
            "{keyword}: Key Concepts and Applications",
            "Implementing {keyword} Effectively"
        ];
        
        $pattern = $patterns[array_rand($patterns)];
        return str_replace(['{keyword}', '{context}'], [$keyword, $this->extractContext($title)], $pattern);
    }
    
    private function generateSectionContent($keyword, $title, $niche) {
        $baseContent = "When discussing {title}, {keyword} plays a fundamental role in {niche} development. ";
        
        $expansions = [
            "This involves several key considerations that professionals should understand.",
            "The implementation requires careful planning and attention to best practices.",
            "Modern approaches to this challenge have evolved significantly in recent years.",
            "Industry experts recommend focusing on scalable, maintainable solutions.",
            "The benefits of proper implementation include improved performance and user experience."
        ];
        
        $examples = [
            "For example, many successful projects have leveraged {keyword} to achieve better results.",
            "Consider how leading companies in {niche} have approached this challenge.",
            "Real-world applications demonstrate the practical value of these concepts.",
            "Case studies show that proper implementation can lead to significant improvements."
        ];
        
        $content = str_replace(['{title}', '{keyword}', '{niche}'], [$title, $keyword, $niche], $baseContent);
        $content .= $expansions[array_rand($expansions)] . " ";
        $content .= $examples[array_rand($examples)];
        
        return $content;
    }
    
    private function generatePracticalSection($title, $niche) {
        return "Implementing the concepts discussed in {title} requires a systematic approach. " .
               "Start by assessing your current {niche} setup and identifying areas for improvement. " .
               "Consider the following steps: First, establish clear objectives and success metrics. " .
               "Second, choose the right tools and technologies for your specific use case. " .
               "Third, implement changes incrementally to minimize risk and allow for testing. " .
               "Finally, monitor results and iterate based on feedback and performance data.";
    }
    
    private function generateBestPractices($title, $niche) {
        return "When working with {title} in {niche}, following established best practices is crucial for success. " .
               "Always prioritize maintainability and scalability in your approach. " .
               "Document your implementation decisions and maintain clear communication with your team. " .
               "Regular testing and validation help ensure reliability and performance. " .
               "Stay updated with industry trends and be prepared to adapt your strategies as new developments emerge. " .
               "Remember that the best solution is often the simplest one that meets your requirements effectively.";
    }
    
    private function generateConclusion($title, $niche) {
        return "Understanding and implementing {title} effectively can significantly impact your {niche} projects. " .
               "The key is to start with a solid foundation and build incrementally. " .
               "By following the principles and practices outlined in this guide, you'll be well-equipped to tackle related challenges. " .
               "Remember that technology and best practices continue to evolve, so staying informed and adaptable is essential. " .
               "Take the time to experiment with these concepts in your own projects and see how they can benefit your work.";
    }
    
    private function extractContext($title) {
        // Extract meaningful context from title
        $words = explode(' ', strtolower($title));
        $contextWords = array_diff($words, ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by']);
        return implode(' ', array_slice($contextWords, 0, 3));
    }
    
    private function enhanceTitle($originalTitle) {
        $prefixes = [
            'The Complete Guide to',
            'Understanding',
            'Mastering',
            'A Deep Dive into',
            'Everything You Need to Know About',
            'The Ultimate Guide to'
        ];
        
        // Only enhance if title is short
        if (strlen($originalTitle) < 50) {
            $prefix = $prefixes[array_rand($prefixes)];
            return $prefix . ' ' . $originalTitle;
        }
        
        return $originalTitle;
    }
    
    private function generateMetaDescription($topic, $keywords) {
        $template = "Explore {title} and learn about {keywords}. Comprehensive guide with practical examples and best practices.";
        return str_replace(['{title}', '{keywords}'], 
                          [$topic['title'], implode(', ', array_slice($keywords, 0, 3))], 
                          $template);
    }
    
    private function extractTags($topic, $keywords) {
        $tags = array_slice($keywords, 0, 3);
        
        // Add tags based on title analysis
        $titleWords = str_word_count(strtolower($topic['title']), 1);
        foreach ($titleWords as $word) {
            if (strlen($word) > 4 && !in_array($word, $tags)) {
                $tags[] = ucfirst($word);
                if (count($tags) >= 5) break;
            }
        }
        
        return $tags;
    }
    
    private function calculateReadTime($content) {
        $wordCount = str_word_count(strip_tags($content));
        return ceil($wordCount / 200);
    }
    
    private function generateTOC($sections) {
        $toc = [];
        foreach ($sections as $index => $section) {
            $toc[] = [
                'heading' => $section['heading'],
                'id' => strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $section['heading'])),
                'level' => 2
            ];
        }
        return $toc;
    }
    
    private function parseResponse($response, $topic) {
        // Parse AI response and structure it properly
        if (!$response) {
            return $this->enhanceWithTemplate($topic, '');
        }
        
        // Try to extract JSON if present
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE && isset($parsed['title'])) {
                return $parsed;
            }
        }
        
        // Parse as plain text and structure
        return [
            'title' => $this->enhanceTitle($topic['title']),
            'meta_description' => $this->generateMetaDescription($topic, ContentSources::getKeywords(Config::NICHE)),
            'content' => $this->formatPlainTextContent($response),
            'tags' => $this->extractTags($topic, ContentSources::getKeywords(Config::NICHE)),
            'read_time' => $this->calculateReadTime($response),
            'seo_keywords' => ContentSources::getKeywords(Config::NICHE),
            'table_of_contents' => $this->generateTOCFromContent($response),
            'source_topic' => $topic,
            'generated_at' => date('Y-m-d H:i:s'),
            'word_count' => str_word_count(strip_tags($response))
        ];
    }
    
    private function formatPlainTextContent($text) {
        // Convert plain text to HTML with basic structure
        $paragraphs = explode("\n\n", $text);
        $formatted = '';
        
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) continue;
            
            // Check if it's a heading (starts with #, all caps, etc.)
            if (preg_match('/^(#{1,3})\s*(.+)/', $paragraph, $matches)) {
                $level = strlen($matches[1]);
                $heading = trim($matches[2]);
                $formatted .= "<h{$level}>{$heading}</h{$level}>\n\n";
            } elseif (preg_match('/^[A-Z][A-Z\s]+:/', $paragraph)) {
                // All caps heading
                $formatted .= "<h2>" . ucwords(strtolower($paragraph)) . "</h2>\n\n";
            } else {
                $formatted .= "<p>{$paragraph}</p>\n\n";
            }
        }
        
        return $formatted;
    }
    
    private function generateTOCFromContent($content) {
        $toc = [];
        if (preg_match_all('/^(#{1,3})\s+(.+)$/m', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $level = strlen($match[1]);
                $heading = trim($match[2]);
                $id = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $heading));
                
                $toc[] = [
                    'heading' => $heading,
                    'id' => $id,
                    'level' => $level
                ];
            }
        }
        return $toc;
    }
    
    public function generateImagePrompts($enhancedContent) {
        // Simple fallback image prompt generation
        $title = $enhancedContent['title'];
        $tags = $enhancedContent['tags'];
        
        return [
            'featured_image' => "Modern, professional illustration representing {$title}, clean design, tech-focused",
            'supporting_images' => [
                "Infographic or diagram related to " . implode(', ', array_slice($tags, 0, 2)),
                "Abstract, modern illustration with tech theme, blue and gray colors"
            ]
        ];
    }
}
