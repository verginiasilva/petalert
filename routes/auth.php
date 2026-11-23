<?php

require_once __DIR__ . "/../controllers/AuthController.php";

$controller = new AuthController();

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

switch ($acao) {

    case 'login':
        $controller->login();
        break;

    case 'cadastro':
        $controller->cadastro();
        break;

    case 'me':
    case 'status':
        $controller->me();
        break;

    case 'logout':
        $controller->logout();
        break;

    case 'alterar-senha':
        $controller->alterarSenha();
        break;

    case 'alterar-foto':
    case 'alterar-foto-perfil':
        $controller->alterarFotoPerfil();
        break;

    case 'excluir':
    case 'excluir-conta':
        $controller->excluirConta();
        break;

    case 'recuperar-senha':
    case 'recuperar':
    case 'esqueci-senha':
        $controller->solicitarRecuperacaoSenha();
        break;

    case 'redefinir-senha':
    case 'reset-senha':
        $controller->redefinirSenha();
        break;

    default:
        // Se nenhum parâmetro acao foi passado, mas é POST, tenta inferir
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Ação de autenticação não especificada (ex: ?rota=auth&acao=login)."
            ]);
        }
}