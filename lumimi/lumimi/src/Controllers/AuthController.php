<?php
namespace Lumiere\Controllers;

use Lumiere\Auth\Session;
use Lumiere\Models\Usuario;

class AuthController {
    public static function login(string $username, string $senha): array {
        $user = Usuario::authenticate($username, $senha);
        if ($user) {
            Session::set('user', $user);
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Usuário ou senha inválidos.'];
    }

    public static function register(string $username, string $senha): array {
        if (empty($username) || empty($senha)) {
            return ['success' => false, 'error' => 'Usuário e senha são obrigatórios.'];
        }

        if (strlen($username) < 3) {
            return ['success' => false, 'error' => 'Usuário deve ter no mínimo 3 caracteres.'];
        }

        if (Usuario::create($username, $senha)) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Este usuário já existe.'];
    }

    public static function logout(): void {
        Session::destroy();
        header('Location: login.php');
        exit;
    }
}
