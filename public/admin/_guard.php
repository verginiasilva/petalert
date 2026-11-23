<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

function petalert_exigir_admin_pagina(): array
{
    $id = (int)($_SESSION['usuario_id'] ?? 0);
    $redirect = '../login/index.html?redirect=' . urlencode('../admin/index.php');

    if ($id <= 0) {
        header('Location: ' . $redirect);
        exit;
    }

    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id_usuario, nome, email, tipo_usuario, ativo FROM tb_usuario WHERE id_usuario = ? LIMIT 1");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        http_response_code(500);
        echo 'Erro ao validar sessão administrativa.';
        exit;
    }

    $tipo = strtolower(trim((string)($usuario['tipo_usuario'] ?? '')));
    $ativo = (int)($usuario['ativo'] ?? 0);

    if (!$usuario || $ativo !== 1 || !in_array($tipo, ['admin', 'administrador'], true)) {
        http_response_code(403);
        include __DIR__ . '/acesso-negado.php';
        exit;
    }

    return $usuario;
}
