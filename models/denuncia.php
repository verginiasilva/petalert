<?php

require_once __DIR__ . '/../config/database.php';

class Denuncia
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Listar todas as denúncias
     * 
     * @return array
     */
    public function listar(): array
    {
        try {
            $sql = "SELECT 
                        d.id_denuncia,
                        d.id_denuncia AS id,
                        d.id_usuario,
                        d.id_pet,
                        COALESCE(d.motivo, d.tipo, 'Denúncia') AS motivo,
                        d.descricao,
                        d.localizacao,
                        d.foto,
                        d.video,
                        d.status,
                        d.data_criacao,
                        u.nome AS nome_denunciante,
                        u.email AS email_denunciante,
                        u.telefone AS telefone_denunciante
                    FROM tb_denuncia d
                    LEFT JOIN tb_usuario u ON d.id_usuario = u.id_usuario
                    ORDER BY d.id_denuncia DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Erro em Denuncia::listar - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar denúncia por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function buscar(int $id): ?array
    {
        try {
            $sql = "SELECT 
                        d.id_denuncia,
                        d.id_denuncia AS id,
                        d.id_usuario,
                        d.id_pet,
                        COALESCE(d.motivo, d.tipo, 'Denúncia') AS motivo,
                        d.descricao,
                        d.localizacao,
                        d.foto,
                        d.video,
                        d.status,
                        d.data_criacao,
                        u.nome AS nome_denunciante,
                        u.email AS email_denunciante,
                        u.telefone AS telefone_denunciante
                    FROM tb_denuncia d
                    LEFT JOIN tb_usuario u ON d.id_usuario = u.id_usuario
                    WHERE d.id_denuncia = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $denuncia = $stmt->fetch(PDO::FETCH_ASSOC);
            return $denuncia ?: null;
        } catch (PDOException $e) {
            error_log("Erro em Denuncia::buscar - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cadastrar nova denúncia
     * 
     * @param array $dados
     * @return int|false
     */
    public function cadastrar(array $dados)
    {
        try {
            $motivo = $dados['motivo'] ?? $dados['tipo'] ?? 'Denúncia de maus-tratos';

            $sql = "INSERT INTO tb_denuncia (
                        id_usuario,
                        id_pet,
                        motivo,
                        tipo,
                        descricao,
                        localizacao,
                        foto,
                        video,
                        status
                    ) VALUES (
                        :id_usuario,
                        :id_pet,
                        :motivo,
                        :tipo,
                        :descricao,
                        :localizacao,
                        :foto,
                        :video,
                        :status
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', !empty($dados['id_usuario']) ? (int)$dados['id_usuario'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':id_pet', !empty($dados['id_pet']) ? (int)$dados['id_pet'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':motivo', $motivo);
            $stmt->bindValue(':tipo', $motivo);
            $stmt->bindValue(':descricao', $dados['descricao'] ?? '');
            $stmt->bindValue(':localizacao', $dados['localizacao'] ?? $dados['endereco'] ?? null);
            $stmt->bindValue(':foto', $dados['foto'] ?? null);
            $stmt->bindValue(':video', $dados['video'] ?? null);
            $stmt->bindValue(':status', $dados['status'] ?? 'pendente');

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erro em Denuncia::cadastrar - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar status da denúncia
     * 
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function atualizarStatus(int $id, string $status): bool
    {
        try {
            $sql = "UPDATE tb_denuncia SET status = :status WHERE id_denuncia = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':status' => $status,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Erro em Denuncia::atualizarStatus - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletar denúncia por ID
     * 
     * @param int $id
     * @return bool
     */
    public function deletar(int $id): bool
    {
        try {
            $sql = "DELETE FROM tb_denuncia WHERE id_denuncia = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erro em Denuncia::deletar - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alias para deletar
     */
    public function excluir(int $id): bool
    {
        return $this->deletar($id);
    }
}