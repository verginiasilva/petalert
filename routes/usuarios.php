<?php

require_once __DIR__ . "/../controllers/UsuarioController.php";

$controller = new UsuarioController();

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($acao === 'me' || $acao === 'perfil') {
            $controller->buscar();
        } elseif (isset($_GET['id'])) {
            $controller->buscar();
        } else {
            $controller->listar();
        }
        break;

    case 'POST':
        if ($acao === 'alterar-senha') {
            $controller->alterarSenha();
        } elseif ($acao === 'alterar-foto' || $acao === 'alterar-foto-perfil') {
            $controller->alterarFotoPerfil();
        } elseif ($acao === 'logout') {
            $controller->logout();
        } elseif ($acao === 'admin-criar') {
            $controller->criarAdmin();
        } elseif ($acao === 'admin-atualizar') {
            $controller->atualizarAdmin();
        } elseif ($acao === 'admin-excluir' || $acao === 'admin-deletar') {
            $controller->deletarAdmin();
        } elseif ($acao === 'excluir' || $acao === 'deletar') {
            $controller->deletar();
        } elseif ($acao === 'atualizar') {
            $controller->atualizar();
        } else {
            $controller->criar();
        }
        break;

    case 'PUT':
        if ($acao === 'alterar-senha') {
            $controller->alterarSenha();
        } elseif ($acao === 'alterar-foto' || $acao === 'alterar-foto-perfil') {
            $controller->alterarFotoPerfil();
        } elseif ($acao === 'admin-atualizar') {
            $controller->atualizarAdmin();
        } else {
            $controller->atualizar();
        }
        break;

    case 'DELETE':
        if ($acao === 'admin-excluir' || $acao === 'admin-deletar') {
            $controller->deletarAdmin();
        } else {
            $controller->deletar();
        }
        break;
}
