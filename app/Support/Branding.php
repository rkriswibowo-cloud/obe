<?php

class Branding
{
    private const DEFAULTS = [
        'logo' => null,
        'favicon' => null,
        'updated_at' => null,
    ];

    public static function all(): array
    {
        $path = self::configPath();
        if (!is_file($path)) {
            return self::DEFAULTS;
        }

        $data = json_decode((string) file_get_contents($path), true);
        return is_array($data) ? array_merge(self::DEFAULTS, $data) : self::DEFAULTS;
    }

    public static function save(array $data): bool
    {
        $payload = array_merge(self::DEFAULTS, $data, ['updated_at' => date(DATE_ATOM)]);
        return file_put_contents(
            self::configPath(),
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        ) !== false;
    }

    public static function assetUrl(?string $filename, string $baseUrl): ?string
    {
        if (!$filename || !preg_match('/^[a-z0-9._-]+$/i', $filename)) {
            return null;
        }
        $path = self::uploadDirectory() . DIRECTORY_SEPARATOR . $filename;
        if (!is_file($path)) {
            return null;
        }
        return rtrim($baseUrl, '/') . '/uploads/branding/' . rawurlencode($filename) . '?v=' . filemtime($path);
    }

    public static function configPath(): string
    {
        return dirname(__DIR__, 2) . '/config/branding.json';
    }

    public static function uploadDirectory(): string
    {
        return dirname(__DIR__, 2) . '/public/uploads/branding';
    }
}
