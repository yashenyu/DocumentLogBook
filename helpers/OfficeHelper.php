<?php

final class OfficeHelper
{
    private static function officesPath(): string
    {
        return __DIR__ . '/../config/offices.json';
    }

    /**
     * @return string[]
     */
    public static function getOffices(): array
    {
        $default = [
            'SOC',
            'SEA',
            'SBA',
            'SAS',
            'SHTM',
            'SNAMS',
            'CCJEF',
            'Basic Education',
        ];

        $path = self::officesPath();
        if (!file_exists($path)) {
            return $default;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return $default;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $default;
        }

        // Normalize + unique (case-insensitive) while preserving first occurrence casing + order.
        $seen = [];
        $out = [];
        foreach ($decoded as $item) {
            if (!is_string($item)) {
                continue;
            }
            $val = trim($item);
            if ($val === '') {
                continue;
            }
            $key = strtolower($val);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $val;
        }

        return !empty($out) ? $out : $default;
    }

    /**
     * Adds a new office to config/offices.json (no DB writes).
     *
     * @return string[] Updated list
     */
    public static function addOffice(string $office): array
    {
        $office = trim(preg_replace('/\s+/', ' ', $office));
        $office = strip_tags($office);

        if ($office === '') {
            throw new RuntimeException('Office name cannot be empty.');
        }
        if (strlen($office) > 60) {
            throw new RuntimeException('Office name is too long (max 60 characters).');
        }

        $list = self::getOffices();
        $want = strtolower($office);
        foreach ($list as $existing) {
            if (strtolower($existing) === $want) {
                throw new RuntimeException('Office already exists.');
            }
        }

        $list[] = $office;

        $path = self::officesPath();
        $json = json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Failed to encode offices list.');
        }

        $ok = @file_put_contents($path, $json . PHP_EOL, LOCK_EX);
        if ($ok === false) {
            throw new RuntimeException('Failed to save offices list (file not writable).');
        }

        return $list;
    }
}

