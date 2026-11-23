<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origin)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}
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
    if (strpos($accept, 'text/html') !== false) {
        header("Location: inicio/index.html", true, 302);
        exit();
    }
    echo json_encode([
        "status" => "online",
        "mensagem" => "PetAlert API v3.0 em funcionamento."
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Mapeamento de rotas para arquivos
$rotasMap = [
    'pets'           => 'routes/pets_api.php',
    'usuarios'       => 'routes/usuarios.php',
    'auth'           => 'routes/auth.php',
    'veterinarios'   => 'routes/veterinarios.php',
    'doacao_pet'     => 'routes/doacao_pet.php',
    'doacao_dinheiro'=> 'routes/doacao_dinheiro.php',
    'denuncias'      => 'routes/denuncias.php',
    'chat'           => 'routes/chat.php',
    'ia'             => 'routes/ia.php',
    'admin'          => 'routes/admin.php',
];

if (!isset($rotasMap[$rota])) {
    http_response_code(404);
    echo json_encode(["status" => "erro", "mensagem" => "Rota não encontrada: {$rota}"]);
    exit();
}

// Detectar a raiz do projeto de forma robusta
// Funciona tanto com estrutura normal (htdocs/public/index.php -> htdocs/routes/)
// quanto após extrações parciais no InfinityFree
$projectRoot = dirname(__DIR__);
$routeFile = $projectRoot . '/' . $rotasMap[$rota];

try {
    if (!file_exists($routeFile)) {
        http_response_code(500);
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Arquivo de rota não encontrado no servidor. Verifique se todos os arquivos foram enviados corretamente para o htdocs. Arquivo esperado: " . basename($rotasMap[$rota])
        ]);
        exit();
    }
    require $routeFile;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Erro interno no servidor: " . $e->getMessage()
    ]);
}
