<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioDAO
{
    private ?PDO $conn = null;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Remove caracteres não numéricos do CPF
     */
    public function limparCpf(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }

    /**
     * Valida se um CPF é matematicamente válido
     */
    public function validarCpf(string $cpf): bool
    {
        $cpf = $this->limparCpf($cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Verifica CPFs com todos os dígitos iguais (ex: 111.111.111-11)
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Primeiro dígito verificador
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += (int)$cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int)$cpf[$c] !== $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cadastrar usuário na tabela tb_usuario
     */
    public function cadastrar(Usuario $usuario): int|bool
    {
        $sql = "INSERT INTO tb_usuario 
                (nome, data_nascimento, cpf, email, telefone, cep, rua, numero, bairro, cidade, estado, endereco, foto, senha, ativo, tipo_usuario, status_identidade, data_verificacao_identidade, protocolo_identidade, idade) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $senhaCriptografada = password_hash($usuario->getSenha(), PASSWORD_DEFAULT);
        $cpfLimpo = $this->limparCpf($usuario->getCpf() ?? '');
        $fotoVal = $usuario->getFoto();
        $fotoPath = (!empty($fotoVal) && is_string($fotoVal)) ? $fotoVal : null;
        $tipoUsuario = $usuario->getTipoUsuario() ?: 'adotante';
        $statusIdentidade = $usuario->getStatusIdentidade() ?: 'pendente';
        $dataVerif = $usuario->getDataVerificacaoIdentidade() ?: null;
        $protocolo = $usuario->getProtocoloIdentidade() ?: null;

        $idade = null;
        if (!empty($usuario->getDataNascimento())) {
            try {
                $nasc = new DateTime($usuario->getDataNascimento());
                $hoje = new DateTime('today');
                $idade = $hoje->diff($nasc)->y;
            } catch (Exception $e) {}
        }
        if ($idade === null && method_exists($usuario, 'getIdade') && $usuario->getIdade() !== null) {
            $idade = (int)$usuario->getIdade();
        }

        $exec = $stmt->execute([
            $usuario->getNome(),
            $usuario->getDataNascimento(),
            $cpfLimpo,
            $usuario->getEmail(),
            $usuario->getTelefone(),
            $usuario->getCep(),
            $usuario->getRua(),
            $usuario->getNumero(),
            $usuario->getBairro(),
            $usuario->getCidade(),
            $usuario->getEstado(),
            $usuario->getEndereco(),
            $fotoPath,
            $senhaCriptografada,
            $tipoUsuario,
            $statusIdentidade,
            $dataVerif,
            $protocolo,
            $idade
        ]);

        if ($exec) {
            $novoId = (int)$this->conn->lastInsertId();
            if ($novoId > 0) {
                $usuario->setId($novoId);
                return $novoId;
            }
            return true;
        }

        return false;
    }

    /**
     * Alias para cadastrar
     */
    public function cadastrarUsuario(Usuario $usuario): bool
    {
        return $this->cadastrar($usuario);
    }

    /**
     * Buscar usuário pelo CPF
     */
    public function buscarPorCpf(string $cpf): array|false
    {
        $cpfLimpo = $this->limparCpf($cpf);
        $sql = "SELECT id_usuario AS id, id_usuario, nome, email, telefone, cpf, cep, rua, numero, bairro, cidade, estado, endereco, data_nascimento, tipo_usuario, foto, status_identidade, data_verificacao_identidade, protocolo_identidade, senha, ativo 
                FROM tb_usuario 
                WHERE (REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ? OR cpf = ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$cpfLimpo, $cpf]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar usuário pelo e-mail (case-insensitive)
     */
    public function buscarPorEmail(string $email): array|false
    {
        $sql = "SELECT id_usuario AS id, id_usuario, nome, email, telefone, cpf, cep, rua, numero, bairro, cidade, estado, endereco, data_nascimento, tipo_usuario, foto, status_identidade, data_verificacao_identidade, protocolo_identidade, senha, ativo 
                FROM tb_usuario 
                WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([trim($email)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Confere os dados informados para recuperação local de senha.
     * Exige e-mail, CPF e data de nascimento correspondentes ao mesmo cadastro.
     */
    public function buscarParaRecuperacao(string $email, string $cpf, string $dataNascimento): array|false
    {
        $cpfLimpo = $this->limparCpf($cpf);
        $sql = "SELECT id_usuario, nome, email, cpf, data_nascimento, ativo
                FROM tb_usuario
                WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))
                  AND REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?
                  AND data_nascimento = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([trim($email), $cpfLimpo, $dataNascimento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar por Email ou CPF
     */
    public function buscarPorEmailOuCpf(string $identificador): array|false
    {
        $identificador = trim($identificador);
        if (strpos($identificador, '@') !== false) {
            return $this->buscarPorEmail($identificador);
        }
        $cpfLimpo = $this->limparCpf($identificador);
        if ($cpfLimpo !== '') {
            return $this->buscarPorCpf($cpfLimpo);
        }
        return false;
    }

    /**
     * Verifica se email ou CPF já estão cadastrados
     */
    public function emailOuCpfJaExiste(string $email, string $cpf): bool
    {
        $email = trim($email);
        $cpf = $this->limparCpf($cpf);

        if ($email !== '' && $this->buscarPorEmail($email)) {
            return true;
        }

        if ($cpf !== '' && $this->buscarPorCpf($cpf)) {
            return true;
        }

        return false;
    }

    /**
     * Login por CPF ou Email
     */
    public function login(string $identificador, string $senha): array|false
    {
        $usuario = $this->buscarPorEmailOuCpf($identificador);

        if ($usuario) {
            $senhaArmazenada = $usuario['senha'];

            // Verifica com password_verify (senhas hasheadas com bcrypt)
            if (password_verify($senha, $senhaArmazenada)) {
                return $usuario;
            }

            // Fallback: comparação direta para senhas legadas em texto puro
            if ($senha === $senhaArmazenada) {
                // Auto-migração segura: converte senha legada para hash bcrypt
                $this->migrarSenhaParaHash(
                    (int)($usuario['id_usuario'] ?? $usuario['id']),
                    $senha
                );
                return $usuario;
            }
        }

        return false;
    }

    /**
     * Listar todos os usuários (sem senhas, ordenados por mais recentes)
     */
    public function listar(): array
    {
        $sql = "SELECT id_usuario AS id, id_usuario, nome, email, telefone, cpf, cep, rua, numero, bairro, cidade, estado, endereco, data_nascimento, idade, tipo_usuario, foto, status_identidade, data_verificacao_identidade, protocolo_identidade, data_criacao, ativo 
                FROM tb_usuario 
                ORDER BY id_usuario DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($usuarios as &$u) {
            unset($u['senha']);
        }
        return $usuarios;
    }

    /**
     * Buscar usuário por ID (sem senha)
     */
    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT id_usuario AS id, id_usuario, nome, email, telefone, cpf, cep, rua, numero, bairro, cidade, estado, endereco, data_nascimento, idade, tipo_usuario, foto, status_identidade, data_verificacao_identidade, protocolo_identidade, data_criacao, ativo FROM tb_usuario WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            unset($u['senha']);
        }
        return $u;
    }

    /**
     * Buscar usuário por ID incluindo o hash da senha (para autenticação de segurança interna)
     */
    public function buscarPorIdComSenha(int $id): array|false
    {
        $sql = "SELECT id_usuario AS id, id_usuario, nome, email, telefone, cpf, cep, rua, numero, bairro, cidade, estado, endereco, data_nascimento, tipo_usuario, foto, status_identidade, data_verificacao_identidade, protocolo_identidade, senha, data_criacao, ativo FROM tb_usuario WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se o e-mail pertence a OUTRO usuário cadastrado
     */
    public function emailPertenceAOutroUsuario(string $email, int $idUsuarioAtual): bool
    {
        $email = trim($email);
        if ($email === '') return false;
        $sql = "SELECT id_usuario FROM tb_usuario WHERE LOWER(TRIM(email)) = LOWER(TRIM(?)) AND id_usuario != ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email, $idUsuarioAtual]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se o CPF pertence a OUTRO usuário cadastrado
     */
    public function cpfPertenceAOutroUsuario(string $cpf, int $idUsuarioAtual): bool
    {
        $cpfLimpo = $this->limparCpf($cpf);
        if ($cpfLimpo === '') return false;
        $sql = "SELECT id_usuario FROM tb_usuario WHERE (REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ? OR cpf = ?) AND id_usuario != ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$cpfLimpo, $cpf, $idUsuarioAtual]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Valida e salva upload de foto de perfil (JPG, JPEG, PNG, WEBP até 5MB)
     * Rejeita executáveis, valida MIME e getimagesize, gera nome único
     */
    public static function processarUploadFotoPerfil(array $file, int|string $idRef = 'novo'): array
    {
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['sucesso' => false, 'nenhum_arquivo' => true];
        }

        if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            return ['sucesso' => false, 'mensagem' => 'Erro ao transferir arquivo de imagem. Código: ' . ($file['error'] ?? 0)];
        }

        $tmpName = $file['tmp_name'];
        $fileSize = (int)($file['size'] ?? 0);
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $extsValidas = ['jpg', 'jpeg', 'png', 'webp'];

        if ($fileSize > 5 * 1024 * 1024) {
            return ['sucesso' => false, 'mensagem' => 'A foto de perfil deve possuir tamanho máximo de 5MB.'];
        }

        if (!in_array($ext, $extsValidas)) {
            return ['sucesso' => false, 'mensagem' => 'Formato de imagem não permitido. Use apenas JPG, JPEG, PNG ou WEBP.'];
        }

        $imageInfo = @getimagesize($tmpName);
        if ($imageInfo === false) {
            return ['sucesso' => false, 'mensagem' => 'O arquivo enviado não é uma imagem válida ou está corrompido.'];
        }

        $mimePermitidos = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/webp'];
        if (!in_array(strtolower($imageInfo['mime'] ?? ''), $mimePermitidos)) {
            return ['sucesso' => false, 'mensagem' => 'Tipo MIME de imagem inválido.'];
        }

        $novoNome = 'usuario_' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$idRef) . '_' . uniqid() . '.' . $ext;
        $dirPublic = __DIR__ . '/../public/uploads/usuarios/';
        $dirRoot = __DIR__ . '/../uploads/usuarios/';

        if (!is_dir($dirPublic)) @mkdir($dirPublic, 0777, true);
        if (!is_dir($dirRoot)) @mkdir($dirRoot, 0777, true);

        $salvo = false;
        if (is_uploaded_file($tmpName)) {
            $salvo = move_uploaded_file($tmpName, $dirPublic . $novoNome);
        } else {
            $salvo = @copy($tmpName, $dirPublic . $novoNome);
        }

        if ($salvo) {
            @copy($dirPublic . $novoNome, $dirRoot . $novoNome);
            $fotoPath = '../uploads/usuarios/' . $novoNome;
            return ['sucesso' => true, 'foto_path' => $fotoPath, 'nome_arquivo' => $novoNome];
        }

        return ['sucesso' => false, 'mensagem' => 'Não foi possível salvar o arquivo de imagem no servidor.'];
    }

    /**
     * Atualizar foto de perfil do usuário
     */
    public function atualizarFoto(int $idUsuario, string $fotoPath): bool
    {
        $sql = "UPDATE tb_usuario SET foto = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$fotoPath, $idUsuario]);
    }

    /**
     * Alterar senha com hash de segurança
     */
    public function alterarSenha(int $idUsuario, string $novaSenha): bool
    {
        $sql = "UPDATE tb_usuario SET senha = ? WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        return $stmt->execute([$hash, $idUsuario]);
    }

    /**
     * Conta solicitações de recuperação de senha do usuário nos últimos X minutos (Rate Limiting).
     *
     * @param int $idUsuario
     * @param int $minutos
     * @return int
     */
    public function contarSolicitacoesRecentes(int $idUsuario, int $minutos = 15): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM tb_reset_senha 
                    WHERE id_usuario = ? AND criado_em >= (NOW() - INTERVAL ? MINUTE)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario, $minutos]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[UsuarioDAO] Erro ao contar solicitações recentes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna a quantidade de segundos desde a última solicitação de recuperação deste usuário.
     * Retorna null se nenhuma solicitação anterior existir.
     *
     * @param int $idUsuario
     * @return int|null
     */
    public function segundosDesdeUltimaSolicitacao(int $idUsuario): ?int
    {
        try {
            $sql = "SELECT TIMESTAMPDIFF(SECOND, MAX(criado_em), NOW()) 
                    FROM tb_reset_senha WHERE id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario]);
            $seg = $stmt->fetchColumn();
            return ($seg !== false && $seg !== null) ? (int)$seg : null;
        } catch (Exception $e) {
            error_log("[UsuarioDAO] Erro ao verificar intervalo de solicitação: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva o hash do token de recuperação de senha no banco de dados.
     * Invalida tokens anteriores não utilizados do mesmo usuário por segurança.
     *
     * @param int $idUsuario
     * @param string $tokenHash Hash SHA-256 do token (64 caracteres)
     * @param string $expiracao Data/hora limite em formato Y-m-d H:i:s
     * @return bool
     */
    public function salvarTokenRecuperacao(int $idUsuario, string $tokenHash, string $expiracao): bool
    {
        try {
            // Invalida tokens anteriores do usuário
            $stmtCancel = $this->conn->prepare("UPDATE tb_reset_senha SET usado = 1 WHERE id_usuario = ? AND usado = 0");
            $stmtCancel->execute([$idUsuario]);

            $sql = "INSERT INTO tb_reset_senha (id_usuario, token, expiracao, usado, criado_em) VALUES (?, ?, ?, 0, NOW())";
            $stmt = $this->conn->prepare($sql);
            $okReset = $stmt->execute([$idUsuario, $tokenHash, $expiracao]);

            // Mantém a tabela redefinicao_senha sincronizada (estrutura já existente)
            try {
                $this->salvarTokenRedefinicao($idUsuario, $tokenHash, $expiracao);
            } catch (Exception $e) {
                error_log("[UsuarioDAO] Aviso ao gravar redefinicao_senha: " . $e->getMessage());
            }

            return $okReset;
        } catch (Exception $e) {
            error_log("[UsuarioDAO] Erro ao salvar token de recuperação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o registro do token de recuperação pelo hash SHA-256.
     * Retorna os dados do token e do usuário se encontrado.
     *
     * @param string $tokenHash
     * @return array|false
     */
    public function buscarTokenRecuperacao(string $tokenHash): array|false
    {
        $sql = "SELECT r.id, r.id_usuario, r.token, r.expiracao, r.usado, r.criado_em,
                       u.nome, u.email, u.ativo
                FROM tb_reset_senha r
                INNER JOIN tb_usuario u ON u.id_usuario = r.id_usuario
                WHERE r.token = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tokenHash]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($registro) {
            $registro['tabela'] = 'tb_reset_senha';
            return $registro;
        }

        $alt = $this->buscarTokenRedefinicao($tokenHash);
        if ($alt) {
            return [
                'id' => $alt['id'],
                'id_usuario' => $alt['id_usuario'],
                'token' => $alt['token'],
                'expiracao' => $alt['validade'] ?? null,
                'usado' => (int)($alt['utilizado'] ?? 0),
                'criado_em' => $alt['criado_em'] ?? null,
                'nome' => $alt['nome'] ?? null,
                'email' => $alt['email'] ?? null,
                'ativo' => $alt['ativo'] ?? 1,
                'tabela' => 'redefinicao_senha'
            ];
        }

        return false;
    }

    /**
     * Marca o token de recuperação como utilizado (uso único).
     *
     * @param int $idReset
     * @return bool
     */
    public function marcarTokenUtilizado(int $idReset): bool
    {
        $sql = "UPDATE tb_reset_senha SET usado = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$idReset]);
    }

    /**
     * Redefine a senha do usuário com validação atômica do token (transação PDO).
     *
     * @param int $idUsuario
     * @param string $novaSenha
     * @param int $idReset
     * @return bool
     */
    public function redefinirSenhaComToken(int $idUsuario, string $novaSenha, int $idReset): bool
    {
        try {
            $this->conn->beginTransaction();

            // 1. Atualiza a senha na tabela tb_usuario com hash seguro
            $hashSenha = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmtSenha = $this->conn->prepare("UPDATE tb_usuario SET senha = ? WHERE id_usuario = ?");
            $stmtSenha->execute([$hashSenha, $idUsuario]);

            // 2. Invalida o token atual
            $stmtToken = $this->conn->prepare("UPDATE tb_reset_senha SET usado = 1 WHERE id = ?");
            $stmtToken->execute([$idReset]);

            // 3. Invalida quaisquer outros tokens pendentes deste usuário
            $stmtOutros = $this->conn->prepare("UPDATE tb_reset_senha SET usado = 1 WHERE id_usuario = ?");
            $stmtOutros->execute([$idUsuario]);

            try {
                $this->conn->prepare("UPDATE redefinicao_senha SET utilizado = 1 WHERE id_usuario = ?")->execute([$idUsuario]);
            } catch (Exception $e) {}

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("[UsuarioDAO] Erro ao redefinir senha com token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar dados do usuário
     */
    public function atualizar(Usuario $usuario): bool
    {
        $cpfLimpo = $this->limparCpf($usuario->getCpf() ?? '');
        $statusIdentidade = $usuario->getStatusIdentidade() ?: 'pendente';
        $dataVerif = $usuario->getDataVerificacaoIdentidade() ?: null;
        $protocolo = $usuario->getProtocoloIdentidade() ?: null;

        $idade = null;
        if (!empty($usuario->getDataNascimento())) {
            try {
                $nasc = new DateTime($usuario->getDataNascimento());
                $hoje = new DateTime('today');
                $idade = $hoje->diff($nasc)->y;
            } catch (Exception $e) {}
        }
        if ($idade === null && method_exists($usuario, 'getIdade') && $usuario->getIdade() !== null) {
            $idade = (int)$usuario->getIdade();
        }

        $sql = "UPDATE tb_usuario SET 
                nome = ?, 
                email = ?, 
                cpf = ?,
                data_nascimento = ?,
                telefone = ?, 
                cep = ?, 
                rua = ?, 
                numero = ?, 
                bairro = ?, 
                cidade = ?, 
                estado = ?, 
                endereco = ?,
                tipo_usuario = ?,
                status_identidade = ?,
                data_verificacao_identidade = ?,
                protocolo_identidade = ?,
                idade = ?
                WHERE id_usuario = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $usuario->getNome(),
            $usuario->getEmail(),
            $cpfLimpo,
            $usuario->getDataNascimento(),
            $usuario->getTelefone(),
            $usuario->getCep(),
            $usuario->getRua(),
            $usuario->getNumero(),
            $usuario->getBairro(),
            $usuario->getCidade(),
            $usuario->getEstado(),
            $usuario->getEndereco(),
            $usuario->getTipoUsuario() ?: 'adotante',
            $statusIdentidade,
            $dataVerif,
            $protocolo,
            $idade,
            $usuario->getId()
        ]);
    }

    /**
     * Excluir conta do usuário tratando relacionamentos prévios (para evitar erro de FK e garantir consistência)
     * Requisito 22: Se for doador e possuir pets, os pets não podem ficar disponíveis para adoção sem responsável.
     * Requisito 34: Preservação de dados fiscais/legais por anonimização, inativação de animais e exclusão segura.
     */
    public function excluirContaComRelacionamentos(int $idUsuario, ?string $motivo = null): bool
    {
        try {
            $this->conn->beginTransaction();

            // 1. Pets do usuário: são inativados para não ficarem órfãos disponíveis no site
            try { $this->conn->prepare("UPDATE tb_animal SET status_adocao = 'inativo', ativo = 0, id_usuario = NULL WHERE id_usuario = ?")->execute([$idUsuario]); } catch (Exception $e) {}

            // 2. Denúncias: preservadas para investigação/segurança animal, mas autor é anonimizado
            try { $this->conn->prepare("UPDATE tb_denuncia SET id_usuario = NULL WHERE id_usuario = ?")->execute([$idUsuario]); } catch (Exception $e) {}

            // 3. Doações financeiras e materiais: preservadas para prestação de contas contábil/legal, autor anonimizado
            try { $this->conn->prepare("UPDATE tb_doacao SET id_usuario = NULL, contato = 'Usuário excluído (Anonimizado)' WHERE id_usuario = ?")->execute([$idUsuario]); } catch (Exception $e) {}
            try { $this->conn->prepare("UPDATE tb_doacao_dinheiro SET id_usuario = NULL WHERE id_usuario = ?")->execute([$idUsuario]); } catch (Exception $e) {}

            // 4. Doações de pets: histórico de adoção preservado com IDs anonimizados
            try { $this->conn->prepare("UPDATE tb_doacao_pet SET id_doador = NULL WHERE id_doador = ?")->execute([$idUsuario]); } catch (Exception $e) {}
            try { $this->conn->prepare("UPDATE tb_doacao_pet SET id_receptor = NULL WHERE id_receptor = ?")->execute([$idUsuario]); } catch (Exception $e) {}

            // 5. Conversas privadas e alertas vinculados
            try { $this->conn->prepare("DELETE FROM tb_chat WHERE id_usuario = ? OR id_usuario_origem = ? OR id_usuario_destino = ?")->execute([$idUsuario, $idUsuario, $idUsuario]); } catch (Exception $e) {}
            try { $this->conn->prepare("DELETE FROM tb_doapet WHERE id_usuario = ?")->execute([$idUsuario]); } catch (Exception $e) {}
            try { $this->conn->prepare("DELETE FROM tb_alerta WHERE id_usuario = ?")->execute([$idUsuario]); } catch (Exception $e) {}
            try { $this->conn->prepare("DELETE FROM tb_reset_senha WHERE id_usuario = ?")->execute([$idUsuario]); } catch (Exception $e) {}
            try { $this->conn->prepare("DELETE FROM redefinicao_senha WHERE id_usuario = ?")->execute([$idUsuario]); } catch (Exception $e) {}

            // 6. Exclui o registro principal do usuário em tb_usuario
            $stmt = $this->conn->prepare("DELETE FROM tb_usuario WHERE id_usuario = ?");
            $stmt->execute([$idUsuario]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erro ao excluir usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletar usuário (alias direto)
     */
    public function deletar(int $id): bool
    {
        return $this->excluirContaComRelacionamentos($id);
    }

    // ============================================================================
    // MÉTODOS PARA TABELA redefinicao_senha (Recuperação via PHPMailer)
    // Colunas: id, id_usuario, token, validade, utilizado, criado_em
    // ============================================================================

    /**
     * Conta solicitações de redefinição nos últimos X minutos (Rate Limiting).
     */
    public function contarSolicitacoesRedefinicao(int $idUsuario, int $minutos = 15): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM redefinicao_senha 
                    WHERE id_usuario = ? AND criado_em >= (NOW() - INTERVAL ? MINUTE)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario, $minutos]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[UsuarioDAO] Erro contarSolicitacoesRedefinicao: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna segundos desde a última solicitação de redefinição.
     */
    public function segundosDesdeUltimaRedefinicao(int $idUsuario): ?int
    {
        try {
            $sql = "SELECT TIMESTAMPDIFF(SECOND, MAX(criado_em), NOW()) 
                    FROM redefinicao_senha WHERE id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario]);
            $seg = $stmt->fetchColumn();
            return ($seg !== false && $seg !== null) ? (int)$seg : null;
        } catch (Exception $e) {
            error_log("[UsuarioDAO] Erro segundosDesdeUltimaRedefinicao: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Salva o hash SHA-256 do token na tabela redefinicao_senha.
     * Invalida tokens anteriores não utilizados do mesmo usuário.
     */
    public function salvarTokenRedefinicao(int $idUsuario, string $tokenHash, string $validade): bool
    {
        try {
            // Invalida tokens anteriores do usuário
            $stmtInvalida = $this->conn->prepare(
                "UPDATE redefinicao_senha SET utilizado = 1 WHERE id_usuario = ? AND utilizado = 0"
            );
            $stmtInvalida->execute([$idUsuario]);

            // Insere o novo token
            $sql = "INSERT INTO redefinicao_senha (id_usuario, token, validade, utilizado, criado_em) 
                    VALUES (?, ?, ?, 0, NOW())";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$idUsuario, $tokenHash, $validade]);
        } catch (Exception $e) {
            error_log("[UsuarioDAO] Erro salvarTokenRedefinicao: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca registro do token pelo hash SHA-256 com dados do usuário.
     */
    public function buscarTokenRedefinicao(string $tokenHash): array|false
    {
        $sql = "SELECT r.id, r.id_usuario, r.token, r.validade, r.utilizado, r.criado_em,
                       u.nome, u.email, u.ativo
                FROM redefinicao_senha r
                INNER JOIN tb_usuario u ON u.id_usuario = r.id_usuario
                WHERE r.token = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tokenHash]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Redefine a senha do usuário com validação atômica do token (transação PDO).
     * Usa FOR UPDATE para impedir uso simultâneo do mesmo token.
     */
    public function redefinirSenhaViaRedefinicao(int $idUsuario, string $novaSenha, int $idRedefinicao): bool
    {
        try {
            $this->conn->beginTransaction();

            // Revalida o token com bloqueio FOR UPDATE (impede uso simultâneo)
            $stmtVerifica = $this->conn->prepare(
                "SELECT id, utilizado FROM redefinicao_senha WHERE id = ? AND id_usuario = ? FOR UPDATE"
            );
            $stmtVerifica->execute([$idRedefinicao, $idUsuario]);
            $registro = $stmtVerifica->fetch(PDO::FETCH_ASSOC);

            if (!$registro || (int)$registro['utilizado'] === 1) {
                $this->conn->rollBack();
                return false;
            }

            // 1. Atualiza a senha na tabela tb_usuario com hash bcrypt seguro
            $hashSenha = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmtSenha = $this->conn->prepare("UPDATE tb_usuario SET senha = ? WHERE id_usuario = ?");
            $stmtSenha->execute([$hashSenha, $idUsuario]);

            // 2. Marca o token como utilizado
            $stmtToken = $this->conn->prepare("UPDATE redefinicao_senha SET utilizado = 1 WHERE id = ?");
            $stmtToken->execute([$idRedefinicao]);

            // 3. Invalida quaisquer outros tokens pendentes deste usuário
            $stmtOutros = $this->conn->prepare(
                "UPDATE redefinicao_senha SET utilizado = 1 WHERE id_usuario = ? AND utilizado = 0"
            );
            $stmtOutros->execute([$idUsuario]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("[UsuarioDAO] Erro redefinirSenhaViaRedefinicao: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Migra uma senha armazenada em texto puro para hash bcrypt seguro.
     * Chamado automaticamente durante o login de contas legadas.
     */
    public function migrarSenhaParaHash(int $idUsuario, string $senhaPlana): bool
    {
        try {
            $hashSenha = password_hash($senhaPlana, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE tb_usuario SET senha = ? WHERE id_usuario = ?");
            return $stmt->execute([$hashSenha, $idUsuario]);
        } catch (Exception $e) {
            error_log("[UsuarioDAO] Erro migrarSenhaParaHash: " . $e->getMessage());
            return false;
        }
    }

    public function contarAdminsAtivos(): int
    {
        $sql = "SELECT COUNT(*) FROM tb_usuario
                WHERE ativo = 1 AND LOWER(TRIM(tipo_usuario)) IN ('admin', 'administrador')";
        return (int)$this->conn->query($sql)->fetchColumn();
    }

    /**
     * Conta vínculos que impedem exclusão segura do usuário.
     */
    public function contarVinculosUsuario(int $idUsuario): array
    {
        $contar = function (string $sql, array $params) {
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute($params);
                return (int)$stmt->fetchColumn();
            } catch (Exception $e) {
                return 0;
            }
        };

        $pets = $contar("SELECT COUNT(*) FROM tb_animal WHERE id_usuario = ?", [$idUsuario]);
        $chats = $contar(
            "SELECT COUNT(*) FROM tb_chat WHERE id_usuario = ? OR id_usuario_origem = ? OR id_usuario_destino = ?",
            [$idUsuario, $idUsuario, $idUsuario]
        );
        $denuncias = $contar("SELECT COUNT(*) FROM tb_denuncia WHERE id_usuario = ?", [$idUsuario]);
        $doacoes = $contar("SELECT COUNT(*) FROM tb_doacao WHERE id_usuario = ?", [$idUsuario]);
        $doacoesDinheiro = $contar("SELECT COUNT(*) FROM tb_doacao_dinheiro WHERE id_usuario = ?", [$idUsuario]);
        $doacoesPet = $contar(
            "SELECT COUNT(*) FROM tb_doacao_pet WHERE id_doador = ? OR id_receptor = ?",
            [$idUsuario, $idUsuario]
        );
        $doapet = $contar("SELECT COUNT(*) FROM tb_doapet WHERE id_usuario = ?", [$idUsuario]);

        $detalhes = [];
        if ($pets > 0) $detalhes[] = "{$pets} pet(s) cadastrado(s)";
        if ($chats > 0) $detalhes[] = "{$chats} mensagem(ns) de chat";
        if ($denuncias > 0) $detalhes[] = "{$denuncias} denúncia(s)";
        if ($doacoes > 0) $detalhes[] = "{$doacoes} doação(ões)";
        if ($doacoesDinheiro > 0) $detalhes[] = "{$doacoesDinheiro} doação(ões) financeira(s)";
        if ($doacoesPet > 0) $detalhes[] = "{$doacoesPet} doação(ões) de pet";
        if ($doapet > 0) $detalhes[] = "{$doapet} vínculo(s) em tb_doapet";

        $total = $pets + $chats + $denuncias + $doacoes + $doacoesDinheiro + $doacoesPet + $doapet;

        return [
            'pets' => $pets,
            'chats' => $chats,
            'denuncias' => $denuncias,
            'doacoes' => $doacoes,
            'doacoes_dinheiro' => $doacoesDinheiro,
            'doacoes_pet' => $doacoesPet,
            'doapet' => $doapet,
            'total' => $total,
            'detalhes' => $detalhes
        ];
    }

    /**
     * Exclui somente o usuário, sem apagar pets, chats, denúncias ou doações.
     * Remove apenas tokens de recuperação (dados auxiliares da própria conta).
     */
    public function excluirSomenteUsuario(int $idUsuario): bool
    {
        try {
            $this->conn->beginTransaction();
            try {
                $this->conn->prepare("DELETE FROM tb_reset_senha WHERE id_usuario = ?")->execute([$idUsuario]);
            } catch (Exception $e) {}
            try {
                $this->conn->prepare("DELETE FROM redefinicao_senha WHERE id_usuario = ?")->execute([$idUsuario]);
            } catch (Exception $e) {}

            $stmt = $this->conn->prepare("DELETE FROM tb_usuario WHERE id_usuario = ?");
            $stmt->execute([$idUsuario]);
            $this->conn->commit();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("[UsuarioDAO] Erro excluirSomenteUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function obterEstatisticasAdmin(): array
    {
        $q = function (string $sql) {
            try {
                return (int)$this->conn->query($sql)->fetchColumn();
            } catch (Exception $e) {
                return 0;
            }
        };

        return [
            'total_usuarios' => $q("SELECT COUNT(*) FROM tb_usuario"),
            'total_adotantes' => $q("SELECT COUNT(*) FROM tb_usuario WHERE LOWER(TRIM(tipo_usuario)) = 'adotante'"),
            'total_doadores' => $q("SELECT COUNT(*) FROM tb_usuario WHERE LOWER(TRIM(tipo_usuario)) = 'doador'"),
            'total_admins' => $q("SELECT COUNT(*) FROM tb_usuario WHERE LOWER(TRIM(tipo_usuario)) IN ('admin','administrador')"),
            'total_pets' => $q("SELECT COUNT(*) FROM tb_animal"),
            'pets_disponiveis' => $q("SELECT COUNT(*) FROM tb_animal WHERE LOWER(TRIM(COALESCE(status_adocao,''))) IN ('disponivel','disponível') AND COALESCE(ativo,1) = 1"),
            'pets_adotados' => $q("SELECT COUNT(*) FROM tb_animal WHERE LOWER(TRIM(COALESCE(status_adocao,''))) IN ('doado','adotado','adotada')"),
            'total_denuncias' => $q("SELECT COUNT(*) FROM tb_denuncia"),
            'total_doacoes' => $q("SELECT COUNT(*) FROM tb_doacao") + $q("SELECT COUNT(*) FROM tb_doacao_dinheiro") + $q("SELECT COUNT(*) FROM tb_doacao_pet")
        ];
    }

    public function listarPetsAdmin(): array
    {
        try {
            $sql = "SELECT a.id_pet, a.nome, a.especie, a.raca, a.porte, a.status_adocao, a.ativo, a.foto, a.id_usuario, u.nome AS nome_tutor
                    FROM tb_animal a
                    LEFT JOIN tb_usuario u ON u.id_usuario = a.id_usuario
                    ORDER BY a.id_pet DESC";
            return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function listarDenunciasAdmin(): array
    {
        try {
            $sql = "SELECT d.id_denuncia, d.id_usuario, d.id_pet, d.motivo, d.tipo, d.descricao, d.status, d.data_criacao, u.nome AS nome_usuario
                    FROM tb_denuncia d
                    LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario
                    ORDER BY d.id_denuncia DESC";
            return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function listarDoacoesAdmin(): array
    {
        $itens = [];
        try {
            $rows = $this->conn->query(
                "SELECT d.id_doacao, d.id_usuario, d.nome_doador, d.tipo_doacao, d.valor_doado, d.objeto_doado, d.status_pagamento, d.data_criacao, u.nome AS nome_usuario
                 FROM tb_doacao d
                 LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario
                 ORDER BY d.id_doacao DESC"
            )->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $r['origem'] = 'tb_doacao';
                $itens[] = $r;
            }
        } catch (Exception $e) {}

        try {
            $rows = $this->conn->query(
                "SELECT d.id_doacao, d.id_usuario, d.valor AS valor_doado, d.tipo_pagamento, d.data_doacao AS data_criacao, u.nome AS nome_usuario
                 FROM tb_doacao_dinheiro d
                 LEFT JOIN tb_usuario u ON u.id_usuario = d.id_usuario
                 ORDER BY d.id_doacao DESC"
            )->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $r['origem'] = 'tb_doacao_dinheiro';
                $r['tipo_doacao'] = 'financeira';
                $itens[] = $r;
            }
        } catch (Exception $e) {}

        return $itens;
    }
}