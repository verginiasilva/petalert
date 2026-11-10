<?php
/**
 * PetAlert - Redirecionamento da Raiz para a Página Inicial Pública
 */

// Se for requisição para a raiz, direciona para a página de início
$target = 'public/inicio/index.html';

// Mantém parâmetros de query string caso existam
if (!empty($_SERVER['QUERY_STRING'])) {
    $target .= '?' . $_SERVER['QUERY_STRING'];
}

header("Location: {$target}", true, 302);
exit;
