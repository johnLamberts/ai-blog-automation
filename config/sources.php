
<?php 

class ContentSources {
  public static function getSources($niche = 'tech') {
        $sources = [
            'tech' => [
                'hacker_news' => [
                    'url' => 'https://hacker-news.firebaseio.com/v0/topstories.json',
                    'detail_url' => 'https://hacker-news.firebaseio.com/v0/item/{id}.json',
                    'weight' => 0.4
                ],
                'reddit_programming' => [
                    'url' => 'https://www.reddit.com/r/programming/hot.json?limit=25',
                    'weight' => 0.3
                ],
                'dev_to' => [
                    'url' => 'https://dev.to/api/articles?top=1',
                    'weight' => 0.3
                ]
            ],
            'ai' => [
                'reddit_ml' => [
                    'url' => 'https://www.reddit.com/r/MachineLearning/hot.json?limit=25',
                    'weight' => 0.5
                ],
                'arxiv' => [
                    'url' => 'http://export.arxiv.org/api/query?search_query=cat:cs.AI&sortBy=submittedDate&sortOrder=descending&max_results=10',
                    'weight' => 0.5
                ]
            ],
            'design' => [
                'dribbble' => [
                    'url' => 'https://dribbble.com/shots/popular.json',
                    'weight' => 0.5
                ],
                'reddit_design' => [
                    'url' => 'https://www.reddit.com/r/web_design/hot.json?limit=25',
                    'weight' => 0.5
                ]
            ]
        ];
        
        return $sources[$niche] ?? $sources['tech'];
    }
    
    public static function getKeywords($niche = 'tech') {
        $keywords = [
            'tech' => ['JavaScript', 'React', 'Node.js', 'Python', 'Web Development', 'APIs'],
            'ai' => ['Machine Learning', 'Deep Learning', 'Neural Networks', 'AI', 'ChatGPT', 'LLM'],
            'design' => ['UI/UX', 'Design Systems', 'Figma', 'CSS', 'Frontend', 'User Experience']
        ];
        
        return $keywords[$niche] ?? $keywords['tech'];
    }
}


?>
