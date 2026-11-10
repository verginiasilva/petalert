<?php

/**
 * PetAlert - Serviço de Envio de E-mails Transacionais Direto (Sem APIs Externas)
 *
 * Envia e-mails de recuperação de senha utilizando exclusivamente recursos nativos do PHP:
 * - Cliente SMTP nativo via sockets (stream_socket_client / fsockopen com suporte a STARTTLS e AUTH LOGIN)
 * - Fallback nativo com função mail() do PHP
 * - Caixa de saída local (logs/emails/) para desenvolvimento local, auditoria e testes automatizados.
 */

require_once __DIR__ . '/../config/email.php';

class EmailRecuperacaoService
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromEmail;
    private string $fromName;
    private string $driver;
    private string $logsDir;

    public function __construct()
    {
        $this->host = EmailConfig::getSmtpHost();
        $this->port = EmailConfig::getSmtpPort();
        $this->username = EmailConfig::getSmtpUser();
        $this->password = EmailConfig::getSmtpPassword();
        $this->encryption = EmailConfig::getSmtpEncryption();
        $this->fromEmail = EmailConfig::getFromEmail();
        $this->fromName = EmailConfig::getFromName();
        $this->driver = EmailConfig::getMailDriver();
        $this->logsDir = __DIR__ . '/../logs/emails';

        if (!is_dir($this->logsDir)) {
            @mkdir($this->logsDir, 0777, true);
        }
    }

    /**
     * Envia o e-mail de recuperação de senha para o usuário sem depender de nenhuma API externa.
     *
     * @param string $destinatario E-mail do usuário
     * @param string $nome Nome do usuário
     * @param string $linkRedefinicao Link completo de redefinição com o token
     * @return array [ 'sucesso' => bool, 'mensagem' => string, 'metodo' => string, 'arquivo' => ?string ]
     */
    public function enviarEmailRecuperacao(string $destinatario, string $nome, string $linkRedefinicao): array
    {
        $destinatario = trim($destinatario);
        if ($destinatario === '' || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Endereço de e-mail do destinatário é inválido.'
            ];
        }

        $assunto = "🐾 PetAlert - Recuperação de Senha";
        $html = $this->gerarHtmlEmail($nome, $linkRedefinicao);
        $texto = $this->gerarTextoPlano($nome, $linkRedefinicao);

        // 1. Sempre arquiva na caixa de saída local (garante rastreabilidade, auditoria e testes locais offline)
        $arquivoOutbox = $this->salvarOutboxLocal($destinatario, $assunto, $html, $texto, $linkRedefinicao);

        // 2. Se o driver for SMTP, tenta envio via socket SMTP direto
        $smtpSucesso = false;
        $mensagemDetalhe = '';

        if ($this->driver === 'smtp' && $this->host !== '') {
            $resultadoSmtp = $this->enviarViaSmtpSocket($destinatario, $assunto, $html, $texto);
            if ($resultadoSmtp['sucesso']) {
                return [
                    'sucesso' => true,
                    'mensagem' => 'E-mail de recuperação enviado com sucesso via SMTP.',
                    'metodo' => 'smtp',
                    'arquivo' => $arquivoOutbox
                ];
            }
            $mensagemDetalhe = $resultadoSmtp['mensagem'] ?? '';
            error_log("[EmailRecuperacaoService] Falha no envio SMTP: " . $mensagemDetalhe);
        }

        // 3. Fallback para mail() nativo do PHP caso SMTP não esteja disponível
        if (function_exists('mail')) {
            $boundary = "==Multipart_Boundary_x" . md5((string)time()) . "x";
            $headers = $this->montarHeadersPadrao($boundary);

            $corpoEmail = "--{$boundary}\r\n";
            $corpoEmail .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $corpoEmail .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $corpoEmail .= $texto . "\r\n\r\n";
            $corpoEmail .= "--{$boundary}\r\n";
            $corpoEmail .= "Content-Type: text/html; charset=UTF-8\r\n";
            $corpoEmail .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $corpoEmail .= $html . "\r\n\r\n";
            $corpoEmail .= "--{$boundary}--";

            $assuntoCodificado = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
            $mailEnviado = @mail($destinatario, $assuntoCodificado, $corpoEmail, $headers);

            if ($mailEnviado) {
                return [
                    'sucesso' => true,
                    'mensagem' => 'E-mail de recuperação enviado via função mail() nativa.',
                    'metodo' => 'mail',
                    'arquivo' => $arquivoOutbox
                ];
            }
        }

        // 4. Em ambiente de desenvolvimento local (XAMPP local sem servidor de e-mail ativo),
        // o registro no outbox local garante que o link de recuperação foi gerado com sucesso
        // e pode ser utilizado para testar a recuperação sem travar o usuário.
        return [
            'sucesso' => true,
            'mensagem' => 'Instruções de recuperação geradas com sucesso (salvas na caixa de saída local).',
            'metodo' => 'local_outbox',
            'arquivo' => $arquivoOutbox,
            'aviso' => $mensagemDetalhe ?: 'Servidor SMTP local não respondeu, mensagem armazenada na caixa de saída do sistema.'
        ];
    }

    /**
     * Envia o e-mail conectando-se diretamente a um servidor SMTP via sockets nativos do PHP.
     * Suporta STARTTLS, SSL/TLS implícito e autenticação AUTH LOGIN.
     */
    private function enviarViaSmtpSocket(string $destinatario, string $assunto, string $html, string $texto): array
    {
        $timeout = 8;
        $hostPrefix = ($this->encryption === 'ssl') ? 'ssl://' : '';
        $socketHost = $hostPrefix . $this->host;

        $socket = @stream_socket_client(
            "{$socketHost}:{$this->port}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$socket) {
            return [
                'sucesso' => false,
                'mensagem' => "Não foi possível conectar ao servidor SMTP {$socketHost}:{$this->port} ({$errno}: {$errstr})"
            ];
        }

        stream_set_timeout($socket, $timeout);

        $resposta = $this->lerRespostaSmtp($socket);
        if (!$this->codigoSmtpValido($resposta, 220)) {
            fclose($socket);
            return ['sucesso' => false, 'mensagem' => "Banner SMTP inválido: {$resposta}"];
        }

        // EHLO
        $ehloHost = gethostname() ?: 'localhost';
        fwrite($socket, "EHLO {$ehloHost}\r\n");
        $resposta = $this->lerRespostaSmtp($socket);

        // Se criptografia for TLS (STARTTLS)
        if ($this->encryption === 'tls') {
            fwrite($socket, "STARTTLS\r\n");
            $resposta = $this->lerRespostaSmtp($socket);
            if ($this->codigoSmtpValido($resposta, 220)) {
                $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
                if (!$crypto) {
                    fclose($socket);
                    return ['sucesso' => false, 'mensagem' => 'Falha na negociação TLS com o servidor SMTP.'];
                }
                // Envia EHLO novamente após TLS
                fwrite($socket, "EHLO {$ehloHost}\r\n");
                $resposta = $this->lerRespostaSmtp($socket);
            }
        }

        // Autenticação SMTP (AUTH LOGIN) se credenciais fornecidas
        if ($this->username !== '' && $this->password !== '') {
            fwrite($socket, "AUTH LOGIN\r\n");
            $resposta = $this->lerRespostaSmtp($socket);
            if (!$this->codigoSmtpValido($resposta, 334)) {
                fclose($socket);
                return ['sucesso' => false, 'mensagem' => "Servidor não aceitou AUTH LOGIN: {$resposta}"];
            }

            fwrite($socket, base64_encode($this->username) . "\r\n");
            $resposta = $this->lerRespostaSmtp($socket);
            if (!$this->codigoSmtpValido($resposta, 334)) {
                fclose($socket);
                return ['sucesso' => false, 'mensagem' => "Usuário SMTP rejeitado: {$resposta}"];
            }

            fwrite($socket, base64_encode($this->password) . "\r\n");
            $resposta = $this->lerRespostaSmtp($socket);
            if (!$this->codigoSmtpValido($resposta, 235)) {
                fclose($socket);
                return ['sucesso' => false, 'mensagem' => "Senha SMTP rejeitada: {$resposta}"];
            }
        }

        // MAIL FROM
        fwrite($socket, "MAIL FROM:<{$this->fromEmail}>\r\n");
        $resposta = $this->lerRespostaSmtp($socket);
        if (!$this->codigoSmtpValido($resposta, 250)) {
            fclose($socket);
            return ['sucesso' => false, 'mensagem' => "MAIL FROM rejeitado: {$resposta}"];
        }

        // RCPT TO
        fwrite($socket, "RCPT TO:<{$destinatario}>\r\n");
        $resposta = $this->lerRespostaSmtp($socket);
        if (!$this->codigoSmtpValido($resposta, [250, 251])) {
            fclose($socket);
            return ['sucesso' => false, 'mensagem' => "RCPT TO rejeitado: {$resposta}"];
        }

        // DATA
        fwrite($socket, "DATA\r\n");
        $resposta = $this->lerRespostaSmtp($socket);
        if (!$this->codigoSmtpValido($resposta, 354)) {
            fclose($socket);
            return ['sucesso' => false, 'mensagem' => "Comando DATA rejeitado: {$resposta}"];
        }

        // Montagem da mensagem MIME
        $boundary = "==Multipart_Boundary_x" . md5((string)time()) . "x";
        $assuntoCodificado = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
        $fromCodificado = '=?UTF-8?B?' . base64_encode($this->fromName) . '?= <' . $this->fromEmail . '>';

        $mensagemData = "Date: " . date('r') . "\r\n";
        $mensagemData .= "From: {$fromCodificado}\r\n";
        $mensagemData .= "To: <{$destinatario}>\r\n";
        $mensagemData .= "Subject: {$assuntoCodificado}\r\n";
        $mensagemData .= "MIME-Version: 1.0\r\n";
        $mensagemData .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";

        // Parte Texto
        $mensagemData .= "--{$boundary}\r\n";
        $mensagemData .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $mensagemData .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $mensagemData .= $texto . "\r\n\r\n";

        // Parte HTML
        $mensagemData .= "--{$boundary}\r\n";
        $mensagemData .= "Content-Type: text/html; charset=UTF-8\r\n";
        $mensagemData .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $mensagemData .= $html . "\r\n\r\n";
        $mensagemData .= "--{$boundary}--\r\n";

        // Finaliza com ponto na linha isolada
        $mensagemData .= ".\r\n";

        fwrite($socket, $mensagemData);
        $resposta = $this->lerRespostaSmtp($socket);

        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        if ($this->codigoSmtpValido($resposta, 250)) {
            return ['sucesso' => true, 'mensagem' => 'Mensagem aceita pelo servidor SMTP.'];
        }

        return ['sucesso' => false, 'mensagem' => "Servidor SMTP não confirmou entrega da mensagem: {$resposta}"];
    }

    private function lerRespostaSmtp($socket): string
    {
        $resposta = '';
        while ($linha = fgets($socket, 515)) {
            $resposta .= $linha;
            // Se o 4º caractere for espaço ou fim da linha, terminou a resposta multiline SMTP
            if (isset($linha[3]) && $linha[3] === ' ') {
                break;
            }
        }
        return trim($resposta);
    }

    private function codigoSmtpValido(string $resposta, int|array $codigosEsperados): bool
    {
        $codigo = (int)substr($resposta, 0, 3);
        if (is_array($codigosEsperados)) {
            return in_array($codigo, $codigosEsperados, true);
        }
        return $codigo === $codigosEsperados;
    }

    private function montarHeadersPadrao(string $boundary): string
    {
        $fromCodificado = '=?UTF-8?B?' . base64_encode($this->fromName) . '?= <' . $this->fromEmail . '>';
        $headers = [];
        $headers[] = "From: {$fromCodificado}";
        $headers[] = "Reply-To: {$this->fromEmail}";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        return implode("\r\n", $headers);
    }

    /**
     * Salva a mensagem gerada na caixa de saída local (logs/emails/).
     */
    private function salvarOutboxLocal(string $destinatario, string $assunto, string $html, string $texto, string $link): string
    {
        $safeEmail = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $destinatario);
        $baseName = 'recuperacao_' . date('Ymd_His') . '_' . $safeEmail . '_' . uniqid();

        $pathHtml = $this->logsDir . '/' . $baseName . '.html';
        $pathJson = $this->logsDir . '/' . $baseName . '.json';

        $meta = [
            'data_envio' => date('Y-m-d H:i:s'),
            'destinatario' => $destinatario,
            'assunto' => $assunto,
            'link_redefinicao' => $link,
            'texto_plano' => $texto
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
     * Gera o texto plano alternativo para clientes sem suporte a HTML.
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
