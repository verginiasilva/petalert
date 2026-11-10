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
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UsuarioController
{
    private UsuarioDAO $usuarioDAO;
    private VerificacaoIdentidadeService $identidadeService;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
        $this->identidadeService = new VerificacaoIdentidadeService();
    }

    /**
     * Listar todos os usuários (Privado - Exclusivo para Administradores)
     */
    public function listar(): void
    {
        AuthMiddleware::verificarAdmin();

        try {
            $usuarios = $this->usuarioDAO->listar();
            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "dados" => $usuarios,
                "total" => count($usuarios)
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "erro" => "Erro interno",
                "mensagem" => "Erro ao listar usuários: " . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Buscar usuário autenticado ou por ID validado
     */
    public function buscar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $logadoId = (int)($_SESSION['usuario_id'] ?? 0);
        $id = (int)($_GET['id'] ?? $logadoId);

        if ($logadoId <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Autenticação obrigatória para acessar dados de perfil."
            ]);
            return;
        }

        // Não permitir que o usuário consulte dados privados de outra conta, a menos que seja administrador
        if ($id > 0 && $id !== $logadoId) {
            if (!AuthMiddleware::isAdmin()) {
                http_response_code(403);
                echo json_encode([
                    "status" => "erro",
                    "erro" => "Acesso não autorizado",
                    "mensagem" => "Acesso não permitido: Você só pode visualizar seus próprios dados de perfil."
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        $targetId = ($id > 0 && AuthMiddleware::isAdmin()) ? $id : $logadoId;

        try {
            $usuario = $this->usuarioDAO->buscarPorId($targetId);

            if ($usuario) {
                unset($usuario['senha']);
                http_response_code(200);
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
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao buscar usuário: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Criar novo usuário (Cadastro) com Verificação de Identidade
     */
    public function criar(): void
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

        if ($nome === '' || $dataNasc === '' || $cpf === '' || $email === '' || $telefone === '' || $cep === '' || $rua === '' || $senha === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Preencha todos os campos obrigatórios (Nome, Data de Nascimento, CPF, E-mail, Telefone, CEP, Endereço e Senha)."
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "E-mail inválido."
            ]);
            return;
        }

        // Validação da Idade Mínima (16 anos) e Data de Nascimento no Servidor
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

        // Validação local de dados de identidade (CPF módulo 11, Nome e Data de Nascimento)
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

        if ($this->usuarioDAO->emailOuCpfJaExiste($email, $cpf)) {
            http_response_code(409);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Este E-mail ou CPF já está cadastrado no sistema."
            ]);
            return;
        }

        // Verificação junto ao serviço oficial de identidade
        $resultadoIdentidade = $this->identidadeService->verificarCompatibilidade($cpf, $nome, $dataNasc);
        if (($resultadoIdentidade['status'] ?? '') === 'falha') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "codigo" => $resultadoIdentidade['codigo'] ?? 'FALHA_VERIFICACAO_IDENTIDADE',
                "mensagem" => $resultadoIdentidade['mensagem'] ?? 'Dados de identidade incompatíveis.'
            ]);
            return;
        }

        try {
            $usuario = new Usuario();
            $usuario->setNome($nome);
            $usuario->setDataNascimento($dataNasc);
            $usuario->setCpf($cpf);
            $usuario->setEmail($email);
            $usuario->setTelefone($telefone);
            $usuario->setCep($cep);
            $usuario->setRua($rua);
            $usuario->setNumero($numero);
            $usuario->setBairro($bairro);
            $usuario->setCidade($cidade);
            $usuario->setEstado($estado);
            $usuario->setEndereco("{$rua}, {$numero}" . ($bairro ? " - {$bairro}" : "") . ($cidade ? " - {$cidade}/{$estado}" : ""));
            $usuario->setSenha($senha);
            $usuario->setTipoUsuario($perfil);

            // Upload opcional de Foto de Perfil
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
            $usuario->setFoto($fotoPath);
            $usuario->setIdade($validacaoLocal['idade'] ?? Usuario::calcularIdadeCompleta($dataNasc));

            if (($resultadoIdentidade['status'] ?? '') === 'verificado') {
                $usuario->setStatusIdentidade('verificado');
                $usuario->setDataVerificacaoIdentidade(date('Y-m-d H:i:s'));
                $usuario->setProtocoloIdentidade($resultadoIdentidade['protocolo'] ?? null);
            } else {
                $usuario->setStatusIdentidade('pendente');
                $usuario->setDataVerificacaoIdentidade(null);
                $usuario->setProtocoloIdentidade(null);
            }

            $resCadastro = $this->usuarioDAO->cadastrar($usuario);
            if ($resCadastro) {
                $novoId = $usuario->getId() ?: (int)$resCadastro;
                http_response_code(201);
                echo json_encode([
                    "status" => "sucesso",
                    "id" => $novoId,
                    "id_usuario" => $novoId,
                    "mensagem" => ($usuario->getStatusIdentidade() === 'verificado')
                        ? "Usuário cadastrado e identidade confirmada com sucesso!"
                        : "Usuário cadastrado com sucesso! Verificação de identidade pendente de configuração da API oficial.",
                    "status_identidade" => $usuario->getStatusIdentidade(),
                    "dados" => [
                        "id" => $novoId,
                        "id_usuario" => $novoId,
                        "nome" => $usuario->getNome(),
                        "email" => $usuario->getEmail(),
                        "cpf" => $usuario->getCpf(),
                        "telefone" => $usuario->getTelefone(),
                        "data_nascimento" => $usuario->getDataNascimento(),
                        "idade" => $usuario->getIdade(),
                        "foto" => $usuario->getFoto(),
                        "tipo_usuario" => $usuario->getTipoUsuario()
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao salvar o cadastro no banco."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro no servidor: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Atualizar dados do usuário (Meu Perfil)
     * Requisito: Verificação de identidade obrigatória se CPF, Nome ou Data de Nascimento forem alterados.
     * Se os dados conferidos forem mantidos, não revalida desnecessariamente.
     * Se a verificação falhar, os dados anteriores são preservados intactos.
     */
    public function atualizar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $logadoId = (int)($_SESSION['usuario_id'] ?? 0);
        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $idEnviado = (int)($dados['id'] ?? $dados['id_usuario'] ?? 0);

        // Bloqueia se usuário não estiver autenticado
        if ($logadoId <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Você precisa estar conectado à sua conta para atualizar seus dados."
            ]);
            return;
        }

        // Impede explicitamente alterar o ID da própria conta ou de outra pessoa
        if ($idEnviado > 0 && $idEnviado !== $logadoId) {
            http_response_code(403);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Ação não permitida: Você não pode alterar o identificador da conta ou dados de outro usuário."
            ]);
            return;
        }

        $id = $logadoId;

        try {
            $usuarioExistente = $this->usuarioDAO->buscarPorId($id);
            if (!$usuarioExistente) {
                http_response_code(404);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Usuário não encontrado."
                ]);
                return;
            }

            $nome = trim((string)($dados['nome'] ?? $usuarioExistente['nome']));
            $email = trim((string)($dados['email'] ?? $usuarioExistente['email']));
            $cpf = trim((string)($dados['cpf'] ?? $usuarioExistente['cpf']));
            $telefone = trim((string)($dados['telefone'] ?? $usuarioExistente['telefone']));
            $cep = trim((string)($dados['cep'] ?? $usuarioExistente['cep']));
            $rua = trim((string)($dados['rua'] ?? $usuarioExistente['rua']));
            $numero = trim((string)($dados['numero'] ?? $usuarioExistente['numero']));
            $bairro = trim((string)($dados['bairro'] ?? $usuarioExistente['bairro']));
            $cidade = trim((string)($dados['cidade'] ?? $usuarioExistente['cidade']));
            $estado = trim((string)($dados['estado'] ?? $usuarioExistente['estado']));
            $dataNascimento = trim((string)($dados['data_nascimento'] ?? $dados['dataNascimento'] ?? $usuarioExistente['data_nascimento']));

            // Validações básicas de preenchimento
            if (mb_strlen($nome, 'UTF-8') < 2) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "O nome completo deve ter pelo menos 2 caracteres."]);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "O endereço de e-mail informado é inválido."]);
                return;
            }

            // Verifica se e-mail já pertence a OUTRO usuário cadastrado
            if ($this->usuarioDAO->emailPertenceAOutroUsuario($email, $id)) {
                http_response_code(409);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "O e-mail informado já está cadastrado em outra conta PetAlert."
                ]);
                return;
            }

            // Verifica se CPF já pertence a OUTRO usuário cadastrado
            if ($cpf !== '' && $this->usuarioDAO->cpfPertenceAOutroUsuario($cpf, $id)) {
                http_response_code(409);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "O CPF informado já está cadastrado em outra conta PetAlert."
                ]);
                return;
            }

            // Normalização e detecção de alteração nos dados de identidade
            $cpfExistenteLimpo = VerificacaoIdentidadeService::normalizarCpf((string)($usuarioExistente['cpf'] ?? ''));
            $cpfNovoLimpo = VerificacaoIdentidadeService::normalizarCpf((string)$cpf);

            $nomeExistenteNorm = trim(preg_replace('/\s+/', ' ', (string)($usuarioExistente['nome'] ?? '')));
            $nomeNovoNorm = trim(preg_replace('/\s+/', ' ', (string)$nome));

            $dataNascExistenteNorm = trim(explode(' ', (string)($usuarioExistente['data_nascimento'] ?? ''))[0] ?? '');
            $dataNascNovaNorm = trim(explode(' ', (string)$dataNascimento)[0] ?? '');

            $mudouIdentidade = (
                $cpfNovoLimpo !== $cpfExistenteLimpo ||
                mb_strtolower($nomeNovoNorm, 'UTF-8') !== mb_strtolower($nomeExistenteNorm, 'UTF-8') ||
                $dataNascNovaNorm !== $dataNascExistenteNorm
            );

            // Regra: Se os dados de identidade foram alterados, executa nova verificação oficial
            if ($mudouIdentidade) {
                // Validação local prévia
                $valLoc = $this->identidadeService->validarDadosLocais($cpfNovoLimpo, $nomeNovoNorm, $dataNascNovaNorm);
                if (!$valLoc['valido']) {
                    http_response_code(422);
                    echo json_encode([
                        "status" => "erro",
                        "codigo" => $valLoc['codigo'],
                        "mensagem" => "Não foi possível validar os novos dados: " . $valLoc['mensagem'] . " Seus dados cadastrais anteriores foram preservados com segurança.",
                        "dados_mantidos" => true
                    ]);
                    return;
                }

                // Consulta de compatibilidade junto ao serviço oficial
                $resultadoIdentidade = $this->identidadeService->verificarCompatibilidade($cpfNovoLimpo, $nomeNovoNorm, $dataNascNovaNorm);

                // Se a verificação falhar, NÃO substitui os dados verificados por informações incompatíveis
                if (($resultadoIdentidade['status'] ?? '') === 'falha') {
                    http_response_code(422);
                    echo json_encode([
                        "status" => "erro",
                        "codigo" => $resultadoIdentidade['codigo'] ?? 'FALHA_VERIFICACAO_IDENTIDADE',
                        "mensagem" => "Verificação de identidade não aprovada: " . ($resultadoIdentidade['mensagem'] ?? 'Dados incompatíveis com o cadastro oficial.') . " Seus dados anteriores foram mantidos sem alterações.",
                        "status_identidade" => $usuarioExistente['status_identidade'] ?? 'pendente',
                        "dados_mantidos" => true
                    ]);
                    return;
                }

                if (($resultadoIdentidade['status'] ?? '') === 'verificado') {
                    $statusIdentidadeFinal = 'verificado';
                    $dataVerifFinal = date('Y-m-d H:i:s');
                    $protocoloFinal = $resultadoIdentidade['protocolo'] ?? null;
                } else {
                    $statusIdentidadeFinal = 'pendente';
                    $dataVerifFinal = null;
                    $protocoloFinal = null;
                }
            } else {
                // Mantém os dados de verificação anteriores sem exigir nova verificação desnecessária
                $statusIdentidadeFinal = $usuarioExistente['status_identidade'] ?? 'pendente';
                $dataVerifFinal = $usuarioExistente['data_verificacao_identidade'] ?? null;
                $protocoloFinal = $usuarioExistente['protocolo_identidade'] ?? null;
            }

            // Monta endereço consolidado
            $enderecoConsolidado = $rua;
            if ($numero) $enderecoConsolidado .= ", {$numero}";
            if ($bairro) $enderecoConsolidado .= " - {$bairro}";
            if ($cidade) $enderecoConsolidado .= " - {$cidade}/{$estado}";

            $usuario = new Usuario();
            $usuario->setId($id);
            $usuario->setNome($nome);
            $usuario->setEmail($email);
            $usuario->setCpf($cpf);
            $usuario->setDataNascimento($dataNascimento);
            $usuario->setTelefone($telefone);
            $usuario->setCep($cep);
            $usuario->setRua($rua);
            $usuario->setNumero($numero);
            $usuario->setBairro($bairro);
            $usuario->setCidade($cidade);
            $usuario->setEstado($estado);
            $usuario->setEndereco($dados['endereco'] ?? $enderecoConsolidado);
            $tipoInformado = trim((string)($dados['tipo_usuario'] ?? $usuarioExistente['tipo_usuario'] ?? 'adotante'));
            // Impede auto-promoção para admin se não for administrador autenticado
            if (in_array(strtolower($tipoInformado), ['admin', 'administrador']) && !AuthMiddleware::isAdmin()) {
                $tipoInformado = $usuarioExistente['tipo_usuario'] ?? 'adotante';
            }
            $usuario->setTipoUsuario($tipoInformado);

            // Proteção de segurança: define os campos de identidade validados pelo backend (ignora qualquer payload externo)
            $usuario->setStatusIdentidade($statusIdentidadeFinal);
            $usuario->setDataVerificacaoIdentidade($dataVerifFinal);
            $usuario->setProtocoloIdentidade($protocoloFinal);

            if ($this->usuarioDAO->atualizar($usuario)) {
                // Atualiza dados na sessão
                $_SESSION['usuario_nome'] = $nome;
                $_SESSION['usuario_email'] = $email;
                $_SESSION['usuario_cpf'] = $cpf;
                $_SESSION['usuario_tipo'] = $usuario->getTipoUsuario();

                $dadosAtualizados = $this->usuarioDAO->buscarPorId($id);
                unset($dadosAtualizados['senha']);

                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => $mudouIdentidade
                        ? ($statusIdentidadeFinal === 'verificado'
                            ? "Informações pessoais atualizadas e identidade confirmada com sucesso!"
                            : "Informações pessoais atualizadas! Seus novos dados estão registrados com verificação pendente da API oficial.")
                        : "Informações pessoais atualizadas com sucesso!",
                    "status_identidade" => $statusIdentidadeFinal,
                    "identidade_revalidada" => $mudouIdentidade,
                    "dados" => $dadosAtualizados
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao atualizar dados no banco."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Alterar senha do usuário autenticado com confirmação da senha atual
     * Requisito 34: Nunca exibe a senha atual e exige validação de segurança.
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
     * Logout de usuário
     */
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $_SESSION = [];
        if (!headers_sent() && ini_get("session.use_cookies")) {
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
            "mensagem" => "Logout realizado com sucesso."
        ]);
    }

    /**
     * Deletar usuário permanentemente (Excluir Conta)
     * Requisito 34: Validação obrigatória da senha atual, confirmação explícita e tratamento transacional.
     */
    public function deletar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $logadoId = (int)($_SESSION['usuario_id'] ?? 0);
        $idEnviado = (int)($dados['id'] ?? $dados['id_usuario'] ?? $_GET['id'] ?? 0);

        if ($logadoId <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Ação não permitida: Você precisa estar logado para excluir a conta."
            ]);
            return;
        }

        // Impede que um usuário tente excluir a conta de outro
        if ($idEnviado > 0 && $idEnviado !== $logadoId) {
            http_response_code(403);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Ação não permitida: Você só pode excluir sua própria conta."
            ]);
            return;
        }

        $id = $logadoId;

        // Validação obrigatória da senha para confirmação de identidade
        $senha = trim((string)($dados['senha'] ?? ''));
        if ($senha === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Para sua segurança e confirmação de identidade, informe sua senha atual."
            ]);
            return;
        }

        $usuario = $this->usuarioDAO->buscarPorIdComSenha($id);
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

        // Confirmação explícita
        $confirmacao = strtoupper(trim((string)($dados['confirmacao'] ?? '')));
        $termoAceito = !empty($dados['confirmacao_termo']) || $confirmacao === 'EXCLUIR' || !empty($dados['confirmar']);
        if (!$termoAceito) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "É necessário confirmar explicitamente a exclusão permanente da conta."
            ]);
            return;
        }

        $tipoAlvo = strtolower(trim((string)($usuario['tipo_usuario'] ?? '')));
        if (in_array($tipoAlvo, ['admin', 'administrador']) && $this->usuarioDAO->contarAdminsAtivos() <= 1) {
            http_response_code(409);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Não é possível excluir o último administrador ativo do sistema."
            ]);
            return;
        }

        try {
            if ($this->usuarioDAO->excluirContaComRelacionamentos($id)) {
                $_SESSION = [];
                if (!headers_sent() && ini_get("session.use_cookies")) {
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
                    "mensagem" => "Sua conta e dados vinculados foram excluídos permanentemente com sucesso."
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao processar a exclusão da conta no banco de dados."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro interno ao excluir conta: " . $e->getMessage()
            ]);
        }
    }

    private function lerPayload(): array
    {
        $dados = json_decode(file_get_contents("php://input"), true);
        if (!is_array($dados) || empty($dados)) {
            $dados = $_POST;
        }
        return is_array($dados) ? $dados : [];
    }

    /**
     * Cadastro administrativo de adotante ou doador.
     */
    public function criarAdmin(): void
    {
        AuthMiddleware::verificarAdmin();
        $dados = $this->lerPayload();

        $nome = trim((string)($dados['nome'] ?? ''));
        $telefone = trim((string)($dados['telefone'] ?? ''));
        $cpf = trim((string)($dados['cpf'] ?? ''));
        $email = trim((string)($dados['email'] ?? ''));
        $dataNasc = trim((string)($dados['data_nascimento'] ?? $dados['dataNascimento'] ?? ''));
        $cep = trim((string)($dados['cep'] ?? ''));
        $rua = trim((string)($dados['rua'] ?? $dados['endereco'] ?? ''));
        $numero = trim((string)($dados['numero'] ?? ''));
        $bairro = trim((string)($dados['bairro'] ?? ''));
        $cidade = trim((string)($dados['cidade'] ?? ''));
        $estado = trim((string)($dados['estado'] ?? ''));
        $senha = trim((string)($dados['senha'] ?? ''));
        $perfil = strtolower(trim((string)($dados['tipo_usuario'] ?? $dados['perfil'] ?? 'adotante')));

        if ($nome === '' || $telefone === '' || $cpf === '' || $email === '' || $dataNasc === '' || $cep === '' || $rua === '' || $numero === '' || $bairro === '' || $cidade === '' || $estado === '' || $senha === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Preencha todos os campos obrigatórios do cadastro."]);
            return;
        }

        if (!in_array($perfil, ['adotante', 'doador'], true)) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "O tipo de usuário deve ser adotante ou doador."]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "E-mail inválido."]);
            return;
        }

        if (strlen($senha) < 6) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "A senha deve ter no mínimo 6 caracteres."]);
            return;
        }

        if ($this->usuarioDAO->buscarPorEmail($email)) {
            http_response_code(409);
            echo json_encode(["status" => "erro", "mensagem" => "Este e-mail já está cadastrado."]);
            return;
        }

        if ($this->usuarioDAO->buscarPorCpf($cpf)) {
            http_response_code(409);
            echo json_encode(["status" => "erro", "mensagem" => "Este CPF já está cadastrado."]);
            return;
        }

        $valIdade = VerificacaoIdentidadeService::validarIdadeMinima($dataNasc, 16);
        if (!$valIdade['valido']) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => $valIdade['mensagem']]);
            return;
        }

        try {
            $usuario = new Usuario();
            $usuario->setNome($nome);
            $usuario->setTelefone($telefone);
            $usuario->setCpf($cpf);
            $usuario->setEmail($email);
            $usuario->setDataNascimento($dataNasc);
            $usuario->setCep($cep);
            $usuario->setRua($rua);
            $usuario->setNumero($numero);
            $usuario->setBairro($bairro);
            $usuario->setCidade($cidade);
            $usuario->setEstado($estado);
            $usuario->setEndereco("{$rua}, {$numero}" . ($bairro ? " - {$bairro}" : "") . ($cidade ? " - {$cidade}/{$estado}" : ""));
            $usuario->setSenha($senha);
            $usuario->setTipoUsuario($perfil);
            $usuario->setStatusIdentidade('pendente');
            $usuario->setIdade($valIdade['idade'] ?? Usuario::calcularIdadeCompleta($dataNasc));

            if (!empty($_FILES['foto'])) {
                $upFoto = UsuarioDAO::processarUploadFotoPerfil($_FILES['foto'], 'admin_' . time());
                if ($upFoto['sucesso']) {
                    $usuario->setFoto($upFoto['foto_path']);
                } elseif (empty($upFoto['nenhum_arquivo'])) {
                    http_response_code(400);
                    echo json_encode(["status" => "erro", "mensagem" => $upFoto['mensagem'] ?? "Erro no envio da foto."]);
                    return;
                }
            }

            $resCadastro = $this->usuarioDAO->cadastrar($usuario);
            if ($resCadastro) {
                $novoId = $usuario->getId() ?: (int)$resCadastro;
                http_response_code(201);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Usuário cadastrado com sucesso no banco petalert.",
                    "id_usuario" => $novoId,
                    "dados" => $this->usuarioDAO->buscarPorId($novoId)
                ]);
                return;
            }

            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Não foi possível salvar o usuário."]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar: " . $e->getMessage()]);
        }
    }

    /**
     * Edição administrativa pelo id_usuario, sem criar novo registro.
     */
    public function atualizarAdmin(): void
    {
        AuthMiddleware::verificarAdmin();
        $dados = $this->lerPayload();
        $id = (int)($dados['id'] ?? $dados['id_usuario'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Informe o id_usuario para editar."]);
            return;
        }

        $existente = $this->usuarioDAO->buscarPorId($id);
        if (!$existente) {
            http_response_code(404);
            echo json_encode(["status" => "erro", "mensagem" => "Usuário não encontrado."]);
            return;
        }

        $nome = trim((string)($dados['nome'] ?? $existente['nome']));
        $telefone = trim((string)($dados['telefone'] ?? $existente['telefone']));
        $cpf = trim((string)($dados['cpf'] ?? $existente['cpf']));
        $email = trim((string)($dados['email'] ?? $existente['email']));
        $dataNasc = trim((string)($dados['data_nascimento'] ?? $dados['dataNascimento'] ?? $existente['data_nascimento']));
        $cep = trim((string)($dados['cep'] ?? $existente['cep']));
        $rua = trim((string)($dados['rua'] ?? $existente['rua']));
        $numero = trim((string)($dados['numero'] ?? $existente['numero']));
        $bairro = trim((string)($dados['bairro'] ?? $existente['bairro']));
        $cidade = trim((string)($dados['cidade'] ?? $existente['cidade']));
        $estado = trim((string)($dados['estado'] ?? $existente['estado']));
        $tipo = strtolower(trim((string)($dados['tipo_usuario'] ?? $existente['tipo_usuario'] ?? 'adotante')));
        $senha = trim((string)($dados['senha'] ?? $dados['nova_senha'] ?? ''));

        if (!in_array($tipo, ['adotante', 'doador', 'admin', 'administrador'], true)) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Tipo de usuário inválido."]);
            return;
        }

        $tipoAtual = strtolower(trim((string)($existente['tipo_usuario'] ?? '')));
        $eraAdmin = in_array($tipoAtual, ['admin', 'administrador'], true);
        $continuaAdmin = in_array($tipo, ['admin', 'administrador'], true);
        if ($eraAdmin && !$continuaAdmin && $this->usuarioDAO->contarAdminsAtivos() <= 1) {
            http_response_code(409);
            echo json_encode(["status" => "erro", "mensagem" => "Não é possível remover o perfil do último administrador ativo."]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "E-mail inválido."]);
            return;
        }

        if ($this->usuarioDAO->emailPertenceAOutroUsuario($email, $id)) {
            http_response_code(409);
            echo json_encode(["status" => "erro", "mensagem" => "Este e-mail já está cadastrado em outra conta."]);
            return;
        }

        if ($cpf !== '' && $this->usuarioDAO->cpfPertenceAOutroUsuario($cpf, $id)) {
            http_response_code(409);
            echo json_encode(["status" => "erro", "mensagem" => "Este CPF já está cadastrado em outra conta."]);
            return;
        }

        $usuario = new Usuario();
        $usuario->setId($id);
        $usuario->setNome($nome);
        $usuario->setTelefone($telefone);
        $usuario->setCpf($cpf);
        $usuario->setEmail($email);
        $usuario->setDataNascimento($dataNasc);
        $usuario->setCep($cep);
        $usuario->setRua($rua);
        $usuario->setNumero($numero);
        $usuario->setBairro($bairro);
        $usuario->setCidade($cidade);
        $usuario->setEstado($estado);
        $usuario->setEndereco("{$rua}, {$numero}" . ($bairro ? " - {$bairro}" : "") . ($cidade ? " - {$cidade}/{$estado}" : ""));
        $usuario->setTipoUsuario($tipo);
        $usuario->setStatusIdentidade($existente['status_identidade'] ?? 'pendente');
        $usuario->setDataVerificacaoIdentidade($existente['data_verificacao_identidade'] ?? null);
        $usuario->setProtocoloIdentidade($existente['protocolo_identidade'] ?? null);

        if (!$this->usuarioDAO->atualizar($usuario)) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Não foi possível atualizar o usuário."]);
            return;
        }

        if (!empty($_FILES['foto'])) {
            $upFoto = UsuarioDAO::processarUploadFotoPerfil($_FILES['foto'], $id);
            if ($upFoto['sucesso']) {
                $this->usuarioDAO->atualizarFoto($id, $upFoto['foto_path']);
            } elseif (empty($upFoto['nenhum_arquivo'])) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => $upFoto['mensagem'] ?? "Erro no envio da foto."]);
                return;
            }
        }

        if ($senha !== '') {
            if (strlen($senha) < 6) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "A nova senha deve ter no mínimo 6 caracteres."]);
                return;
            }
            $this->usuarioDAO->alterarSenha($id, $senha);
        }

        $atualizado = $this->usuarioDAO->buscarPorId($id);
        unset($atualizado['senha']);
        echo json_encode([
            "status" => "sucesso",
            "mensagem" => "Usuário atualizado com sucesso.",
            "dados" => $atualizado
        ]);
    }

    /**
     * Exclusão administrativa com checagem de vínculos e FKs.
     */
    public function deletarAdmin(): void
    {
        AuthMiddleware::verificarAdmin();
        $dados = $this->lerPayload();
        $id = (int)($dados['id'] ?? $dados['id_usuario'] ?? $_GET['id'] ?? 0);
        $logadoId = (int)($_SESSION['usuario_id'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Informe o id_usuario para excluir."]);
            return;
        }

        if ($id === $logadoId) {
            http_response_code(409);
            echo json_encode(["status" => "erro", "mensagem" => "Você não pode excluir o administrador logado."]);
            return;
        }

        $alvo = $this->usuarioDAO->buscarPorId($id);
        if (!$alvo) {
            http_response_code(404);
            echo json_encode(["status" => "erro", "mensagem" => "Usuário não encontrado."]);
            return;
        }

        $tipoAlvo = strtolower(trim((string)($alvo['tipo_usuario'] ?? '')));
        if (in_array($tipoAlvo, ['admin', 'administrador'], true) && $this->usuarioDAO->contarAdminsAtivos() <= 1) {
            http_response_code(409);
            echo json_encode(["status" => "erro", "mensagem" => "Não é possível excluir o último administrador ativo."]);
            return;
        }

        $vinculos = $this->usuarioDAO->contarVinculosUsuario($id);
        if ($vinculos['total'] > 0) {
            http_response_code(409);
            echo json_encode([
                "status" => "erro",
                "codigo" => "VINCULOS_IMPEDIDOS",
                "mensagem" => "Não foi possível excluir " . ($alvo['nome'] ?? 'o usuário') . " porque existem vínculos no banco: " . implode(', ', $vinculos['detalhes']) . ". Pets, conversas, denúncias e doações foram preservados.",
                "vinculos" => $vinculos
            ]);
            return;
        }

        if ($this->usuarioDAO->excluirSomenteUsuario($id)) {
            echo json_encode([
                "status" => "sucesso",
                "mensagem" => "Usuário " . ($alvo['nome'] ?? '') . " excluído com sucesso."
            ]);
            return;
        }

        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => "A exclusão foi bloqueada pelo banco (chave estrangeira). Nenhum registro relacionado foi removido."]);
    }
}