<?php
namespace Lumiere\Models;

class Pacote {
    private static string $file;

    public static function init(string $basePath): void {
        self::$file = $basePath . '/data/pacotes.json';
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

    public static function findById(string $id): ?array {
        foreach (self::load() as $p) {
            if ($p['id'] === $id) return $p;
        }
        return null;
    }

    public static function updateValor(string $id, float $valor): bool {
        $data = self::load();
        foreach ($data as &$p) {
            if ($p['id'] === $id) {
                $p['valor'] = $valor;
                self::save($data);
                return true;
            }
        }
        return false;
    }

    public static function uploadImage(array $file): ?string {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        if (!in_array($file['type'], $allowedTypes, true)) {
            return null;
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('pacote_', true) . '.' . strtolower($ext);
        $targetDir = BASE_PATH . '/uploads/pacotes';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $targetPath = $targetDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return null;
        }
        return 'uploads/pacotes/' . $filename;
    }

    public static function create(array $data): bool {
        $id = trim($data['id'] ?? '');
        $nome = trim($data['nome'] ?? '');
        $capacidade = trim($data['capacidade'] ?? '');
        $valor = floatval($data['valor'] ?? 0);
        if (!$id || !$nome || !$capacidade || $valor <= 0) {
            return false;
        }
        $pacotes = self::load();
        foreach ($pacotes as $pacote) {
            if ($pacote['id'] === $id) {
                return false;
            }
        }
        $novo = [
            'id' => $id,
            'nome' => $nome,
            'capacidade' => $capacidade,
            'valor' => $valor,
        ];
        if (!empty($data['imagem'])) {
            $novo['imagem'] = $data['imagem'];
        }
        $pacotes[] = $novo;
        self::save($pacotes);
        return true;
    }

    public static function getValor(string $id): ?float {
        $pacote = self::findById($id);
        return $pacote ? $pacote['valor'] : null;
    }
}
