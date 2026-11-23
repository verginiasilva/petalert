<?php

require_once __DIR__ . "/../controllers/VeterinarioController.php";

$controller = new VeterinarioController();

$acao = $_GET['acao'] ?? '';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($acao === 'cep') {
            $controller->consultarCep();
        } elseif (isset($_GET['id'])) {
            $controller->buscar();
        } else {
            $controller->listar();
        }
        break;

    case 'POST':
        $controller->criar();
        break;

    case 'PUT':
        $controller->atualizar();
        break;

    case 'DELETE':
        $controller->deletar();
        break;
}