<?php

/**
 * PetAlert - Configuração da Verificação de Identidade (CPF, Nome e Data de Nascimento)
 * Centraliza o carregamento seguro das credenciais via .env sem expor no frontend.
 */

require_once __DIR__ . '/openai.php'; // Garante o carregamento do .env via petalert_load_env

function get_identidade_config(): array
{
    $key = getenv('IDENTIDADE_API_KEY') ?: ($_ENV['IDENTIDADE_API_KEY'] ?? '');
    $url = getenv('IDENTIDADE_API_URL') ?: ($_ENV['IDENTIDADE_API_URL'] ?? '');
    $modo = getenv('IDENTIDADE_MODO') ?: ($_ENV['IDENTIDADE_MODO'] ?? 'producao');

    $keyClean = trim((string)$key);
    $urlClean = trim((string)$url);

    return [
        'api_key' => $keyClean,
        'api_url' => $urlClean,
        'modo' => trim((string)$modo),
        'tem_chave' => ($keyClean !== ''),
        'tem_url' => ($urlClean !== ''),
        'configurado' => ($keyClean !== '' && $urlClean !== '' && filter_var($urlClean, FILTER_VALIDATE_URL) !== false)
    ];
}
