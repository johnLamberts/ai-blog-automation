<?php 

class LoadConfigurables {
  private static array $settings = [];

    public static function load(string $path = __DIR__ . '/../.env'): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Env file not found at: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, '"\''); // strip quotes
            self::$settings[$name] = $value;
        }
    }

    public static function get(string $key, $default = null): ?string
    {
        return self::$settings[$key] ?? $default;
    }
}


?>
