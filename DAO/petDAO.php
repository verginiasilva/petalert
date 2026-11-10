<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/pet.php';

class PetDAO
{
    private ?PDO $conn = null;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Listar todos os pets
     *
     * @return array
     */
    public function listarTodos(): array
    {
        $query = "SELECT id_pet, nome, idade, raca, peso, cor, porte, especie, tem_foto, descricao FROM tb_animal ORDER BY id_pet DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar pet por ID
     *
     * @param int $id
     * @return array|false
     */
    public function buscarPorId(int $id): array|false
    {
        $query = "SELECT id_pet, nome, idade, raca, peso, cor, porte, especie, tem_foto, descricao FROM tb_animal WHERE id_pet = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cadastrar novo pet
     *
     * @param array $dados
     * @param string $fotoNome
     * @return bool
     */
    public function cadastrar(array $dados, string $fotoNome = '../img/PetAlert-Logo.png'): bool
    {
        $temFoto = !empty($dados['tem_foto']) ? (int) $dados['tem_foto'] : 0;

        $query = "INSERT INTO tb_animal (nome, idade, raca, peso, cor, porte, especie, tem_foto, descricao)
                  VALUES (:nome, :idade, :raca, :peso, :cor, :porte, :especie, :tem_foto, :descricao)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome', $dados['nome'] ?? null);
        $stmt->bindValue(':idade', $dados['idade'] ?? null);
        $stmt->bindValue(':raca', $dados['raca'] ?? $dados['raça'] ?? null);
        $stmt->bindValue(':peso', $dados['peso'] ?? 0.00);
        $stmt->bindValue(':cor', $dados['cor'] ?? 'Não informada');
        $stmt->bindValue(':porte', $dados['porte'] ?? null);
        $stmt->bindValue(':especie', $dados['especie'] ?? null);
        $stmt->bindValue(':tem_foto', $temFoto, PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $dados['descricao'] ?? 'Animal disponível para adoção.');

        $ok = $stmt->execute();
        return $ok ? ((int)$this->conn->lastInsertId() ?: true) : false;
    }

    /**
     * Atualizar pet
     *
     * @param int $id
     * @param array $dados
     * @return bool
     */
    public function atualizar(int $id, array $dados): bool
    {
        $query = "UPDATE tb_animal SET 
                  nome = :nome, 
                  idade = :idade, 
                  raca = :raca, 
                  peso = :peso, 
                  cor = :cor, 
                  porte = :porte, 
                  especie = :especie";
        
        if (isset($dados['foto'])) {
            $query .= ", foto = :foto";
        }
        if (array_key_exists('tem_foto', $dados)) {
            $query .= ", tem_foto = :tem_foto";
        }
        if (isset($dados['descricao'])) {
            $query .= ", descricao = :descricao";
        }
        
        $query .= " WHERE id_pet = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':nome', $dados['nome'] ?? null);
        $stmt->bindValue(':idade', $dados['idade'] ?? null);
        $stmt->bindValue(':raca', $dados['raca'] ?? $dados['raça'] ?? null);
        $stmt->bindValue(':peso', $dados['peso'] ?? 0.00);
        $stmt->bindValue(':cor', $dados['cor'] ?? 'Não informada');
        $stmt->bindValue(':porte', $dados['porte'] ?? null);
        $stmt->bindValue(':especie', $dados['especie'] ?? null);
        
        if (isset($dados['foto'])) {
            $stmt->bindValue(':foto', $dados['foto']);
        }
        if (array_key_exists('tem_foto', $dados)) {
            $stmt->bindValue(':tem_foto', (int) $dados['tem_foto'], PDO::PARAM_INT);
        }
        if (isset($dados['descricao'])) {
            $stmt->bindValue(':descricao', $dados['descricao']);
        }

        return $stmt->execute();
    }

    /**
     * Deletar pet
     *
     * @param int $id
     * @return bool
     */
    public function deletar(int $id): bool
    {
        $query = "DELETE FROM tb_animal WHERE id_pet = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Buscar pets por espécie
     *
     * @param string $especie
     * @return array
     */
    public function buscarPorEspecie(string $especie): array
    {
        $query = "SELECT id_pet, nome, idade, raca, peso, cor, porte, especie, tem_foto, descricao FROM tb_animal WHERE especie = ? ORDER BY nome";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$especie]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>