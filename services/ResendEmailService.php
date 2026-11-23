<?php
/**
 * Serviço de envio de e-mails transacionais do PetAlert usando a API Resend.
 * Utiliza cURL no PHP, garantindo requisições HTTP seguras do backend.
 */

require_once __DIR__ . '/../config/resend.php';

class ResendEmailService
{
    private const RESEND_API_ENDPOINT = 'https://api.resend.com/emails';

    /**
     * Envia e-mail de recuperação de senha com link seguro
     *
     * @param string $destinatario E-mail do usuário
     * @param string $nome Nome do usuário
     * @param string $linkRedefinicao Link completo com token seguro
     * @return array [ 'sucesso' => bool, 'mensagem' => string, 'id' => ?string ]
     */
    public function enviarEmailRecuperacaoSenha(string $destinatario, string $nome, string $linkRedefinicao): array
    {
        $apiKey = ResendConfig::getApiKey();

        if (!$apiKey) {
            error_log("[PetAlert Resend] Chave RESEND_API_KEY não configurada no arquivo .env.");
            return [
                'sucesso' => false,
                'mensagem' => 'O serviço de envio de e-mails (Resend) não está configurado no servidor.',
                'erro_codigo' => 'RESEND_KEY_MISSING'
            ];
        }

        $from = ResendConfig::getFromEmail();
        $assunto = "🐾 PetAlert - Recuperação de Senha";

        $nomeSeguro = htmlspecialchars($nome ?: 'Usuário', ENT_QUOTES, 'UTF-8');
        $linkSeguro = htmlspecialchars($linkRedefinicao, ENT_QUOTES, 'UTF-8');

        // Template HTML do e-mail com a identidade visual do PetAlert (rosa, amarelo pastel e bordas arredondadas)
        $html = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha - PetAlert</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FFFDF9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #2D3748;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #FFFDF9; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 580px; background: #FFFFFF; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 2px solid #FFE4E6; overflow: hidden;" cellspacing="0" cellpadding="0">
                    <!-- Cabeçalho com as cores do PetAlert -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #FF6B8B 0%, #FFA07A 50%, #FFD166 100%); padding: 35px 25px; text-align: center;">
                            <h1 style="color: #FFFFFF; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                                🐾 PetAlert
                            </h1>
                            <p style="color: #FFFFFF; margin: 8px 0 0; font-size: 15px; font-weight: 500; opacity: 0.95;">
                                Plataforma de Proteção & Adoção de Animais
                            </p>
                        </td>
                    </tr>

                    <!-- Conteúdo Principal -->
                    <tr>
                        <td style="padding: 35px 35px 25px;">
                            <h2 style="color: #1A202C; margin-top: 0; font-size: 20px; font-weight: 700;">
                                Olá, {$nomeSeguro}!
                            </h2>
                            <p style="font-size: 15px; line-height: 1.6; color: #4A5568; margin-bottom: 20px;">
                                Recebemos uma solicitação para redefinir a senha da sua conta no <strong>PetAlert</strong>. Se foi você quem solicitou, clique no botão abaixo para cadastrar uma nova senha:
                            </p>

                            <!-- Botão de Ação -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{$linkSeguro}" target="_blank" style="background: linear-gradient(135deg, #FF6B8B 0%, #FA5252 100%); color: #FFFFFF; text-decoration: none; padding: 14px 34px; border-radius: 30px; font-size: 16px; font-weight: 700; display: inline-block; box-shadow: 0 6px 18px rgba(255, 107, 139, 0.35);">
                                    Redefinir Minha Senha
                                </a>
                            </div>

                            <!-- Alerta de Segurança e Validade -->
                            <div style="background-color: #FFF9DB; border-left: 4px solid #FAB005; border-radius: 8px; padding: 14px 18px; margin: 25px 0;">
                                <p style="margin: 0; font-size: 13.5px; line-height: 1.5; color: #744210;">
                                    ⏰ <strong>Atenção:</strong> Este link tem validade de <strong>30 minutos</strong> e só pode ser utilizado <strong>uma única vez</strong>.
                                </p>
                            </div>

                            <p style="font-size: 14px; line-height: 1.6; color: #718096; margin-top: 25px;">
                                Caso o botão acima não funcione, copie e cole o link a seguir no seu navegador:<br>
                                <a href="{$linkSeguro}" style="color: #E64980; word-break: break-all; font-size: 13px;">{$linkSeguro}</a>
                            </p>

                            <p style="font-size: 13px; line-height: 1.5; color: #A0AEC0; margin-top: 30px; border-top: 1px solid #EDF2F7; padding-top: 20px;">
                                Se você <strong>não</strong> solicitou a redefinição de senha, fique tranquilo: nenhuma alteração foi feita e você pode simplesmente desconsiderar esta mensagem.
                            </p>
                        </td>
                    </tr>

                    <!-- Rodapé -->
                    <tr>
                        <td style="background-color: #F8FAFC; padding: 20px; text-align: center; border-top: 1px solid #EDF2F7;">
                            <p style="margin: 0; font-size: 12px; color: #A0AEC0;">
                                © 2026 PetAlert. Cuidando com carinho dos nossos amigos de quatro patas.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        $textoSimples = "Olá, {$nome}!\n\nRecebemos uma solicitação para redefinir a senha da sua conta no PetAlert.\n\nPara redefinir sua senha, acesse o link:\n{$linkRedefinicao}\n\nEste link é válido por 30 minutos e só pode ser usado uma vez.\n\nSe você não solicitou, por favor desconsidere este e-mail.\n\nPetAlert";

        $payload = [
            'from' => $from,
            'to' => [$destinatario],
            'subject' => $assunto,
            'html' => $html,
            'text' => $textoSimples
        ];

        return $this->enviarRequisicaoResend($payload, $apiKey);
    }

    /**
     * Executa a chamada HTTP via cURL para o endpoint do Resend
     */
    private function enviarRequisicaoResend(array $payload, string $apiKey): array
    {
        $ch = curl_init(self::RESEND_API_ENDPOINT);

        $jsonPayload = json_encode($payload);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'User-Agent: PetAlert-Backend/1.0'
            ],
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($curlErrno !== 0) {
            error_log("[PetAlert Resend cURL Error] Code {$curlErrno}: {$curlError}");
            return [
                'sucesso' => false,
                'mensagem' => 'Falha de conexão com a API do Resend.',
                'detalhe' => $curlError
            ];
        }

        $dadosResposta = json_decode($response, true) ?: [];

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'sucesso' => true,
                'mensagem' => 'E-mail enviado com sucesso pelo Resend.',
                'id' => $dadosResposta['id'] ?? null
            ];
        }

        // Falha na API do Resend (ex: 401 Unauthorized, 422 Unprocessable, etc.)
        $msgErro = $dadosResposta['message'] ?? 'Erro desconhecido retornado pelo Resend.';
        error_log("[PetAlert Resend API Error] HTTP {$httpCode}: {$msgErro}");

        return [
            'sucesso' => false,
            'mensagem' => 'A API Resend rejeitou o envio do e-mail: ' . $msgErro,
            'http_code' => $httpCode,
            'detalhes' => $dadosResposta
        ];
    }
}
