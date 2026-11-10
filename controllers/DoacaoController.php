<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PixPayload.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class DoacaoController
{
    private PDO $pdo;
    private array $pixConfig;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->pixConfig = require __DIR__ . '/../config/pix.php';
    }

    /**
     * Retorna o ID do usuário logado se houver sessão ativa
     */
    private function getUsuarioLogadoId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = $_SESSION['usuario_id'] ?? null;
        return $id ? (int)$id : null;
    }

    /**
     * Retorna o nome do usuário logado se houver
     */
    private function getUsuarioLogadoNome(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['usuario_nome'] ?? null;
    }

    /**
     * 1.2 Gera QR Code PIX dinâmico com payload BACEN BR Code oficial
     */
    public function gerarPix(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;

        $valorRaw = $dados['valor'] ?? $dados['valor_doado'] ?? 0;
        $valor = (float)str_replace(',', '.', (string)$valorRaw);

        if ($valor <= 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Por favor, informe um valor de doação válido em reais (ex: 25.00)."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Identificação do doador
        $idUsuario = $this->getUsuarioLogadoId();
        $anonimo = !empty($dados['anonimo']) && ($dados['anonimo'] === true || $dados['anonimo'] === 'true' || $dados['anonimo'] === '1');

        if ($anonimo) {
            $nomeDoador = 'Usuário anônimo';
        } elseif ($idUsuario) {
            $nomeDoador = $this->getUsuarioLogadoNome();
            if (!$nomeDoador) {
                $stmtU = $this->pdo->prepare("SELECT nome FROM tb_usuario WHERE id_usuario = ?");
                $stmtU->execute([$idUsuario]);
                $nomeDoador = $stmtU->fetchColumn() ?: 'Usuário';
            }
        } else {
            $nomeInformado = trim((string)($dados['nome_doador'] ?? $dados['nome'] ?? ''));
            $nomeDoador = $nomeInformado !== '' ? $nomeInformado : 'Usuário anônimo';
        }

        $txid = 'PET' . strtoupper(substr(uniqid(), -10));

        $pixPayload = new PixPayload(
            $this->pixConfig['chave_pix'],
            $this->pixConfig['beneficiario'],
            $this->pixConfig['cidade'],
            $valor,
            $txid,
            'Doacao PetAlert'
        );
        $codigoPix = $pixPayload->gerarCodigo();

        // Salvar a doação financeira no banco com status 'aguardando_pagamento'
        $sql = "INSERT INTO tb_doacao 
                (id_usuario, nome_doador, tipo_doacao, data_doacao, valor_doado, objeto_doado, status_pagamento, txid, codigo_pix)
                VALUES 
                (:id_usuario, :nome_doador, 'financeira', CURDATE(), :valor_doado, 'Doação financeira (PIX)', 'aguardando_pagamento', :txid, :codigo_pix)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':nome_doador' => $nomeDoador,
            ':valor_doado' => $valor,
            ':txid' => $txid,
            ':codigo_pix' => $codigoPix
        ]);

        $idDoacao = (int)$this->pdo->lastInsertId();

        echo json_encode([
            "status" => "sucesso",
            "id_doacao" => $idDoacao,
            "valor" => $valor,
            "valor_formatado" => "R$ " . number_format($valor, 2, ',', '.'),
            "nome_doador" => $nomeDoador,
            "beneficiario" => $this->pixConfig['beneficiario'],
            "chave_pix" => $this->pixConfig['chave_pix'],
            "cidade" => $this->pixConfig['cidade'],
            "txid" => $txid,
            "codigo_pix" => $codigoPix,
            "status_pagamento" => "aguardando_pagamento",
            "instrucoes" => [
                "Abra o app do seu banco ou instituição financeira favorito;",
                "Escolha a opção Pagar via PIX com Copia e Cola ou leitura de QR Code;",
                "Cole o código ou aponte a câmera para o QR Code gerado abaixo;",
                "Confirme as informações do recebedor (" . $this->pixConfig['beneficiario'] . ") e o valor de R$ " . number_format($valor, 2, ',', '.') . ";",
                "Conclua a transferência com segurança."
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 1.4 Doação de insumos
     */
    public function doarInsumo(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;

        $item = trim((string)($dados['item'] ?? $dados['objeto_doado'] ?? ''));
        if ($item === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Por favor, informe o item ou material a ser doado."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $quantidade = trim((string)($dados['quantidade'] ?? ''));
        $contato = trim((string)($dados['contato'] ?? $dados['telefone'] ?? ''));
        $anonimo = !empty($dados['anonimo']) && ($dados['anonimo'] === true || $dados['anonimo'] === 'true' || $dados['anonimo'] === '1');

        $idUsuario = $this->getUsuarioLogadoId();

        if ($anonimo) {
            $nomeDoador = 'Usuário anônimo';
        } elseif ($idUsuario) {
            $nomeDoador = $this->getUsuarioLogadoNome();
            if (!$nomeDoador) {
                $stmtU = $this->pdo->prepare("SELECT nome FROM tb_usuario WHERE id_usuario = ?");
                $stmtU->execute([$idUsuario]);
                $nomeDoador = $stmtU->fetchColumn() ?: 'Usuário';
            }
        } else {
            $nomeInformado = trim((string)($dados['nome_doador'] ?? $dados['nome'] ?? ''));
            $nomeDoador = $nomeInformado !== '' ? $nomeInformado : 'Usuário anônimo';
        }

        $sql = "INSERT INTO tb_doacao 
                (id_usuario, nome_doador, tipo_doacao, data_doacao, valor_doado, objeto_doado, quantidade, contato, status_pagamento)
                VALUES 
                (:id_usuario, :nome_doador, 'insumo', CURDATE(), 0.00, :objeto_doado, :quantidade, :contato, 'confirmado')";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':nome_doador' => $nomeDoador,
            ':objeto_doado' => $item,
            ':quantidade' => $quantidade !== '' ? $quantidade : null,
            ':contato' => $contato !== '' ? $contato : null
        ]);

        $idDoacao = (int)$this->pdo->lastInsertId();

        $resumoItem = $quantidade !== '' ? "{$quantidade} de {$item}" : $item;
        $resumoCompleto = "{$nomeDoador} — {$resumoItem}";

        echo json_encode([
            "status" => "sucesso",
            "mensagem" => "Doação de insumo registrada com sucesso!",
            "id_doacao" => $idDoacao,
            "nome_doador" => $nomeDoador,
            "item" => $item,
            "quantidade" => $quantidade,
            "resumo" => $resumoCompleto
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 1.1 Mural de Doadores reais (separado em Financeiras e Insumos)
     */
    public function mural(): void
    {
        // 1. Doações financeiras confirmadas (somente status = 'pago')
        $sqlFin = "SELECT d.id_doacao,
                          COALESCE(NULLIF(d.nome_doador, ''), u.nome, 'Usuário anônimo') AS nome_exibicao,
                          d.valor_doado,
                          d.data_doacao
                   FROM tb_doacao d
                   LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario
                   WHERE d.tipo_doacao = 'financeira' AND d.status_pagamento = 'pago' AND d.valor_doado > 0
                   ORDER BY d.id_doacao DESC
                   LIMIT 50";

        $stmtFin = $this->pdo->query($sqlFin);
        $rawFin = $stmtFin->fetchAll(PDO::FETCH_ASSOC);

        $financeiras = [];
        foreach ($rawFin as $row) {
            $financeiras[] = [
                "id_doacao" => (int)$row['id_doacao'],
                "nome" => htmlspecialchars($row['nome_exibicao'], ENT_QUOTES, 'UTF-8'),
                "valor" => "R$ " . number_format((float)$row['valor_doado'], 2, ',', '.'),
                "texto" => htmlspecialchars($row['nome_exibicao'], ENT_QUOTES, 'UTF-8') . " — R$ " . number_format((float)$row['valor_doado'], 2, ',', '.'),
                "data" => $row['data_doacao']
            ];
        }

        // 2. Doações de insumos confirmadas
        $sqlIns = "SELECT d.id_doacao,
                          COALESCE(NULLIF(d.nome_doador, ''), u.nome, 'Usuário anônimo') AS nome_exibicao,
                          d.objeto_doado,
                          d.quantidade,
                          d.data_doacao
                   FROM tb_doacao d
                   LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario
                   WHERE d.tipo_doacao = 'insumo' AND d.status_pagamento = 'confirmado' AND d.objeto_doado IS NOT NULL AND d.objeto_doado != ''
                   ORDER BY d.id_doacao DESC
                   LIMIT 50";

        $stmtIns = $this->pdo->query($sqlIns);
        $rawIns = $stmtIns->fetchAll(PDO::FETCH_ASSOC);

        $insumos = [];
        foreach ($rawIns as $row) {
            $desc = $row['objeto_doado'];
            if (!empty($row['quantidade'])) {
                $desc = "{$row['quantidade']} de {$row['objeto_doado']}";
            }
            $insumos[] = [
                "id_doacao" => (int)$row['id_doacao'],
                "nome" => htmlspecialchars($row['nome_exibicao'], ENT_QUOTES, 'UTF-8'),
                "item" => htmlspecialchars($row['objeto_doado'], ENT_QUOTES, 'UTF-8'),
                "quantidade" => htmlspecialchars($row['quantidade'] ?? '', ENT_QUOTES, 'UTF-8'),
                "texto" => htmlspecialchars($row['nome_exibicao'], ENT_QUOTES, 'UTF-8') . " — " . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'),
                "data" => $row['data_doacao']
            ];
        }

        echo json_encode([
            "status" => "sucesso",
            "financeiras" => $financeiras,
            "insumos" => $insumos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Validação do ID do item no banco
     */
    public function validarItem(): void
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "encontrado" => false,
                "mensagem" => "ID do item inválido."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $stmt = $this->pdo->prepare("SELECT id_doacao, objeto_doado, tipo_doacao, quantidade, status_pagamento FROM tb_doacao WHERE id_doacao = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            echo json_encode([
                "status" => "sucesso",
                "encontrado" => true,
                "id_doacao" => (int)$item['id_doacao'],
                "objeto_doado" => $item['objeto_doado'],
                "tipo_doacao" => $item['tipo_doacao'],
                "quantidade" => $item['quantidade'],
                "status_pagamento" => $item['status_pagamento'],
                "mensagem" => "Item cadastrado encontrado com sucesso!"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => "erro",
                "encontrado" => false,
                "mensagem" => "Item com o ID #{$id} não foi encontrado no banco de dados do PetAlert."
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Consulta de status do pagamento de uma doação financeira
     */
    public function consultarStatus(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID não informado."]);
            return;
        }

        $stmt = $this->pdo->prepare("SELECT id_doacao, status_pagamento, valor_doado, data_doacao FROM tb_doacao WHERE id_doacao = ?");
        $stmt->execute([$id]);
        $doacao = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doacao) {
            http_response_code(404);
            echo json_encode(["status" => "erro", "mensagem" => "Doação não encontrada."]);
            return;
        }

        echo json_encode([
            "status" => "sucesso",
            "id_doacao" => (int)$doacao['id_doacao'],
            "status_pagamento" => $doacao['status_pagamento'],
            "pago" => ($doacao['status_pagamento'] === 'pago'),
            "valor" => (float)$doacao['valor_doado']
        ]);
    }

    /**
     * Confirmação segura de pagamento PIX (Webhook / Backend)
     */
    public function confirmarPix(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $id = (int)($dados['id_doacao'] ?? 0);
        $txid = trim((string)($dados['txid'] ?? ''));
        $secret = trim((string)($dados['secret'] ?? $_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? ''));

        // Validação de token de segurança para impedir manipulação indevida do status pelo frontend
        if ($secret !== $this->pixConfig['webhook_secret']) {
            http_response_code(403);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Acesso não autorizado: credencial de confirmação de pagamento inválida."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($id > 0) {
            $stmt = $this->pdo->prepare("UPDATE tb_doacao SET status_pagamento = 'pago' WHERE id_doacao = ?");
            $stmt->execute([$id]);
        } elseif ($txid !== '') {
            $stmt = $this->pdo->prepare("UPDATE tb_doacao SET status_pagamento = 'pago' WHERE txid = ?");
            $stmt->execute([$txid]);
        } else {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Informe id_doacao ou txid."]);
            return;
        }

        echo json_encode([
            "status" => "sucesso",
            "mensagem" => "Pagamento PIX confirmado com sucesso no banco de dados."
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Métodos legados mantidos para compatibilidade total
     */
    public function listar(): void
    {
        if (isset($_GET['mural']) || isset($_GET['acao']) && $_GET['acao'] === 'mural') {
            $this->mural();
            return;
        }

        $sql = "SELECT d.id_doacao,
                       COALESCE(NULLIF(d.nome_doador, ''), u.nome, 'Usuário anônimo') AS nome_doador,
                       d.valor_doado,
                       d.objeto_doado,
                       d.tipo_doacao,
                       d.quantidade,
                       d.status_pagamento,
                       d.data_doacao
                FROM tb_doacao d
                LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario
                ORDER BY d.id_doacao DESC";

        $stmt = $this->pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }

    public function buscar(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->pdo->prepare("SELECT d.*, COALESCE(NULLIF(d.nome_doador, ''), u.nome, 'Usuário anônimo') AS nome_doador FROM tb_doacao d LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario WHERE d.id_doacao = ?");
        $stmt->execute([$id]);
        $doacao = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($doacao ?: ['erro' => 'ID não encontrado'], JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        if (!empty($dados['acao']) && $dados['acao'] === 'doar_insumo') {
            $this->doarInsumo();
            return;
        }
        $this->gerarPix();
    }

    public function atualizar(): void
    {
        AuthMiddleware::verificarLogin();
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $id = (int)($dados['id_doacao'] ?? 0);

        $sql = "UPDATE tb_doacao SET
                    data_doacao = :data_doacao,
                    valor_doado = :valor_doado,
                    objeto_doado = :objeto_doado
                WHERE id_doacao = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':data_doacao' => $dados['data_doacao'] ?? date('Y-m-d'),
            ':valor_doado' => $dados['valor_doado'] ?? 0.00,
            ':objeto_doado' => $dados['objeto_doado'] ?? null,
            ':id' => $id
        ]);

        echo json_encode(["status" => "sucesso", "mensagem" => "Doação atualizada com sucesso."], JSON_UNESCAPED_UNICODE);
    }

    public function deletar(): void
    {
        AuthMiddleware::verificarLogin();
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $id = (int)($dados['id_doacao'] ?? $_GET['id'] ?? 0);

        $stmt = $this->pdo->prepare("DELETE FROM tb_doacao WHERE id_doacao = ?");
        $stmt->execute([$id]);

        echo json_encode(["status" => "sucesso", "mensagem" => "Doação removida com sucesso."], JSON_UNESCAPED_UNICODE);
    }
}
