<?php
/**
 * Carrega variáveis de ambiente do arquivo .env no diretório raiz do projeto.
 * A chave da Gemini deve ficar somente no backend/.env e nunca em frontend.
 */

function petalert_load_env(string $envPath): void
{
    if (!is_file($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_contains($line, '=')) {
            [$name, $value] = array_map('trim', explode('=', $line, 2));
            $name = trim($name);
            $value = trim($value);

            if ($name === '') {
                continue;
            }

            if (($value[0] ?? '') === '"' && substr($value, -1) === '"') {
                $value = substr($value, 1, -1);
            }

            $_ENV[$name] = $value;
            putenv($name . '=' . $value);
            $_SERVER[$name] = $value;
        }
    }
}

petalert_load_env(__DIR__ . '/../.env');

function get_openai_key(): ?string
{
    $key = getenv('OPENAI_API_KEY');
    return $key && strlen($key) > 0 ? $key : null;
}

function get_google_ai_key(): ?string
{
    $key = getenv('GOOGLE_API_KEY') ?: getenv('GEMINI_API_KEY');
    return $key && strlen($key) > 0 ? $key : null;
}
