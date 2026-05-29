<?php
namespace Lumiere\Models;

class Categoria {
    private static string $file;

    public static function init(string $basePath): void {
        self::$file = $basePath . '/data/categories.json';
    }

    private static function load(): array {
        if (!file_exists(self::$file)) return [];
        return json_decode(file_get_contents(self::$file), true) ?? [];
    }

    private static function save(array $data): void {
        file_put_contents(self::$file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function all(): array {
        return self::load();
    }

    public static function create(string $id, string $nome, float $diaria = 0.0): bool {
        $data = self::load();
        // evita duplicados por id
        foreach ($data as $c) {
            if ($c['id'] === $id) return false;
        }
        $data[] = ['id' => $id, 'nome' => $nome, 'diaria' => $diaria];
        self::save($data);
        return true;
    }

    public static function getTipos(): array {
        $out = [];
        foreach (self::load() as $c) {
            $out[$c['id']] = $c['nome'];
        }
        return $out;
    }

    public static function getDiaria(string $id): float {
        foreach (self::load() as $c) {
            if ($c['id'] === $id) return $c['diaria'] + 0.0;
        }
        return 0.0;
    }

    public static function delete(string $id): array {
        $data = self::load();
        $found = false;
        foreach ($data as $c) {
            if ($c['id'] === $id) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            return ['success' => false, 'error' => 'Categoria não encontrada.'];
        }
        if (class_exists('Lumiere\\Models\\Item')) {
            foreach (Item::all() as $item) {
                if (($item['tipo'] ?? '') === $id) {
                    return ['success' => false, 'error' => 'Existem itens usando esta categoria. Remova ou altere-os antes.'];
                }
            }
        }
        $filtered = array_values(array_filter($data, fn($c) => $c['id'] !== $id));
        self::save($filtered);
        return ['success' => true];
    }
}
