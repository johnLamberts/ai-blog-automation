
<?php 

class Config {
  
  // Workflow settings
  const NICHE = 'tech';
  const DAILY_RUN_TIME = '9:00';
  const OUTPUT_DIR = __DIR__ . '/../output/';
  CONST LOG_DIR = __DIR__ . '/../logs/';

  // Content Settings
  const TARGET_WORD_COUNT = 1800;
  const MAX_ARTICLES_TO_ANALYZE = 5;
  const CONTENT_FRESHNESS_HOURS = 24;

  // Free AI Options
  const AI_STRATEGY = 'template';
  // AI API - OpenAI
  // const OPENAI_API_KEY = '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';
  // const OPENAI_MODEL = 'cheaper_option_model_gtp-3.5-turbo';
  const HUGGINGFACE_API_KEY = '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';


  // Image generation settings
  const BING_IMAGE_API = 'bing_api_key';
  const UNSPLASH_ACCESS_KEY = 'hTt-9RJvWxzP4j9s2BNOTAhuVeP49Va3HaIPag86_VQ';
  const DEFAULT_IMAGE_COUNT = 3;

  // Email settings
  const SMTP_HOST = 'stmp.gmail.com';
  const SMTP_PORT = 587;
  const SMTP_USER = 'faithsacredoo3@gmail.com';
  const SMTP_PASS = 'vpjv cwfi xnoc scdm';
  const RECIPIENT_EMAIL = 'asisjohnlambert5@gmail.com';

  // SEO settings
  const BASE_URL = 'https://localhost:5170';
  const SITE_NAME = 'Daily Tech News for Canadian Citizen';
  const AUTHOR = 'May Content Creator';

  public static function get($key) {
    return defined("self::$key") ? constant("self::$key") : null;
  }
}








?>
