<?php

require_once __DIR__ . "/../models/Veterinario.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";
require_once __DIR__ . "/../config/database.php";

class VeterinarioController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    private function getUsuarioLogadoId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $id = $_SESSION['usuario_id'] ?? null;
        return $id ? (int)$id : null;
    }

    /**
     * Consulta endereço a partir de um CEP via ViaCEP com timeout e validação rigorosa
     */
    public function consultarViaCep(string $cep): ?array
    {
        $cepLimpo = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cepLimpo) !== 8) {
            return null;
        }

        $url = "https://viacep.com.br/ws/{$cepLimpo}/json/";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'PetAlert-Plataforma/3.0'
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && $res) {
            $dados = json_decode($res, true);
            if (is_array($dados) && empty($dados['erro'])) {
                return $dados;
            }
        }
        return null;
    }

    /**
     * Endpoint dedicado para consulta de CEP independente (Requisito 1)
     */
    public function consultarCep(): void
    {
        $cep = trim((string)($_GET['cep'] ?? ''));
        $cepLimpo = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cepLimpo) !== 8) {
            http_response_code(400);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => 'CEP inválido. O CEP deve conter exatamente 8 dígitos numéricos.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $dadosViaCep = $this->consultarViaCep($cepLimpo);
        if (!$dadosViaCep) {
            http_response_code(404);
            echo json_encode([
                'status' => 'erro',
                'mensagem' => "CEP {$cep} não encontrado. Verifique o número digitado e tente novamente."
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'status' => 'sucesso',
            'dados' => [
                'cep' => $dadosViaCep['cep'] ?? $cepLimpo,
                'logradouro' => $dadosViaCep['logradouro'] ?? '',
                'bairro' => $dadosViaCep['bairro'] ?? '',
                'cidade' => $dadosViaCep['localidade'] ?? '',
                'estado' => $dadosViaCep['uf'] ?? '',
                'uf' => $dadosViaCep['uf'] ?? ''
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Listar clínicas com suporte a visitante e usuário autenticado
     */
    public function listar(): void
    {
        $termo = trim((string)($_GET['termo'] ?? $_GET['busca'] ?? ''));
        $cidadeFiltro = trim((string)($_GET['cidade'] ?? ''));
        $estadoFiltro = strtoupper(trim((string)($_GET['estado'] ?? $_GET['uf'] ?? '')));
        $cepInput = trim((string)($_GET['cep'] ?? ''));
        $cepLimpo = preg_replace('/[^0-9]/', '', $cepInput);

        $usuarioId = $this->getUsuarioLogadoId();
        $usuarioAutenticado = ($usuarioId !== null && $usuarioId > 0);

        $regiaoUsuario = [
            'autenticado' => $usuarioAutenticado,
            'tem_cep' => false,
            'cidade' => null,
            'estado' => null,
            'rotulo' => 'Clínicas e hospitais veterinários cadastrados no Brasil'
        ];

        $userCidade = null;
        $userEstado = null;

        // Se usuário estiver logado, recupera CEP e região do perfil para ordenar/recomendar
        if ($usuarioAutenticado) {
            $stmtU = $this->pdo->prepare("SELECT nome, cep, cidade, estado FROM tb_usuario WHERE id_usuario = ?");
            $stmtU->execute([$usuarioId]);
            $usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                $userCep = preg_replace('/[^0-9]/', '', (string)($usuario['cep'] ?? ''));
                if (strlen($userCep) === 8) {
                    $regiaoUsuario['tem_cep'] = true;
                    $userCidade = trim((string)($usuario['cidade'] ?? ''));
                    $userEstado = strtoupper(trim((string)($usuario['estado'] ?? '')));

                    if ($userCidade === '' || $userEstado === '') {
                        $viaCepUser = $this->consultarViaCep($userCep);
                        if ($viaCepUser) {
                            $userCidade = $viaCepUser['localidade'] ?? $userCidade;
                            $userEstado = $viaCepUser['uf'] ?? $userEstado;
                        }
                    }

                    if ($userCidade !== '' && $userEstado !== '') {
                        $regiaoUsuario['cidade'] = $userCidade;
                        $regiaoUsuario['estado'] = $userEstado;
                    }
                }
            }
        }

        $buscaAtiva = false;
        $rotuloResultado = '';
        $subtituloResultado = '';

        // 1. Busca por CEP (com validação e consulta via ViaCEP)
        if ($cepInput !== '') {
            $buscaAtiva = true;
            if (strlen($cepLimpo) !== 8) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => 'CEP inválido. O CEP deve conter exatamente 8 dígitos.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $viaCep = $this->consultarViaCep($cepLimpo);
            if (!$viaCep) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => "CEP {$cepInput} não foi encontrado na base dos Correios. Verifique os dígitos e tente novamente."
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $cidadeFiltro = $viaCep['localidade'] ?? $cidadeFiltro;
            $estadoFiltro = $viaCep['uf'] ?? $estadoFiltro;
            $bairroCep = $viaCep['bairro'] ?? '';
            $logradouroCep = $viaCep['logradouro'] ?? '';

            $rotuloResultado = "Clínicas veterinárias próximas à região de {$cidadeFiltro}/{$estadoFiltro}";
            $partesSub = array_filter([$logradouroCep, $bairroCep]);
            if (!empty($partesSub)) {
                $subtituloResultado = implode(' - ', $partesSub);
            }
        }
        // 2. Busca por Cidade informada
        elseif ($cidadeFiltro !== '') {
            $buscaAtiva = true;
            $rotuloResultado = "Clínicas veterinárias em {$cidadeFiltro}" . ($estadoFiltro ? "/{$estadoFiltro}" : "");
        }
        // 3. Busca por Termo livre
        elseif ($termo !== '') {
            $buscaAtiva = true;
            // Verifica se o termo coincide com o nome de uma cidade cadastrada no banco
            $stmtCid = $this->pdo->prepare("SELECT DISTINCT cidade, estado FROM tb_veterinario WHERE cidade LIKE ? LIMIT 1");
            $stmtCid->execute(["%{$termo}%"]);
            $cidadeMatch = $stmtCid->fetch(PDO::FETCH_ASSOC);

            if ($cidadeMatch && (mb_strlen($termo) >= 4 || mb_strtolower($termo) === mb_strtolower($cidadeMatch['cidade']))) {
                $cidadeFiltro = $cidadeMatch['cidade'];
                if (empty($estadoFiltro)) {
                    $estadoFiltro = $cidadeMatch['estado'];
                }
                $rotuloResultado = "Clínicas veterinárias em {$cidadeFiltro}" . ($estadoFiltro ? "/{$estadoFiltro}" : "");
            } else {
                $rotuloResultado = "Resultados para \"{$termo}\"" . ($estadoFiltro ? " no estado {$estadoFiltro}" : "");
            }
        }
        // 4. Busca apenas por Estado (UF)
        elseif ($estadoFiltro !== '') {
            $buscaAtiva = true;
            $rotuloResultado = "Clínicas veterinárias no estado de {$estadoFiltro}";
        }
        // 5. Sem filtro ativo
        else {
            if ($userCidade && $userEstado) {
                $rotuloResultado = "Clínicas veterinárias recomendadas para você — {$userCidade}/{$userEstado}";
            } else {
                $rotuloResultado = "Clínicas e hospitais veterinários cadastrados no Brasil";
            }
        }

        $regiaoUsuario['rotulo'] = $rotuloResultado;

        // Montagem da query
        $sql = "SELECT id_veterinario, nome, telefone, endereco, cidade, estado, cep, crmv, email, horario_atendimento, site, foto, latitude, longitude
                FROM tb_veterinario
                WHERE ativo = 1";
        $params = [];

        // Filtro por cidade
        if ($cidadeFiltro !== '') {
            $sql .= " AND (cidade LIKE :cidade OR endereco LIKE :cidadeEnd)";
            $params[':cidade'] = "%{$cidadeFiltro}%";
            $params[':cidadeEnd'] = "%{$cidadeFiltro}%";
        }

        // Filtro por estado
        if ($estadoFiltro !== '') {
            $sql .= " AND (estado = :estado OR endereco LIKE :estadoEnd)";
            $params[':estado'] = $estadoFiltro;
            $params[':estadoEnd'] = "%/{$estadoFiltro}%";
        }

        // Filtro por termo geral (somente quando não for filtrado por cidade)
        if ($termo !== '' && $cidadeFiltro === '') {
            $sql .= " AND (nome LIKE :termo OR endereco LIKE :termo OR telefone LIKE :termo OR crmv LIKE :termo)";
            $params[':termo'] = "%{$termo}%";
        }

        // Ordenação inteligente: Quando não há busca ativa, prioriza a região do usuário
        if (!$buscaAtiva && $userCidade && $userEstado) {
            $sql .= " ORDER BY (CASE WHEN cidade = :userCidade THEN 1 WHEN estado = :userEstado THEN 2 ELSE 3 END), nome ASC";
            $params[':userCidade'] = $userCidade;
            $params[':userEstado'] = $userEstado;
        } else {
            $sql .= " ORDER BY estado ASC, cidade ASC, nome ASC";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $clinicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatação dos resultados para o frontend
        $resultados = [];
        foreach ($clinicas as $c) {
            $cidade = $c['cidade'] ?: 'Cidade não informada';
            $estado = $c['estado'] ?: 'SP';
            $enderecoCompleto = $c['endereco'] ?: "{$cidade} - {$estado}";

            // Gera link seguro para o Google Maps
            $mapaQuery = rawurlencode("{$c['nome']}, {$enderecoCompleto}");
            $mapaUrl = "https://www.google.com/maps/search/?api=1&query={$mapaQuery}";

            // Validar link de site oficial
            $siteValido = null;
            if (!empty($c['site'])) {
                $siteClean = trim($c['site']);
                if (filter_var($siteClean, FILTER_VALIDATE_URL) && !str_contains($siteClean, 'google.com/maps') && !str_contains($siteClean, 'maps.google')) {
                    $siteValido = $siteClean;
                }
            }

            $resultados[] = [
                'id_veterinario' => (int)$c['id_veterinario'],
                'nome' => $c['nome'],
                'telefone' => $c['telefone'] ?: 'Telefone não disponível',
                'endereco' => $enderecoCompleto,
                'cidade' => $cidade,
                'estado' => $estado,
                'localizacao_formatada' => "{$cidade} / {$estado}",
                'crmv' => $c['crmv'] ?: null,
                'horario_atendimento' => $c['horario_atendimento'] ?: 'Consulte horários no estabelecimento',
                'site' => $siteValido,
                'mapa_url' => $mapaUrl,
                'foto' => !empty($c['foto']) ? $c['foto'] : 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=600&auto=format&fit=crop'
            ];
        }

        echo json_encode([
            'status' => 'sucesso',
            'titulo' => $rotuloResultado,
            'subtitulo' => $subtituloResultado,
            'regiao_usuario' => $regiaoUsuario,
            'total' => count($resultados),
            'filtros' => [
                'termo' => $termo,
                'cidade' => $cidadeFiltro,
                'estado' => $estadoFiltro,
                'cep' => $cepInput
            ],
            'dados' => $resultados
        ], JSON_UNESCAPED_UNICODE);
    }

    public function buscar(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->pdo->prepare("SELECT * FROM tb_veterinario WHERE id_veterinario = ?");
        $stmt->execute([$id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            echo json_encode([
                'status' => 'sucesso',
                'dados' => $dados
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Veterinário não encontrado.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function criar(): void
    {
        AuthMiddleware::verificarLogin();

        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;

        $nome = trim((string)($dados['nome'] ?? ''));
        if ($nome === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Nome é obrigatório."]);
            return;
        }

        $sql = "INSERT INTO tb_veterinario (nome, telefone, endereco, cidade, estado, cep, horario_atendimento, site, foto)
                VALUES (:nome, :telefone, :endereco, :cidade, :estado, :cep, :horario, :site, :foto)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':telefone' => $dados['telefone'] ?? null,
            ':endereco' => $dados['endereco'] ?? null,
            ':cidade' => $dados['cidade'] ?? null,
            ':estado' => $dados['estado'] ?? null,
            ':cep' => $dados['cep'] ?? null,
            ':horario' => $dados['horario_atendimento'] ?? null,
            ':site' => $dados['site'] ?? null,
            ':foto' => $dados['foto'] ?? null
        ]);

        echo json_encode([
            "status" => "sucesso",
            "mensagem" => "Veterinário cadastrado com sucesso",
            "id" => (int)$this->pdo->lastInsertId()
        ], JSON_UNESCAPED_UNICODE);
    }

    public function atualizar(): void
    {
        AuthMiddleware::verificarLogin();

        $dados = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $id = (int)($dados['id_veterinario'] ?? $_GET['id'] ?? 0);

        $sql = "UPDATE tb_veterinario SET 
                    nome = :nome,
                    telefone = :telefone,
                    endereco = :endereco,
                    cidade = :cidade,
                    estado = :estado,
                    cep = :cep,
                    horario_atendimento = :horario,
                    site = :site,
                    foto = :foto
                WHERE id_veterinario = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $dados['nome'] ?? '',
            ':telefone' => $dados['telefone'] ?? null,
            ':endereco' => $dados['endereco'] ?? null,
            ':cidade' => $dados['cidade'] ?? null,
            ':estado' => $dados['estado'] ?? null,
            ':cep' => $dados['cep'] ?? null,
            ':horario' => $dados['horario_atendimento'] ?? null,
            ':site' => $dados['site'] ?? null,
            ':foto' => $dados['foto'] ?? null,
            ':id' => $id
        ]);

        echo json_encode(["status" => "sucesso", "mensagem" => "Veterinário atualizado com sucesso"], JSON_UNESCAPED_UNICODE);
    }

    public function deletar(): void
    {
        AuthMiddleware::verificarLogin();

        $id = (int)($_POST['id_veterinario'] ?? $_GET['id'] ?? 0);
        $stmt = $this->pdo->prepare("UPDATE tb_veterinario SET ativo = 0 WHERE id_veterinario = ?");
        $stmt->execute([$id]);

        echo json_encode(["status" => "sucesso", "mensagem" => "Veterinário removido com sucesso"], JSON_UNESCAPED_UNICODE);
    }
}