<?php

namespace App\Support {

use Exception;
use PDO;
use PDOException;

/**
 * EnvLoader
 *
 * A lightweight, robust environment variable loader.
 * Reads the .env file, strips comments, handles quoted values,
 * and loads variables into putenv(), $_ENV, and $_SERVER.
 */
class EnvLoader {

    protected $envPath;
    protected $required;

    public function __construct(string $envPath, array $required = []) {
        $this->envPath  = $envPath;
        $this->required = $required;
    }

    /**
     * Load the .env file and populate environment.
     *
     * @throws \RuntimeException if .env is missing or required vars are absent.
     */
    public function load(): void {
        if (!file_exists($this->envPath)) {
            throw new \RuntimeException(
                ".env file not found at: {$this->envPath}\n" .
                "Copy .env.example to .env and configure your settings."
            );
        }

        $lines = file($this->envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip blank lines and comments
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            // Must contain an = sign
            if (strpos($line, '=') === false) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name  = trim($name);
            $value = $this->parseValue(trim($value));

            // Don't override existing system environment variables
            if (array_key_exists($name, $_SERVER) || array_key_exists($name, $_ENV)) {
                continue;
            }

            putenv("{$name}={$value}");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }

        $this->validateRequired();
    }

    /**
     * Parse a raw .env value: strip surrounding quotes, handle inline comments.
     */
    protected function parseValue(string $value): string {
        // Strip optional inline comment (# preceded by a space)
        if (preg_match('/^([^#]*)\s+#/', $value, $m)) {
            $value = trim($m[1]);
        }

        // Strip surrounding double or single quotes
        $len = strlen($value);
        if ($len >= 2) {
            $first = $value[0];
            $last  = $value[$len - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        return $value;
    }

    /**
     * Validate that all required variables are present and non-empty.
     *
     * @throws RuntimeException
     */
    protected function validateRequired(): void {
        $missing = [];
        foreach ($this->required as $var) {
            if (empty($_ENV[$var])) {
                $missing[] = $var;
            }
        }

        if (!empty($missing)) {
            throw new RuntimeException(
                "Missing required environment variables: " . implode(', ', $missing) . "\n" .
                "Please update your .env file."
            );
        }
    }
}

}

namespace {
    /**
     * Helper: get an environment variable with an optional default.
     */
function env(string $key, $default = null) {
    $value = isset($_ENV[$key]) ? $_ENV[$key] : getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }

    $lowerValue = strtolower((string) $value);
    switch ($lowerValue) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        default:
            return $value;
    }
}
}

