<?php

require_once __DIR__ . '/../config/database.php';

class Pet
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Erro em Pet::listar - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar somente os pets pertencentes ao usuário autenticado (Requisito 19)
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Erro em Pet::meusPets - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar pet por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function buscarPorId(int $id): ?array
    {
        return $this->buscar($id);
    }

    /**
     * Buscar pet por ID
     * 
     * @param int $id
     * @return array|null
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
            return $pet ?: null;
        } catch (PDOException $e) {
            error_log("Erro em Pet::buscar - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cadastrar um novo pet para adoção/doação
     * 
     * @param array $dados
     * @return int|false
     */
    public function cadastrar(array $dados)
    {
        try {
            $temFoto = (!empty($dados['foto'])) ? 1 : 0;
            $raca = $dados['raca'] ?? $dados['raça'] ?? 'SRD';
            if (empty($raca)) $raca = 'SRD';

            $nome = !empty($dados['nome']) ? trim($dados['nome']) : 'Pet';
            $especie = !empty($dados['especie']) ? trim($dados['especie']) : 'Outros';
            $idade = !empty($dados['idade']) ? (int)$dados['idade'] : 1;
            $porte = !empty($dados['porte']) ? trim($dados['porte']) : 'Médio';
            $peso = !empty($dados['peso']) ? (float)$dados['peso'] : 1.0;
            $cor = !empty($dados['cor']) ? trim($dados['cor']) : 'Não informada';
            $motivoDoacao = !empty($dados['motivo_doacao']) ? trim($dados['motivo_doacao']) : 'Doação responsável';
            $descricao = !empty($dados['descricao']) ? trim($dados['descricao']) : 'Animal dócil para adoção.';
            $foto = !empty($dados['foto']) ? trim($dados['foto']) : '../img/PetAlert-Logo.png';

            $sql = "INSERT INTO tb_animal (
                        id_usuario,
                        nome,
                        especie,
                        raca,
                        raça,
                        idade,
                        porte,
                        peso,
                        cor,
                        motivo_doacao,
                        descricao,
                        foto,
                        tem_foto,
                        ativo,
                        status_adocao
                    ) VALUES (
                        :id_usuario,
                        :nome,
                        :especie,
                        :raca,
                        :raca2,
                        :idade,
                        :porte,
                        :peso,
                        :cor,
                        :motivo_doacao,
                        :descricao,
                        :foto,
                        :tem_foto,
                        1,
                        'disponivel'
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', !empty($dados['id_usuario']) ? (int)$dados['id_usuario'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':especie', $especie);
            $stmt->bindValue(':raca', $raca);
            $stmt->bindValue(':raca2', $raca);
            $stmt->bindValue(':idade', $idade, PDO::PARAM_INT);
            $stmt->bindValue(':porte', $porte);
            $stmt->bindValue(':peso', $peso);
            $stmt->bindValue(':cor', $cor);
            $stmt->bindValue(':motivo_doacao', $motivoDoacao);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':foto', $foto);
            $stmt->bindValue(':tem_foto', $temFoto, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $novoId = (int)$this->db->lastInsertId();

                // Mantém tb_doapet sincronizada e com compatibilidade total (Requisito 1)
                if (!empty($dados['id_usuario'])) {
                    try {
                        $stmtDoapet = $this->db->prepare("INSERT INTO tb_doapet (id_usuario, id_pet, motivo_doacao, data_doacao) VALUES (?, ?, ?, CURDATE())");
                        $stmtDoapet->execute([(int)$dados['id_usuario'], $novoId, $motivoDoacao]);
                    } catch (Exception $e) {
                        error_log("Aviso: Falha ao sincronizar tb_doapet - " . $e->getMessage());
                    }
                }

                return $novoId;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erro em Pet::cadastrar - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Classificar pet como doado (Requisito 23)
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
     * Listar interessados nos pets do doador (Requisito 20)
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
     * Atualizar dados de um pet existente (com validação de dono do pet)
     */
    public function atualizar(int $id, array $dados, ?int $idUsuario = null): bool
    {
        try {
            $campos = [];
            $params = [':id' => $id];

            $permitidos = ['nome', 'especie', 'idade', 'porte', 'peso', 'cor', 'motivo_doacao', 'descricao', 'foto', 'tem_foto', 'ativo', 'status_adocao'];

            if (isset($dados['raca']) || isset($dados['raça'])) {
                $valRaca = $dados['raca'] ?? $dados['raça'];
                $campos[] = "raca = :raca";
                $campos[] = "`raça` = :raca_latin";
                $params[':raca'] = $valRaca;
                $params[':raca_latin'] = $valRaca;
            }

            foreach ($permitidos as $campo) {
                if (array_key_exists($campo, $dados)) {
                    $campos[] = "{$campo} = :{$campo}";
                    $params[":{$campo}"] = $dados[$campo];
                }
            }

            if (empty($campos)) {
                return false;
            }

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
            $executou = $stmt->execute($params);

            // Sincroniza tb_doapet se motivo_doacao for atualizado
            if ($executou && isset($dados['motivo_doacao'])) {
                try {
                    $this->db->prepare("UPDATE tb_doapet SET motivo_doacao = ? WHERE id_pet = ?")->execute([$dados['motivo_doacao'], $id]);
                } catch (Exception $e) {}
            }

            return $executou;
        } catch (PDOException $e) {
            error_log("Erro em Pet::atualizar - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletar pet e limpar vínculos relacionais (com validação de dono do pet)
     */
    public function deletar(int $id, ?int $idUsuario = null): bool
    {
        try {
            // Se idUsuario for informado, verifica se o pet pertence a este usuário
            if ($idUsuario !== null) {
                $check = $this->db->prepare("SELECT 1 FROM tb_animal WHERE id_pet = :id AND id_usuario = :id_usuario");
                $check->execute([':id' => $id, ':id_usuario' => $idUsuario]);
                if (!$check->fetchColumn()) {
                    return false; // Permissão negada: pet não pertence ao usuário
                }
            }

            // 1. Limpar relacionamentos em tabelas dependentes sem apagar o histórico de conversas
            try {
                $this->db->prepare("UPDATE tb_chat SET id_pet = NULL WHERE id_pet = :id")->execute([':id' => $id]);
            } catch (Exception $e) {}

            try {
                $this->db->prepare("DELETE FROM tb_doapet WHERE id_pet = :id")->execute([':id' => $id]);
            } catch (Exception $e) {}

            try {
                $this->db->prepare("DELETE FROM tb_doacao_pet WHERE id_pet = :id")->execute([':id' => $id]);
            } catch (Exception $e) {}

            try {
                $this->db->prepare("UPDATE tb_denuncia SET id_pet = NULL WHERE id_pet = :id")->execute([':id' => $id]);
            } catch (Exception $e) {}

            // 2. Excluir o pet da tabela tb_animal
            $sql = "DELETE FROM tb_animal WHERE id_pet = :id";
            $params = [':id' => $id];
            if ($idUsuario !== null) {
                $sql .= " AND id_usuario = :id_usuario";
                $params[':id_usuario'] = $idUsuario;
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro em Pet::deletar - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alias para deletar
     */
    public function excluir(int $id, ?int $idUsuario = null): bool
    {
        return $this->deletar($id, $idUsuario);
    }
}