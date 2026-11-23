<?php

/**
 * PetAlert - Serviço de Envio de E-mails via PHPMailer (SMTP Autenticado)
 *
 * Este arquivo é o equivalente funcional ao "enviar_email.php" solicitado.
 * Utiliza PHPMailer instalado via Composer para envio confiável de e-mails
 * de recuperação de senha através de SMTP autenticado.
 *
 * Credenciais SMTP são carregadas do arquivo .env via config/email.php.
 * Nunca são expostas diretamente no código-fonte.
 *
 * Para configurar, edite o arquivo .env na raiz do projeto:
 *   MAIL_HOST=smtp.gmail.com          ← Servidor SMTP do seu provedor
 *   MAIL_PORT=587                     ← Porta (587 para TLS, 465 para SSL)
 *   MAIL_USERNAME=seuemail@gmail.com  ← Seu e-mail ou usuário SMTP
 *   MAIL_PASSWORD=suasenhaapp         ← Senha de aplicativo (não a senha normal)
 *   MAIL_ENCRYPTION=tls              ← tls ou ssl
 *   MAIL_FROM_ADDRESS=contato@petalert.com.br
 *   MAIL_FROM_NAME=PetAlert Proteção Animal
 */

require_once __DIR__ . '/../config/email.php';

// Carrega autoload do Composer (PHPMailer)
$_composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($_composerAutoload)) {
    require_once $_composerAutoload;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class PHPMailerEmailService
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromEmail;
    private string $fromName;
    private string $logsDir;

    public function __construct()
    {
        $this->host       = EmailConfig::getSmtpHost();
        $this->port       = EmailConfig::getSmtpPort();
        $this->username   = EmailConfig::getSmtpUser();
        $this->password   = EmailConfig::getSmtpPassword();
        $this->encryption = EmailConfig::getSmtpEncryption();
        $this->fromEmail  = EmailConfig::getFromEmail();
        $this->fromName   = EmailConfig::getFromName();
        $this->logsDir    = __DIR__ . '/../logs/emails';

        if (!is_dir($this->logsDir)) {
            @mkdir($this->logsDir, 0777, true);
        }
    }

    /**
     * Envia o e-mail de recuperação de senha utilizando PHPMailer (SMTP).
     *
     * Fluxo:
     * 1. Sempre salva na caixa de saída local (logs/emails/) para auditoria.
     * 2. Tenta enviar via PHPMailer SMTP com as credenciais do .env.
     * 3. Se o SMTP não estiver configurado ou falhar, o link fica disponível
     *    na caixa de saída local para testes em ambiente de desenvolvimento.
     *
     * @param string $destinatario E-mail do usuário
     * @param string $nome         Nome do usuário
     * @param string $linkRedefinicao Link completo com o token
     * @return array ['sucesso' => bool, 'mensagem' => string, 'metodo' => string]
     */
    public function enviarEmailRecuperacao(string $destinatario, string $nome, string $linkRedefinicao): array
    {
        $destinatario = trim($destinatario);
        if ($destinatario === '' || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'mensagem' => 'Endereço de e-mail do destinatário é inválido.'];
        }

        $assunto = "🐾 PetAlert - Recuperação de Senha";
        $html    = $this->gerarHtmlEmail($nome, $linkRedefinicao);
        $texto   = $this->gerarTextoPlano($nome, $linkRedefinicao);

        // 1. Sempre salva na caixa de saída local (auditoria e desenvolvimento)
        $arquivoOutbox = $this->salvarOutboxLocal($destinatario, $assunto, $html, $texto, $linkRedefinicao);

        if (!EmailConfig::isSmtpConfigured()) {
            return [
                'sucesso'  => false,
                'mensagem' => EmailConfig::mensagemConfiguracaoPendente(),
                'metodo'   => 'nao_configurado',
                'arquivo'  => $arquivoOutbox
            ];
        }

        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            error_log("[PHPMailerEmailService] PHPMailer não encontrado. Execute 'composer install' na raiz do projeto.");
            return [
                'sucesso'  => false,
                'mensagem' => 'PHPMailer não está instalado. Execute composer install na raiz do projeto.',
                'metodo'   => 'phpmailer_ausente',
                'arquivo'  => $arquivoOutbox
            ];
        }
        try {
            $mail = new PHPMailer(true); // true = habilita exceções

            // ─── Configuração do servidor SMTP ───
            $mail->isSMTP();
            $mail->Host       = $this->host;       // Ex: smtp.gmail.com
            $mail->Port       = $this->port;        // Ex: 587 (TLS) ou 465 (SSL)
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';
            $mail->Timeout    = 10;

            // ─── Autenticação SMTP ───
            if ($this->username !== '' && $this->password !== '') {
                $mail->SMTPAuth = true;
                $mail->Username = $this->username;  // Definido em .env → MAIL_USERNAME
                $mail->Password = $this->password;  // Definido em .env → MAIL_PASSWORD
            } else {
                $mail->SMTPAuth = false;
            }

            // ─── Criptografia ───
            if ($this->encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($this->encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }

            // ─── Remetente e destinatário ───
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($destinatario, $nome);

            // ─── Conteúdo do e-mail ───
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $html;
            $mail->AltBody = $texto;

            // ─── Envio ───
            $mail->send();

            return [
                'sucesso'  => true,
                'mensagem' => 'E-mail de recuperação enviado com sucesso via PHPMailer SMTP.',
                'metodo'   => 'phpmailer_smtp',
                'arquivo'  => $arquivoOutbox
            ];

        } catch (PHPMailerException $e) {
            $msgErro = $e->getMessage();
            error_log("[PHPMailerEmailService] Falha PHPMailer SMTP: " . $msgErro);
            return [
                'sucesso'  => false,
                'mensagem' => 'Falha no envio SMTP. Confira usuário, senha de aplicativo, porta e criptografia no .env.',
                'metodo'   => 'smtp_falhou',
                'arquivo'  => $arquivoOutbox,
                'aviso'    => $msgErro
            ];
        } catch (\Exception $e) {
            error_log("[PHPMailerEmailService] Erro geral ao enviar: " . $e->getMessage());
            return [
                'sucesso'  => false,
                'mensagem' => 'Não foi possível enviar o e-mail de recuperação.',
                'metodo'   => 'erro',
                'arquivo'  => $arquivoOutbox
            ];
        }
    }

    /**
     * Salva a mensagem na caixa de saída local (logs/emails/) para
     * auditoria, desenvolvimento e testes no XAMPP.
     */
    private function salvarOutboxLocal(string $destinatario, string $assunto, string $html, string $texto, string $link): string
    {
        $safeEmail = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $destinatario);
        $baseName  = 'recuperacao_' . date('Ymd_His') . '_' . $safeEmail . '_' . uniqid();

        $pathHtml = $this->logsDir . '/' . $baseName . '.html';
        $pathJson = $this->logsDir . '/' . $baseName . '.json';

        $meta = [
            'data_envio'       => date('Y-m-d H:i:s'),
            'destinatario'     => $destinatario,
            'assunto'          => $assunto,
            'link_redefinicao' => $link,
            'texto_plano'      => $texto,
            'metodo_envio'     => 'phpmailer'
        ];

        @file_put_contents($pathHtml, $html);
        @file_put_contents($pathJson, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $pathHtml;
    }

    /**
     * Gera o template HTML do e-mail com a identidade visual do PetAlert.
     */
    public function gerarHtmlEmail(string $nome, string $linkRedefinicao): string
    {
        $nomeSeguro = htmlspecialchars($nome ?: 'Usuário', ENT_QUOTES, 'UTF-8');
        $linkSeguro = htmlspecialchars($linkRedefinicao, ENT_QUOTES, 'UTF-8');

        return <<<HTML
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
                    <!-- Cabeçalho PetAlert -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #FF6B8B 0%, #FFA07A 50%, #FFD166 100%); padding: 35px 25px; text-align: center;">
                            <h1 style="color: #FFFFFF; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                                🐾 PetAlert
                            </h1>
                            <p style="color: #FFFFFF; margin: 8px 0 0; font-size: 15px; font-weight: 500; opacity: 0.95;">
                                Plataforma de Proteção &amp; Adoção de Animais
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
                                Se você <strong>não</strong> solicitou a redefinição de senha, fique tranquilo: nenhuma alteração foi feita e você pode simplesmente desconsiderar este e-mail.
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
    }

    /**
     * Gera o texto plano alternativo para clientes de e-mail sem suporte a HTML.
     */
    public function gerarTextoPlano(string $nome, string $linkRedefinicao): string
    {
        return "Olá, {$nome}!\n\n"
            . "Recebemos uma solicitação para redefinir a senha da sua conta no PetAlert.\n\n"
            . "Para redefinir sua senha, acesse o link:\n"
            . "{$linkRedefinicao}\n\n"
            . "Atenção: Este link é válido por 30 minutos e só pode ser usado uma única vez.\n\n"
            . "Se você não solicitou esta redefinição, por favor desconsidere esta mensagem.\n\n"
            . "Atenciosamente,\nEquipe PetAlert";
    }
}
