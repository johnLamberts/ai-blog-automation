<?php 

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/sources.php';

class ContentScraper {
  private $sources;
  private $niche;

  public function __construct($niche = 'tech') {
    $this->niche = $niche;
    $this->sources = ContentSources::getSources($niche);
  }


  public function getTrendingTopic() {
    $allTopics = [];

    foreach ($this->sources as $source => $config) {
        $topics = $this->scrapeSource($source, $config);
            $allTopics = array_merge($allTopics, $topics);
    }

    // Score and rank topics
        $scoredTopics = $this->scoreTopics($allTopics);
        
    // Return best topic
    return $scoredTopics[0] ?? null;
  }


  public function scrapeSource($sourceName, $config) {
       $topics = [];
        
        try {
            switch ($sourceName) {
                case 'hacker_news':
                    $topics = $this->scrapeHackerNews($config);
                    break;
                case 'reddit_programming':
                case 'reddit_ml':
                case 'reddit_design':
                    $topics = $this->scrapeReddit($config);
                    break;
                case 'dev_to':
                    $topics = $this->scrapeDevTo($config);
                    break;
            }
        } catch (Exception $e) {
            error_log("Error scraping $sourceName: " . $e->getMessage());
        }
        
        return $topics;
  }


   private function scrapeHackerNews($config) {
        $topics = [];
        $response = $this->makeRequest($config['url']);
        $topStories = json_decode($response, true);
        
        if (!$topStories) return $topics;
        
        // Get top 10 stories
        $topIds = array_slice($topStories, 0, 10);
        
        foreach ($topIds as $id) {
            $detailUrl = str_replace('{id}', $id, $config['detail_url']);
            $storyData = json_decode($this->makeRequest($detailUrl), true);
            
            if ($storyData && isset($storyData['title']) && $storyData['score'] > 50) {
                $topics[] = [
                    'title' => $storyData['title'],
                    'url' => $storyData['url'] ?? '',
                    'score' => $storyData['score'],
                    'comments' => $storyData['descendants'] ?? 0,
                    'source' => 'Hacker News',
                    'weight' => $config['weight'],
                    'timestamp' => $storyData['time']
                ];
            }
        }
        
        return $topics;
    }
    
    private function scrapeReddit($config) {
        $topics = [];
        $headers = [
            'User-Agent: BlogAutomation/1.0'
        ];
        
        $response = $this->makeRequest($config['url'], $headers);
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['data']['children'])) return $topics;
        
        foreach ($data['data']['children'] as $post) {
            $postData = $post['data'];
            
            if ($postData['score'] > 20 && !$postData['is_self']) {
                $topics[] = [
                    'title' => $postData['title'],
                    'url' => $postData['url'],
                    'score' => $postData['score'],
                    'comments' => $postData['num_comments'],
                    'source' => 'Reddit',
                    'weight' => $config['weight'],
                    'timestamp' => $postData['created_utc'],
                    'selftext' => $postData['selftext'] ?? ''
                ];
            }
        }
        
        return $topics;
    }
    
    private function scrapeDevTo($config) {
        $topics = [];
        $response = $this->makeRequest($config['url']);
        $articles = json_decode($response, true);
        
        if (!$articles) return $topics;
        
        foreach ($articles as $article) {
            if ($article['public_reactions_count'] > 10) {
                $topics[] = [
                    'title' => $article['title'],
                    'url' => $article['url'],
                    'score' => $article['public_reactions_count'],
                    'comments' => $article['comments_count'],
                    'source' => 'Dev.to',
                    'weight' => $config['weight'],
                    'timestamp' => strtotime($article['published_at']),
                    'description' => $article['description'] ?? '',
                    'tags' => $article['tag_list'] ?? []
                ];
            }
        }
        
        return $topics;
    }
    
    private function scoreTopics($topics) {
        $keywords = ContentSources::getKeywords($this->niche);
        $now = time();
        $dayInSeconds = 86400;
        
        foreach ($topics as &$topic) {
            $score = 0;
            
            // Base engagement score
            $score += ($topic['score'] * 0.4) + ($topic['comments'] * 0.2);
            
            // Freshness score (newer is better)
            $age = ($now - $topic['timestamp']) / $dayInSeconds;
            $freshness = max(0, 1 - ($age / 7)); // Decay over 7 days
            $score += $freshness * 50;
            
            // Keyword relevance
            $title = strtolower($topic['title']);
            foreach ($keywords as $keyword) {
                if (strpos($title, strtolower($keyword)) !== false) {
                    $score += 30;
                }
            }
            
            // Source weight
            $score *= $topic['weight'];
            
            $topic['final_score'] = $score;
        }
        
        // Sort by score descending
        usort($topics, function($a, $b) {
            return $b['final_score'] <=> $a['final_score'];
        });
        
        return $topics;
    }
    
    private function makeRequest($url, $headers = []) {
        $defaultHeaders = [
            'User-Agent: Mozilla/5.0 (compatible; BlogAutomation/1.0)',
            'Accept: application/json,text/html,application/xhtml+xml'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode for URL: $url");
        }
        
        return $response;
    }
    
    public function extractContentFromUrl($url) {
        try {
            $html = $this->makeRequest($url);
            
            // Simple content extraction (you might want to use libraries like Readability.php)
            $content = $this->extractTextFromHtml($html);
            
            return [
                'title' => $this->extractTitle($html),
                'content' => $content,
                'meta_description' => $this->extractMetaDescription($html),
                'word_count' => str_word_count($content)
            ];
        } catch (Exception $e) {
            error_log("Content extraction failed for $url: " . $e->getMessage());
            return null;
        }
    }
    
    private function extractTextFromHtml($html) {
        // Remove scripts and styles
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        
        // Extract main content (basic approach)
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        
        $xpath = new DOMXPath($dom);
        
        // Try common content selectors
        $selectors = [
            '//main',
            '//article',
            '//*[@class="content"]',
            '//*[@id="content"]',
            '//div[contains(@class, "post")]',
            '//div[contains(@class, "article")]'
        ];
        
        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                return strip_tags($nodes->item(0)->textContent);
            }
        }
        
        // Fallback: extract from body
        $body = $xpath->query('//body')->item(0);
        if ($body) {
            return strip_tags($body->textContent);
        }
        
        return strip_tags($html);
    }
    
    private function extractTitle($html) {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return trim(html_entity_decode($matches[1]));
        }
        return 'Untitled';
    }
    
    private function extractMetaDescription($html) {
        if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/is', $html, $matches)) {
            return trim(html_entity_decode($matches[1]));
        }
        return '';
    }
    
}






?>
