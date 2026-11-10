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

require_once __DIR__ . '/../models/Denuncia.php';

class DenunciaController
{
    private Denuncia $denunciaModel;

    public function __construct()
    {
        $this->denunciaModel = new Denuncia();
    }

    /**
     * Listar todas as denúncias
     */
    public function listar(): void
    {
        try {
            $denuncias = $this->denunciaModel->listar();
            http_response_code(200);
            echo json_encode([
                "status" => "sucesso",
                "dados" => $denuncias
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao listar denúncias: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar denúncia por ID
     */
    public function buscar(): void
    {
        $id = (int)($_GET['id'] ?? $_GET['id_denuncia'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID da denúncia não informado ou inválido."
            ]);
            return;
        }

        try {
            $denuncia = $this->denunciaModel->buscar($id);

            if ($denuncia) {
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "dados" => $denuncia
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Denúncia não encontrada."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao buscar denúncia: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Criar/Registrar denúncia
     * REGRA OBRIGATÓRIA: Deve conter pelo menos uma mídia (Foto OU Vídeo).
     */
    public function criar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $input = json_decode(file_get_contents("php://input"), true) ?: [];
        $dados = !empty($_POST) ? $_POST : $input;

        $motivo = trim((string)($dados['motivo'] ?? $dados['tipo'] ?? ''));
        $descricao = trim((string)($dados['descricao'] ?? ''));
        $localizacao = trim((string)($dados['localizacao'] ?? $dados['endereco'] ?? ''));
        $idPet = !empty($dados['id_pet']) ? (int)$dados['id_pet'] : null;
        $idUsuario = !empty($dados['id_usuario']) ? (int)$dados['id_usuario'] : ($_SESSION['usuario_id'] ?? null);

        // Validação de texto básico
        if ($motivo === '' || $descricao === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Preencha o motivo/tipo e a descrição detalhada da denúncia."
            ]);
            return;
        }

        $fotoPath = null;
        $videoPath = null;

        // Processar upload de foto
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $extsValidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($ext, $extsValidas)) {
                $novoNome = 'denuncia_foto_' . uniqid() . '.' . $ext;
                $dirRoot = __DIR__ . '/../uploads/denuncias/';
                $dirPublic = __DIR__ . '/../public/uploads/denuncias/';

                if (!is_dir($dirRoot)) mkdir($dirRoot, 0777, true);
                if (!is_dir($dirPublic)) mkdir($dirPublic, 0777, true);

                if (move_uploaded_file($tmpName, $dirPublic . $novoNome)) {
                    @copy($dirPublic . $novoNome, $dirRoot . $novoNome);
                    $fotoPath = '../uploads/denuncias/' . $novoNome;
                }
            }
        } elseif (!empty($dados['foto'])) {
            $fotoPath = $dados['foto'];
        }

        // Processar upload de vídeo
        if (!empty($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['video']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
            $extsValidas = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'];

            if (in_array($ext, $extsValidas)) {
                $novoNome = 'denuncia_video_' . uniqid() . '.' . $ext;
                $dirRoot = __DIR__ . '/../uploads/denuncias/';
                $dirPublic = __DIR__ . '/../public/uploads/denuncias/';

                if (!is_dir($dirRoot)) mkdir($dirRoot, 0777, true);
                if (!is_dir($dirPublic)) mkdir($dirPublic, 0777, true);

                if (move_uploaded_file($tmpName, $dirPublic . $novoNome)) {
                    @copy($dirPublic . $novoNome, $dirRoot . $novoNome);
                    $videoPath = '../uploads/denuncias/' . $novoNome;
                }
            }
        } elseif (!empty($dados['video'])) {
            $videoPath = $dados['video'];
        }

        // VALIDAÇÃO DE MÍDIA: Deve possuir pelo menos FOTO ou VÍDEO
        if (!$fotoPath && !$videoPath) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "É obrigatório anexar pelo menos uma mídia comprobatória: FOTO ou VÍDEO do animal."
            ]);
            return;
        }

        try {
            $novoId = $this->denunciaModel->cadastrar([
                'id_usuario' => $idUsuario,
                'id_pet' => $idPet,
                'motivo' => $motivo,
                'tipo' => $motivo,
                'descricao' => $descricao,
                'localizacao' => $localizacao,
                'foto' => $fotoPath,
                'video' => $videoPath,
                'status' => 'pendente'
            ]);

            if ($novoId) {
                http_response_code(201);
                echo json_encode([
                    "status" => "sucesso",
                    "sucesso" => true,
                    "id" => $novoId,
                    "mensagem" => "Denúncia registrada com sucesso! Nossa equipe e protetores parceiros irão averiguar."
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao salvar a denúncia no banco de dados."
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro no servidor: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Atualizar status da denúncia
     */
    public function atualizarStatus(): void
    {
        $input = json_decode(file_get_contents("php://input"), true) ?: [];
        $dados = !empty($_POST) ? $_POST : $input;

        $id = (int)($dados['id'] ?? $dados['id_denuncia'] ?? $_GET['id'] ?? 0);
        $status = trim((string)($dados['status'] ?? ''));

        if ($id <= 0 || $status === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID da denúncia e novo status são obrigatórios."
            ]);
            return;
        }

        try {
            if ($this->denunciaModel->atualizarStatus($id, $status)) {
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Status da denúncia atualizado com sucesso!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao atualizar status da denúncia."
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
     * Alias para atualizar
     */
    public function atualizar(): void
    {
        $this->atualizarStatus();
    }

    /**
     * Deletar denúncia
     */
    public function deletar(): void
    {
        $input = json_decode(file_get_contents("php://input"), true) ?: [];
        $dados = !empty($_POST) ? $_POST : $input;

        $id = (int)($dados['id'] ?? $dados['id_denuncia'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "erro",
                "mensagem" => "ID da denúncia não informado."
            ]);
            return;
        }

        try {
            if ($this->denunciaModel->deletar($id)) {
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Denúncia excluída com sucesso!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao excluir denúncia do banco."
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
}