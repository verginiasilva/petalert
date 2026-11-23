<?php

require_once __DIR__ . '/../config/database.php';

class AlertaDAO
{
    private ?PDO $conn = null;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function listarTodos(): array
    {
        $query = "SELECT id_alerta, id_usuario, titulo, descricao, tipo, data_criacao FROM tb_alerta ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $query = "SELECT id_alerta, id_usuario, titulo, descricao, tipo, data_criacao FROM tb_alerta WHERE id_alerta = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorUsuario(int $idUsuario): array
    {
        $query = "SELECT id_alerta, id_usuario, titulo, descricao, tipo, data_criacao FROM tb_alerta WHERE id_usuario = ? ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorTipo(string $tipo): array
    {
        $query = "SELECT id_alerta, id_usuario, titulo, descricao, tipo, data_criacao FROM tb_alerta WHERE tipo = ? ORDER BY data_criacao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastrar(int $idUsuario, string $titulo, string $descricao, string $tipo): bool
    {
        $query = "INSERT INTO tb_alerta (id_usuario, titulo, descricao, tipo, data_criacao) 
                  VALUES (:id_usuario, :titulo, :descricao, :tipo, :data_criacao)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id_usuario', $idUsuario);
        $stmt->bindValue(':titulo', $titulo);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->bindValue(':data_criacao', date('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    public function atualizar(int $id, string $titulo, string $descricao, string $tipo): bool
    {
        $query = "UPDATE tb_alerta SET titulo = :titulo, descricao = :descricao, tipo = :tipo WHERE id_alerta = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':titulo', $titulo);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':tipo', $tipo);

        return $stmt->execute();
    }

    public function deletar(int $id): bool
    {
        $query = "DELETE FROM tb_alerta WHERE id_alerta = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function deletarPorUsuario(int $idUsuario): bool
    {
        $query = "DELETE FROM tb_alerta WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$idUsuario]);
    }

    public function contar(): int
    {
        $query = "SELECT COUNT(*) as total FROM tb_alerta";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function contarPorUsuario(int $idUsuario): int
    {
        $query = "SELECT COUNT(*) as total FROM tb_alerta WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idUsuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>
