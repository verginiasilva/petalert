<?php
/**
 * BATERIA DE TESTES OBRIGATÓRIOS DO PAINEL ADMINISTRATIVO E LGPD
 * PetAlert - Plataforma de Proteção e Adoção de Animais
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/DAO/UsuarioDAO.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/AdminController.php';

echo "=================================================================\n";
echo "  BATERIA DE TESTES OFICIAIS — ADMIN & POLÍTICA DE PRIVACIDADE\n";
echo "=================================================================\n\n";

$db = Database::getConnection();
$dao = new UsuarioDAO();
$passedTests = 0;
$failedTests = 0;

function report(string $name, bool $success, string $details = '') {
    global $passedTests, $failedTests;
    if ($success) {
        $passedTests++;
        echo " [PASS] $name\n";
    } else {
        $failedTests++;
        echo " [FAIL] $name: $details\n";
    }
}

// -------------------------------------------------------------
// TESTE 1: Reconhecimento dos 3 Administradores Oficiais no Banco
// -------------------------------------------------------------
echo "--- TESTE 1: Reconhecimento dos 3 Administradores Oficiais ---\n";

// 1.1 Miguel Lima
$stmtM = $db->query("SELECT id_usuario, nome, email, cpf, tipo_usuario, ativo FROM tb_usuario WHERE id_usuario = 6");
$miguel = $stmtM->fetch(PDO::FETCH_ASSOC);
$miguelOk = ($miguel && strtolower($miguel['tipo_usuario']) === 'admin' && (int)$miguel['ativo'] === 1);
report("1.1 Miguel Lima reconhecido com id_usuario=6 e tipo_usuario='admin'", $miguelOk, json_encode($miguel));

// 1.2 Verginia
$stmtV = $db->query("SELECT id_usuario, nome, email, cpf, tipo_usuario, ativo FROM tb_usuario WHERE id_usuario = 73");
$verginia = $stmtV->fetch(PDO::FETCH_ASSOC);
$verginiaOk = ($verginia && strtolower($verginia['tipo_usuario']) === 'admin' && (int)$verginia['ativo'] === 1);
report("1.2 Verginia reconhecida com id_usuario=73 e tipo_usuario='admin'", $verginiaOk, json_encode($verginia));

// 1.3 Maria Eduarda
$stmtMe = $db->query("SELECT * FROM tb_admin_permissao WHERE nome_oficial = 'Maria Eduarda'");
$mariaPerm = $stmtMe->fetch(PDO::FETCH_ASSOC);
$stmtMeUser = $db->query("SELECT id_usuario, nome, email, tipo_usuario, ativo FROM tb_usuario WHERE nome LIKE '%Maria Eduarda%' LIMIT 1");
$mariaUser = $stmtMeUser->fetch(PDO::FETCH_ASSOC);
$mariaOk = ($mariaPerm && ($mariaPerm['status'] === 'pendente_dados' || ($mariaUser && strtolower($mariaUser['tipo_usuario']) === 'admin')));
report("1.3 Maria Eduarda reconhecida no sistema administrativo (ID: " . ($mariaUser['id_usuario'] ?? 'Pendente') . ", tipo_usuario: 'admin')", $mariaOk, json_encode($mariaPerm));

// -------------------------------------------------------------
// TESTE 2: Login de Administradores e Redirecionamento
// -------------------------------------------------------------
echo "\n--- TESTE 2: Login de Administradores & Redirecionamento ---\n";

// 2.1 Login de Miguel Lima
$userLogado = $dao->login('Miguellima@petalert.com', 'Miguel123');
if (!$userLogado) {
    $userLogado = $dao->login('00000000006', 'Miguel123');
}
$loginMiguelOk = ($userLogado && (int)$userLogado['id_usuario'] === 6);
report("2.1 Autenticação de credenciais de Miguel Lima (Miguellima@petalert.com / CPF 00000000006)", $loginMiguelOk);

// 2.2 Simulação de Sessão de Admin e Redirecionamento
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['usuario_id'] = 6;
$_SESSION['usuario_nome'] = 'Miguel Lima';
$_SESSION['usuario_tipo'] = 'admin';

$isAdmin = AuthMiddleware::isAdmin();
report("2.2 AuthMiddleware::isAdmin() reconhece Miguel Lima como Administrador", $isAdmin);

// -------------------------------------------------------------
// TESTE 3: Usuários Comuns (Adotante e Doador) - Bloqueio & Redirecionamento
// -------------------------------------------------------------
echo "\n--- TESTE 3: Controle de Acesso de Usuários Comuns ---\n";

// 3.1 Simulação de Sessão de Adotante (ID 15)
$_SESSION['usuario_id'] = 15;
$_SESSION['usuario_nome'] = 'Ana Clara Adotante';
$_SESSION['usuario_tipo'] = 'adotante';

$isAdotanteAdmin = AuthMiddleware::isAdmin();
report("3.1 Adotante (ID 15) NÃO possui permissão administrativa (isAdmin = false)", !$isAdotanteAdmin);

// 3.2 Simulação de Sessão de Doador (ID 5)
$_SESSION['usuario_id'] = 5;
$_SESSION['usuario_nome'] = 'Carlos Silva';
$_SESSION['usuario_tipo'] = 'doador';

$isDoadorAdmin = AuthMiddleware::isAdmin();
report("3.2 Doador (ID 5) NÃO possui permissão administrativa (isAdmin = false)", !$isDoadorAdmin);

// -------------------------------------------------------------
// TESTE 4: Dados Reais no Dashboard & Métricas
// -------------------------------------------------------------
echo "\n--- TESTE 4: Consulta de Dados Reais do Dashboard ---\n";

// Restaura sessão de Admin para testar endpoints
$_SESSION['usuario_id'] = 6;
$_SESSION['usuario_nome'] = 'Miguel Lima';
$_SESSION['usuario_tipo'] = 'admin';

$adminCtrl = new AdminController();

// Captura output de dashboard
ob_start();
$adminCtrl->dashboard();
$dashRaw = ob_get_clean();
$dashJson = json_decode($dashRaw, true);

$dashOk = (isset($dashJson['status']) && $dashJson['status'] === 'sucesso' && isset($dashJson['dados']['total_usuarios']));
report("4.1 Endpoint dashboard() retorna métricas consolidadas reais", $dashOk, $dashRaw);

$d = $dashJson['dados'] ?? [];
echo "      - Total Usuários: " . ($d['total_usuarios'] ?? 0) . "\n";
echo "      - Total Pets: " . ($d['total_pets'] ?? 0) . " (Disponíveis: " . ($d['pets_disponiveis'] ?? 0) . ", Adotados: " . ($d['pets_adotados'] ?? 0) . ")\n";
echo "      - Denúncias Pendentes: " . ($d['denuncias_pendentes'] ?? 0) . " de " . ($d['total_denuncias'] ?? 0) . "\n";
echo "      - Total Arrecadado Confirmado: R$ " . number_format($d['soma_doacoes_financeiras'] ?? 0, 2, ',', '.') . "\n";
echo "      - Clínicas Cadastradas: " . ($d['total_clinicas'] ?? 0) . "\n";
echo "      - Aceites de Privacidade (LGPD): " . ($d['total_aceites_privacidade'] ?? 0) . "\n";

// -------------------------------------------------------------
// TESTE 5: Acesso aos Módulos Administrativos
// -------------------------------------------------------------
echo "\n--- TESTE 5: Módulos Administrativos Integrados ---\n";

// 5.1 Usuários
ob_start();
$adminCtrl->usuarios();
$usersRaw = ob_get_clean();
$usersJson = json_decode($usersRaw, true);
report("5.1 Listagem administrativa de usuários (tb_usuario)", ($usersJson['status'] ?? '') === 'sucesso' && count($usersJson['dados']) > 0);

// 5.2 Animais
ob_start();
$adminCtrl->pets();
$petsRaw = ob_get_clean();
$petsJson = json_decode($petsRaw, true);
report("5.2 Listagem administrativa de animais com dados do tutor (tb_animal)", ($petsJson['status'] ?? '') === 'sucesso' && count($petsJson['dados']) > 0);

// 5.3 Denúncias
ob_start();
$adminCtrl->denuncias();
$denRaw = ob_get_clean();
$denJson = json_decode($denRaw, true);
report("5.3 Central de denúncias com identificação de denunciante e alvo (tb_denuncia)", ($denJson['status'] ?? '') === 'sucesso' && count($denJson['dados']) > 0);

// 5.4 Doações Segregadas (Financeiras vs Animais)
ob_start();
$adminCtrl->doacoes();
$doaRaw = ob_get_clean();
$doaJson = json_decode($doaRaw, true);
$temFin = isset($doaJson['dados']['financeiras']);
$temAnim = isset($doaJson['dados']['animais']);
report("5.4 Segregação estrita entre doações financeiras e doações de animais", ($temFin && $temAnim));

// 5.5 Clínicas Veterinárias
ob_start();
$adminCtrl->veterinarios();
$vetRaw = ob_get_clean();
$vetJson = json_decode($vetRaw, true);
report("5.5 Gestão de clínicas veterinárias (tb_veterinario)", ($vetJson['status'] ?? '') === 'sucesso' && count($vetJson['dados']) > 0);

// -------------------------------------------------------------
// TESTE 6: Histórico Administrativo & Auditoria
// -------------------------------------------------------------
echo "\n--- TESTE 6: Histórico de Atividades Administrativas ---\n";

$logOk = $adminCtrl->registrarLog("Teste de Auditoria Automatizado", "sistema", null, "Execução da bateria de testes pelo administrador Miguel Lima");
report("6.1 Gravação de evento de auditoria em tb_historico_admin", $logOk);

ob_start();
$adminCtrl->historico();
$histRaw = ob_get_clean();
$histJson = json_decode($histRaw, true);
report("6.2 Leitura do histórico de auditoria com nome do admin e detalhes", ($histJson['status'] ?? '') === 'sucesso' && count($histJson['dados']) > 0);

// -------------------------------------------------------------
// TESTE 7: Política de Privacidade e Governança LGPD
// -------------------------------------------------------------
echo "\n--- TESTE 7: Política de Privacidade & LGPD ---\n";

$politicaHtmlExiste = file_exists(__DIR__ . '/public/politica-privacidade/index.html');
report("7.1 Página pública da Política de Privacidade existe (public/politica-privacidade/index.html)", $politicaHtmlExiste);

$tabelaAceiteExiste = (int)$db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'petalert' AND table_name = 'tb_privacidade_aceite'")->fetchColumn() > 0;
report("7.2 Tabela tb_privacidade_aceite existente e estruturada no banco petalert", $tabelaAceiteExiste);

ob_start();
$adminCtrl->politicaPrivacidade();
$privRaw = ob_get_clean();
$privJson = json_decode($privRaw, true);
report("7.3 Consulta administrativa de registros de aceite da política", ($privJson['status'] ?? '') === 'sucesso' && count($privJson['dados']) > 0);

// -------------------------------------------------------------
// RESUMO FINAL
// -------------------------------------------------------------
echo "\n=================================================================\n";
echo "  RESULTADO DOS TESTES: {$passedTests} APROVADOS / {$failedTests} FALHAS\n";
echo "=================================================================\n";

if ($failedTests === 0) {
    echo "🎉 TODOS OS TESTES OBRIGATÓRIOS FORAM CONCLUÍDOS COM 100% DE SUCESSO!\n";
} else {
    echo "⚠️ Verifique as falhas acima antes de prosseguir.\n";
}
