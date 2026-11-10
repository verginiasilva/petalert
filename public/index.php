<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$rota = $_GET['rota'] ?? '';

// Se acessou /public/ diretamente pelo navegador sem especificar rota, redireciona para a página inicial
if ($rota === '') {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (str_contains($accept, 'text/html')) {
        header("Location: inicio/index.html", true, 302);
        exit();
    }
    echo json_encode([
        "status" => "online",
        "mensagem" => "PetAlert API v3.0 em funcionamento."
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

switch ($rota) {

    case 'pets':
        require __DIR__ . '/../routes/pets_api.php';
        break;

    case 'usuarios':
        require __DIR__ . '/../routes/usuarios.php';
        break;

    case 'auth':
        require __DIR__ . '/../routes/auth.php';
        break;

    case 'veterinarios':
        require __DIR__ . '/../routes/veterinarios.php';
        break;

    case 'doacao_pet':
        require __DIR__ . '/../routes/doacao_pet.php';
        break;

    case 'doacao_dinheiro':
        require __DIR__ . '/../routes/doacao_dinheiro.php';
        break;

    case 'denuncias':
        require __DIR__ . '/../routes/denuncias.php';
        break;

    case 'chat':
        require __DIR__ . '/../routes/chat.php';
        break;

    case 'ia':
        require __DIR__ . '/../routes/ia.php';
        break;

    case 'admin':
        require __DIR__ . '/../routes/admin.php';
        break;

    default:
        echo json_encode([
            "erro" => "Rota não encontrada"
        ]);
}