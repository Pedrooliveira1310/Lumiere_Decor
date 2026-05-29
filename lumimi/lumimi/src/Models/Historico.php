<?php
namespace Lumiere\Models;

class Historico {
    private static string $file;

    public static function init(string $basePath): void {
        self::$file = $basePath . '/data/historico.json';
    }

    private static function load(): array {
        if (!file_exists(self::$file)) return [];
        return json_decode(file_get_contents(self::$file), true) ?? [];
    }

    private static function save(array $data): void {
        file_put_contents(self::$file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function addEntry(array $entry): array {
        $entries = self::load();
        $maxId = 0;
        foreach ($entries as $item) {
            if (!empty($item['id'])) {
                $maxId = max($maxId, (int)$item['id']);
            }
        }
        $entry['id'] = $maxId + 1;
        $entries[] = $entry;
        self::save($entries);
        return $entry;
    }

    public static function getAll(): array {
        return self::load();
    }

    public static function getByUser(string $username): array {
        return array_values(array_filter(self::load(), fn($item) => isset($item['username']) && $item['username'] === $username));
    }

    public static function markReturned(int $itemId, string $username): void {
        $entries = self::load();
        for ($i = count($entries) - 1; $i >= 0; $i--) {
            if ((int)($entries[$i]['item_id'] ?? 0) === $itemId
                && ($entries[$i]['username'] ?? '') === $username
                && empty($entries[$i]['returned_at'])) {
                $entries[$i]['returned_at'] = date('Y-m-d H:i:s');
                $entries[$i]['status'] = 'devolvido';
                self::save($entries);
                return;
            }
        }
    }

    public static function getFinanceSummary(): array {
        $entries = self::load();
        $ganhosRealizados = 0.0;
        $devolucoes = 0;
        foreach ($entries as $e) {
            if (!empty($e['returned_at'])) {
                $ganhosRealizados += (float)($e['valor_total'] ?? 0);
                $devolucoes++;
            }
        }
        return [
            'ganhos_realizados' => $ganhosRealizados,
            'devolucoes'        => $devolucoes,
            'total_registros'   => count($entries),
        ];
    }

    public static function clearAll(): bool {
        self::save([]);
        return true;
    }

    public static function clearByUser(string $username): bool {
        $filtered = array_values(array_filter(
            self::load(),
            fn($e) => ($e['username'] ?? '') !== $username
        ));
        self::save($filtered);
        return true;
    }
}
