<?php

/**
 * PetAlert - Configurações do Provedor de Pagamentos e PIX
 * Permite customização via variáveis de ambiente (.env) ou valores padrões.
 */

require_once __DIR__ . '/openai.php'; // Garante o carregamento do .env

return [
    'chave_pix' => getenv('PIX_CHAVE') ?: ($_ENV['PIX_CHAVE'] ?? 'contato@petalert.com.br'),
    'beneficiario' => getenv('PIX_BENEFICIARIO') ?: ($_ENV['PIX_BENEFICIARIO'] ?? 'PETALERT PROTECAO ANIMAL'),
    'cidade' => getenv('PIX_CIDADE') ?: ($_ENV['PIX_CIDADE'] ?? 'OURINHOS'),
    'provedor' => getenv('PIX_PROVIDER') ?: ($_ENV['PIX_PROVIDER'] ?? 'padrao_bacen'), // 'padrao_bacen', 'mercadopago', 'efipay'
    'webhook_secret' => getenv('PIX_WEBHOOK_SECRET') ?: ($_ENV['PIX_WEBHOOK_SECRET'] ?? 'petalert_pix_secret_token_2026')
];
