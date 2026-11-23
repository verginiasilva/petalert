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

require_once __DIR__ . '/../models/pet.php';

class PetController
{
    private Pet $petModel;

    public function __construct()
    {
        $this->petModel = new Pet();
    }

    /**
     * Helper para processar upload de até 5 fotos
     */
    private function processarUploadFotos(int $limite = 5): array
    {
        $fotosSalvas = [];
        $extsValidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $dirPublic = __DIR__ . '/../public/uploads/pets/';
        $dirRoot = __DIR__ . '/../uploads/pets/';

        if (!is_dir($dirPublic)) @mkdir($dirPublic, 0777, true);
        if (!is_dir($dirRoot)) @mkdir($dirRoot, 0777, true);

        // Caso 1: $_FILES['fotos'] (múltiplas fotos)
        if (!empty($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
            $totalArquivos = count($_FILES['fotos']['name']);
            for ($i = 0; $i < $totalArquivos && count($fotosSalvas) < $limite; $i++) {
                if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['fotos']['tmp_name'][$i];
                    $nomeOriginal = $_FILES['fotos']['name'][$i];
                    $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

                    if (in_array($ext, $extsValidas) && @getimagesize($tmpName) !== false) {
                        $novoNome = 'pet_' . uniqid() . '_' . $i . '.' . $ext;
                        if (move_uploaded_file($tmpName, $dirPublic . $novoNome)) {
                            @copy($dirPublic . $novoNome, $dirRoot . $novoNome);
                            $fotosSalvas[] = '../uploads/pets/' . $novoNome;
                        }
                    }
                }
            }
        }

        // Caso 2: $_FILES['foto'] (foto única ou foto principal de fallback)
        if (count($fotosSalvas) < $limite && !empty($_FILES['foto']) && is_string($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $extsValidas) && @getimagesize($tmpName) !== false) {
                $novoNome = 'pet_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($tmpName, $dirPublic . $novoNome)) {
                    @copy($dirPublic . $novoNome, $dirRoot . $novoNome);
                    $fotosSalvas[] = '../uploads/pets/' . $novoNome;
                }
            }
        }

        return $fotosSalvas;
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
     * Buscar pet por ID completo com biografia, fotos e doador
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
     * Cadastrar novo pet para doação/adoção com suporte a até 5 fotos
     */
    public function criar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
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
        $sexo = trim((string)($dados['sexo'] ?? 'Macho'));
        $personalidade = trim((string)($dados['personalidade'] ?? ''));
        $descricao = trim((string)($dados['descricao'] ?? $dados['historia'] ?? ''));
        $motivoDoacao = trim((string)($dados['motivo_doacao'] ?? $dados['motivoDoacao'] ?? $dados['motivo'] ?? ''));

        // Validações obrigatórias
        if ($nome === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "O nome do pet é obrigatório."]);
            return;
        }
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
            echo json_encode(["status" => "erro", "mensagem" => "A idade do animal (em anos) é obrigatória."]);
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
            echo json_encode(["status" => "erro", "mensagem" => "A biografia e história do pet é obrigatória."]);
            return;
        }
        if ($motivoDoacao === '') {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "O motivo da doação é obrigatório."]);
            return;
        }

        // Processa upload de até 5 fotos
        $fotos = $this->processarUploadFotos(5);

        // Fallback caso fotos tenham sido enviadas via URL ou texto
        if (empty($fotos)) {
            if (!empty($dados['fotos']) && is_array($dados['fotos'])) {
                $fotos = array_slice($dados['fotos'], 0, 5);
            } elseif (!empty($dados['foto'])) {
                $fotos = [$dados['foto']];
            }
        }

        $fotoPrincipal = !empty($fotos[0]) ? $fotos[0] : '../img/PetAlert-Logo.png';

        try {
            $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
            if ($idUsuario <= 0) {
                $idPost = (int)($dados['id_usuario'] ?? $dados['id'] ?? 0);
                if ($idPost > 0) {
                    $idUsuario = $idPost;
                }
            }

            if ($idUsuario <= 0) {
                http_response_code(401);
                echo json_encode([
                    "status" => "erro",
                    "autenticacao_obrigatoria" => true,
                    "mensagem" => "Para doar um pet, você precisa criar uma conta ou entrar no PetAlert."
                ]);
                return;
            }

            $_SESSION['usuario_id'] = $idUsuario;

            $novoId = $this->petModel->cadastrar([
                'id_usuario' => $idUsuario,
                'nome' => $nome,
                'especie' => ucfirst($especie),
                'raca' => $raca,
                'idade' => (float)$idade,
                'porte' => ucfirst($porte),
                'peso' => (float)$peso,
                'cor' => $cor,
                'sexo' => $sexo !== '' ? $sexo : 'Macho',
                'personalidade' => $personalidade !== '' ? $personalidade : 'Dócil, carinhoso e companheiro',
                'descricao' => $descricao,
                'motivo_doacao' => $motivoDoacao,
                'foto' => $fotoPrincipal,
                'fotos' => $fotos
            ]);

            if ($novoId) {
                http_response_code(201);
                echo json_encode([
                    "status" => "sucesso",
                    "sucesso" => true,
                    "id" => $novoId,
                    "id_pet" => $novoId,
                    "total_fotos" => count($fotos),
                    "mensagem" => "Pet cadastrado para doação com sucesso!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Não foi possível salvar o pet no banco de dados. Verifique os dados e tente novamente."
                ]);
            }
        } catch (Exception $e) {
            error_log("PetController::criar - Exceção: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao processar cadastro: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Listar os pets do próprio usuário logado
     */
    public function meusPets(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idUsuario <= 0) {
            $idGet = (int)($_GET['id_usuario'] ?? 0);
            if ($idGet > 0) $idUsuario = $idGet;
        }

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
            echo json_encode($pets);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao buscar seus pets: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Classificar pet como doado
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

        $sucesso = $this->petModel->classificarDoado($idPet, $idUsuario > 0 ? $idUsuario : null);
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
                "mensagem" => "Não foi possível classificar o pet como doado."
            ]);
        }
    }

    /**
     * Listar interessados nos pets do doador autenticado
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
     * Atualizar dados de um pet (com suporte a até 5 fotos)
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

        // Processa fotos existentes mantidas
        $fotosManter = [];
        if (!empty($dados['fotos_existentes'])) {
            if (is_string($dados['fotos_existentes'])) {
                $dec = json_decode($dados['fotos_existentes'], true);
                $fotosManter = is_array($dec) ? $dec : [$dados['fotos_existentes']];
            } elseif (is_array($dados['fotos_existentes'])) {
                $fotosManter = $dados['fotos_existentes'];
            }
        }

        // Processa novas fotos enviadas (com limite máximo de 5 no total)
        $limiteNovas = max(0, 5 - count($fotosManter));
        $novasFotos = $limiteNovas > 0 ? $this->processarUploadFotos($limiteNovas) : [];

        $todasFotos = array_slice(array_merge($fotosManter, $novasFotos), 0, 5);

        $dadosUpdate = $dados;
        if (!empty($todasFotos)) {
            $dadosUpdate['fotos'] = $todasFotos;
            $dadosUpdate['foto'] = $todasFotos[0];
            $dadosUpdate['tem_foto'] = 1;
        }

        try {
            $sucesso = $this->petModel->atualizar($id, $dadosUpdate, $idUsuario);

            if ($sucesso) {
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "sucesso" => true,
                    "total_fotos" => count($todasFotos),
                    "mensagem" => "Informações do pet atualizadas com sucesso!"
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Não foi possível atualizar o pet. Verifique os dados ou permissões."
                ]);
            }
        } catch (Exception $e) {
            error_log("PetController::atualizar - Erro: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro interno ao atualizar: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Deletar pet
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
                "mensagem" => "Usuário não autenticado."
            ]);
            return;
        }

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID do pet é obrigatório."]);
            return;
        }

        try {
            $sucesso = $this->petModel->deletar($id, $idUsuario);
            if ($sucesso) {
                http_response_code(200);
                echo json_encode(["status" => "sucesso", "mensagem" => "Pet excluído com sucesso."]);
            } else {
                http_response_code(403);
                echo json_encode(["status" => "erro", "mensagem" => "Não foi possível excluir o pet. Verifique se o pet pertence à sua conta."]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => "Erro ao excluir pet: " . $e->getMessage()]);
        }
    }
}
