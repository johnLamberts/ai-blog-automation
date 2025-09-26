<?php 


require_once __DIR__ . '/../config/config.php';

class EmailSender {
  private $smtpHost;
  private $SMTP_PASS;
  private $smtpUser;
  private $smtpPass;
  private $recipientEmail;


  public function __construct() {
    $this->smtpHost = Config::SMTP_HOST;
    $this->$smtpPort = Config::SMTP_PORT;
    $this->$smtpUser = Config::SMTP_USER;
    $this->SMTP_PASS = Config::SMTP_PASS;
    $this->$recipientEmail = Config::RECIPIENT_EMAIL;
  }

  public function sendBlogEmail($pageData, $blogData, $images) {
    $subject = "Daily Automated Content: " . $blogData['title'];

    // Create email body
    $emailBody = $this->createEmailBody($pageData, $blogData, $images);
    
    // Prepare attachments
    $attachments = $this->prepareAttachments($pageData, $images);

    // Send email
    return $this->sendEmail($subject, $emailBody, $images);
  }



  private function createEmailBody($pageData, $blogData, $images) {
    $html = '
    <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .header { 
            background: linear-gradient(135deg, #0090ff, #46a758); 
            color: white; 
            padding: 30px; 
            border-radius: 8px; 
            text-align: center; 
        }
        .content { 
            padding: 20px 0; 
        }
        .stats { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 6px; 
            margin: 20px 0; 
            display: flex; 
            justify-content: space-between; 
        }
        .stat { 
            text-align: center; 
        }
        .stat-value { 
            font-size: 24px; 
            font-weight: bold; 
            color: #0090ff; 
        }
        .stat-label { 
            font-size: 12px; 
            color: #666; 
        }
        .preview { 
            border: 1px solid #e1e1e6; 
            border-radius: 8px; 
            padding: 20px; 
            margin: 20px 0; 
            background: #fcfcfd; 
        }
        .tags { 
            display: flex; 
            gap: 8px; 
            flex-wrap: wrap; 
            margin: 15px 0; 
        }
        .tag { 
            background: #e1e1e6; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px; 
            color: #62656d; 
        }
        .footer { 
            text-align: center; 
            color: #666; 
            font-size: 14px; 
            margin-top: 30px; 
            padding-top: 20px; 
            border-top: 1px solid #e1e1e6; 
        }
        .cta { 
            background: #0090ff; 
            color: white; 
            padding: 12px 24px; 
            border-radius: 6px; 
            text-decoration: none; 
            display: inline-block; 
            margin: 20px 0; 
            font-weight: 500; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 Daily Blog Generated</h1>
        <p>Your AI-powered blog post is ready!</p>
    </div>
    
    <div class="content">
        <h2>' . htmlspecialchars($blogData['title']) . '</h2>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-value">' . $blogData['word_count'] . '</div>
                <div class="stat-label">Words</div>
            </div>
            <div class="stat">
                <div class="stat-value">' . $blogData['read_time'] . '</div>
                <div class="stat-label">Min Read</div>
            </div>
            <div class="stat">
                <div class="stat-value">' . count($blogData['tags']) . '</div>
                <div class="stat-label">Tags</div>
            </div>
            <div class="stat">
                <div class="stat-value">' . $pageData['filesize'] . '</div>
                <div class="stat-label">File Size</div>
            </div>
        </div>
        
        <div class="preview">
            <h3>Preview</h3>
            <p>' . htmlspecialchars($blogData['meta_description']) . '</p>
            
            <div class="tags">
                ' . $this->generateTagsHtml($blogData['tags']) . '
            </div>
            
            <p><strong>Source:</strong> ' . htmlspecialchars($blogData['source_topic']['source']) . '</p>
            <p><strong>Generated:</strong> ' . $blogData['generated_at'] . '</p>
        </div>
        
        <div style="text-align: center;">
            <a href="' . htmlspecialchars($pageData['url']) . '" class="cta">View Full Blog Post</a>
        </div>
        
        <h3>📎 Attachments</h3>
        <ul>
            <li><strong>' . htmlspecialchars($pageData['filename']) . '</strong> - Complete HTML blog post</li>
            ' . $this->generateAttachmentList($images) . '
        </ul>
        
        <h3>📊 Content Analysis</h3>
        <ul>
            <li><strong>Topic Relevance:</strong> High (Score: ' . number_format($blogData['source_topic']['final_score'], 1) . ')</li>
            <li><strong>SEO Keywords:</strong> ' . implode(', ', array_slice($blogData['seo_keywords'], 0, 3)) . '</li>
            <li><strong>Structure:</strong> ' . count($blogData['table_of_contents']) . ' sections with proper headings</li>
            <li><strong>Images:</strong> ' . (1 + count($images['supporting'])) . ' AI-generated/sourced images</li>
        </ul>
        
        ' . $this->generateImagePreview($images) . '
    </div>
    
    <div class="footer">
        <p>Generated by AI Blog Automation System</p>
        <p>Next blog will be generated tomorrow at ' . Config::DAILY_RUN_TIME . '</p>
    </div>
</body>
</html>';
    return $html;
  }


  private function generateTagsHtml($tags) {
    $html = '';
    
    foreach ($tags as $tag) {
      $html += '<span class="tag">' . htmlspecialchars($tag) . '</span>';
    }
    
    return $html;

  }

  private function generateAttachmentList($images) {
    $html = '';

    if ($images['featured']) {
            $html .= '<li>Featured Image: ' . htmlspecialchars($images['featured']['filename']) . '</li>';
    }
        
    foreach ($images['supporting'] as $index => $image) {
      $html .= '<li>Supporting Image ' . ($index + 1) . ': ' . htmlspecialchars($image['filename']) . '</li>';
    }
        
    return $html;
  }

   private function generateImagePreview($images) {
        $html = '<h3>🖼️ Generated Images</h3><div style="display: flex; gap: 10px; flex-wrap: wrap;">';
        
        if ($images['featured']) {
            $html .= $this->createImagePreviewHtml($images['featured'], 'Featured');
        }
        
        foreach ($images['supporting'] as $index => $image) {
            $html .= $this->createImagePreviewHtml($image, 'Support ' . ($index + 1));
        }
        
        $html .= '</div>';
        return $html;
    }

    private function createImagePreviewHtml($image, $label) {
        if ($image['source'] === 'generated' && isset($image['path'])) {
            // For SVG images, embed them directly
            $imageContent = file_get_contents($image['path']);
            return '<div style="text-align: center; margin: 10px;">
                <div style="width: 150px; height: 75px; border: 1px solid #ddd; display: inline-block; overflow: hidden;">
                    ' . $imageContent . '
                </div>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">' . $label . '</div>
            </div>';
        } else {
            // For external images, show placeholder
            return '<div style="text-align: center; margin: 10px;">
                <div style="width: 150px; height: 75px; border: 1px solid #ddd; display: inline-block; background: #f5f5f5; line-height: 75px; font-size: 12px; color: #999;">
                    Image Preview
                </div>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">' . $label . '</div>
            </div>';
        }
    }

    private function prepareAttachments($pageData, $images) {
        $attachments = [];
        
        // Add the main HTML file
        $attachments[] = [
            'path' => $pageData['filepath'],
            'name' => $pageData['filename'],
            'type' => 'text/html'
        ];
        
        // Add images
        if ($images['featured'] && file_exists($images['featured']['path'])) {
            $attachments[] = [
                'path' => $images['featured']['path'],
                'name' => $images['featured']['filename'],
                'type' => $this->getMimeType($images['featured']['path'])
            ];
        }
        
        foreach ($images['supporting'] as $image) {
            if (file_exists($image['path'])) {
                $attachments[] = [
                    'path' => $image['path'],
                    'name' => $image['filename'],
                    'type' => $this->getMimeType($image['path'])
                ];
            }
        }
        
        return $attachments;
    }
    
      private function getMimeType($filepath) {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        
        $mimeTypes = [
            'html' => 'text/html',
            'svg' => 'image/svg+xml',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
    
    private function sendEmail($subject, $body, $attachments) {
        // Using PHPMailer or similar would be ideal, but for simplicity, using mail() function
        // In production, definitely use PHPMailer or SwiftMailer
        
        $boundary = md5(uniqid(time()));
        
        // Headers
        $headers = "From: " . $this->smtpUser . "\r\n";
        $headers .= "Reply-To: " . $this->smtpUser . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
        
        // Body
        $message = "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $body . "\r\n";
        
        // Attachments
        foreach ($attachments as $attachment) {
            if (file_exists($attachment['path'])) {
                $fileContent = file_get_contents($attachment['path']);
                $encodedContent = chunk_split(base64_encode($fileContent));
                
                $message .= "--{$boundary}\r\n";
                $message .= "Content-Type: {$attachment['type']}; name=\"{$attachment['name']}\"\r\n";
                $message .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $message .= $encodedContent . "\r\n";
            }
        }
        
        $message .= "--{$boundary}--";
        
        // Send email
        $result = mail($this->recipientEmail, $subject, $message, $headers);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Email sent successfully to ' . $this->recipientEmail,
                'attachments_count' => count($attachments)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to send email',
                'error' => error_get_last()
            ];
        }
    }

     public function sendErrorEmail($error, $context = []) {
        $subject = "Blog Automation Error - " . date('Y-m-d H:i:s');
        
        $body = '<html><body style="font-family: Arial, sans-serif;">';
        $body .= '<h2 style="color: #e54d2e;">Blog Automation Error</h2>';
        $body .= '<p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>';
        $body .= '<p><strong>Error:</strong> ' . htmlspecialchars($error) . '</p>';
        
        if (!empty($context)) {
            $body .= '<h3>Context:</h3><pre>' . htmlspecialchars(json_encode($context, JSON_PRETTY_PRINT)) . '</pre>';
        }
        
        $body .= '<p>Please check the system and logs for more details.</p>';
        $body .= '</body></html>';
        
        $headers = "From: " . $this->smtpUser . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        return mail($this->recipientEmail, $subject, $body, $headers);
    }
}



?>
