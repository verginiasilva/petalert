<?php

require_once __DIR__ . '/../config/database.php';

class Pet
{
    private PDO $db;

    private static bool $estruturaGarantida = false;

    public function __construct()
    {
        $this->db = Database::getConnection();
        if (!self::$estruturaGarantida) {
            $this->garantirEstruturaFotos();
            self::$estruturaGarantida = true;
        }
    }

    /**
     * Garante automaticamente a criação da tabela de fotos e novas colunas
     */
    private function garantirEstruturaFotos(): void
    {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS tb_pet_fotos (
                id_foto INT AUTO_INCREMENT PRIMARY KEY,
                id_pet INT NOT NULL,
                caminho_foto VARCHAR(255) NOT NULL,
                ordem INT DEFAULT 0,
                data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_pet (id_pet)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Tenta adicionar colunas caso não existam no banco conectado
            try {
                $this->db->exec("ALTER TABLE tb_animal ADD COLUMN IF NOT EXISTS sexo VARCHAR(10) DEFAULT 'Macho'");
            } catch (Exception $e) {}

            try {
                $this->db->exec("ALTER TABLE tb_animal ADD COLUMN IF NOT EXISTS personalidade VARCHAR(255) DEFAULT 'Dócil, carinhoso e companheiro'");
            } catch (Exception $e) {}
        } catch (Exception $e) {
            error_log("Aviso: Falha ao garantir estrutura de fotos - " . $e->getMessage());
        }
    }

    /**
     * Listar todos os pets ativos com suporte a filtros dinâmicos
     * 
     * @param mixed $filtros
     * @return array
     */
    public function listar($filtros = true): array
    {
        try {
            $sql = "SELECT 
                        a.id_pet,
                        a.id_pet AS id,
                        a.nome,
                        a.especie,
                        COALESCE(a.raca, a.raça, 'SRD') AS raca,
                        a.idade,
                        a.porte,
                        a.peso,
                        a.cor,
                        COALESCE(a.sexo, 'Macho') AS sexo,
                        COALESCE(a.personalidade, 'Dócil e carinhoso') AS personalidade,
                        a.motivo_doacao,
                        a.descricao,
                        a.foto,
                        COALESCE(a.id_usuario, d.id_usuario) AS id_usuario,
                        a.ativo,
                        COALESCE(a.status_adocao, 'disponivel') AS status_adocao,
                        CASE WHEN a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END AS tem_foto,
                        u.nome AS nome_responsavel,
                        u.email AS email_responsavel,
                        u.telefone AS telefone_responsavel,
                        COALESCE(u.cidade, '') AS cidade,
                        COALESCE(u.estado, '') AS estado,
                        COALESCE(u.cep, '') AS cep,
                        COALESCE(u.bairro, '') AS bairro,
                        COALESCE(u.endereco, '') AS endereco
                    FROM tb_animal a
                    LEFT JOIN tb_doapet d ON d.id_pet = a.id_pet
                    LEFT JOIN tb_usuario u ON u.id_usuario = COALESCE(a.id_usuario, d.id_usuario)
                    WHERE (a.ativo = 1 OR a.ativo IS NULL)";

            $params = [];

            if (empty($filtros['incluir_doados'])) {
                $sql .= " AND (a.status_adocao IS NULL OR a.status_adocao = 'disponivel')";
            }

            if (is_array($filtros)) {
                if (!empty($filtros['especie'])) {
                    $sql .= " AND a.especie LIKE :especie";
                    $params[':especie'] = '%' . trim($filtros['especie']) . '%';
                }
                if (!empty($filtros['porte'])) {
                    $sql .= " AND a.porte = :porte";
                    $params[':porte'] = trim($filtros['porte']);
                }
                if (!empty($filtros['cidade'])) {
                    $cidadeTerm = trim($filtros['cidade']);
                    $sql .= " AND (u.cidade LIKE :cidade OR u.endereco LIKE :cidadeEnd)";
                    $params[':cidade'] = '%' . $cidadeTerm . '%';
                    $params[':cidadeEnd'] = '%' . $cidadeTerm . '%';
                }
                if (!empty($filtros['estado'])) {
                    $estadoTerm = strtoupper(trim($filtros['estado']));
                    $sql .= " AND (u.estado = :estado OR u.endereco LIKE :estadoEnd)";
                    $params[':estado'] = $estadoTerm;
                    $params[':estadoEnd'] = '%/' . $estadoTerm . '%';
                }
                if (!empty($filtros['cep'])) {
                    $cepClean = preg_replace('/[^0-9]/', '', $filtros['cep']);
                    if ($cepClean !== '') {
                        $sql .= " AND (REPLACE(u.cep, '-', '') LIKE :cep)";
                        $params[':cep'] = '%' . $cepClean . '%';
                    }
                }
                if (!empty($filtros['busca'])) {
                    $sql .= " AND (a.nome LIKE :busca OR a.raca LIKE :busca OR a.especie LIKE :busca OR u.cidade LIKE :buscaCidade)";
                    $params[':busca'] = '%' . trim($filtros['busca']) . '%';
                    $params[':buscaCidade'] = '%' . trim($filtros['busca']) . '%';
                }
            }

            $sql .= " ORDER BY a.id_pet DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $pets = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Anexa fotos adicionais para cada pet
            foreach ($pets as &$p) {
                $p['fotos'] = $this->buscarFotos((int)$p['id_pet'], $p['foto']);
                $p['total_fotos'] = count($p['fotos']);
            }

            return $pets;
        } catch (PDOException $e) {
            error_log("Erro em Pet::listar - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar somente os pets pertencentes ao usuário autenticado
     */
    public function meusPets(int $idUsuario): array
    {
        try {
            $sql = "SELECT 
                        a.id_pet,
                        a.id_pet AS id,
                        a.nome,
                        a.especie,
                        COALESCE(a.raca, a.raça, 'SRD') AS raca,
                        a.idade,
                        a.porte,
                        a.peso,
                        a.cor,
                        COALESCE(a.sexo, 'Macho') AS sexo,
                        COALESCE(a.personalidade, 'Dócil e carinhoso') AS personalidade,
                        a.motivo_doacao,
                        a.descricao,
                        a.foto,
                        a.id_usuario,
                        a.ativo,
                        COALESCE(a.status_adocao, 'disponivel') AS status_adocao,
                        CASE WHEN a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END AS tem_foto,
                        u.nome AS nome_responsavel
                    FROM tb_animal a
                    LEFT JOIN tb_usuario u ON a.id_usuario = u.id_usuario
                    WHERE a.id_usuario = :id_usuario
                    ORDER BY a.id_pet DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            $pets = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($pets as &$p) {
                $p['fotos'] = $this->buscarFotos((int)$p['id_pet'], $p['foto']);
                $p['total_fotos'] = count($p['fotos']);
            }

            return $pets;
        } catch (PDOException $e) {
            error_log("Erro em Pet::meusPets - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar pet por ID
     */
    public function buscarPorId(int $id): ?array
    {
        return $this->buscar($id);
    }

    /**
     * Buscar pet por ID completo com biografia, galeria de fotos e dados do doador
     */
    public function buscar(int $id): ?array
    {
        try {
            $sql = "SELECT 
                        a.id_pet,
                        a.id_pet AS id,
                        a.nome,
                        a.especie,
                        COALESCE(a.raca, a.raça, 'SRD') AS raca,
                        a.idade,
                        a.porte,
                        a.peso,
                        a.cor,
                        COALESCE(a.sexo, 'Macho') AS sexo,
                        COALESCE(a.personalidade, 'Dócil, carinhoso e companheiro') AS personalidade,
                        a.motivo_doacao,
                        a.descricao,
                        a.foto,
                        COALESCE(a.id_usuario, d.id_usuario) AS id_usuario,
                        a.ativo,
                        COALESCE(a.status_adocao, 'disponivel') AS status_adocao,
                        CASE WHEN a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END AS tem_foto,
                        u.nome AS nome_responsavel,
                        u.email AS email_responsavel,
                        u.telefone AS telefone_responsavel,
                        COALESCE(u.cidade, '') AS cidade,
                        COALESCE(u.estado, '') AS estado,
                        COALESCE(u.cep, '') AS cep,
                        COALESCE(u.bairro, '') AS bairro,
                        COALESCE(u.endereco, '') AS endereco
                    FROM tb_animal a
                    LEFT JOIN tb_doapet d ON d.id_pet = a.id_pet
                    LEFT JOIN tb_usuario u ON u.id_usuario = COALESCE(a.id_usuario, d.id_usuario)
                    WHERE a.id_pet = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $pet = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$pet) return null;

            // Busca galeria de fotos cadastradas (até 5 fotos)
            $pet['fotos'] = $this->buscarFotos($id, $pet['foto'] ?? '');
            $pet['total_fotos'] = count($pet['fotos']);

            return $pet;
        } catch (PDOException $e) {
            error_log("Erro em Pet::buscar - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca a lista de caminhos de fotos de um animal (até 5 fotos)
     */
    public function buscarFotos(int $idPet, string $fotoCapaPadrao = ''): array
    {
        try {
            $stmt = $this->db->prepare("SELECT caminho_foto FROM tb_pet_fotos WHERE id_pet = :id ORDER BY ordem ASC, id_foto ASC LIMIT 5");
            $stmt->execute([':id' => $idPet]);
            $fotos = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

            // Se não encontrou nenhuma na tabela de fotos, utiliza a foto principal de tb_animal
            if (empty($fotos) && !empty($fotoCapaPadrao)) {
                $fotos = [$fotoCapaPadrao];
            }

            return array_slice($fotos, 0, 5);
        } catch (Exception $e) {
            return !empty($fotoCapaPadrao) ? [$fotoCapaPadrao] : [];
        }
    }

    /**
     * Salva ou atualiza a galeria de fotos de um pet (limite de 5 fotos)
     */
    public function salvarFotosPet(int $idPet, array $caminhosFotos): bool
    {
        if ($idPet <= 0 || empty($caminhosFotos)) {
            return false;
        }

        try {
            // Limita a no máximo 5 fotos
            $caminhosValidos = array_slice(array_unique(array_filter($caminhosFotos)), 0, 5);

            // Remove fotos anteriores para reordenar/sincronizar
            $stmtDel = $this->db->prepare("DELETE FROM tb_pet_fotos WHERE id_pet = :id");
            $stmtDel->execute([':id' => $idPet]);

            $stmtIns = $this->db->prepare("INSERT INTO tb_pet_fotos (id_pet, caminho_foto, ordem) VALUES (:id_pet, :caminho, :ordem)");

            $ordem = 0;
            foreach ($caminhosValidos as $caminho) {
                $stmtIns->execute([
                    ':id_pet' => $idPet,
                    ':caminho' => trim($caminho),
                    ':ordem' => $ordem++
                ]);
            }

            // Atualiza a foto principal de tb_animal com a primeira foto
            if (!empty($caminhosValidos[0])) {
                $stmtFotoPrincipal = $this->db->prepare("UPDATE tb_animal SET foto = :foto, tem_foto = 1 WHERE id_pet = :id");
                $stmtFotoPrincipal->execute([
                    ':foto' => $caminhosValidos[0],
                    ':id' => $idPet
                ]);
            }

            return true;
        } catch (Exception $e) {
            error_log("Erro em Pet::salvarFotosPet - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cadastrar um novo pet para adoção/doação com suporte a até 5 fotos
     * 
     * @param array $dados
     * @return int|false
     */
    public function cadastrar(array $dados)
    {
        try {
            $raca = $dados['raca'] ?? $dados['raça'] ?? 'SRD';
            if (empty($raca)) $raca = 'SRD';

            $nome = !empty($dados['nome']) ? trim($dados['nome']) : 'Pet';
            $especie = !empty($dados['especie']) ? trim($dados['especie']) : 'Outros';
            $idade = !empty($dados['idade']) ? (float)$dados['idade'] : 1;
            $porte = !empty($dados['porte']) ? trim($dados['porte']) : 'Médio';
            $peso = !empty($dados['peso']) ? (float)$dados['peso'] : 1.0;
            $cor = !empty($dados['cor']) ? trim($dados['cor']) : 'Não informada';
            $sexo = !empty($dados['sexo']) ? trim($dados['sexo']) : 'Macho';
            $personalidade = !empty($dados['personalidade']) ? trim($dados['personalidade']) : 'Dócil, carinhoso e companheiro';
            $motivoDoacao = !empty($dados['motivo_doacao']) ? trim($dados['motivo_doacao']) : 'Doação responsável';
            $descricao = !empty($dados['descricao']) ? trim($dados['descricao']) : 'Animal dócil para adoção.';
            
            // Fotos (array de até 5 fotos ou foto única)
            $fotos = !empty($dados['fotos']) && is_array($dados['fotos']) ? $dados['fotos'] : [];
            if (empty($fotos) && !empty($dados['foto'])) {
                $fotos = [$dados['foto']];
            }
            $fotoPrincipal = !empty($fotos[0]) ? $fotos[0] : (!empty($dados['foto']) ? $dados['foto'] : '../img/PetAlert-Logo.png');
            $temFoto = (!empty($fotos) || !empty($dados['foto'])) ? 1 : 0;

            $idUsuario = !empty($dados['id_usuario']) ? (int)$dados['id_usuario'] : null;

            // Inspeciona colunas existentes em tb_animal
            $colunasExistentes = [];
            try {
                $stmtCols = $this->db->query("SHOW COLUMNS FROM tb_animal");
                $colunasExistentes = $stmtCols->fetchAll(PDO::FETCH_COLUMN) ?: [];
            } catch (Exception $e) {
                $colunasExistentes = ['id_pet', 'id_usuario', 'nome', 'especie', 'raca', 'raça', 'idade', 'porte', 'peso', 'cor', 'sexo', 'personalidade', 'motivo_doacao', 'descricao', 'foto', 'tem_foto', 'ativo', 'status_adocao'];
            }

            $campos = [];
            $placeholders = [];
            $binds = [];

            $mapaCampos = [
                'id_usuario' => [$idUsuario, PDO::PARAM_INT],
                'nome' => [$nome, PDO::PARAM_STR],
                'especie' => [$especie, PDO::PARAM_STR],
                'idade' => [$idade, PDO::PARAM_STR],
                'porte' => [$porte, PDO::PARAM_STR],
                'peso' => [$peso, PDO::PARAM_STR],
                'cor' => [$cor, PDO::PARAM_STR],
                'sexo' => [$sexo, PDO::PARAM_STR],
                'personalidade' => [$personalidade, PDO::PARAM_STR],
                'motivo_doacao' => [$motivoDoacao, PDO::PARAM_STR],
                'descricao' => [$descricao, PDO::PARAM_STR],
                'foto' => [$fotoPrincipal, PDO::PARAM_STR],
                'tem_foto' => [$temFoto, PDO::PARAM_INT],
                'ativo' => [1, PDO::PARAM_INT],
                'status_adocao' => ['disponivel', PDO::PARAM_STR]
            ];

            foreach ($mapaCampos as $col => $info) {
                if (in_array($col, $colunasExistentes, true)) {
                    $campos[] = "`$col`";
                    $placeholders[] = ":$col";
                    $binds[":$col"] = $info;
                }
            }

            if (in_array('raca', $colunasExistentes, true)) {
                $campos[] = "`raca`";
                $placeholders[] = ":raca";
                $binds[":raca"] = [$raca, PDO::PARAM_STR];
            }
            if (in_array('raça', $colunasExistentes, true)) {
                $campos[] = "`raça`";
                $placeholders[] = ":raca_cedilha";
                $binds[":raca_cedilha"] = [$raca, PDO::PARAM_STR];
            }

            $sql = "INSERT INTO tb_animal (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            foreach ($binds as $param => $info) {
                $stmt->bindValue($param, $info[0], $info[1]);
            }

            if ($stmt->execute()) {
                $novoId = (int)$this->db->lastInsertId();

                // Salva a lista de fotos (até 5) na tabela tb_pet_fotos
                if (!empty($fotos)) {
                    $this->salvarFotosPet($novoId, $fotos);
                }

                // Sincroniza tb_doapet
                if (!empty($idUsuario)) {
                    try {
                        $motivoTruncado = mb_substr($motivoDoacao, 0, 80);
                        $stmtDoapet = $this->db->prepare("INSERT INTO tb_doapet (id_usuario, id_pet, motivo_doacao, data_doacao) VALUES (?, ?, ?, CURDATE())");
                        $stmtDoapet->execute([$idUsuario, $novoId, $motivoTruncado]);
                    } catch (Exception $e) {
                        error_log("Aviso: Falha ao sincronizar tb_doapet - " . $e->getMessage());
                    }
                }

                return $novoId;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erro em Pet::cadastrar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar dados de um pet existente (com validação de dono do pet e suporte a até 5 fotos)
     */
    public function atualizar(int $id, array $dados, ?int $idUsuario = null): bool
    {
        try {
            $colunasExistentes = [];
            try {
                $stmtCols = $this->db->query("SHOW COLUMNS FROM tb_animal");
                $colunasExistentes = $stmtCols->fetchAll(PDO::FETCH_COLUMN) ?: [];
            } catch (Exception $e) {
                $colunasExistentes = ['id_pet', 'id_usuario', 'nome', 'especie', 'raca', 'raça', 'idade', 'porte', 'peso', 'cor', 'sexo', 'personalidade', 'motivo_doacao', 'descricao', 'foto', 'tem_foto', 'ativo', 'status_adocao'];
            }

            $campos = [];
            $params = [':id' => $id];

            $permitidos = ['nome', 'especie', 'idade', 'porte', 'peso', 'cor', 'sexo', 'personalidade', 'motivo_doacao', 'descricao', 'foto', 'tem_foto', 'ativo', 'status_adocao'];

            if (isset($dados['raca']) || isset($dados['raça'])) {
                $valRaca = $dados['raca'] ?? $dados['raça'];
                if (in_array('raca', $colunasExistentes, true)) {
                    $campos[] = "`raca` = :raca";
                    $params[':raca'] = $valRaca;
                }
                if (in_array('raça', $colunasExistentes, true)) {
                    $campos[] = "`raça` = :raca_cedilha";
                    $params[':raca_cedilha'] = $valRaca;
                }
            }

            foreach ($permitidos as $campo) {
                if (array_key_exists($campo, $dados) && in_array($campo, $colunasExistentes, true)) {
                    $campos[] = "`{$campo}` = :{$campo}";
                    $params[":{$campo}"] = $dados[$campo];
                }
            }

            if (!empty($campos)) {
                $sql = "UPDATE tb_animal SET " . implode(', ', $campos) . " WHERE id_pet = :id";
                if ($idUsuario !== null) {
                    $check = $this->db->prepare("SELECT 1 FROM tb_animal WHERE id_pet = :id AND id_usuario = :id_usuario");
                    $check->execute([':id' => $id, ':id_usuario' => $idUsuario]);
                    if (!$check->fetchColumn()) {
                        return false; // Permissão negada: o pet não pertence a este usuário
                    }
                    $sql .= " AND id_usuario = :id_usuario";
                    $params[':id_usuario'] = $idUsuario;
                }

                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            // Atualiza galeria de fotos se informada
            if (isset($dados['fotos']) && is_array($dados['fotos'])) {
                $this->salvarFotosPet($id, $dados['fotos']);
            }

            // Sincroniza tb_doapet se motivo_doacao for atualizado
            if (isset($dados['motivo_doacao'])) {
                try {
                    $motivoTrunc = mb_substr($dados['motivo_doacao'], 0, 80);
                    $this->db->prepare("UPDATE tb_doapet SET motivo_doacao = ? WHERE id_pet = ?")->execute([$motivoTrunc, $id]);
                } catch (Exception $e) {}
            }

            return true;
        } catch (PDOException $e) {
            error_log("Erro em Pet::atualizar - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Classificar pet como doado
     */
    public function classificarDoado(int $idPet, ?int $idUsuario = null): bool
    {
        try {
            $sql = "UPDATE tb_animal SET status_adocao = 'doado' WHERE id_pet = :id";
            $params = [':id' => $idPet];

            if ($idUsuario !== null) {
                $sql .= " AND id_usuario = :id_usuario";
                $params[':id_usuario'] = $idUsuario;
            }

            $stmt = $this->db->prepare($sql);
            $sucesso = $stmt->execute($params);
            return $sucesso && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro em Pet::classificarDoado - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar interessados nos pets do doador
     */
    public function interessados(int $idDoador): array
    {
        try {
            $sql = "SELECT DISTINCT 
                        p.id_pet,
                        p.nome AS nome_pet,
                        p.foto AS foto_pet,
                        COALESCE(p.status_adocao, 'disponivel') AS status_adocao,
                        u.id_usuario AS id_interessado,
                        u.nome AS nome_interessado,
                        u.email AS email_interessado,
                        u.telefone AS telefone_interessado,
                        u.cidade AS cidade_interessado,
                        u.estado AS estado_interessado,
                        c.mensagem AS ultima_mensagem,
                        c.data_criacao AS data_contato
                    FROM tb_animal p
                    JOIN tb_chat c ON c.id_pet = p.id_pet
                    JOIN tb_usuario u ON (u.id_usuario = c.id_usuario_origem OR u.id_usuario = c.id_usuario)
                    WHERE p.id_usuario = :id_doador 
                      AND u.id_usuario <> :id_doador2
                    ORDER BY c.data_criacao DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_doador' => $idDoador,
                ':id_doador2' => $idDoador
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Erro em Pet::interessados - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Deletar pet e limpar vínculos relacionais
     */
    public function deletar(int $id, ?int $idUsuario = null): bool
    {
        try {
            if ($idUsuario !== null) {
                $check = $this->db->prepare("SELECT 1 FROM tb_animal WHERE id_pet = :id AND id_usuario = :id_usuario");
                $check->execute([':id' => $id, ':id_usuario' => $idUsuario]);
                if (!$check->fetchColumn()) {
                    return false;
                }
            }

            // Exclui fotos de tb_pet_fotos
            try {
                $this->db->prepare("DELETE FROM tb_pet_fotos WHERE id_pet = ?")->execute([$id]);
            } catch (Exception $e) {}

            $stmt = $this->db->prepare("DELETE FROM tb_animal WHERE id_pet = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro em Pet::deletar - " . $e->getMessage());
            return false;
        }
    }
}
