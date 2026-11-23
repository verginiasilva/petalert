<?php
header('Content-Type: text/html; charset=UTF-8');

// Proteção por chave de acesso para segurança
$chave = $_GET['key'] ?? $_POST['key'] ?? '';
if ($chave !== 'petalert_diag_2026') {
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Acesso Restrito</title>';
    echo '<style>body{font-family:sans-serif;background:#FFF9F5;color:#2D2522;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;} .box{background:#fff;padding:2rem;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,0.08);max-width:400px;text-align:center;}</style></head><body>';
    echo '<div class="box"><h3>🔒 Diagnóstico Protegido</h3><p>Para executar este diagnóstico, informe a chave de segurança autorizada:</p>';
    echo '<form method="GET"><input type="password" name="key" placeholder="Chave de segurança" style="padding:10px;width:80%;margin-bottom:10px;border:1px solid #ccc;border-radius:6px;"><br><button type="submit" style="padding:10px 20px;background:#FF6B6B;color:#fff;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Acessar Diagnóstico</button></form></div></body></html>';
    exit();
}

require_once __DIR__ . '/../config/database.php';

echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Diagnóstico PetAlert</title>';
echo '<style>body{font-family:sans-serif;background:#FFF9F5;color:#2D2522;padding:2rem;} .card{background:#fff;padding:2rem;border-radius:16px;max-width:680px;margin:auto;box-shadow:0 10px 30px rgba(0,0,0,0.08);} .ok{color:#15803d;background:#dcfce7;padding:0.8rem 1rem;border-radius:10px;font-weight:bold;margin:0.5rem 0;} .erro{color:#b91c1c;background:#fee2e2;padding:0.8rem 1rem;border-radius:10px;font-weight:bold;margin:0.5rem 0;} code{background:#f1f5f9;padding:2px 6px;border-radius:4px;}</style></head><body>';
echo '<div class="card">';
echo '<h2>🐾 Diagnóstico Seguro de Conexão MySQL (PetAlert)</h2>';

try {
    $db = Database::getConnection();
    echo '<div class="ok">✅ 1. Conexão PDO com o MySQL estabelecida com sucesso!</div>';

    // Teste 1: SELECT
    $stmt = $db->query("SELECT COUNT(*) FROM tb_animal");
    $totalPets = $stmt->fetchColumn();
    echo "<div class='ok'>✅ 2. Consulta SELECT executada com sucesso! Total de pets encontrados: <strong>{$totalPets}</strong></div>";

    // Teste 2: INSERT com ROLLBACK (sem alterar dados reais)
    $db->beginTransaction();
    $insertSql = "INSERT INTO tb_animal (nome, especie, raca, idade, porte, descricao, ativo) VALUES ('__teste_temp__', 'Cachorro', 'Teste', 1, 'Pequeno', 'Registro temporário para teste de permissão', 0)";
    $db->exec($insertSql);
    $idTeste = $db->lastInsertId();
    $db->rollBack(); // Reverte imediatamente, garantindo que nada seja alterado
    echo "<div class='ok'>✅ 3. Permissão de INSERT verificada com sucesso (transação executada e revertida com ROLLBACK seguro, nenhum registro foi criado).</div>";

    // Teste 3: Tabela de fotos
    $stmtFotos = $db->query("SELECT COUNT(*) FROM tb_pet_fotos");
    $totalFotos = $stmtFotos->fetchColumn();
    echo "<div class='ok'>✅ 4. Tabela tb_pet_fotos consultada com sucesso! Total de fotos cadastradas: <strong>{$totalFotos}</strong></div>";

    echo '<p style="margin-top:1.5rem;"><a href="pets/index.html" style="display:inline-block;padding:10px 20px;background:#FF6B6B;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">🐾 Ir para o Catálogo de Pets</a></p>';

} catch (Throwable $e) {
    echo '<div class="erro">❌ Falha na conexão ou execução SQL</div>';
    echo '<p><strong>Mensagem retornada:</strong></p>';
    // Protege exibição da senha
    $msg = htmlspecialchars($e->getMessage());
    echo "<pre style='background:#f8fafc;padding:1rem;border:1px solid #e2e8f0;border-radius:8px;white-space:pre-wrap;color:#b91c1c;'>{$msg}</pre>";
}

echo '</div></body></html>';

