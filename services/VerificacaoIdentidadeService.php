<?php

/**
 * PetAlert - Serviço Centralizado de Verificação de Identidade
 * Valida a compatibilidade cadastral entre CPF, Nome Completo e Data de Nascimento.
 * Integração segura via backend PHP com provedores oficiais.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/identidade.php';

class VerificacaoIdentidadeService
{
    private array $config;
    private PDO $pdo;

    public function __construct()
    {
        $this->config = get_identidade_config();
        $this->pdo = Database::getConnection();
    }

    /**
     * Remove pontuações e mantém apenas números do CPF
     */
    public static function normalizarCpf(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }

    /**
     * Validação matemática rigorosa do CPF pelos dígitos verificadores (módulo 11)
     */
    public static function validarDigitosCpf(string $cpf): bool
    {
        $cpf = self::normalizarCpf($cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Rejeita sequências de dígitos idênticos (ex: 111.111.111-11)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Cálculo dos dois dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
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
     * Validação estrutural de Nome Completo (deve conter nome e sobrenome)
     */
    public static function validarNomeCompleto(string $nome): bool
    {
        $nome = trim(preg_replace('/\s+/', ' ', $nome));
        $partes = explode(' ', $nome);

        // Deve possuir ao menos 2 termos e tamanho mínimo razoável
        if (count($partes) < 2 || mb_strlen($nome, 'UTF-8') < 4) {
            return false;
        }

        foreach ($partes as $p) {
            if (mb_strlen($p, 'UTF-8') < 2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validação de Data de Nascimento (formato, existência no calendário e não futura)
     */
    public static function validarDataNascimento(string $data): bool
    {
        $data = trim($data);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return false;
        }

        [$ano, $mes, $dia] = array_map('intval', explode('-', $data));

        if (!checkdate($mes, $dia, $ano)) {
            return false;
        }

        $hoje = new DateTime('today');
        $nascimento = DateTime::createFromFormat('Y-m-d', $data);

        if (!$nascimento) {
            return false;
        }

        // Data de nascimento não pode ser hoje nem futura
        if ($nascimento >= $hoje) {
            return false;
        }

        // Idade mínima humana razoável (ano >= 1900)
        if ($ano < 1900) {
            return false;
        }

        return true;
    }

    /**
     * Calcula a idade exata com base em dia, mês e ano completos
     */
    public static function calcularIdade(string $dataNascimento): ?int
    {
        $data = trim($dataNascimento);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return null;
        }

        [$ano, $mes, $dia] = array_map('intval', explode('-', $data));
        if (!checkdate($mes, $dia, $ano)) {
            return null;
        }

        $nasc = DateTime::createFromFormat('!Y-m-d', $data);
        if (!$nasc) {
            $nasc = new DateTime($data);
        }
        $nasc->setTime(0, 0, 0);

        $hoje = new DateTime('today');
        $hoje->setTime(0, 0, 0);

        if ($nasc > $hoje) {
            return -1;
        }

        return (int)$hoje->diff($nasc)->y;
    }

    /**
     * Validação de Idade Mínima Obrigatória (Regra: Somente >= 16 anos podem criar conta)
     */
    public static function validarIdadeMinima(string $dataNascimento, int $idadeMinima = 16): array
    {
        $data = trim($dataNascimento);
        if ($data === '') {
            return [
                'valido' => false,
                'codigo' => 'DATA_OBRIGATORIA',
                'mensagem' => 'Por favor, informe sua data de nascimento.'
            ];
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return [
                'valido' => false,
                'codigo' => 'DATA_INVALIDA',
                'mensagem' => 'A data de nascimento informada é inválida ou está no futuro.'
            ];
        }

        [$ano, $mes, $dia] = array_map('intval', explode('-', $data));
        if (!checkdate($mes, $dia, $ano)) {
            return [
                'valido' => false,
                'codigo' => 'DATA_INEXISTENTE',
                'mensagem' => 'A data de nascimento informada é inválida ou está no futuro.'
            ];
        }

        $hoje = new DateTime('today');
        $hoje->setTime(0, 0, 0);

        $nascimento = DateTime::createFromFormat('!Y-m-d', $data);
        if (!$nascimento) {
            $nascimento = new DateTime($data);
        }
        $nascimento->setTime(0, 0, 0);

        if ($nascimento >= $hoje) {
            return [
                'valido' => false,
                'codigo' => 'DATA_FUTURA',
                'mensagem' => 'A data de nascimento informada é inválida ou está no futuro.'
            ];
        }

        if ($ano < 1900) {
            return [
                'valido' => false,
                'codigo' => 'DATA_INVALIDA',
                'mensagem' => 'A data de nascimento informada é inválida.'
            ];
        }

        $diff = $hoje->diff($nascimento);
        $idade = (int)$diff->y;

        if ($idade < $idadeMinima) {
            return [
                'valido' => false,
                'codigo' => 'IDADE_INSUFICIENTE',
                'mensagem' => 'Você precisa ter pelo menos 16 anos para se cadastrar na nossa plataforma.',
                'idade' => $idade
            ];
        }

        return [
            'valido' => true,
            'idade' => $idade
        ];
    }

    /**
     * Validação local completa de pré-requisitos antes da chamada externa
     */
    public function validarDadosLocais(string $cpf, string $nome, string $dataNascimento): array
    {
        $cpfLimpo = self::normalizarCpf($cpf);

        if (!self::validarDigitosCpf($cpfLimpo)) {
            return [
                'valido' => false,
                'codigo' => 'CPF_INVALIDO',
                'mensagem' => 'O CPF informado possui dígitos verificadores inválidos.'
            ];
        }

        if (!self::validarNomeCompleto($nome)) {
            return [
                'valido' => false,
                'codigo' => 'NOME_INCOMPLETO',
                'mensagem' => 'Por favor, informe seu nome completo (nome e sobrenome).'
            ];
        }

        if (!self::validarDataNascimento($dataNascimento)) {
            return [
                'valido' => false,
                'codigo' => 'DATA_NASCIMENTO_INVALIDA',
                'mensagem' => 'A data de nascimento informada é inválida ou está no futuro.'
            ];
        }

        $valIdade = self::validarIdadeMinima($dataNascimento, 16);
        if (!$valIdade['valido']) {
            return [
                'valido' => false,
                'codigo' => $valIdade['codigo'],
                'mensagem' => $valIdade['mensagem']
            ];
        }

        return ['valido' => true, 'idade' => $valIdade['idade'] ?? null];
    }

    /**
     * Executa a verificação de compatibilidade de identidade.
     * 
     * Regras de Segurança:
     * - Não aprova a conta automaticamente se a API externa não estiver configurada.
     * - Não inventa dados de validação fictícios.
     * - Registra se está pendente de documentação/URL do provedor.
     */
    public function verificarCompatibilidade(string $cpf, string $nome, string $dataNascimento): array
    {
        // 1. Validação local dos dados de entrada
        $validacaoLocal = $this->validarDadosLocais($cpf, $nome, $dataNascimento);
        if (!$validacaoLocal['valido']) {
            return [
                'sucesso' => false,
                'status' => 'falha',
                'compativel' => false,
                'codigo' => $validacaoLocal['codigo'],
                'mensagem' => $validacaoLocal['mensagem']
            ];
        }

        $cpfLimpo = self::normalizarCpf($cpf);
        $nomeLimpo = trim(preg_replace('/\s+/', ' ', $nome));
        $dataLimpa = trim($dataNascimento);

        // 2. Verifica se a integração está totalmente configurada no .env
        if (!$this->config['configurado']) {
            // A chave está informada, mas a URL do endpoint oficial ainda aguarda a documentação do serviço
            return [
                'sucesso' => false,
                'status' => 'pendente',
                'compativel' => null,
                'codigo' => 'INTEGRACAO_AGUARDANDO_URL',
                'mensagem' => 'A chave da API de verificação de identidade está configurada no .env, porém o endpoint oficial (IDENTIDADE_API_URL) ainda não foi definido com a documentação do provedor.',
                'requer_configuracao_url' => true,
                'chave_configurada' => $this->config['tem_chave'],
                'url_configurada' => $this->config['tem_url']
            ];
        }

        // 3. Chamada segura ao provedor oficial da API
        return $this->enviarParaProvedorOficial($cpfLimpo, $nomeLimpo, $dataLimpa);
    }

    /**
     * Executa a requisição HTTP segura ao serviço oficial de validação de identidade
     * 
     * Ponto centralizado para mapear os parâmetros conforme a documentação oficial da API.
     */
    private function enviarParaProvedorOficial(string $cpf, string $nome, string $dataNasc): array
    {
        $url = $this->config['api_url'];
        $apiKey = $this->config['api_key'];

        // Template de payload para envio ao provedor
        $payload = [
            'cpf' => $cpf,
            'nome' => $nome,
            'data_nascimento' => $dataNasc
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $apiKey,
                'x-api-key: ' . $apiKey
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($resposta === false || !empty($curlError)) {
            return [
                'sucesso' => false,
                'status' => 'falha',
                'compativel' => false,
                'codigo' => 'SERVICO_INDISPONIVEL',
                'mensagem' => 'O serviço oficial de verificação de identidade está temporariamente inacessível. Tente novamente mais tarde.'
            ];
        }

        $dadosResposta = json_decode($resposta, true);

        // Tratamento de códigos de retorno HTTP
        if ($httpCode === 401 || $httpCode === 403) {
            return [
                'sucesso' => false,
                'status' => 'falha',
                'compativel' => false,
                'codigo' => 'FALHA_AUTENTICACAO_API',
                'mensagem' => 'Falha de autenticação junto ao serviço de verificação de identidade. Verifique a chave de API.'
            ];
        }

        if ($httpCode === 429) {
            return [
                'sucesso' => false,
                'status' => 'falha',
                'compativel' => false,
                'codigo' => 'LIMITE_REQUISICOES',
                'mensagem' => 'O limite de consultas da API de identidade foi atingido. Aguarde alguns instantes.'
            ];
        }

        // Ponto de interpretação da resposta oficial (ajustável conforme contrato do provedor)
        $compativel = false;
        $mensagemOficial = 'Dados conferidos junto ao serviço oficial de identidade.';

        if ($httpCode === 200 && is_array($dadosResposta)) {
            // Mapeamento padrão flexível para os formatos comuns de APIs de identidade
            if (isset($dadosResposta['compativel'])) {
                $compativel = (bool)$dadosResposta['compativel'];
            } elseif (isset($dadosResposta['status']) && in_array(strtolower($dadosResposta['status']), ['regular', 'aprovado', 'compativel', 'sucesso'])) {
                $compativel = true;
            } elseif (isset($dadosResposta['valido'])) {
                $compativel = (bool)$dadosResposta['valido'];
            }

            if (!empty($dadosResposta['mensagem'])) {
                $mensagemOficial = (string)$dadosResposta['mensagem'];
            }
        }

        $protocolo = $dadosResposta['protocolo'] ?? $dadosResposta['id'] ?? uniqid('id_');

        if ($compativel) {
            return [
                'sucesso' => true,
                'status' => 'verificado',
                'compativel' => true,
                'protocolo' => $protocolo,
                'mensagem' => 'Identidade confirmada com sucesso: CPF compatível com o nome e data de nascimento informados.'
            ];
        }

        return [
            'sucesso' => false,
            'status' => 'falha',
            'compativel' => false,
            'protocolo' => $protocolo,
            'codigo' => 'DADOS_INCOMPATIVEIS',
            'mensagem' => 'Os dados informados (CPF, Nome e Data de Nascimento) não coincidem com o registro oficial da Receita Federal.'
        ];
    }

    /**
     * Atualiza o status de verificação de identidade do usuário no banco de dados
     */
    public function atualizarStatusUsuario(int $idUsuario, string $status, ?string $protocolo = null): bool
    {
        $statusPermitidos = ['nao_verificado', 'pendente', 'verificado', 'falha'];
        if (!in_array($status, $statusPermitidos)) {
            $status = 'pendente';
        }

        $sql = "UPDATE tb_usuario 
                SET status_identidade = :status, 
                    data_verificacao_identidade = NOW(),
                    protocolo_identidade = :protocolo
                WHERE id_usuario = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':protocolo' => $protocolo,
            ':id' => $idUsuario
        ]);
    }
}
