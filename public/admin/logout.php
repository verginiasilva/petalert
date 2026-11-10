<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Saindo do Painel Administrativo...</title>
    <script>
        localStorage.removeItem('petalert_usuario');
        sessionStorage.removeItem('petalert_usuario');
        window.location.href = '../login/index.html';
    </script>
</head>
<body>
    <p>Desconectando do Painel Administrativo do PetAlert...</p>
</body>
</html>
