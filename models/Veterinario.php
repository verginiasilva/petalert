<?php

require_once __DIR__ . "/../config/database.php";

class Veterinario {

    public ?int $id_veterinario = null;
    public ?string $nome = null;
    public ?string $crmv = null;
    public ?string $telefone = null;
    public ?string $especialidade = null;
    public ?string $endereco = null;

    public function all(): array {
        $db = new Database();
        $pdo = $db->getConnection();

        $stmt = $pdo->query("SELECT * FROM tb_veterinario");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): array|false {
        $db = new Database();
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM tb_veterinario WHERE id_veterinario = :id");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrar(string $nome, string $telefone, string $endereco): bool {
        $db = new Database();
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("INSERT INTO tb_veterinario (nome, telefone, endereco) VALUES (:nome, :telefone, :endereco)");
        return $stmt->execute([
            ':nome' => $nome,
            ':telefone' => $telefone,
            ':endereco' => $endereco
        ]);
    }

    public function atualizar(int $id, string $nome, string $telefone, string $endereco): bool {
        $db = new Database();
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("UPDATE tb_veterinario SET nome = :nome, telefone = :telefone, endereco = :endereco WHERE id_veterinario = :id");
        return $stmt->execute([
            ':nome' => $nome,
            ':telefone' => $telefone,
            ':endereco' => $endereco,
            ':id' => $id
        ]);
    }

    public function deletar(int $id): bool {
        $db = new Database();
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("DELETE FROM tb_veterinario WHERE id_veterinario = :id");
        return $stmt->execute([':id' => $id]);
    }

}