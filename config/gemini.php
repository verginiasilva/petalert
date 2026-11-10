<?php

/**
 * PetAlert - Configuração da API do Google Gemini
 * Lê com segurança a chave da variável de ambiente GEMINI_API_KEY ou .env
 */

require_once __DIR__ . '/openai.php'; // Carrega o .env

function get_gemini_api_key(): ?string
{
    $key = getenv('GEMINI_API_KEY') ?: getenv('GOOGLE_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? $_ENV['GOOGLE_API_KEY'] ?? null);
    if ($key && trim($key) !== '') {
        return trim($key);
    }
    return null;
}
