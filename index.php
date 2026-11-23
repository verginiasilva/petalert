<?php
/**
 * PetAlert - Ponto de Entrada da Raiz (InfinityFree)
 * No InfinityFree, este arquivo fica em /htdocs/index.php
 * As páginas públicas ficam em /htdocs/public/...
 */

// Se for requisição de API com parâmetro de rota, encaminha para o dispatcher principal
if (!empty($_GET['rota'])) {
    require __DIR__ . '/public/index.php';
    exit;
}

// Se a landing page existe, redireciona o visitante para a página inicial
if (file_exists(__DIR__ . '/public/inicio/index.html')) {
    header("Location: public/inicio/index.html", true, 302);
    exit;
}

// Mensagem informativa amigável caso os arquivos ainda não tenham sido extraídos
header("Content-Type: text/html; charset=UTF-8");
echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>PetAlert</title></head>';
echo '<body style="font-family: sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; background:#FFF9F5; color:#2D2522;">';
echo '<div style="text-align:center; max-width:500px; padding:30px; background:#fff; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.08);">';
echo '<h2 style="color:#FF6B6B;">🐾 PetAlert</h2>';
echo '<p>Os arquivos do site estão sendo carregados.</p>';
echo '<p style="color:#666; font-size:14px;">Certifique-se de que a pasta <strong>public/</strong> foi enviada e descompactada dentro de <code>/htdocs</code>.</p>';
echo '</div></body></html>';
exit;
