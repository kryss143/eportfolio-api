<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class MockDataService
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $path = database_path('mock-data.json');

        if (! File::exists($path)) {
            return [];
        }

        $json = File::get($path);
        self::$cache = json_decode($json, true) ?? [];

        return self::$cache;
    }

    public static function get(string $collection): array
    {
        $data = self::all();

        return $data[$collection] ?? [];
    }

    public static function findById(string $collection, string $id): ?array
    {
        $items = self::get($collection);

        foreach ($items as $item) {
            if (($item['id'] ?? null) === $id) {
                return $item;
            }
        }

        return null;
    }

    public static function findBySlug(string $collection, string $slug): ?array
    {
        $items = self::get($collection);

        foreach ($items as $item) {
            if (($item['slug'] ?? null) === $slug) {
                return $item;
            }
        }

        return null;
    }

    public static function filter(string $collection, array $conditions = []): array
    {
        $items = self::get($collection);

        foreach ($conditions as $key => $value) {
            $items = array_filter($items, function ($item) use ($key, $value) {
                return ($item[$key] ?? null) === $value;
            });
        }

        return array_values($items);
    }
}
