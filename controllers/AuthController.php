<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../DAO/UsuarioDAO.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../services/VerificacaoIdentidadeService.php';
class AuthController
{
    private UsuarioDAO $usuarioDAO;
    private VerificacaoIdentidadeService $identidadeService;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
        $this->identidadeService = new VerificacaoIdentidadeService();
    }

    /**
     * Login de usuário
     */
    public function login(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;

        $identificador = trim((string)($dados['identifier'] ?? $dados['identificador'] ?? $dados['email'] ?? $dados['cpf'] ?? ''));
        $senha = trim((string)($dados['senha'] ?? ''));

        if ($identificador === '' || $senha === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "CPF/e-mail ou senha inválidos"
            ]);
            return;
        }

        $usuario = $this->usuarioDAO->login($identificador, $senha);

        if ($usuario) {
            if (isset($usuario['ativo']) && (int)$usuario['ativo'] === 0) {
                http_response_code(403);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Esta conta está inativa. Entre em contato com o administrador."
                ]);
                return;
            }

            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }
            $_SESSION['usuario_id'] = $usuario['id_usuario'] ?? $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_cpf'] = $usuario['cpf'];
            $_SESSION['usuario_tipo'] = $usuario['tipo_usuario'] ?? 'adotante';
            $_SESSION['usuario_foto'] = $usuario['foto'] ?? null;

            $isAdmin = in_array(strtolower($usuario['tipo_usuario'] ?? ''), ['admin', 'administrador']);

            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "mensagem" => "Login realizado com sucesso!",
                "redirect" => $isAdmin ? "../admin/index.php" : "../pets/index.html",
                "usuario" => [
                    "id" => $usuario['id_usuario'] ?? $usuario['id'],
                    "id_usuario" => $usuario['id_usuario'] ?? $usuario['id'],
                    "nome" => $usuario['nome'],
                    "email" => $usuario['email'],
                    "cpf" => $usuario['cpf'],
                    "telefone" => $usuario['telefone'],
                    "tipo_usuario" => $usuario['tipo_usuario'] ?? 'adotante',
                    "is_admin" => $isAdmin,
                    "foto" => $usuario['foto'] ?? null,
                    "cidade" => $usuario['cidade'] ?? null,
                    "estado" => $usuario['estado'] ?? null,
                    "cep" => $usuario['cep'] ?? null
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "CPF/e-mail ou senha inválidos"
            ]);
        }
    }

    /**
     * Cadastro de novo usuário com Verificação de Identidade
     */
    public function cadastro(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;

        $nome = trim((string)($dados['nome'] ?? ''));
        $dataNasc = trim((string)($dados['data_nascimento'] ?? $dados['dataNascimento'] ?? ''));
        $cpf = trim((string)($dados['cpf'] ?? ''));
        $email = trim((string)($dados['email'] ?? ''));
        $telefone = trim((string)($dados['telefone'] ?? ''));
        $cep = trim((string)($dados['cep'] ?? ''));
        $rua = trim((string)($dados['rua'] ?? $dados['endereco'] ?? ''));
        $numero = trim((string)($dados['numero'] ?? $dados['numero_casa'] ?? ''));
        if ($rua !== '' && $numero === '') {
            $numero = 'S/N';
        }
        $bairro = trim((string)($dados['bairro'] ?? ''));
        $cidade = trim((string)($dados['cidade'] ?? ''));
        $estado = trim((string)($dados['estado'] ?? ''));
        $senha = trim((string)($dados['senha'] ?? ''));
        $perfil = trim((string)($dados['perfil'] ?? $dados['tipo_usuario'] ?? 'adotante'));
        $perfil = in_array(strtolower($perfil), ['doador', 'adotante']) ? strtolower($perfil) : 'adotante';

        // 1. Validação de preenchimento dos campos obrigatórios
        if ($nome === '' || $dataNasc === '' || $cpf === '' || $email === '' || $telefone === '' || $cep === '' || $rua === '' || $senha === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Preencha todos os campos obrigatórios (Nome, Data de Nascimento, CPF, E-mail, Telefone, CEP, Endereço e Senha)."
            ]);
            return;
        }

        // 2. Validação de formato de e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "O endereço de e-mail informado é inválido."
            ]);
            return;
        }

        // 3. Validação rigorosa de Idade Mínima (16 anos) e Data de Nascimento no Servidor
        $valIdade = VerificacaoIdentidadeService::validarIdadeMinima($dataNasc, 16);
        if (!$valIdade['valido']) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "codigo" => $valIdade['codigo'],
                "mensagem" => $valIdade['mensagem']
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // 4. Validação dos dados locais de identidade (CPF módulo 11, Nome Completo e Data de Nascimento)
        $validacaoLocal = $this->identidadeService->validarDadosLocais($cpf, $nome, $dataNasc);
        if (!$validacaoLocal['valido']) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "codigo" => $validacaoLocal['codigo'],
                "mensagem" => $validacaoLocal['mensagem']
            ]);
            return;
        }

        // 5. Verificação de unicidade no banco (E-mail e CPF)
        if ($this->usuarioDAO->emailOuCpfJaExiste($email, $cpf)) {
            http_response_code(409);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Este E-mail ou CPF já está cadastrado no sistema. Faça login para continuar."
            ]);
            return;
        }

        // 5. Verificação de Identidade junto ao serviço oficial (quando configurado)
        $resultadoIdentidade = $this->identidadeService->verificarCompatibilidade($cpf, $nome, $dataNasc);

        // Se o serviço oficial acusar falha / incompatibilidade de dados, não permite cadastro
        if (($resultadoIdentidade['status'] ?? '') === 'falha') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "codigo" => $resultadoIdentidade['codigo'] ?? 'FALHA_VERIFICACAO_IDENTIDADE',
                "mensagem" => $resultadoIdentidade['mensagem'] ?? 'Dados de identidade incompatíveis.'
            ]);
            return;
        }

        // 6. Upload opcional de Foto de Perfil
        $fotoPath = null;
        if (!empty($_FILES['foto'])) {
            $upFoto = UsuarioDAO::processarUploadFotoPerfil($_FILES['foto'], 'cadastro_' . time());
            if ($upFoto['sucesso']) {
                $fotoPath = $upFoto['foto_path'];
            } elseif (empty($upFoto['nenhum_arquivo'])) {
                http_response_code(400);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => $upFoto['mensagem'] ?? "Erro no envio da foto de perfil."
                ]);
                return;
            }
        } elseif (!empty($dados['foto']) && is_string($dados['foto'])) {
            $fotoPath = trim($dados['foto']);
        }

        // 7. Montagem do objeto de usuário
        $usuarioObj = new Usuario();
        $usuarioObj->setNome($nome);
        $usuarioObj->setDataNascimento($dataNasc);
        $usuarioObj->setCpf($cpf);
        $usuarioObj->setEmail($email);
        $usuarioObj->setTelefone($telefone);
        $usuarioObj->setCep($cep);
        $usuarioObj->setRua($rua);
        $usuarioObj->setNumero($numero);
        $usuarioObj->setBairro($bairro);
        $usuarioObj->setCidade($cidade);
        $usuarioObj->setEstado($estado);
        $usuarioObj->setEndereco("{$rua}, {$numero}" . ($bairro ? " - {$bairro}" : "") . ($cidade ? " - {$cidade}/{$estado}" : ""));
        $usuarioObj->setSenha($senha);
        $usuarioObj->setTipoUsuario($perfil);
        $usuarioObj->setFoto($fotoPath);
        $usuarioObj->setIdade($validacaoLocal['idade'] ?? Usuario::calcularIdadeCompleta($dataNasc));

        // Define status de identidade com base na resposta oficial
        if (($resultadoIdentidade['status'] ?? '') === 'verificado') {
            $usuarioObj->setStatusIdentidade('verificado');
            $usuarioObj->setDataVerificacaoIdentidade(date('Y-m-d H:i:s'));
            $usuarioObj->setProtocoloIdentidade($resultadoIdentidade['protocolo'] ?? null);
        } else {
            // Se pendente de endpoint oficial (.env sem URL), marca como pendente (nunca aprova automaticamente)
            $usuarioObj->setStatusIdentidade('pendente');
            $usuarioObj->setDataVerificacaoIdentidade(null);
            $usuarioObj->setProtocoloIdentidade(null);
        }

        $resultado = $this->usuarioDAO->cadastrar($usuarioObj);

        if ($resultado) {
            $novoId = $usuarioObj->getId() ?: (int)$resultado;

            // Registro do aceite da Política de Privacidade (LGPD)
            try {
                $dbConn = Database::getConnection();
                $ipOrigem = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Navegador Web', 0, 250);
                $stmtAceite = $dbConn->prepare("INSERT INTO tb_privacidade_aceite (id_usuario, versao_politica, ip_origem, user_agent, data_hora_aceite) VALUES (?, '2026-11-10', ?, ?, NOW())");
                $stmtAceite->execute([$novoId, $ipOrigem, $userAgent]);
            } catch (Exception $e) {
                error_log("[AuthController] Falha ao registrar aceite da Política de Privacidade: " . $e->getMessage());
            }

            http_response_code(201);
            echo json_encode([
                "status" => "sucesso",
                "id" => $novoId,
                "id_usuario" => $novoId,
                "mensagem" => ($usuarioObj->getStatusIdentidade() === 'verificado')
                    ? "Cadastro realizado e identidade confirmada com sucesso! Você já pode fazer login."
                    : "Cadastro realizado com sucesso! Sua identidade foi pré-validada e está pendente de confirmação com o endpoint oficial. Você já pode fazer login.",
                "status_identidade" => $usuarioObj->getStatusIdentidade(),
                "perfil" => $perfil,
                "dados" => [
                    "id" => $novoId,
                    "id_usuario" => $novoId,
                    "nome" => $usuarioObj->getNome(),
                    "email" => $usuarioObj->getEmail(),
                    "cpf" => $usuarioObj->getCpf(),
                    "telefone" => $usuarioObj->getTelefone(),
                    "data_nascimento" => $usuarioObj->getDataNascimento(),
                    "idade" => $usuarioObj->getIdade(),
                    "foto" => $usuarioObj->getFoto(),
                    "tipo_usuario" => $perfil
                ],
                "detalhes_identidade" => [
                    "status" => $resultadoIdentidade['status'],
                    "mensagem" => $resultadoIdentidade['mensagem'] ?? ""
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao salvar o cadastro no banco de dados."
            ]);
        }
    }

    /**
     * Obter dados do usuário logado (Sessão atual)
     */
    public function me(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId === 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Usuário não autenticado."
            ]);
            return;
        }

        $usuario = $this->usuarioDAO->buscarPorId($usuarioId);
        if ($usuario) {
            if (isset($usuario['ativo']) && (int)$usuario['ativo'] === 0) {
                http_response_code(403);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Esta conta está inativa."
                ]);
                return;
            }
            unset($usuario['senha']);
            $usuario['is_admin'] = in_array(strtolower($usuario['tipo_usuario'] ?? ''), ['admin', 'administrador']);
            echo json_encode([
                "status" => "sucesso",
                "dados" => $usuario
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Usuário não encontrado."
            ]);
        }
    }

    /**
     * Logout de usuário
     */
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        echo json_encode([
            "status" => "sucesso",
            "mensagem" => "Logout realizado com sucesso."
        ]);
    }

    /**
     * Alterar senha do usuário autenticado
     */
    public function alterarSenha(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $logadoId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($logadoId <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Você precisa estar conectado à sua conta para alterar a senha."
            ]);
            return;
        }

        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $senhaAtual = trim((string)($dados['senha_atual'] ?? $dados['senhaAtual'] ?? ''));
        $novaSenha = trim((string)($dados['nova_senha'] ?? $dados['novaSenha'] ?? ''));
        $confirmarSenha = trim((string)($dados['confirmar_senha'] ?? $dados['confirmarSenha'] ?? ''));

        if ($senhaAtual === '' || $novaSenha === '' || $confirmarSenha === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Por favor, informe a senha atual, a nova senha e a confirmação."
            ]);
            return;
        }

        if ($novaSenha !== $confirmarSenha) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "A confirmação da nova senha não confere."
            ]);
            return;
        }

        if (mb_strlen($novaSenha, 'UTF-8') < 6) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "A nova senha deve possuir no mínimo 6 caracteres."
            ]);
            return;
        }

        $usuario = $this->usuarioDAO->buscarPorIdComSenha($logadoId);
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(["status" => "erro", "mensagem" => "Usuário não encontrado."]);
            return;
        }

        $senhaValida = password_verify($senhaAtual, $usuario['senha']) || ($senhaAtual === $usuario['senha']);
        if (!$senhaValida) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "A senha atual informada está incorreta."
            ]);
            return;
        }

        if ($this->usuarioDAO->alterarSenha($logadoId, $novaSenha)) {
            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "mensagem" => "Senha alterada com sucesso! Utilize a nova senha no próximo acesso."
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao registrar a nova senha no banco de dados."
            ]);
        }
    }

    /**
     * Alterar foto de perfil do usuário autenticado
     */
    public function alterarFotoPerfil(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $logadoId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($logadoId <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Você precisa estar conectado à sua conta para alterar sua foto de perfil."
            ]);
            return;
        }

        if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Por favor, selecione um arquivo de imagem válido para envio."
            ]);
            return;
        }

        $tmpName = $_FILES['foto']['tmp_name'];
        $fileSize = $_FILES['foto']['size'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extsValidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        // Limite de 5MB
        if ($fileSize > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "A foto de perfil deve ter no máximo 5MB."
            ]);
            return;
        }

        if (!in_array($ext, $extsValidas)) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Formato de imagem inválido. Formatos aceitos: JPG, JPEG, PNG, WEBP e GIF."
            ]);
            return;
        }

        // Validação adicional de imagem real via getimagesize
        $imageInfo = @getimagesize($tmpName);
        if ($imageInfo === false) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "O arquivo enviado não é uma imagem válida."
            ]);
            return;
        }

        $novoNome = 'usuario_' . $logadoId . '_' . uniqid() . '.' . $ext;
        $dirPublic = __DIR__ . '/../public/uploads/usuarios/';
        $dirRoot = __DIR__ . '/../uploads/usuarios/';

        if (!is_dir($dirPublic)) mkdir($dirPublic, 0777, true);
        if (!is_dir($dirRoot)) mkdir($dirRoot, 0777, true);

        if (move_uploaded_file($tmpName, $dirPublic . $novoNome)) {
            @copy($dirPublic . $novoNome, $dirRoot . $novoNome);
            $fotoPath = '../uploads/usuarios/' . $novoNome;

            // Busca foto anterior para remover arquivo substituído (se estava em uploads/usuarios)
            $usuarioAtual = $this->usuarioDAO->buscarPorId($logadoId);
            $fotoAntiga = $usuarioAtual['foto'] ?? null;

            if ($this->usuarioDAO->atualizarFoto($logadoId, $fotoPath)) {
                $_SESSION['usuario_foto'] = $fotoPath;

                // Limpa foto antiga se for um upload personalizado
                if ($fotoAntiga && strpos($fotoAntiga, 'uploads/usuarios/') !== false) {
                    $nomeAntigo = basename($fotoAntiga);
                    @unlink($dirPublic . $nomeAntigo);
                    @unlink($dirRoot . $nomeAntigo);
                }

                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Foto de perfil atualizada com sucesso!",
                    "foto" => $fotoPath
                ]);
                return;
            } else {
                @unlink($dirPublic . $novoNome);
                @unlink($dirRoot . $novoNome);
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao registrar a foto de perfil no banco de dados."
                ]);
                return;
            }
        }

        http_response_code(500);
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Não foi possível processar e salvar a imagem no servidor."
        ]);
    }

    /**
     * Excluir a própria conta do usuário com confirmação de senha (Requisito 34)
     */
    public function excluirConta(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $dados = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);

        if ($usuarioId === 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Ação não permitida: Você precisa estar logado para excluir a conta."
            ]);
            return;
        }

        // Validação da senha atual
        $senha = trim((string)($dados['senha'] ?? ''));
        if ($senha === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Para sua segurança e confirmação de identidade, informe sua senha atual."
            ]);
            return;
        }

        $usuario = $this->usuarioDAO->buscarPorIdComSenha($usuarioId);
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(["status" => "erro", "mensagem" => "Usuário não encontrado."]);
            return;
        }

        $senhaValida = password_verify($senha, $usuario['senha']) || ($senha === $usuario['senha']);
        if (!$senhaValida) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Senha incorreta. A exclusão da conta foi cancelada por segurança."
            ]);
            return;
        }

        $sucesso = $this->usuarioDAO->excluirContaComRelacionamentos($usuarioId);

        if ($sucesso) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            @session_destroy();

            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "mensagem" => "Sua conta e dados vinculados foram excluídos com sucesso."
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Não foi possível excluir a conta no momento. Tente novamente."
            ]);
        }
    }

    /**
     * Primeira etapa da recuperação local: confere e-mail + CPF + data de nascimento.
     * Não envia e-mail e não altera a senha nesta etapa.
     */
    public function solicitarRecuperacaoSenha(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $email = trim((string)($dados['email'] ?? ''));
        $cpf = trim((string)($dados['cpf'] ?? ''));
        $dataNascimento = trim((string)($dados['data_nascimento'] ?? $dados['dataNascimento'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $cpf === '' || $dataNascimento === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Informe o e-mail, CPF e data de nascimento cadastrados."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $tentativas = (int)($_SESSION['recuperacao_tentativas'] ?? 0);
        if ($tentativas >= 5) {
            http_response_code(429);
            echo json_encode(["status" => "erro", "mensagem" => "Muitas tentativas. Aguarde e tente novamente mais tarde."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $_SESSION['recuperacao_tentativas'] = (int)($_SESSION['recuperacao_tentativas'] ?? 0) + 1;

        try {
            $usuario = $this->usuarioDAO->buscarParaRecuperacao($email, $cpf, $dataNascimento);
        } catch (Throwable $e) {
            error_log('[AuthController] Erro ao consultar dados de recuperação: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Não foi possível verificar os dados agora."], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$usuario || (isset($usuario['ativo']) && (int)$usuario['ativo'] === 0)) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Os dados informados não correspondem a uma conta ativa."], JSON_UNESCAPED_UNICODE);
            return;
        }

        session_regenerate_id(true);
        $chave = bin2hex(random_bytes(32));
        $_SESSION['recuperacao_autorizada_id'] = (int)$usuario['id_usuario'];
        $_SESSION['recuperacao_chave_hash'] = hash('sha256', $chave);
        $_SESSION['recuperacao_expira'] = time() + 600;

        http_response_code(200);
        echo json_encode([
            "status" => "sucesso",
            "etapa" => "definir_senha",
            "chave" => $chave,
            "mensagem" => "Dados conferidos. Agora você pode cadastrar uma nova senha."
        ], JSON_UNESCAPED_UNICODE);
    }

    /** Segunda etapa: atualiza a senha no próprio registro de tb_usuario. */
    public function redefinirSenha(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $chave = trim((string)($dados['chave'] ?? ''));
        $novaSenha = (string)($dados['senha'] ?? $dados['novaSenha'] ?? '');
        $confirmarSenha = (string)($dados['confirmarSenha'] ?? '');

        $chaveSalva = (string)($_SESSION['recuperacao_chave_hash'] ?? '');
        $idUsuario = (int)($_SESSION['recuperacao_autorizada_id'] ?? 0);
        $expira = (int)($_SESSION['recuperacao_expira'] ?? 0);

        if ($chave === '' || $chaveSalva === '' || $idUsuario <= 0 || $expira < time() || !hash_equals($chaveSalva, hash('sha256', $chave))) {
            http_response_code(403);
            echo json_encode(["status" => "erro", "mensagem" => "A autorização expirou ou é inválida. Faça a verificação novamente."], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (strlen($novaSenha) < 6) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "A nova senha deve possuir no mínimo 6 caracteres."], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($novaSenha !== $confirmarSenha) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "A confirmação da nova senha não confere."], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $sucesso = $this->usuarioDAO->alterarSenha($idUsuario, $novaSenha);
        } catch (Throwable $e) {
            error_log('[AuthController] Erro ao atualizar senha: ' . $e->getMessage());
            $sucesso = false;
        }

        if ($sucesso) {
            unset($_SESSION['recuperacao_autorizada_id'], $_SESSION['recuperacao_chave_hash'], $_SESSION['recuperacao_expira'], $_SESSION['recuperacao_tentativas']);
            http_response_code(200);
            echo json_encode(["status" => "sucesso", "mensagem" => "Senha alterada no banco de dados! Você já pode entrar com a nova senha."], JSON_UNESCAPED_UNICODE);
            return;
        }

        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => "Não foi possível atualizar sua senha. Tente novamente."], JSON_UNESCAPED_UNICODE);
    }

}