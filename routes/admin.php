<?php

require_once __DIR__ . "/../controllers/AdminController.php";

$controller = new AdminController();
$acao = $_GET['acao'] ?? $_POST['acao'] ?? 'dashboard';

switch ($acao) {
    case 'dashboard':
    case 'resumo':
        $controller->dashboard();
        break;

    // Usuários
    case 'usuarios':
        $controller->usuarios();
        break;
    case 'usuario-toggle-status':
    case 'usuario-alternar-status':
        $controller->usuarioAlternarStatus();
        break;

    // Pets / Animais
    case 'pets':
        $controller->pets();
        break;
    case 'pet-salvar':
        $controller->petSalvar();
        break;
    case 'pet-status':
    case 'pet-marcar-adotado':
        $controller->petMarcarAdotado();
        break;
    case 'pet-excluir':
        $controller->petExcluir();
        break;

    // Denúncias
    case 'denuncias':
        $controller->denuncias();
        break;
    case 'denuncia-atualizar':
    case 'denuncia-status':
        $controller->denunciaAtualizar();
        break;

    // Doações
    case 'doacoes':
        $controller->doacoes();
        break;

    // Clínicas Veterinárias
    case 'veterinarios':
        $controller->veterinarios();
        break;
    case 'veterinario-salvar':
        $controller->veterinarioSalvar();
        break;
    case 'veterinario-excluir':
        $controller->veterinarioExcluir();
        break;

    // Histórico de Atividades
    case 'historico':
        $controller->historico();
        break;

    // Política de Privacidade & LGPD
    case 'politica-privacidade':
    case 'privacidade':
        $controller->politicaPrivacidade();
        break;

    // 3 Administradores Oficiais
    case 'admins-oficiais':
        $controller->adminsOficiais();
        break;
    case 'cadastrar-maria':
    case 'cadastrar-maria-eduarda':
        $controller->cadastrarMariaEduarda();
        break;

    default:
        http_response_code(400);
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Ação administrativa não especificada: {$acao}"
        ], JSON_UNESCAPED_UNICODE);
}
