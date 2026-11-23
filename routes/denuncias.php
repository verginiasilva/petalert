<?php

require_once __DIR__ . "/../controllers/DenunciaController.php";

$controller = new DenunciaController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id']) || isset($_GET['id_denuncia'])) {
            $controller->buscar();
        } else {
            $controller->listar();
        }
        break;

    case 'POST':
        $methodOverride = strtoupper($_POST['_method'] ?? '');
        if ($methodOverride === 'PUT') {
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