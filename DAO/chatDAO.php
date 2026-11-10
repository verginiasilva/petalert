<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Chat.php';

class ChatDAO
{
    private ?PDO $conn = null;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function listarTodas(): array
    {
        $query = "SELECT id_chat, id_usuario, id_pet, id_usuario_origem, id_usuario_destino, mensagem, data_criacao, data_mensagem, lida FROM tb_chat ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $query = "SELECT id_chat, id_usuario, id_pet, id_usuario_origem, id_usuario_destino, mensagem, data_criacao, data_mensagem, lida FROM tb_chat WHERE id_chat = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorUsuario(int $idUsuario): array
    {
        $query = "SELECT id_chat, id_usuario, id_pet, id_usuario_origem, id_usuario_destino, mensagem, data_criacao, data_mensagem, lida 
                  FROM tb_chat 
                  WHERE id_usuario = ? OR id_usuario_origem = ? OR id_usuario_destino = ? 
                  ORDER BY data_criacao ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idUsuario, $idUsuario, $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorPet(int $idPet, ?string $tipoConversa = null, ?int $idUsuario = null): array
    {
        $query = "SELECT id_chat, id_usuario, id_pet, id_usuario_origem, id_usuario_destino, mensagem, data_criacao, data_mensagem, lida, tipo_conversa 
                  FROM tb_chat 
                  WHERE id_pet = :id_pet";
        $params = [':id_pet' => $idPet];

        if ($tipoConversa) {
            $query .= " AND tipo_conversa = :tipo_conversa";
            $params[':tipo_conversa'] = $tipoConversa;
        }

        if ($idUsuario) {
            $query .= " AND (id_usuario = :user1 OR id_usuario_origem = :user2 OR id_usuario_destino = :user3)";
            $params[':user1'] = $idUsuario;
            $params[':user2'] = $idUsuario;
            $params[':user3'] = $idUsuario;
        }

        $query .= " ORDER BY data_criacao ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarConversasPorUsuario(int $idUsuario): array
    {
        $query = "
            SELECT latest.id_pet,
                   p.nome AS nome_pet,
                   COALESCE(p.id_usuario, d.id_usuario) AS id_doador,
                   COALESCE(u.nome, 'Tutor do pet') AS nome_doador,
                   c.mensagem AS ultima_mensagem,
                   c.data_criacao AS ultima_data,
                   c.id_chat AS ultimo_chat_id,
                   c.tipo_conversa
            FROM (
                SELECT MAX(id_chat) AS ultimo_id, id_pet
                FROM tb_chat
                WHERE id_pet IS NOT NULL
                  AND (id_usuario = :id_usuario OR id_usuario_origem = :id_usuario OR id_usuario_destino = :id_usuario)
                GROUP BY id_pet
            ) latest
            JOIN tb_chat c ON c.id_chat = latest.ultimo_id
            LEFT JOIN tb_animal p ON p.id_pet = latest.id_pet
            LEFT JOIN tb_doapet d ON d.id_pet = latest.id_pet
            LEFT JOIN tb_usuario u ON u.id_usuario = COALESCE(p.id_usuario, d.id_usuario)
            ORDER BY c.data_criacao DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPetExiste(int $idPet): bool
    {
        $query = 'SELECT 1 FROM tb_animal WHERE id_pet = :id_pet LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_pet' => $idPet]);
        return (bool) $stmt->fetchColumn();
    }

    public function buscarUsuarioExiste(int $idUsuario): bool
    {
        $query = 'SELECT 1 FROM tb_usuario WHERE id_usuario = :id_usuario LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_usuario' => $idUsuario]);
        return (bool) $stmt->fetchColumn();
    }

    public function buscarDoadorPorPet(int $idPet): array|false
    {
        $query = 'SELECT a.id_pet, a.nome AS nome_pet, a.especie, COALESCE(a.raca, a.raça, \'SRD\') AS raca,
                         a.idade, a.peso, a.porte, a.cor, a.descricao, a.motivo_doacao, a.foto,
                         COALESCE(a.id_usuario, d.id_usuario) AS id_usuario,
                         COALESCE(u.nome, \'Tutor(a) do pet\') AS nome,
                         u.email, u.telefone, u.cidade, u.estado 
                  FROM tb_animal a 
                  LEFT JOIN tb_doapet d ON d.id_pet = a.id_pet
                  LEFT JOIN tb_usuario u ON u.id_usuario = COALESCE(a.id_usuario, d.id_usuario) 
                  WHERE a.id_pet = :id_pet LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_pet' => $idPet]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function cadastrar(Chat $chat): bool
    {
        $query = "INSERT INTO tb_chat (id_usuario, id_pet, id_usuario_origem, id_usuario_destino, mensagem, data_criacao, data_mensagem, tipo_conversa) 
                  VALUES (:id_usuario, :id_pet, :id_usuario_origem, :id_usuario_destino, :mensagem, :data_criacao, :data_mensagem, :tipo_conversa)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id_usuario', $chat->getIdUsuario(), $chat->getIdUsuario() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id_pet', $chat->getIdPet(), $chat->getIdPet() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario_origem', $chat->getIdUsuarioOrigem(), $chat->getIdUsuarioOrigem() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario_destino', $chat->getIdUsuarioDestino(), $chat->getIdUsuarioDestino() === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':mensagem', $chat->getMensagem());
        $dataAgora = date('Y-m-d H:i:s');
        $stmt->bindValue(':data_criacao', $dataAgora);
        $stmt->bindValue(':data_mensagem', $dataAgora);
        $stmt->bindValue(':tipo_conversa', $chat->getTipoConversa() ?: 'doador');

        return $stmt->execute();
    }

    public function atualizar(int $id, string $mensagem): bool
    {
        $query = "UPDATE tb_chat SET mensagem = :mensagem WHERE id_chat = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':mensagem', $mensagem);

        return $stmt->execute();
    }

    public function deletar(int $id): bool
    {
        $query = "DELETE FROM tb_chat WHERE id_chat = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function deletarPorUsuario(int $idUsuario): bool
    {
        $query = "DELETE FROM tb_chat WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$idUsuario]);
    }

    public function contar(): int
    {
        $query = "SELECT COUNT(*) as total FROM tb_chat";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>
