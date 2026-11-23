<?php

require_once __DIR__ . "/../controllers/DoacaoPetController.php";

$controller = new DoacaoPetController();

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        if(isset($_GET['id'])) {
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