<?php
namespace Lumiere\Models;

class Usuario {
    private static string $file;

    public static function init(string $basePath): void {
        self::$file = $basePath . '/data/usuarios.json';
    }

    private static function load(): array {
        if (!file_exists(self::$file)) return [];
        return json_decode(file_get_contents(self::$file), true) ?? [];
    }

    private static function save(array $data): void {
        file_put_contents(self::$file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function findByUsername(string $username): ?array {
        foreach (self::load() as $u) {
            if ($u['username'] === $username) return $u;
        }
        return null;
    }

    public static function authenticate(string $username, string $senha): ?array {
        $user = self::findByUsername($username);
        if ($user && password_verify($senha, $user['senha'])) {
            return $user;
        }
        return null;
    }

    public static function create(string $username, string $senha): bool {
        // Verifica se o usuário já existe
        if (self::findByUsername($username)) {
            return false;
        }

        $data = self::load();
        $newUser = [
            'id' => (count($data) > 0 ? max(array_column($data, 'id')) : 0) + 1,
            'username' => $username,
            'senha' => password_hash($senha, PASSWORD_BCRYPT),
            'perfil' => 'usuario' // Apenas usuários comuns podem se registrar
        ];

        $data[] = $newUser;
        self::save($data);
        return true;
    }
}
