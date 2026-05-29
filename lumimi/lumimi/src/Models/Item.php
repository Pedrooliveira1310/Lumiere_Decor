<?php
namespace Lumiere\Models;

class Item {
    private static string $file;

    const DIARIAS = [
        'cadeira'    => 45.00,
        'mesa'       => 85.00,
        'iluminacao' => 120.00,
    ];

    const TIPOS = [
        'cadeira'    => 'Cadeira',
        'mesa'       => 'Mesa',
        'iluminacao' => 'Iluminação',
    ];

    public static function init(string $basePath): void {
        self::$file = $basePath . '/data/itens.json';
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

    public static function find(int $id): ?array {
        foreach (self::load() as $item) {
            if ($item['id'] === $id) return $item;
        }
        return null;
    }

    public static function create(array $data): array {
        $items = self::load();
        $maxId = 0;
        foreach ($items as $i) $maxId = max($maxId, $i['id']);
        $new = [
            'id'          => $maxId + 1,
            'modelo'      => $data['modelo'],
            'tipo'        => $data['tipo'],
            'detalhes'    => $data['detalhes'],
            'status'      => 'disponivel',
            'cliente'     => null,
            'dias'        => null,
            'data_aluguel'=> null,
            'imagem'      => $data['imagem'] ?? null,
        ];
        $items[] = $new;
        self::save($items);
        return $new;
    }

    public static function alugar(int $id, int $dias, string $cliente): bool {
        $items = self::load();
        foreach ($items as &$item) {
            if ($item['id'] === $id && $item['status'] === 'disponivel') {
                $item['status']      = 'alugado';
                $item['dias']        = $dias;
                $item['cliente']     = $cliente;
                $item['data_aluguel']= date('Y-m-d');
                self::save($items);

                if (class_exists('Lumiere\\Models\\Historico')) {
                    Historico::addEntry([
                        'username'   => $cliente,
                        'item_id'    => $item['id'],
                        'modelo'     => $item['modelo'],
                        'tipo'       => $item['tipo'],
                        'dias'       => $dias,
                        'diaria'     => self::getDiariaFromItem($item),
                        'valor_total'=> round(self::getDiariaFromItem($item) * $dias, 2),
                        'rented_at'  => date('Y-m-d H:i:s'),
                    ]);
                }

                return true;
            }
        }
        return false;
    }

    public static function devolver(int $id): bool {
        $items = self::load();
        foreach ($items as &$item) {
            if ($item['id'] === $id && $item['status'] === 'alugado') {
                $item['status']      = 'disponivel';
                $item['dias']        = null;
                $item['cliente']     = null;
                $item['data_aluguel']= null;
                self::save($items);
                return true;
            }
        }
        return false;
    }

    public static function delete(int $id): bool {
        $items = self::load();
        $filtered = array_filter($items, fn($i) => $i['id'] !== $id);
        if (count($filtered) === count($items)) return false;
        self::save(array_values($filtered));
        return true;
    }

    public static function calcularPrevisao(string $tipo, int $dias): float {
        $diaria = 0;
        // Prefira diária definida em Categoria quando disponível
        if (class_exists('Lumiere\\Models\\Categoria')) {
            try {
                $diaria = Categoria::getDiaria($tipo);
            } catch (\Throwable $e) {
                $diaria = 0;
            }
        }
        if (empty($diaria)) {
            $diaria = self::DIARIAS[$tipo] ?? 0;
        }
        return $diaria * $dias;
    }

    public static function getDiaria(string $tipo): float {
        // Prefira diária definida em Categoria quando disponível
        if (class_exists('Lumiere\\Models\\Categoria')) {
            try {
                $d = Categoria::getDiaria($tipo);
                if ($d !== null && $d !== 0.0) return $d;
            } catch (\Throwable $e) {
                // fallback
            }
        }
        return self::DIARIAS[$tipo] ?? 0;
    }

    private static function parseBrazilianCurrency(string $value): float {
        $value = trim($value);
        $value = preg_replace('/[^0-9.,-]/u', '', $value);
        if ($value === '') {
            return 0.0;
        }
        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }
        return (float)$value;
    }

    public static function getDiariaFromItem(array $item): float {
        if (!empty($item['diaria'])) {
            return (float)$item['diaria'];
        }
        if (!empty($item['valor_total']) && !empty($item['dias'])) {
            return round((float)$item['valor_total'] / max(1, (int)$item['dias']), 2);
        }
        if (!empty($item['detalhes']) && mb_stripos($item['detalhes'], 'Diária R$') !== false) {
            if (preg_match('/Diária\s+R\$\s*([0-9\.,]+)/u', $item['detalhes'], $m)) {
                return self::parseBrazilianCurrency($m[1]);
            }
        }
        if (!empty($item['detalhes']) && mb_stripos($item['detalhes'], 'Valor R$') !== false) {
            if (preg_match('/Valor\s+R\$\s*([0-9\.,]+)/u', $item['detalhes'], $m)) {
                return self::parseBrazilianCurrency($m[1]);
            }
        }
        return self::getDiaria($item['tipo'] ?? '');
    }

    public static function getImageUrl(array $item): ?string {
        return !empty($item['imagem']) ? $item['imagem'] : null;
    }

    public static function getHistoricoPorUsuario(string $username): array {
        if (class_exists('Lumiere\\Models\\Historico')) {
            return Historico::getByUser($username);
        }
        return [];
    }

    public static function uploadImage(array $file): ?string {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes, true)) {
            return null;
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }
        $targetDir = BASE_PATH . '/uploads/items';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $filename = 'item_' . uniqid() . '.' . $extension;
        $target = $targetDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return 'uploads/items/' . $filename;
        }
        return null;
    }

    public static function getTipos(): array {
        // Se existir categorias dinâmicas, prefira-as
        if (class_exists('Lumiere\\Models\\Categoria')) {
            try {
                $cats = Categoria::getTipos();
                if (!empty($cats)) return $cats;
            } catch (\Throwable $e) {
                // fallback
            }
        }
        return self::TIPOS;
    }

    public static function createRentalFromPacote(array $pacote, string $cliente, int $dias): array {
        $items = self::load();
        $maxId = 0;
        foreach ($items as $i) $maxId = max($maxId, $i['id']);
        $valorDiaria = isset($pacote['valor']) ? (float)$pacote['valor'] : 0.0;
        $valorTotal = $valorDiaria * max(1, $dias);

        $new = [
            'id'          => $maxId + 1,
            'modelo'      => $pacote['nome'],
            'tipo'        => 'pacote',
            'detalhes'    => 'Pacote: ' . ($pacote['capacidade'] ?? '') . ' — Diária R$ ' . number_format($valorDiaria,2,',','.') . ' — Total R$ ' . number_format($valorTotal,2,',','.'),
            'status'      => 'alugado',
            'cliente'     => $cliente,
            'dias'        => $dias,
            'data_aluguel'=> date('Y-m-d'),
            'diaria'      => $valorDiaria,
            'valor_total' => $valorTotal,
        ];
        $items[] = $new;
        self::save($items);

        if (class_exists('Lumiere\\Models\\Historico')) {
            Historico::addEntry([
                'username'    => $cliente,
                'item_id'     => $new['id'],
                'modelo'      => $new['modelo'],
                'tipo'        => 'pacote',
                'dias'        => $dias,
                'diaria'      => $valorDiaria,
                'valor_total' => $valorTotal,
                'rented_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        return $new;
    }

    public static function getAlugadosPorUsuario(string $username): array {
        return array_values(array_filter(
            self::load(),
            fn($i) => $i['status'] === 'alugado' && ($i['cliente'] ?? '') === $username
        ));
    }

    public static function getDisponiveis(): array {
        return array_values(array_filter(self::load(), fn($i) => $i['status'] === 'disponivel'));
    }
}
