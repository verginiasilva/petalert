<?php

require_once __DIR__ . '/../config/database.php';

class DenunciaDAO
{
    private ?PDO $conn = null;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function listarTodas(): array
    {
        $query = "SELECT id_denuncia, id_usuario, id_pet, motivo, descricao, status, data_criacao FROM tb_denuncia ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $query = "SELECT id_denuncia, id_usuario, id_pet, motivo, descricao, status, data_criacao FROM tb_denuncia WHERE id_denuncia = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorUsuario(int $idUsuario): array
    {
        $query = "SELECT id_denuncia, id_usuario, id_pet, motivo, descricao, status, data_criacao FROM tb_denuncia WHERE id_usuario = ? ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorStatus(string $status): array
    {
        $query = "SELECT id_denuncia, id_usuario, id_pet, motivo, descricao, status, data_criacao FROM tb_denuncia WHERE status = ? ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastrar(?int $idUsuario, ?int $idPet, string $motivo, string $descricao): bool
    {
        $query = "INSERT INTO tb_denuncia (id_usuario, id_pet, motivo, descricao, status, data_criacao) 
                  VALUES (:id_usuario, :id_pet, :motivo, :descricao, :status, :data_criacao)";
        
        $stmt = $this->conn->prepare($query);
        if ($idUsuario === null) {
            $stmt->bindValue(':id_usuario', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        }

        if ($idPet === null) {
            $stmt->bindValue(':id_pet', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':id_pet', $idPet, PDO::PARAM_INT);
        }
        $stmt->bindValue(':motivo', $motivo);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':status', 'pendente');
        $stmt->bindValue(':data_criacao', date('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    public function atualizar(int $id, string $status): bool
    {
        $query = "UPDATE tb_denuncia SET status = :status WHERE id_denuncia = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':status', $status);

        return $stmt->execute();
    }

    public function deletar(int $id): bool
    {
        $query = "DELETE FROM tb_denuncia WHERE id_denuncia = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function contar(): int
    {
        $query = "SELECT COUNT(*) as total FROM tb_denuncia";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function contarPorStatus(string $status): int
    {
        $query = "SELECT COUNT(*) as total FROM tb_denuncia WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$status]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>
