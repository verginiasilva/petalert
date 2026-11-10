<?php

// Definir fuso horário padrão do Brasil (São Paulo / Brasília)
date_default_timezone_set('America/Sao_Paulo');

// Em produção, desativar display de erros diretos na tela e registrar em log
$isProd = (getenv('APP_ENV') === 'production' || (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production'));
if ($isProd) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
}

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            // Valores padrão locais (fallback XAMPP)
            $host = '127.0.0.1';
            $port = '3306';
            $dbname = 'petalert';
            $username = 'root';
            $password = '';

            // 1. Suporte a string de conexão DATABASE_URL ou MYSQL_URL (Railway, Heroku, Render)
            $databaseUrl = getenv('DATABASE_URL') ?: (getenv('MYSQL_URL') ?: ($_ENV['DATABASE_URL'] ?? ($_ENV['MYSQL_URL'] ?? null)));
            if ($databaseUrl) {
                $dbParts = parse_url($databaseUrl);
                if ($dbParts) {
                    $host = $dbParts['host'] ?? $host;
                    $port = (string)($dbParts['port'] ?? $port);
                    $username = $dbParts['user'] ?? $username;
                    $password = $dbParts['pass'] ?? $password;
                    $dbname = ltrim($dbParts['path'] ?? "/{$dbname}", '/');
                }
            }

            // 2. Carregar variáveis de ambiente do sistema (injetadas pelo servidor ou nuvem)
            $envHost = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: ($_ENV['DB_HOST'] ?? ($_ENV['MYSQL_HOST'] ?? null)));
            if ($envHost) $host = $envHost;

            $envPort = getenv('DB_PORT') ?: (getenv('MYSQL_PORT') ?: ($_ENV['DB_PORT'] ?? ($_ENV['MYSQL_PORT'] ?? null)));
            if ($envPort) $port = (string)$envPort;

            $envName = getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: (getenv('MYSQL_DATABASE') ?: ($_ENV['DB_NAME'] ?? ($_ENV['DB_DATABASE'] ?? ($_ENV['MYSQL_DATABASE'] ?? null)))));
            if ($envName) $dbname = $envName;

            $envUser = getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: (getenv('MYSQL_USER') ?: ($_ENV['DB_USER'] ?? ($_ENV['DB_USERNAME'] ?? ($_ENV['MYSQL_USER'] ?? null)))));
            if ($envUser) $username = $envUser;

            $envPass = getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: ($_ENV['DB_PASS'] ?? ($_ENV['DB_PASSWORD'] ?? ($_ENV['MYSQL_PASSWORD'] ?? null)))));
            if ($envPass !== null && $envPass !== false) $password = (string)$envPass;

            // 3. Carregar variáveis do arquivo .env se existir na raiz
            $envPath = dirname(__DIR__) . '/.env';
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) continue;
                    if (str_contains($line, '=')) {
                        [$k, $v] = explode('=', $line, 2);
                        $k = trim($k);
                        $v = trim($v, " \t\n\r\0\x0B\"'");
                        if ($k === 'DB_HOST' || $k === 'MYSQL_HOST') $host = $v;
                        if ($k === 'DB_PORT' || $k === 'MYSQL_PORT') $port = $v;
                        if ($k === 'DB_NAME' || $k === 'DB_DATABASE' || $k === 'MYSQL_DATABASE') $dbname = $v;
                        if ($k === 'DB_USER' || $k === 'DB_USERNAME' || $k === 'MYSQL_USER') $username = $v;
                        if ($k === 'DB_PASS' || $k === 'DB_PASSWORD' || $k === 'MYSQL_PASSWORD') $password = $v;
                    }
                }
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            // Sincronizar timezone do MySQL com o fuso brasileiro (-03:00)
            try {
                self::$connection->exec("SET time_zone = '-03:00'");
            } catch (Exception $e) {}
        }
        return self::$connection;
    }
}
