<?php
/**
 * Configuração e recuperação de credenciais da API Resend.
 * As credenciais são carregadas do arquivo .env ou do ambiente do sistema.
 * NUNCA exponha a chave RESEND_API_KEY no frontend ou em logs.
 */

require_once __DIR__ . '/openai.php'; // Garante o carregamento do .env via petalert_load_env

class ResendConfig
{
    /**
     * Retorna a chave da API Resend
     */
    public static function getApiKey(): ?string
    {
        $key = getenv('RESEND_API_KEY') ?: ($_ENV['RESEND_API_KEY'] ?? ($_SERVER['RESEND_API_KEY'] ?? null));
        if ($key && trim($key) !== '' && trim($key) !== 're_xxxxxxxxx') {
            return trim($key);
        }
        return null;
    }

    /**
     * Retorna o e-mail/remetente configurado
     * Por padrão na conta gratuita de teste do Resend usa onboarding@resend.dev
     */
    public static function getFromEmail(): string
    {
        $from = getenv('RESEND_FROM_EMAIL') ?: ($_ENV['RESEND_FROM_EMAIL'] ?? ($_SERVER['RESEND_FROM_EMAIL'] ?? ''));
        if ($from && trim($from) !== '') {
            return trim($from);
        }
        return 'PetAlert <onboarding@resend.dev>';
    }

    /**
     * Retorna a URL base da aplicação para links em e-mails
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
        
        $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptPath);
        if ($baseDir === '/' || $baseDir === '\\') {
            $baseDir = '';
        }

        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            if (str_contains($scriptPath, 'Backand')) {
                return "{$protocol}://{$host}/Backand%203%20(1).0/public";
            }
        }

        if (str_ends_with($baseDir, '/public')) {
            return "{$protocol}://{$host}{$baseDir}";
        }

        return "{$protocol}://{$host}" . ($baseDir ? "{$baseDir}/public" : "/public");
    }

    /**
     * Diagnóstico simples do status da API sem vazar dados sensíveis
     */
    public static function getStatus(): array
    {
        $key = self::getApiKey();
        return [
            'configurado' => !empty($key),
            'from' => self::getFromEmail(),
            'app_url' => self::getAppUrl(),
            'status' => !empty($key) ? 'pronto' : 'pendente_configuracao'
        ];
    }
}
