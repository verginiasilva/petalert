<?php

if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../DAO/UsuarioDAO.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AdminController
{
    private PDO $pdo;
    private UsuarioDAO $usuarioDAO;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->usuarioDAO = new UsuarioDAO();
    }

    private function getAdminLogado(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = (int)($_SESSION['usuario_id'] ?? 0);
        $nome = $_SESSION['usuario_nome'] ?? 'Administrador';
        return ['id' => $id, 'nome' => $nome];
    }

    public function registrarLog(string $acao, string $modulo, ?int $idRegistro = null, ?string $detalhes = null): bool
    {
        try {
            $admin = $this->getAdminLogado();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $this->pdo->prepare("INSERT INTO tb_historico_admin (id_usuario, nome_admin, acao, modulo, id_registro, detalhes, ip) VALUES (?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $admin['id'],
                $admin['nome'],
                $acao,
                $modulo,
                $idRegistro,
                $detalhes,
                $ip
            ]);
        } catch (Exception $e) {
            error_log("[AdminController] Erro registrarLog: " . $e->getMessage());
            return false;
        }
    }

    private function lerPayload(): array
    {
        $raw = file_get_contents("php://input");
        $json = json_decode($raw, true);
        if (is_array($json) && !empty($json)) {
            return $json;
        }
        return $_POST ?: [];
    }

    // ==========================================
    // 1. DASHBOARD & MÉTRICAS
    // ==========================================
    public function dashboard(): void
    {
        AuthMiddleware::verificarAdmin();

        $q = function (string $sql, array $params = []) {
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                return (int)$stmt->fetchColumn();
            } catch (Exception $e) {
                return 0;
            }
        };

        $qFloat = function (string $sql, array $params = []) {
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                return (float)$stmt->fetchColumn();
            } catch (Exception $e) {
                return 0.0;
            }
        };

        // Métricas reais do banco petalert
        $totalUsuarios = $q("SELECT COUNT(*) FROM tb_usuario");
        $totalAdotantes = $q("SELECT COUNT(*) FROM tb_usuario WHERE LOWER(TRIM(tipo_usuario)) = 'adotante'");
        $totalDoadores = $q("SELECT COUNT(*) FROM tb_usuario WHERE LOWER(TRIM(tipo_usuario)) = 'doador'");
        $totalAdmins = $q("SELECT COUNT(*) FROM tb_usuario WHERE LOWER(TRIM(tipo_usuario)) IN ('admin', 'administrador')");
        
        $totalPets = $q("SELECT COUNT(*) FROM tb_animal");
        $petsDisponiveis = $q("SELECT COUNT(*) FROM tb_animal WHERE LOWER(TRIM(COALESCE(status_adocao,''))) IN ('disponivel','disponível') AND COALESCE(ativo,1) = 1");
        $petsAdotados = $q("SELECT COUNT(*) FROM tb_animal WHERE LOWER(TRIM(COALESCE(status_adocao,''))) IN ('doado','adotado','adotada')");
        
        $totalDenuncias = $q("SELECT COUNT(*) FROM tb_denuncia");
        $denunciasPendentes = $q("SELECT COUNT(*) FROM tb_denuncia WHERE LOWER(TRIM(status)) IN ('pendente','em_analise')");
        $denunciasResolvidas = $q("SELECT COUNT(*) FROM tb_denuncia WHERE LOWER(TRIM(status)) = 'resolvida'");

        $totalDoacoes = $q("SELECT COUNT(*) FROM tb_doacao");
        $doacoesFinanceiras = $q("SELECT COUNT(*) FROM tb_doacao WHERE LOWER(TRIM(tipo_doacao)) = 'financeira' OR valor_doado > 0") + $q("SELECT COUNT(*) FROM tb_doacao_dinheiro");
        $somaArrecadada = $qFloat("SELECT SUM(valor_doado) FROM tb_doacao WHERE LOWER(TRIM(status_pagamento)) IN ('pago', 'confirmado')") + $qFloat("SELECT SUM(valor) FROM tb_doacao_dinheiro");
        
        $totalDoacoesPets = $q("SELECT COUNT(*) FROM tb_doapet") + $q("SELECT COUNT(*) FROM tb_doacao_pet");
        $totalClinicas = $q("SELECT COUNT(*) FROM tb_veterinario WHERE COALESCE(ativo, 1) = 1");
        $totalAceitesPrivacidade = $q("SELECT COUNT(*) FROM tb_privacidade_aceite");

        // Séries mensais reais para os gráficos dinâmicos
        $meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $mesAno = date('Y-m', strtotime("-$i months"));
            $rotulo = date('M/Y', strtotime("-$i months"));
            $meses[$mesAno] = [
                'rotulo' => $rotulo,
                'cadastros' => 0,
                'adocoes' => 0,
                'doacoes' => 0.0
            ];
        }

        try {
            $stmtC = $this->pdo->query("SELECT DATE_FORMAT(data_criacao, '%Y-%m') as mes, COUNT(*) as c FROM tb_usuario WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY mes");
            while ($r = $stmtC->fetch(PDO::FETCH_ASSOC)) {
                if (isset($meses[$r['mes']])) {
                    $meses[$r['mes']]['cadastros'] = (int)$r['c'];
                }
            }
        } catch (Exception $e) {}

        try {
            $stmtA = $this->pdo->query("SELECT DATE_FORMAT(data_cadastro, '%Y-%m') as mes, COUNT(*) as c FROM tb_animal WHERE LOWER(TRIM(COALESCE(status_adocao,''))) IN ('doado','adotado','adotada') AND data_cadastro >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY mes");
            while ($r = $stmtA->fetch(PDO::FETCH_ASSOC)) {
                if (isset($meses[$r['mes']])) {
                    $meses[$r['mes']]['adocoes'] = (int)$r['c'];
                }
            }
        } catch (Exception $e) {}

        try {
            $stmtD = $this->pdo->query("SELECT DATE_FORMAT(data_criacao, '%Y-%m') as mes, SUM(valor_doado) as total FROM tb_doacao WHERE LOWER(TRIM(status_pagamento)) IN ('pago','confirmado') AND data_criacao >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY mes");
            while ($r = $stmtD->fetch(PDO::FETCH_ASSOC)) {
                if (isset($meses[$r['mes']])) {
                    $meses[$r['mes']]['doacoes'] = (float)$r['total'];
                }
            }
        } catch (Exception $e) {}

        echo json_encode([
            "status" => "sucesso",
            "dados" => [
                "total_usuarios" => $totalUsuarios,
                "total_adotantes" => $totalAdotantes,
                "total_doadores" => $totalDoadores,
                "total_admins" => $totalAdmins,
                "total_pets" => $totalPets,
                "pets_disponiveis" => $petsDisponiveis,
                "pets_adotados" => $petsAdotados,
                "total_denuncias" => $totalDenuncias,
                "denuncias_pendentes" => $denunciasPendentes,
                "denuncias_resolvidas" => $denunciasResolvidas,
                "total_doacoes" => $totalDoacoes,
                "total_doacoes_financeiras" => $doacoesFinanceiras,
                "soma_doacoes_financeiras" => $somaArrecadada,
                "total_doacoes_animais" => $totalDoacoesPets,
                "total_clinicas" => $totalClinicas,
                "total_aceites_privacidade" => $totalAceitesPrivacidade,
                "series_graficos" => array_values($meses)
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    // ==========================================
    // 2. GERENCIAMENTO DE USUÁRIOS
    // ==========================================
    public function usuarios(): void
    {
        AuthMiddleware::verificarAdmin();
        $termo = trim((string)($_GET['termo'] ?? $_GET['busca'] ?? ''));
        $tipo = trim((string)($_GET['tipo'] ?? ''));

        $sql = "SELECT id_usuario, nome, email, telefone, cpf, cep, rua, numero, bairro, cidade, estado, endereco, data_nascimento, idade, tipo_usuario, foto, status_identidade, ativo, data_criacao 
                FROM tb_usuario 
                WHERE 1=1";
        $params = [];

        if ($termo !== '') {
            $sql .= " AND (nome LIKE ? OR email LIKE ? OR cpf LIKE ?)";
            $params[] = "%$termo%";
            $params[] = "%$termo%";
            $params[] = "%$termo%";
        }

        if ($tipo !== '') {
            if ($tipo === 'admin') {
                $sql .= " AND LOWER(TRIM(tipo_usuario)) IN ('admin', 'administrador')";
            } else {
                $sql .= " AND LOWER(TRIM(tipo_usuario)) = ?";
                $params[] = strtolower($tipo);
            }
        }

        $sql .= " ORDER BY id_usuario DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        echo json_encode([
            "status" => "sucesso",
            "total" => count($dados),
            "dados" => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    public function usuarioAlternarStatus(): void
    {
        AuthMiddleware::verificarAdmin();
        $payload = $this->lerPayload();
        $id = (int)($payload['id_usuario'] ?? $payload['id'] ?? $_GET['id'] ?? 0);
        $admin = $this->getAdminLogado();

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID do usuário não informado."]);
            return;
        }

        if ($id === $admin['id']) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Você não pode bloquear sua própria conta de administrador."]);
            return;
        }

        $stmtU = $this->pdo->prepare("SELECT nome, tipo_usuario, ativo FROM tb_usuario WHERE id_usuario = ?");
        $stmtU->execute([$id]);
        $alvo = $stmtU->fetch(PDO::FETCH_ASSOC);

        if (!$alvo) {
            http_response_code(404);
            echo json_encode(["status" => "erro", "mensagem" => "Usuário não encontrado."]);
            return;
        }

        $novoStatus = ((int)$alvo['ativo'] === 1) ? 0 : 1;
        
        // Se for admin, impedir desativação do último admin ativo
        if (in_array(strtolower($alvo['tipo_usuario']), ['admin', 'administrador']) && $novoStatus === 0) {
            if ($this->usuarioDAO->contarAdminsAtivos() <= 1) {
                http_response_code(409);
                echo json_encode(["status" => "erro", "mensagem" => "Não é possível bloquear o único administrador ativo do sistema."]);
                return;
            }
        }

        $stmtUp = $this->pdo->prepare("UPDATE tb_usuario SET ativo = ? WHERE id_usuario = ?");
        $stmtUp->execute([$novoStatus, $id]);

        $acaoTxt = ($novoStatus === 1) ? "desbloqueou" : "bloqueou";
        $this->registrarLog(
            ucfirst($acaoTxt) . " usuário",
            "usuarios",
            $id,
            "{$admin['nome']} {$acaoTxt} a conta de '{$alvo['nome']}' (ID: {$id})"
        );

        echo json_encode([
            "status" => "sucesso",
            "mensagem" => "Usuário " . ($novoStatus === 1 ? "desbloqueado e ativado" : "bloqueado e desativado") . " com sucesso!",
            "novo_status" => $novoStatus
        ], JSON_UNESCAPED_UNICODE);
    }

    // ==========================================
    // 3. GERENCIAMENTO DE ANIMAIS (PETS)
    // ==========================================
    public function pets(): void
    {
        AuthMiddleware::verificarAdmin();
        $termo = trim((string)($_GET['termo'] ?? $_GET['busca'] ?? ''));
        $especie = trim((string)($_GET['especie'] ?? ''));
        $porte = trim((string)($_GET['porte'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));

        $sql = "SELECT a.id_pet, a.nome, a.especie, a.raca, a.porte, a.peso, a.cor, a.idade, a.descricao, a.motivo_doacao, a.status_adocao, a.ativo, a.foto, a.data_cadastro, a.id_usuario, u.nome AS nome_tutor, u.email AS email_tutor, u.telefone AS telefone_tutor, u.cidade AS cidade_tutor, u.estado AS estado_tutor
                FROM tb_animal a
                LEFT JOIN tb_usuario u ON u.id_usuario = a.id_usuario
                WHERE 1=1";
        $params = [];

        if ($termo !== '') {
            $sql .= " AND (a.nome LIKE ? OR a.raca LIKE ? OR u.nome LIKE ?)";
            $params[] = "%$termo%";
            $params[] = "%$termo%";
            $params[] = "%$termo%";
        }

        if ($especie !== '') {
            $sql .= " AND LOWER(TRIM(a.especie)) = ?";
            $params[] = strtolower($especie);
        }

        if ($porte !== '') {
            $sql .= " AND LOWER(TRIM(a.porte)) = ?";
            $params[] = strtolower($porte);
        }

        if ($status !== '') {
            $sql .= " AND LOWER(TRIM(a.status_adocao)) = ?";
            $params[] = strtolower($status);
        }

        $sql .= " ORDER BY a.id_pet DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        echo json_encode([
            "status" => "sucesso",
            "total" => count($dados),
            "dados" => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    public function petSalvar(): void
    {
        AuthMiddleware::verificarAdmin();
        $payload = $this->lerPayload();
        $idPet = (int)($payload['id_pet'] ?? $payload['id'] ?? 0);
        $admin = $this->getAdminLogado();

        if ($idPet <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID do pet é obrigatório."]);
            return;
        }

        $nome = trim((string)($payload['nome'] ?? ''));
        $especie = trim((string)($payload['especie'] ?? ''));
        $raca = trim((string)($payload['raca'] ?? ''));
        $porte = trim((string)($payload['porte'] ?? ''));
        $idade = isset($payload['idade']) ? (int)$payload['idade'] : null;
        $statusAdocao = trim((string)($payload['status_adocao'] ?? 'disponivel'));
        $descricao = trim((string)($payload['descricao'] ?? ''));

        $stmt = $this->pdo->prepare("UPDATE tb_animal SET nome = ?, especie = ?, raca = ?, porte = ?, idade = ?, status_adocao = ?, descricao = ? WHERE id_pet = ?");
        $stmt->execute([$nome, $especie, $raca, $porte, $idade, $statusAdocao, $descricao, $idPet]);

        $this->registrarLog(
            "Edição de animal",
            "animais",
            $idPet,
            "{$admin['nome']} editou as informações do pet '{$nome}' (ID: {$idPet})"
        );

        echo json_encode(["status" => "sucesso", "mensagem" => "Pet atualizado com sucesso!"]);
    }

    public function petMarcarAdotado(): void
    {
        AuthMiddleware::verificarAdmin();
        $payload = $this->lerPayload();
        $idPet = (int)($payload['id_pet'] ?? $payload['id'] ?? 0);
        $novoStatus = trim((string)($payload['status_adocao'] ?? 'adotado'));
        $admin = $this->getAdminLogado();

        if ($idPet <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID do pet é obrigatório."]);
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE tb_animal SET status_adocao = ? WHERE id_pet = ?");
        $stmt->execute([$novoStatus, $idPet]);

        $this->registrarLog(
            "Alteração de status do pet",
            "animais",
            $idPet,
            "{$admin['nome']} alterou o status do pet ID {$idPet} para '{$novoStatus}'"
        );

        echo json_encode(["status" => "sucesso", "mensagem" => "Status do pet atualizado para '{$novoStatus}'!"]);
    }

    public function petExcluir(): void
    {
        AuthMiddleware::verificarAdmin();
        $payload = $this->lerPayload();
        $idPet = (int)($payload['id_pet'] ?? $payload['id'] ?? 0);
        $admin = $this->getAdminLogado();

        if ($idPet <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID do pet é obrigatório."]);
            return;
        }

        // Buscar nome antes de excluir
        $stmtB = $this->pdo->prepare("SELECT nome FROM tb_animal WHERE id_pet = ?");
        $stmtB->execute([$idPet]);
        $petNome = $stmtB->fetchColumn() ?: "ID $idPet";

        // Excluir registros dependentes seguros
        try {
            $this->pdo->prepare("DELETE FROM tb_chat WHERE id_pet = ?")->execute([$idPet]);
            $this->pdo->prepare("DELETE FROM tb_doapet WHERE id_pet = ?")->execute([$idPet]);
            $this->pdo->prepare("DELETE FROM tb_doacao_pet WHERE id_pet = ?")->execute([$idPet]);
        } catch (Exception $e) {}

        $stmt = $this->pdo->prepare("DELETE FROM tb_animal WHERE id_pet = ?");
        $stmt->execute([$idPet]);

        $this->registrarLog(
            "Exclusão de anúncio de animal",
            "animais",
            $idPet,
            "{$admin['nome']} excluiu o anúncio do pet '{$petNome}' (ID: {$idPet})"
        );

        echo json_encode(["status" => "sucesso", "mensagem" => "Anúncio do pet excluído com sucesso!"]);
    }

    // ==========================================
    // 4. CENTRAL DE DENÚNCIAS
    // ==========================================
    public function denuncias(): void
    {
        AuthMiddleware::verificarAdmin();
        $status = trim((string)($_GET['status'] ?? ''));

        $sql = "SELECT d.id_denuncia, d.id_usuario, d.id_pet, d.motivo, d.tipo, d.descricao, d.localizacao, d.foto, d.video, d.status, d.data_criacao, u.nome AS nome_denunciante, u.email AS email_denunciante, u.telefone AS telefone_denunciante, p.nome AS nome_pet, p.id_usuario AS id_tutor_denunciado, ut.nome AS nome_tutor_denunciado
                FROM tb_denuncia d
                LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario
                LEFT JOIN tb_animal p ON p.id_pet = d.id_pet
                LEFT JOIN tb_usuario ut ON ut.id_usuario = p.id_usuario
                WHERE 1=1";
        $params = [];

        if ($status !== '') {
            $sql .= " AND LOWER(TRIM(d.status)) = ?";
            $params[] = strtolower($status);
        }

        $sql .= " ORDER BY d.id_denuncia DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        echo json_encode([
            "status" => "sucesso",
            "total" => count($dados),
            "dados" => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    public function denunciaAtualizar(): void
    {
        AuthMiddleware::verificarAdmin();
        $payload = $this->lerPayload();
        $id = (int)($payload['id_denuncia'] ?? $payload['id'] ?? 0);
        $novoStatus = trim((string)($payload['status'] ?? ''));
        $observacoes = trim((string)($payload['observacoes'] ?? ''));
        $admin = $this->getAdminLogado();

        if ($id <= 0 || $novoStatus === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID da denúncia e novo status são obrigatórios."]);
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE tb_denuncia SET status = ? WHERE id_denuncia = ?");
        $stmt->execute([$novoStatus, $id]);

        $detalhesLog = "{$admin['nome']} alterou o status da denúncia #{$id} para '{$novoStatus}'";
        if ($observacoes !== '') {
            $detalhesLog .= ". Observações: {$observacoes}";
        }

        $this->registrarLog(
            "Atualização de denúncia",
            "denuncias",
            $id,
            $detalhesLog
        );

        echo json_encode(["status" => "sucesso", "mensagem" => "Denúncia atualizada com sucesso!"]);
    }

    // ==========================================
    // 5. GERENCIAMENTO DE DOAÇÕES
    // ==========================================
    public function doacoes(): void
    {
        AuthMiddleware::verificarAdmin();

        $itensFinanceiros = [];
        $itensAnimais = [];

        // 1. Doações Financeiras (tb_doacao onde tipo_doacao = 'financeira' ou valor_doado > 0, mais tb_doacao_dinheiro)
        try {
            $stmtF = $this->pdo->query("SELECT d.id_doacao, d.id_usuario, d.nome_doador, d.tipo_doacao, d.valor_doado, d.objeto_doado, d.status_pagamento, d.txid, d.data_doacao, d.data_criacao, d.observacoes, u.nome AS nome_usuario, u.email AS email_usuario, u.telefone AS telefone_usuario
                                        FROM tb_doacao d
                                        LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario
                                        WHERE LOWER(TRIM(d.tipo_doacao)) = 'financeira' OR d.valor_doado > 0
                                        ORDER BY d.id_doacao DESC");
            $itensFinanceiros = $stmtF->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {}

        // Doações adicionais em tb_doacao_dinheiro
        try {
            $stmtFD = $this->pdo->query("SELECT d.id_doacao, d.id_usuario, d.valor AS valor_doado, d.tipo_pagamento, d.chave_pix, d.data_doacao AS data_criacao, u.nome AS nome_usuario, u.email AS email_usuario, 'pago' AS status_pagamento
                                         FROM tb_doacao_dinheiro d
                                         LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario
                                         ORDER BY d.id_doacao DESC");
            $din = $stmtFD->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($din as $item) {
                $item['tipo_doacao'] = 'financeira (PIX)';
                $itensFinanceiros[] = $item;
            }
        } catch (Exception $e) {}

        // 2. Doações de Animais (tb_doapet e tb_doacao_pet)
        try {
            $stmtA = $this->pdo->query("SELECT dp.id_usuario, dp.id_pet, dp.motivo_doacao, dp.data_doacao, u.nome AS nome_doador, a.nome AS nome_pet, a.especie, a.raca, a.foto, a.status_adocao
                                        FROM tb_doapet dp
                                        LEFT JOIN tb_usuario u ON u.id_usuario = dp.id_usuario
                                        LEFT JOIN tb_animal a ON a.id_pet = dp.id_pet
                                        ORDER BY dp.data_doacao DESC");
            $itensAnimais = $stmtA->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {}

        echo json_encode([
            "status" => "sucesso",
            "dados" => [
                "financeiras" => $itensFinanceiros,
                "animais" => $itensAnimais,
                "total_financeiras" => count($itensFinanceiros),
                "total_animais" => count($itensAnimais)
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    // ==========================================
    // 6. CLÍNICAS VETERINÁRIAS
    // ==========================================
    public function veterinarios(): void
    {
        AuthMiddleware::verificarAdmin();
        $termo = trim((string)($_GET['termo'] ?? ''));
        $cidade = trim((string)($_GET['cidade'] ?? ''));

        $sql = "SELECT id_veterinario, nome, telefone, endereco, cidade, estado, cep, crmv, email, horario_atendimento, site, foto, ativo, data_cadastro
                FROM tb_veterinario
                WHERE 1=1";
        $params = [];

        if ($termo !== '') {
            $sql .= " AND (nome LIKE ? OR crmv LIKE ?)";
            $params[] = "%$termo%";
            $params[] = "%$termo%";
        }

        if ($cidade !== '') {
            $sql .= " AND LOWER(TRIM(cidade)) LIKE ?";
            $params[] = "%" . strtolower($cidade) . "%";
        }

        $sql .= " ORDER BY id_veterinario DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        echo json_encode([
            "status" => "sucesso",
            "total" => count($dados),
            "dados" => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    public function veterinarioSalvar(): void
    {
        AuthMiddleware::verificarAdmin();
        $payload = $this->lerPayload();
        $id = (int)($payload['id_veterinario'] ?? $payload['id'] ?? 0);
        $admin = $this->getAdminLogado();

        $nome = trim((string)($payload['nome'] ?? ''));
        if ($nome === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Nome da clínica é obrigatório."]);
            return;
        }

        $telefone = trim((string)($payload['telefone'] ?? ''));
        $endereco = trim((string)($payload['endereco'] ?? ''));
        $cidade = trim((string)($payload['cidade'] ?? ''));
        $estado = strtoupper(trim((string)($payload['estado'] ?? '')));
        $cep = trim((string)($payload['cep'] ?? ''));
        $crmv = trim((string)($payload['crmv'] ?? ''));
        $email = trim((string)($payload['email'] ?? ''));
        $horario = trim((string)($payload['horario_atendimento'] ?? ''));
        $site = trim((string)($payload['site'] ?? ''));

        if ($id > 0) {
            $stmt = $this->pdo->prepare("UPDATE tb_veterinario SET nome=?, telefone=?, endereco=?, cidade=?, estado=?, cep=?, crmv=?, email=?, horario_atendimento=?, site=? WHERE id_veterinario=?");
            $stmt->execute([$nome, $telefone, $endereco, $cidade, $estado, $cep, $crmv, $email, $horario, $site, $id]);
            $this->registrarLog("Edição de clínica veterinária", "veterinarios", $id, "{$admin['nome']} atualizou dados da clínica '{$nome}' (ID: {$id})");
            echo json_encode(["status" => "sucesso", "mensagem" => "Clínica atualizada com sucesso!"]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO tb_veterinario (nome, telefone, endereco, cidade, estado, cep, crmv, email, horario_atendimento, site, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$nome, $telefone, $endereco, $cidade, $estado, $cep, $crmv, $email, $horario, $site]);
            $novoId = (int)$this->pdo->lastInsertId();
            $this->registrarLog("Cadastro de clínica veterinária", "veterinarios", $novoId, "{$admin['nome']} cadastrou nova clínica '{$nome}' (ID: {$novoId})");
            echo json_encode(["status" => "sucesso", "mensagem" => "Clínica cadastrada com sucesso!", "id" => $novoId]);
        }
    }

    public function veterinarioExcluir(): void
    {
        AuthMiddleware::verificarAdmin();
        $payload = $this->lerPayload();
        $id = (int)($payload['id_veterinario'] ?? $payload['id'] ?? 0);
        $admin = $this->getAdminLogado();

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID da clínica é obrigatório."]);
            return;
        }

        $stmtB = $this->pdo->prepare("SELECT nome FROM tb_veterinario WHERE id_veterinario = ?");
        $stmtB->execute([$id]);
        $nomeClinica = $stmtB->fetchColumn() ?: "ID $id";

        $stmt = $this->pdo->prepare("DELETE FROM tb_veterinario WHERE id_veterinario = ?");
        $stmt->execute([$id]);

        $this->registrarLog("Exclusão de clínica veterinária", "veterinarios", $id, "{$admin['nome']} removeu o registro da clínica '{$nomeClinica}' (ID: {$id})");

        echo json_encode(["status" => "sucesso", "mensagem" => "Clínica excluída com sucesso!"]);
    }

    // ==========================================
    // 7. HISTÓRICO DE ATIVIDADES ADMINISTRATIVAS
    // ==========================================
    public function historico(): void
    {
        AuthMiddleware::verificarAdmin();
        $modulo = trim((string)($_GET['modulo'] ?? ''));

        $sql = "SELECT h.id_log, h.id_usuario, h.nome_admin, h.acao, h.modulo, h.id_registro, h.detalhes, h.ip, h.data_hora, u.email AS email_admin
                FROM tb_historico_admin h
                LEFT JOIN tb_usuario u ON u.id_usuario = h.id_usuario
                WHERE 1=1";
        $params = [];

        if ($modulo !== '') {
            $sql .= " AND LOWER(TRIM(h.modulo)) = ?";
            $params[] = strtolower($modulo);
        }

        $sql .= " ORDER BY h.id_log DESC LIMIT 200";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        echo json_encode([
            "status" => "sucesso",
            "total" => count($dados),
            "dados" => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    // ==========================================
    // 8. POLÍTICA DE PRIVACIDADE & LGPD
    // ==========================================
    public function politicaPrivacidade(): void
    {
        AuthMiddleware::verificarAdmin();

        $stmt = $this->pdo->query("SELECT a.id_aceite, a.id_usuario, a.versao_politica, a.ip_origem, a.user_agent, a.data_hora_aceite, u.nome, u.email, u.cpf, u.tipo_usuario
                                  FROM tb_privacidade_aceite a
                                  INNER JOIN tb_usuario u ON u.id_usuario = a.id_usuario
                                  ORDER BY a.id_aceite DESC");
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        echo json_encode([
            "status" => "sucesso",
            "total" => count($dados),
            "versao_atual" => "2026-11-10",
            "dados" => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    // ==========================================
    // 9. CONFIGURAÇÃO DOS 3 ADMINS OFICIAIS
    // ==========================================
    public function adminsOficiais(): void
    {
        AuthMiddleware::verificarAdmin();

        $stmt = $this->pdo->query("SELECT p.id_permissao, p.id_usuario, p.nome_oficial, p.nivel, p.status, p.observacoes, p.data_concessao, u.email, u.cpf, u.telefone, u.ativo
                                  FROM tb_admin_permissao p
                                  LEFT JOIN tb_usuario u ON u.id_usuario = p.id_usuario
                                  ORDER BY p.id_permissao ASC");
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        echo json_encode([
            "status" => "sucesso",
            "dados" => $dados
        ], JSON_UNESCAPED_UNICODE);
    }

    public function cadastrarMariaEduarda(): void
    {
        AuthMiddleware::verificarAdmin();
        $payload = $this->lerPayload();
        $admin = $this->getAdminLogado();

        $nome = trim((string)($payload['nome'] ?? 'Maria Eduarda'));
        $cpf = preg_replace('/\D/', '', (string)($payload['cpf'] ?? ''));
        $email = trim((string)($payload['email'] ?? ''));
        $telefone = trim((string)($payload['telefone'] ?? ''));
        $dataNascimento = trim((string)($payload['data_nascimento'] ?? ''));
        $senha = trim((string)($payload['senha'] ?? ''));
        $cep = trim((string)($payload['cep'] ?? '01310-100'));
        $rua = trim((string)($payload['rua'] ?? 'Avenida Paulista'));
        $numero = trim((string)($payload['numero'] ?? '1000'));
        $bairro = trim((string)($payload['bairro'] ?? 'Bela Vista'));
        $cidade = trim((string)($payload['cidade'] ?? 'São Paulo'));
        $estado = strtoupper(trim((string)($payload['estado'] ?? 'SP')));

        if (strlen($cpf) !== 11 || !$this->usuarioDAO->validarCpf($cpf)) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "CPF de Maria Eduarda é inválido."]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "E-mail informado é inválido."]);
            return;
        }

        if (strlen($senha) < 6) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "A senha deve ter pelo menos 6 caracteres."]);
            return;
        }

        if ($this->usuarioDAO->emailOuCpfJaExiste($email, $cpf)) {
            http_response_code(409);
            echo json_encode(["status" => "erro", "mensagem" => "CPF ou e-mail já cadastrado na tb_usuario."]);
            return;
        }

        $usuario = new Usuario();
        $usuario->setNome($nome);
        $usuario->setCpf($cpf);
        $usuario->setEmail($email);
        $usuario->setTelefone($telefone);
        $usuario->setDataNascimento($dataNascimento);
        $usuario->setSenha($senha);
        $usuario->setCep($cep);
        $usuario->setRua($rua);
        $usuario->setNumero($numero);
        $usuario->setBairro($bairro);
        $usuario->setCidade($cidade);
        $usuario->setEstado($estado);
        $usuario->setEndereco("{$rua}, {$numero} - {$bairro} - {$cidade}/{$estado}");
        $usuario->setTipoUsuario('admin');
        $usuario->setStatusIdentidade('verificado');
        $usuario->setIdade(Usuario::calcularIdadeCompleta($dataNascimento));

        $idCriado = $this->usuarioDAO->cadastrar($usuario);
        if (!$idCriado) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Falha ao gravar registro em tb_usuario."]);
            return;
        }

        // Atualizar tabela de permissões oficiais
        $stmtPerm = $this->pdo->prepare("UPDATE tb_admin_permissao SET id_usuario = ?, status = 'ativo', observacoes = 'Administradora oficial cadastrada e ativa' WHERE nome_oficial = 'Maria Eduarda'");
        $stmtPerm->execute([$idCriado]);

        // Registrar aceite da política
        $stmtAceite = $this->pdo->prepare("INSERT INTO tb_privacidade_aceite (id_usuario, versao_politica, ip_origem, user_agent) VALUES (?, '2026-11-10', '127.0.0.1', 'Cadastro Oficial de Administrador')");
        $stmtAceite->execute([$idCriado]);

        $this->registrarLog("Cadastro de administradora oficial", "sistema", $idCriado, "{$admin['nome']} concluiu o cadastro oficial da administradora Maria Eduarda (ID: {$idCriado})");

        echo json_encode([
            "status" => "sucesso",
            "mensagem" => "Maria Eduarda cadastrada com sucesso como Administradora Oficial!",
            "id_usuario" => $idCriado
        ], JSON_UNESCAPED_UNICODE);
    }
}
