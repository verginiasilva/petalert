<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Webhook-Secret");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../controllers/DoacaoController.php';

$controller = new DoacaoController();
$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        if ($acao === 'mural' || isset($_GET['mural'])) {
            $controller->mural();
        } elseif ($acao === 'validar_item') {
            $controller->validarItem();
        } elseif ($acao === 'status_pagamento') {
            $controller->consultarStatus();
        } elseif (isset($_GET['id'])) {
            $controller->buscar();
        } else {
            $controller->listar();
        }
        break;

    case 'POST':
        if ($acao === 'gerar_pix' || $acao === 'pix') {
            $controller->gerarPix();
        } elseif ($acao === 'doar_insumo' || $acao === 'insumo') {
            $controller->doarInsumo();
        } elseif ($acao === 'confirmar_pix') {
            $controller->confirmarPix();
        } elseif ($acao === 'validar_item') {
            $controller->validarItem();
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
        echo json_encode(["status" => "erro", "mensagem" => "Método não permitido."]);
        break;
}