<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

class AuthMiddleware
{
    /**
     * Verifica se o usuário está autenticado na sessão.
     * Retorna HTTP 401 caso não esteja autenticado.
     */
    public static function verificarLogin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || (int)$_SESSION['usuario_id'] <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "erro" => "Você precisa estar logado para acessar isso",
                "mensagem" => "Você precisa estar logado para acessar isso"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!self::usuarioSessaoAtivo()) {
            http_response_code(403);
            echo json_encode([
                "status" => "erro",
                "erro" => "Conta inativa",
                "mensagem" => "Esta conta está inativa e não pode acessar o sistema."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return true;
    }

    public static function usuarioSessaoAtivo(): bool
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId <= 0) {
            return false;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT ativo, tipo_usuario FROM tb_usuario WHERE id_usuario = ? LIMIT 1");
            $stmt->execute([$usuarioId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$res) {
                return false;
            }
            if (!empty($res['tipo_usuario'])) {
                $_SESSION['usuario_tipo'] = $res['tipo_usuario'];
            }
            return (int)($res['ativo'] ?? 0) === 1;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Retorna se o usuário logado possui perfil de Administrador (admin).
     */
    public static function isAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId <= 0) {
            return false;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT tipo_usuario, ativo FROM tb_usuario WHERE id_usuario = ? LIMIT 1");
            $stmt->execute([$usuarioId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$res || (int)($res['ativo'] ?? 0) !== 1) {
                return false;
            }
            $tipoBanco = strtolower(trim((string)($res['tipo_usuario'] ?? '')));
            $_SESSION['usuario_tipo'] = $res['tipo_usuario'];
            return in_array($tipoBanco, ['admin', 'administrador']);
        } catch (Exception $e) {
            $tipo = strtolower(trim((string)($_SESSION['usuario_tipo'] ?? '')));
            return in_array($tipo, ['admin', 'administrador']);
        }
    }

    /**
     * Middleware de autorização para rotas administrativas.
     * Apenas usuários com perfil de Administrador (admin) podem prosseguir.
     * Para usuários comuns (não-autenticados ou com perfil padrão), bloqueia com HTTP 403 (Forbidden)
     * e JSON amigável com a chave {"erro": "Acesso não autorizado"}.
     */
    public static function verificarAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!self::isAdmin()) {
            http_response_code(403);
            echo json_encode([
                "status" => "erro",
                "erro" => "Acesso não autorizado",
                "mensagem" => "Acesso não autorizado: A listagem de pessoas/usuários é privada e acessível apenas por administradores (admin)."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return true;
    }

    /**
     * Retorna informações básicas do usuário logado na sessão atual.
     */
    public static function obterUsuarioLogado(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId <= 0) {
            return null;
        }

        return [
            'id' => $usuarioId,
            'id_usuario' => $usuarioId,
            'nome' => $_SESSION['usuario_nome'] ?? '',
            'email' => $_SESSION['usuario_email'] ?? '',
            'tipo_usuario' => $_SESSION['usuario_tipo'] ?? 'adotante',
            'is_admin' => self::isAdmin()
        ];
    }
}
