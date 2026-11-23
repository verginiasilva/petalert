<?php

require_once __DIR__ . "/../controllers/ChatController.php";

$controller = new ChatController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['acao']) && $_GET['acao'] === 'pet-context') {
            $controller->buscarContextoPet();
        } elseif (isset($_GET['acao']) && $_GET['acao'] === 'minhas-conversas') {
            $controller->buscarConversasPorUsuario();
        } elseif (isset($_GET['id_pet'])) {
            $controller->buscarPorPet();
        } elseif (isset($_GET['id_usuario'])) {
            $controller->buscarPorUsuario();
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