<?php

/**
 * PetAlert - Configurações de E-mail Direto (Sem APIs Externas)
 */

require_once __DIR__ . '/openai.php'; // Carrega variáveis do arquivo .env

class EmailConfig
{
    /**
     * Retorna a URL base pública da aplicação
     */
    public static function getAppUrl(): string
    {
        $url = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? ($_SERVER['APP_URL'] ?? ''));
        if ($url && trim($url) !== '') {
            return rtrim(trim($url), '/');
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $protocol = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Detecção automática de caminho de contexto
        $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptPath);
        if ($baseDir === '/' || $baseDir === '\\') {
            $baseDir = '';
        }

        // Se o host for local (XAMPP antigo), preserva compatibilidade
        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            if (str_contains($scriptPath, 'Backand')) {
                return "{$protocol}://{$host}/Backand%203%20(1).0/public";
            }
        }

        // Em servidor de produção (raiz ou subpasta pública)
        if (str_ends_with($baseDir, '/public')) {
            return "{$protocol}://{$host}{$baseDir}";
        }

        return "{$protocol}://{$host}" . ($baseDir ? "{$baseDir}/public" : "/public");
    }

    public static function getMailDriver(): string
    {
        return strtolower(trim((string)(getenv('MAIL_DRIVER') ?: ($_ENV['MAIL_DRIVER'] ?? 'smtp'))));
    }

    public static function getSmtpHost(): string
    {
        return trim((string)(getenv('MAIL_HOST') ?: ($_ENV['MAIL_HOST'] ?? 'localhost')));
    }

    public static function getSmtpPort(): int
    {
        return (int)(getenv('MAIL_PORT') ?: ($_ENV['MAIL_PORT'] ?? 587));
    }

    public static function getSmtpUser(): string
    {
        return trim((string)(getenv('MAIL_USERNAME') ?: ($_ENV['MAIL_USERNAME'] ?? '')));
    }

    public static function getSmtpPassword(): string
    {
        return trim((string)(getenv('MAIL_PASSWORD') ?: ($_ENV['MAIL_PASSWORD'] ?? '')));
    }

    public static function getSmtpEncryption(): string
    {
        return strtolower(trim((string)(getenv('MAIL_ENCRYPTION') ?: ($_ENV['MAIL_ENCRYPTION'] ?? 'tls'))));
    }

    public static function getFromEmail(): string
    {
        $from = getenv('MAIL_FROM_ADDRESS') ?: ($_ENV['MAIL_FROM_ADDRESS'] ?? 'contato@petalert.com.br');
        return trim((string)$from);
    }

    public static function getFromName(): string
    {
        $name = getenv('MAIL_FROM_NAME') ?: ($_ENV['MAIL_FROM_NAME'] ?? 'PetAlert Proteção Animal');
        return trim((string)$name);
    }

    /**
     * Indica se o SMTP autenticado está pronto para envio real.
     * Host localhost sem usuário/senha não conta como configuração de produção.
     */
    public static function isSmtpConfigured(): bool
    {
        $host = self::getSmtpHost();
        $user = self::getSmtpUser();
        $pass = self::getSmtpPassword();
        if ($host === '' || strtolower($host) === 'localhost' || $host === '127.0.0.1') {
            return $user !== '' && $pass !== '';
        }
        return $host !== '' && $user !== '' && $pass !== '';
    }

    public static function mensagemConfiguracaoPendente(): string
    {
        return 'O envio de e-mail ainda não está configurado. Preencha no arquivo .env (raiz do projeto, não público): MAIL_FROM_ADDRESS, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD e MAIL_ENCRYPTION. Para Gmail use smtp.gmail.com, porta 587, tls e uma senha de aplicativo.';
    }
}
