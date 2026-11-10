<?php

if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/Pet.php';

class PetController
{
    private Pet $petModel;

    public function __construct()
    {
        $this->petModel = new Pet();
    }

    /**
     * Listar todos os pets
     */
    public function listar(): void
    {
        try {
            $filtros = [
                'especie' => $_GET['especie'] ?? null,
                'porte' => $_GET['porte'] ?? null,
                'busca' => $_GET['busca'] ?? $_GET['q'] ?? null,
                'cidade' => $_GET['cidade'] ?? null,
                'estado' => $_GET['estado'] ?? null,
                'cep' => $_GET['cep'] ?? null
            ];

            $pets = $this->petModel->listar($filtros);
            http_response_code(200);
            echo json_encode($pets);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao listar pets: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar pet por ID
     */
    public function buscar(): void
    {
        $id = (int)($_GET['id'] ?? $_GET['id_pet'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID do pet não informado ou inválido."
            ]);
            return;
        }

        try {
            $pet = $this->petModel->buscarPorId($id);

            if ($pet) {
                http_response_code(200);
                echo json_encode($pet);
            } else {
                http_response_code(404);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Pet não encontrado."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao buscar pet: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Cadastrar novo pet para doação/adoção
     */
    public function criar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $input = json_decode(file_get_contents("php://input"), true) ?: [];
        $dados = !empty($_POST) ? $_POST : $input;

        $nome = trim((string)($dados['nome'] ?? $dados['nomePet'] ?? ''));
        $especie = trim((string)($dados['especie'] ?? ''));
        $raca = trim((string)($dados['raca'] ?? $dados['raça'] ?? ''));
        $idade = trim((string)($dados['idade'] ?? ''));
        $porte = trim((string)($dados['porte'] ?? ''));
        $peso = trim((string)($dados['peso'] ?? ''));
        $cor = trim((string)($dados['cor'] ?? ''));
        $descricao = trim((string)($dados['descricao'] ?? $dados['historia'] ?? ''));
        $motivoDoacao = trim((string)($dados['motivo_doacao'] ?? $dados['motivoDoacao'] ?? $dados['motivo'] ?? ''));

        // Validações obrigatórias
        if ($especie === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "A espécie do pet é obrigatória."]);
            return;
        }
        if ($raca === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "A raça do pet é obrigatória (informe SRD se não souber)."]);
            return;
        }
        if ($idade === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "A idade do animal (em anos ou meses) é obrigatória."]);
            return;
        }
        if ($porte === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "O porte do pet é obrigatório (Pequeno, Médio ou Grande)."]);
            return;
        }
        if ($peso === '' || (float)$peso <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "O peso aproximado em kg é obrigatório."]);
            return;
        }
        if ($cor === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "A cor do pet é obrigatória."]);
            return;
        }
        if ($descricao === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "A breve história/descrição do pet é obrigatória."]);
            return;
        }
        if ($motivoDoacao === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "O motivo da doação é obrigatório."]);
            return;
        }

        // Upload de foto
        $fotoPath = '../img/PetAlert-Logo.png';
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $extsValidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($ext, $extsValidas)) {
                $novoNome = 'pet_' . uniqid() . '.' . $ext;
                
                $dirRoot = __DIR__ . '/../uploads/pets/';
                $dirPublic = __DIR__ . '/../public/uploads/pets/';

                if (!is_dir($dirRoot)) mkdir($dirRoot, 0777, true);
                if (!is_dir($dirPublic)) mkdir($dirPublic, 0777, true);

                if (move_uploaded_file($tmpName, $dirPublic . $novoNome)) {
                    @copy($dirPublic . $novoNome, $dirRoot . $novoNome);
                    $fotoPath = '../uploads/pets/' . $novoNome;
                }
            }
        } elseif (!empty($dados['foto'])) {
            $fotoPath = $dados['foto'];
        }

        try {
            $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
            if ($idUsuario <= 0) {
                http_response_code(401);
                echo json_encode([
                    "status" => "erro",
                    "autenticacao_obrigatoria" => true,
                    "mensagem" => "Para doar um pet, você precisa criar uma conta ou entrar no PetAlert."
                ]);
                return;
            }

            $novoId = $this->petModel->cadastrar([
                'id_usuario' => $idUsuario,
                'nome' => $nome !== '' ? $nome : 'Pet sem nome',
                'especie' => ucfirst($especie),
                'raca' => $raca,
                'idade' => $idade,
                'porte' => ucfirst($porte),
                'peso' => (float)$peso,
                'cor' => $cor,
                'descricao' => $descricao,
                'motivo_doacao' => $motivoDoacao,
                'foto' => $fotoPath
            ]);

            if ($novoId) {
                http_response_code(201);
                echo json_encode([
                    "status" => "sucesso",
                    "sucesso" => true,
                    "id" => $novoId,
                    "id_pet" => $novoId,
                    "mensagem" => "Pet cadastrado para doação com sucesso!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Não foi possível salvar o pet no banco de dados."
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

    /**
     * Listar os pets do próprio usuário logado (Requisito 19)
     */
    public function meusPets(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idUsuario <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Usuário não autenticado."
            ]);
            return;
        }

        try {
            $pets = $this->petModel->meusPets($idUsuario);
            http_response_code(200);
            echo json_encode(["status" => "sucesso", "dados" => $pets]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Erro ao buscar seus pets: " . $e->getMessage()]);
        }
    }

    /**
     * Classificar pet como doado (Requisito 23)
     */
    public function classificarDoado(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $input = json_decode(file_get_contents("php://input"), true) ?: [];
        $dados = !empty($_POST) ? $_POST : $input;

        $idPet = (int)($dados['id_pet'] ?? $dados['id'] ?? $_GET['id'] ?? 0);
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);

        if ($idPet <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID do pet é obrigatório."]);
            return;
        }

        if ($idUsuario <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Apenas o doador responsável autenticado pode classificar o pet como doado."
            ]);
            return;
        }

        $sucesso = $this->petModel->classificarDoado($idPet, $idUsuario);
        if ($sucesso) {
            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "mensagem" => "Pet classificado como doado com sucesso! O animal foi retirado da lista de pets disponíveis."
            ]);
        } else {
            http_response_code(403);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Não foi possível classificar o pet como doado. Verifique se o pet pertence à sua conta."
            ]);
        }
    }

    /**
     * Listar interessados nos pets do doador autenticado (Requisito 20)
     */
    public function interessados(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idUsuario <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Usuário não autenticado."
            ]);
            return;
        }

        try {
            $interessados = $this->petModel->interessados($idUsuario);
            http_response_code(200);
            echo json_encode(["status" => "sucesso", "dados" => $interessados]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Erro ao buscar interessados: " . $e->getMessage()]);
        }
    }

    /**
     * Atualizar dados de um pet (Validando que pertence ao doador)
     */
    public function atualizar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $input = json_decode(file_get_contents("php://input"), true) ?: [];
        $dados = !empty($_POST) ? $_POST : $input;

        $id = (int)($dados['id'] ?? $dados['id_pet'] ?? $_GET['id'] ?? 0);
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);

        if ($idUsuario <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Para atualizar dados de um pet, você precisa estar autenticado."
            ]);
            return;
        }

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID do pet é obrigatório."]);
            return;
        }

        $fotoPath = null;
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $fileSize = $_FILES['foto']['size'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $extsValidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            // Limite de tamanho: 5MB
            if ($fileSize > 5 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "A imagem do pet deve ter no máximo 5MB."]);
                return;
            }

            if (!in_array($ext, $extsValidas)) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "Formato de imagem inválido. Formatos permitidos: JPG, JPEG, PNG, WEBP e GIF."]);
                return;
            }

            // Validação de imagem real
            if (@getimagesize($tmpName) === false) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "O arquivo enviado não é uma imagem válida."]);
                return;
            }

            $novoNome = 'pet_' . uniqid() . '.' . $ext;
            $dirPublic = __DIR__ . '/../public/uploads/pets/';
            $dirRoot = __DIR__ . '/../uploads/pets/';

            if (!is_dir($dirPublic)) mkdir($dirPublic, 0777, true);
            if (!is_dir($dirRoot)) mkdir($dirRoot, 0777, true);

            if (move_uploaded_file($tmpName, $dirPublic . $novoNome)) {
                @copy($dirPublic . $novoNome, $dirRoot . $novoNome);
                $fotoPath = '../uploads/pets/' . $novoNome;
            }
        }

        $dadosUpdate = $dados;
        if ($fotoPath) {
            $dadosUpdate['foto'] = $fotoPath;
            $dadosUpdate['tem_foto'] = 1;
        }

        try {
            $sucesso = $this->petModel->atualizar($id, $dadosUpdate, $idUsuario);
            if ($sucesso) {
                http_response_code(200);
                $resposta = [
                    "status" => "sucesso",
                    "mensagem" => "Pet atualizado com sucesso!"
                ];
                if ($fotoPath) {
                    $resposta['foto'] = $fotoPath;
                }
                echo json_encode($resposta);
            } else {
                http_response_code(403);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Ação não permitida: Você não tem permissão para editar este pet."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Erro: " . $e->getMessage()]);
        }
    }

    /**
     * Deletar pet (Validando que pertence ao doador)
     */
    public function deletar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $input = json_decode(file_get_contents("php://input"), true) ?: [];
        $dados = !empty($_POST) ? $_POST : $input;

        $id = (int)($dados['id'] ?? $dados['id_pet'] ?? $_GET['id'] ?? 0);
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);

        if ($idUsuario <= 0) {
            http_response_code(401);
            echo json_encode([
                "status" => "erro",
                "autenticacao_obrigatoria" => true,
                "mensagem" => "Para excluir um pet, você precisa estar autenticado."
            ]);
            return;
        }

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID do pet não informado."]);
            return;
        }

        try {
            if ($this->petModel->deletar($id, $idUsuario)) {
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Pet excluído com sucesso!"
                ]);
            } else {
                http_response_code(403);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Ação não permitida: Não foi possível excluir o pet ou você não é o tutor responsável."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Erro: " . $e->getMessage()]);
        }
    }
}