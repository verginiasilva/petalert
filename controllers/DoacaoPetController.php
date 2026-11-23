<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";

class DoacaoPetController {

    public function criar() {

        AuthMiddleware::verificarLogin();

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "INSERT INTO tb_doapet
        (id_usuario, id_pet, motivo_doacao, data_doacao)
        VALUES
        (:id_usuario, :id_pet, :motivo_doacao, :data_doacao)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id_usuario" => $_POST["id_usuario"],
            ":id_pet" => $_POST["id_pet"],
            ":motivo_doacao" => $_POST["motivo_doacao"],
            ":data_doacao" => $_POST["data_doacao"]
        ]);

        echo json_encode([
            "mensagem" => "Doação cadastrada com sucesso"
        ]);
    }

    public function listar() {

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "SELECT * FROM tb_doapet";

        $stmt = $pdo->query($sql);

        $doacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($doacoes);
    }

    public function buscar() {

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "SELECT * FROM tb_doapet WHERE id_doapet = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $_GET["id"]
        ]);

        $doacao = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($doacao);
    }

    public function atualizar() {

        AuthMiddleware::verificarLogin();

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "UPDATE tb_doapet SET
            id_usuario = :id_usuario,
            id_pet = :id_pet,
            motivo_doacao = :motivo_doacao,
            data_doacao = :data_doacao
        WHERE id_doapet = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id_usuario" => $_POST["id_usuario"],
            ":id_pet" => $_POST["id_pet"],
            ":motivo_doacao" => $_POST["motivo_doacao"],
            ":data_doacao" => $_POST["data_doacao"],
            ":id" => $_POST["id_doapet"]
        ]);

        echo json_encode([
            "mensagem" => "Doação atualizada"
        ]);
    }

    public function deletar() {

        AuthMiddleware::verificarLogin();

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "DELETE FROM tb_doapet WHERE id_doapet = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $_POST["id_doapet"]
        ]);

        echo json_encode([
            "mensagem" => "Doação removida"
        ]);
    }
}
?>