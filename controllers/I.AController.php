<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";

class IAController {

    public function criar() {

        AuthMiddleware::verificarLogin();

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "INSERT INTO tb_ia
        (pergunta, resposta, categoria)
        VALUES
        (:pergunta, :resposta, :categoria)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":pergunta" => $_POST["pergunta"],
            ":resposta" => $_POST["resposta"],
            ":categoria" => $_POST["categoria"]
        ]);

        echo json_encode([
            "mensagem" => "Registro criado com sucesso"
        ]);
    }

    public function listar() {

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "SELECT * FROM tb_ia";

        $stmt = $pdo->query($sql);

        $ias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($ias);
    }

    public function buscar() {

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "SELECT * FROM tb_ia WHERE id_ia = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $_GET["id"]
        ]);

        $ia = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($ia);
    }

    public function atualizar() {

        AuthMiddleware::verificarLogin();

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "UPDATE tb_ia SET
            pergunta = :pergunta,
            resposta = :resposta,
            categoria = :categoria
        WHERE id_ia = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":pergunta" => $_POST["pergunta"],
            ":resposta" => $_POST["resposta"],
            ":categoria" => $_POST["categoria"],
            ":id" => $_POST["id_ia"]
        ]);

        echo json_encode([
            "mensagem" => "Registro atualizado"
        ]);
    }

    public function deletar() {

        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "DELETE FROM tb_ia WHERE id_ia = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $_POST["id_ia"]
        ]);

        echo json_encode([
            "mensagem" => "Registro removido"
        ]);
    }
}
?>