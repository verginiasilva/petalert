<?php

if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../DAO/chatDAO.php';
require_once __DIR__ . '/../models/Chat.php';

class ChatController
{
    private ChatDAO $chatDAO;

    public function __construct()
    {
        $this->chatDAO = new ChatDAO();
    }

    public function listar(): void
    {
        try {
            $chats = $this->chatDAO->listarTodas();
            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "dados" => $chats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao listar chats: " . $e->getMessage()
            ]);
        }
    }

    public function buscar(): void
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID do chat não informado!"
            ]);
            return;
        }

        try {
            $chat = $this->chatDAO->buscarPorId($id);

            if ($chat) {
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "dados" => $chat
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Chat não encontrado!"
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao buscar chat: " . $e->getMessage()
            ]);
        }
    }

    public function buscarPorUsuario(): void
    {
        $idUsuario = $_GET['id_usuario'] ?? null;

        if (!$idUsuario) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID do usuário não informado!"
            ]);
            return;
        }

        try {
            $chats = $this->chatDAO->buscarPorUsuario((int) $idUsuario);
            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "dados" => $chats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao buscar conversa: " . $e->getMessage()
            ]);
        }
    }

    public function buscarPorPet(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idUsuario <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Para conversar com o doador, você precisa entrar na sua conta ou criar uma conta no PetAlert.",
                "autenticacao_obrigatoria" => true
            ]);
            return;
        }

        $idPet = $_GET['id_pet'] ?? null;

        if (!$idPet || !filter_var($idPet, FILTER_VALIDATE_INT)) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID do pet inválido!"
            ]);
            return;
        }

        try {
            $idPet = (int) $idPet;
            if (!$this->chatDAO->buscarPetExiste($idPet)) {
                http_response_code(404);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Pet não encontrado."
                ]);
                return;
            }

            $tipoConversa = $_GET['tipo_conversa'] ?? $_GET['contexto'] ?? null;

            $chats = $this->chatDAO->buscarPorPet($idPet, $tipoConversa, $idUsuario);
            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "dados" => $chats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao buscar histórico do pet: " . $e->getMessage()
            ]);
        }
    }

    public function buscarConversasPorUsuario(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);

        if ($idUsuario <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Para conversar com o doador, você precisa entrar na sua conta ou criar uma conta no PetAlert.",
                "autenticacao_obrigatoria" => true
            ]);
            return;
        }

        try {
            $conversas = $this->chatDAO->buscarConversasPorUsuario($idUsuario);
            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "dados" => $conversas
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao buscar conversas do usuário: " . $e->getMessage()
            ]);
        }
    }

    public function buscarContextoPet(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idUsuario <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Para conversar com o doador, você precisa entrar na sua conta ou criar uma conta no PetAlert.",
                "autenticacao_obrigatoria" => true
            ]);
            return;
        }

        $idPet = $_GET['id_pet'] ?? null;

        if (!$idPet || !filter_var($idPet, FILTER_VALIDATE_INT)) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID do pet inválido!"
            ]);
            return;
        }

        try {
            $idPet = (int) $idPet;
            $pet = (new Database())->getConnection()->prepare('SELECT id_pet, nome, especie, raca, idade, peso, cor, porte, descricao FROM tb_animal WHERE id_pet = :id_pet LIMIT 1');
            $pet->execute([':id_pet' => $idPet]);
            $petDados = $pet->fetch(PDO::FETCH_ASSOC);

            if (!$petDados) {
                http_response_code(404);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Pet não encontrado."
                ]);
                return;
            }

            $doador = $this->chatDAO->buscarDoadorPorPet($idPet);

            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "pet" => $petDados,
                "doador" => $doador
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao buscar contexto do pet: " . $e->getMessage()
            ]);
        }
    }

    public function criar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $dados = json_decode(file_get_contents("php://input"), true);
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);

        if ($idUsuario <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Para conversar com o doador, você precisa entrar na sua conta ou criar uma conta no PetAlert.",
                "autenticacao_obrigatoria" => true
            ]);
            return;
        }

        if (
            !isset($dados['mensagem']) || empty(trim((string) $dados['mensagem'])) ||
            !isset($dados['id_pet']) || empty($dados['id_pet'])
        ) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID do pet e mensagem são obrigatórios!"
            ]);
            return;
        }

        try {
            $idPet = (int) $dados['id_pet'];
            $mensagem = trim((string) $dados['mensagem']);

            if (!$this->chatDAO->buscarUsuarioExiste($idUsuario)) {
                http_response_code(404);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Usuário não encontrado."
                ]);
                return;
            }

            if (!$this->chatDAO->buscarPetExiste($idPet)) {
                http_response_code(404);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Pet não encontrado."
                ]);
                return;
            }

            $chat = new Chat();
            $chat->setIdUsuario($idUsuario);
            $chat->setIdPet($idPet);
            if (isset($dados['id_usuario_origem']) && $dados['id_usuario_origem'] !== '') {
                $chat->setIdUsuarioOrigem((int)$dados['id_usuario_origem']);
            }
            if (isset($dados['id_usuario_destino']) && $dados['id_usuario_destino'] !== '') {
                $chat->setIdUsuarioDestino((int)$dados['id_usuario_destino']);
            }
            $chat->setMensagem($mensagem);
            $tipoConversa = trim((string)($dados['tipo_conversa'] ?? $dados['contexto'] ?? 'doador'));
            $chat->setTipoConversa($tipoConversa);

            if ($this->chatDAO->cadastrar($chat)) {
                http_response_code(201);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Mensagem enviada com sucesso!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao enviar mensagem!"
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro: " . $e->getMessage()
            ]);
        }
    }

    public function atualizar(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true);

        if (
            !isset($dados['id']) || empty($dados['id']) ||
            !isset($dados['mensagem']) || empty($dados['mensagem'])
        ) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID e mensagem são obrigatórios!"
            ]);
            return;
        }

        try {
            if ($this->chatDAO->atualizar($dados['id'], $dados['mensagem'])) {
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Mensagem atualizada com sucesso!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao atualizar mensagem!"
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro: " . $e->getMessage()
            ]);
        }
    }

    public function deletar(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true);

        if (!isset($dados['id']) || empty($dados['id'])) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID do chat não informado!"
            ]);
            return;
        }

        try {
            if ($this->chatDAO->deletar($dados['id'])) {
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Mensagem deletada com sucesso!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao deletar mensagem!"
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro: " . $e->getMessage()
            ]);
        }
    }
}
?>
