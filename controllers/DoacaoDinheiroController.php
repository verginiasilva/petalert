<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";

class DoacaoDinheiroController {

    public function criar() {

        AuthMiddleware::verificarLogin();

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "INSERT INTO tb_doacao
        (data_doacao, valor_doado, objeto_doado)
        VALUES
        (:data_doacao, :valor_doado, :objeto_doado)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":data_doacao" => $_POST["data_doacao"],
            ":valor_doado" => $_POST["valor_doado"],
            ":objeto_doado" => $_POST["objeto_doado"]
        ]);

        echo json_encode([
            "mensagem" => "Doação cadastrada com sucesso"
        ]);
    }

    public function listar() {

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "SELECT * FROM tb_doacao";

        $stmt = $pdo->query($sql);

        $doacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($doacoes);
    }

    public function buscar() {

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "SELECT * FROM tb_doacao WHERE id_doacao = :id";

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

        $sql = "UPDATE tb_doacao SET
            data_doacao = :data_doacao,
            valor_doado = :valor_doado,
            objeto_doado = :objeto_doado
        WHERE id_doacao = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":data_doacao" => $_POST["data_doacao"],
            ":valor_doado" => $_POST["valor_doado"],
            ":objeto_doado" => $_POST["objeto_doado"],
            ":id" => $_POST["id_doacao"]
        ]);

        echo json_encode([
            "mensagem" => "Doação atualizada"
        ]);
    }

    public function deletar() {

        AuthMiddleware::verificarLogin();

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "DELETE FROM tb_doacao WHERE id_doacao = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $_POST["id_doacao"]
        ]);

        echo json_encode([
            "mensagem" => "Doação removida"
        ]);
    }
}
?>

