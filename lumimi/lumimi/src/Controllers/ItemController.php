<?php
namespace Lumiere\Controllers;

use Lumiere\Auth\Session;
use Lumiere\Models\Item;

class ItemController {
    public static function adicionar(array $data, array $files = []): array {
        if (!Session::isAdmin()) return ['success' => false, 'error' => 'Acesso negado.'];
        if (empty($data['modelo']) || empty($data['tipo']) || empty($data['detalhes'])) {
            return ['success' => false, 'error' => 'Preencha todos os campos.'];
        }

        if (!empty($files['imagem']) && $files['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagemPath = Item::uploadImage($files['imagem']);
            if ($imagemPath === null) {
                return ['success' => false, 'error' => 'Falha ao enviar imagem. Use JPG, PNG ou WEBP.'];
            }
            $data['imagem'] = $imagemPath;
        }

        $item = Item::create($data);
        return ['success' => true, 'item' => $item];
    }

    public static function alugar(int $id, int $dias): array {
        if (!Session::isLoggedIn()) return ['success' => false, 'error' => 'Não autenticado.'];
        if (Session::isAdmin()) return ['success' => false, 'error' => 'Admin não pode alugar itens.'];
        $user = Session::get('user');
        $ok = Item::alugar($id, max(1, $dias), $user['username']);
        return $ok
            ? ['success' => true]
            : ['success' => false, 'error' => 'Item não disponível.'];
    }

    public static function alugarPacote(string $pacoteId, int $dias): array {
        if (!Session::isLoggedIn()) return ['success' => false, 'error' => 'Não autenticado.'];
        if (Session::isAdmin()) return ['success' => false, 'error' => 'Admin não pode alugar pacotes.'];
        $user = Session::get('user');
        $pacote = \Lumiere\Models\Pacote::findById($pacoteId);
        if (!$pacote) return ['success' => false, 'error' => 'Pacote não encontrado.'];
        // cria um registro de aluguel representando o pacote
        $item = Item::createRentalFromPacote($pacote, $user['username'], max(1, $dias));
        return ['success' => true, 'item' => $item];
    }

    public static function devolver(int $id): array {
        if (!Session::isLoggedIn()) return ['success' => false, 'error' => 'Não autenticado.'];
        $item = Item::find($id);
        if (!$item || $item['status'] !== 'alugado') {
            return ['success' => false, 'error' => 'Item não encontrado ou não alugado.'];
        }
        if (!Session::isAdmin()) {
            $user = Session::get('user');
            if (($item['cliente'] ?? '') !== ($user['username'] ?? '')) {
                return ['success' => false, 'error' => 'Você só pode devolver seus próprios aluguéis.'];
            }
        }
        $ok = Item::devolver($id);
        if ($ok && class_exists('Lumiere\\Models\\Historico')) {
            $cliente = $item['cliente'] ?? Session::get('user')['username'] ?? '';
            Historico::markReturned($id, $cliente);
        }
        return $ok
            ? ['success' => true]
            : ['success' => false, 'error' => 'Item não encontrado ou não alugado.'];
    }

    public static function deletar(int $id): array {
        if (!Session::isAdmin()) return ['success' => false, 'error' => 'Acesso negado.'];
        $ok = Item::delete($id);
        return $ok
            ? ['success' => true]
            : ['success' => false, 'error' => 'Item não encontrado.'];
    }

    public static function previsao(string $tipo, int $dias): array {
        if (!Session::isLoggedIn()) return ['success' => false, 'error' => 'Não autenticado.'];
        $valor = Item::calcularPrevisao($tipo, $dias);
        return ['success' => true, 'valor' => $valor, 'diaria' => Item::getDiaria($tipo)];
    }
}
