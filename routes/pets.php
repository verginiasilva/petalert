<?php

require_once __DIR__ . "/../controllers/PetController.php";

$controller = new PetController();

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($acao === 'meus-pets') {
            $controller->meusPets();
        } elseif ($acao === 'interessados') {
            $controller->interessados();
        } elseif (isset($_GET['id']) || isset($_GET['id_pet'])) {
            $controller->buscar();
        } else {
            $controller->listar();
        }
        break;

    case 'POST':
        // Suporte a emulação de PUT/DELETE em form data via _method
        $methodOverride = strtoupper($_POST['_method'] ?? '');
        if ($acao === 'classificar-doado') {
            $controller->classificarDoado();
        } elseif ($methodOverride === 'PUT') {
            $controller->atualizar();
        } elseif ($methodOverride === 'DELETE') {
            $controller->deletar();
        } else {
            $controller->criar();
        }
        break;

    case 'PUT':
        $controller->atualizar();
        break;

    case 'DELETE':
        $controller->deletar();
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "erro", "mensagem" => "Método não permitido"]);
}