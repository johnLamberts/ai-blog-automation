<?php
// index.php - Main execution script
ini_set('max_execution_time', 300); // 5 minutes max
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/ContentScraper.php';
require_once __DIR__ . '/src/AIContentEnhancer.php';
require_once __DIR__ . '/src/ImageGenerator.php';
require_once __DIR__ . '/src/PageBuilder.php';
require_once __DIR__ . '/src/EmailSender.php';

class BlogAutomation {
    private $logger;
    private $emailSender;
    
    public function __construct() {
        $this->setupLogging();
        $this->emailSender = new EmailSender();
    }
    
    public function run() {
        $this->log("Starting blog automation workflow");
        
        try {
            // Step 1: Research trending topic
            $this->log("Step 1: Researching trending topics");
            $scraper = new ContentScraper(Config::NICHE);
            $topic = $scraper->getTrendingTopic();
            
            if (!$topic) {
                throw new Exception("No suitable trending topic found");
            }
            
            $this->log("Selected topic: " . $topic['title'] . " (Score: " . $topic['final_score'] . ")");
            
            // Extract additional content if available
            $originalContent = '';
            if (!empty($topic['url'])) {
                $contentData = $scraper->extractContentFromUrl($topic['url']);
                if ($contentData) {
                    $originalContent = $contentData['content'];
                    $this->log("Extracted " . $contentData['word_count'] . " words from source");
                }
            }
            
            // Step 2: Enhance content with AI
              $this->log("Step 2: Enhancing content with FREE AI (" . Config::AI_STRATEGY . ")");
            $enhancer = new FreeAIEnhancer(Config::AI_STRATEGY);
            $enhancedContent = $enhancer->enhanceContent($topic, $originalContent);
            
            if (!$enhancedContent) {
                throw new Exception("AI content enhancement failed");
            }
            
            $this->log("Generated " . $enhancedContent['word_count'] . " words of enhanced content");
            
            // Step 3: Generate images
            $this->log("Step 3: Generating images");
            $imageGenerator = new ImageGenerator();
            
            // Get image prompts from AI
            $imagePrompts = $enhancer->generateImagePrompts($enhancedContent);
            $this->log("Generated image prompts: " . json_encode($imagePrompts));
            
            $images = $imageGenerator->generateImages($imagePrompts, $enhancedContent);
            $this->log("Generated " . (1 + count($images['supporting'])) . " images");
            
            // Step 4: Build landing page
            $this->log("Step 4: Building landing page");
            $pageBuilder = new PageBuilder();
            $pageData = $pageBuilder->buildPage($enhancedContent, $images);
            
            if (!$pageData) {
                throw new Exception("Page building failed");
            }
            
            $this->log("Created page: " . $pageData['filename'] . " (" . $pageData['filesize'] . " bytes)");
            
            // Step 5: Send email
            $this->log("Step 5: Sending email");
            $emailResult = $this->emailSender->sendBlogEmail($pageData, $enhancedContent, $images);
            
            if ($emailResult['success']) {
                $this->log("Email sent successfully with " . $emailResult['attachments_count'] . " attachments");
                
                // Log success metrics
                $this->logSuccess([
                    'topic' => $topic['title'],
                    'source' => $topic['source'],
                    'word_count' => $enhancedContent['word_count'],
                    'read_time' => $enhancedContent['read_time'],
                    'images_count' => 1 + count($images['supporting']),
                    'file_size' => $pageData['filesize'],
                    'generation_time' => $this->getExecutionTime()
                ]);
                
                return [
                    'status' => 'success',
                    'message' => 'Blog automation completed successfully',
                    'data' => [
                        'topic' => $topic['title'],
                        'file' => $pageData['filename'],
                        'url' => $pageData['url']
                    ]
                ];
            } else {
                throw new Exception("Email sending failed: " . $emailResult['message']);
            }
            
        } catch (Exception $e) {
            $this->log("ERROR: " . $e->getMessage());
            $this->emailSender->sendErrorEmail($e->getMessage(), [
                'step' => $this->getCurrentStep(),
                'niche' => Config::NICHE,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function runTest() {
        $this->log("Running test mode");
        
        try {
            // Test email functionality
            $result = $this->emailSender->sendTestEmail();
            
            if ($result) {
                $this->log("Test email sent successfully");
                return ['status' => 'success', 'message' => 'Test completed successfully'];
            } else {
                throw new Exception("Test email failed");
            }
            
        } catch (Exception $e) {
            $this->log("Test failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    public function setupCron() {
        $this->log("Setting up cron job");
        
        // Generate cron command
        $phpPath = PHP_BINARY;
        $scriptPath = __FILE__;
        $cronTime = $this->convertTimeToCron(Config::DAILY_RUN_TIME);
        $logPath = Config::LOG_DIR . 'cron.log';
        
        $cronCommand = "$cronTime $phpPath $scriptPath >> $logPath 2>&1";
        
        echo "Add this line to your crontab:\n";
        echo "$cronCommand\n\n";
        echo "To edit crontab, run: crontab -e\n";
        echo "To view current crontab: crontab -l\n\n";
        echo "The blog will run daily at " . Config::DAILY_RUN_TIME . "\n";
        
        return $cronCommand;
    }
    
    private function convertTimeToCron($time) {
        // Convert "09:00" to cron format "0 9 * * *"
        list($hour, $minute) = explode(':', $time);
        return "$minute $hour * * *";
    }
    
    private function setupLogging() {
        if (!is_dir(Config::LOG_DIR)) {
            mkdir(Config::LOG_DIR, 0755, true);
        }
        
        $this->startTime = microtime(true);
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        
        echo $logMessage; // Console output
        
        // File logging
        $logFile = Config::LOG_DIR . 'automation_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    private function logSuccess($metrics) {
        $successLog = Config::LOG_DIR . 'success_metrics.json';
        
        $successData = [
            'date' => date('Y-m-d H:i:s'),
            'metrics' => $metrics
        ];
        
        // Append to success log
        $existingData = [];
        if (file_exists($successLog)) {
            $existingData = json_decode(file_get_contents($successLog), true) ?: [];
        }
        
        $existingData[] = $successData;
        
        // Keep only last 30 days
        $existingData = array_slice($existingData, -30);
        
        file_put_contents($successLog, json_encode($existingData, JSON_PRETTY_PRINT));
    }
    
    private function getExecutionTime() {
        return round(microtime(true) - $this->startTime, 2);
    }
    
    private function getCurrentStep() {
        // This would be set during execution to track which step failed
        return 'unknown';
    }
}

// CLI Interface
if (php_sapi_name() === 'cli') {
    $automation = new BlogAutomation();
    
    $command = $argv[1] ?? 'run';
    
    switch ($command) {
        case 'test':
            echo "Running test mode...\n";
            $result = $automation->runTest();
            break;
            
        case 'setup':
            echo "Setting up cron job...\n";
            $automation->setupCron();
            exit(0);
            
        case 'run':
        default:
            echo "Starting blog automation...\n";
            $result = $automation->run();
            break;
    }
    
    echo "\nResult: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    exit($result['status'] === 'success' ? 0 : 1);
    
} else {
    // Web interface (simple)
    echo "<h1>Blog Automation System</h1>";
    
    if (isset($_GET['action'])) {
        $automation = new BlogAutomation();
        
        switch ($_GET['action']) {
            case 'test':
                echo "<h2>Test Mode</h2>";
                $result = $automation->runTest();
                break;
                
            case 'run':
                echo "<h2>Manual Run</h2>";
                echo "<p>This may take several minutes...</p>";
                $result = $automation->run();
                break;
                
            default:
                $result = ['status' => 'error', 'message' => 'Invalid action'];
        }
        
        echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        echo "<p><a href='?'>Back to menu</a></p>";
    } else {
        echo "<ul>";
        echo "<li><a href='?action=test'>Run Test</a> - Test email functionality</li>";
        echo "<li><a href='?action=run'>Manual Run</a> - Generate blog post now</li>";
        echo "</ul>";
        
        echo "<h2>Configuration</h2>";
        echo "<ul>";
        echo "<li><strong>Niche:</strong> " . Config::NICHE . "</li>";
        echo "<li><strong>Daily run time:</strong> " . Config::DAILY_RUN_TIME . "</li>";
        echo "<li><strong>Target word count:</strong> " . Config::TARGET_WORD_COUNT . "</li>";
        echo "<li><strong>Recipient email:</strong> " . Config::RECIPIENT_EMAIL . "</li>";
        echo "</ul>";
        
        echo "<h2>Setup Instructions</h2>";
        echo "<p>Run <code>php index.php setup</code> to get the cron job command.</p>";
    }
}
?>
