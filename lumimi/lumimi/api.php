<?php
require_once __DIR__ . '/config.php';

use Lumiere\Controllers\AuthController;
use Lumiere\Controllers\ItemController;
use Lumiere\Models\Pacote;
use Lumiere\Auth\Session;

header('Content-Type: application/json');

if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Não autenticado.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'adicionar':
        echo json_encode(ItemController::adicionar($_POST, $_FILES));
        break;

    case 'alugar':
        $id   = (int)($_POST['id'] ?? 0);
        $dias = (int)($_POST['dias'] ?? 1);
        echo json_encode(ItemController::alugar($id, $dias));
        break;

    case 'devolver':
        $id = (int)($_POST['id'] ?? 0);
        echo json_encode(ItemController::devolver($id));
        break;

    case 'deletar':
        $id = (int)($_POST['id'] ?? 0);
        echo json_encode(ItemController::deletar($id));
        break;

    case 'previsao':
        $tipo = $_POST['tipo'] ?? $_GET['tipo'] ?? '';
        $dias = (int)($_POST['dias'] ?? $_GET['dias'] ?? 1);
        echo json_encode(ItemController::previsao($tipo, $dias));
        break;

    case 'adicionarCategoria':
        if (!Session::isAdmin()) { echo json_encode(['success'=>false,'error'=>'Acesso negado.']); break; }
        $id = trim($_POST['id'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $diaria = (float)($_POST['diaria'] ?? 0);
        if (!$id || !$nome) { echo json_encode(['success'=>false,'error'=>'Dados inválidos.']); break; }
        $ok = \Lumiere\Models\Categoria::create($id, $nome, $diaria);
        echo json_encode($ok ? ['success'=>true] : ['success'=>false,'error'=>'Categoria já existe.']);
        break;

    case 'deletarCategoria':
        if (!Session::isAdmin()) { echo json_encode(['success'=>false,'error'=>'Acesso negado.']); break; }
        $id = trim($_POST['id'] ?? '');
        if (!$id) { echo json_encode(['success'=>false,'error'=>'ID inválido.']); break; }
        echo json_encode(\Lumiere\Models\Categoria::delete($id));
        break;

    case 'adicionarPacote':
        if (!Session::isAdmin()) { echo json_encode(['success'=>false,'error'=>'Acesso negado.']); break; }
        $id = trim($_POST['id'] ?? '');
        $nome = trim($_POST['nome'] ?? '');
        $capacidade = trim($_POST['capacidade'] ?? '');
        $valor = (float)($_POST['valor'] ?? 0);
        if (!$id || !$nome || !$capacidade || $valor <= 0) {
            echo json_encode(['success'=>false,'error'=>'Dados inválidos.']);
            break;
        }
        $imagem = null;
        if (!empty($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagem = \Lumiere\Models\Pacote::uploadImage($_FILES['imagem']);
            if ($imagem === null) {
                echo json_encode(['success'=>false,'error'=>'Falha no upload da imagem do pacote.']);
                break;
            }
        }
        echo json_encode(\Lumiere\Models\Pacote::create([
            'id' => $id,
            'nome' => $nome,
            'capacidade' => $capacidade,
            'valor' => $valor,
            'imagem' => $imagem,
        ]) ? ['success'=>true] : ['success'=>false,'error'=>'Pacote já existe ou dados inválidos.']);
        break;

    case 'alugarPacote':
        $id = $_POST['id'] ?? '';
        $dias = (int)($_POST['dias'] ?? 1);
        echo json_encode(\Lumiere\Controllers\ItemController::alugarPacote($id, $dias));
        break;

    case 'limparHistorico':
        if (Session::isAdmin()) {
            \Lumiere\Models\Historico::clearAll();
        } else {
            $user = Session::get('user');
            $username = $user['username'] ?? '';
            if (!$username) {
                echo json_encode(['success' => false, 'error' => 'Usuário inválido.']);
                break;
            }
            \Lumiere\Models\Historico::clearByUser($username);
        }
        echo json_encode(['success' => true]);
        break;

    case 'atualizarPacote':
        if (!Session::isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
            break;
        }
        $id = $_POST['id'] ?? '';
        $valor = (float)($_POST['valor'] ?? 0);
        if (!$id || $valor < 0) {
            echo json_encode(['success' => false, 'error' => 'Dados inválidos.']);
            break;
        }
        if (Pacote::updateValor($id, $valor)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao atualizar pacote.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Ação inválida.']);
}

