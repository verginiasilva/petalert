<?php

class Usuario
{
    private $id;
    private $nome;
    private $dataNascimento;
    private $cpf;
    private $email;
    private $telefone;
    private $cep;
    private $rua;
    private $numero;
    private $bairro;
    private $cidade;
    private $estado;
    private $endereco;
    private $foto;
    private $senha;
    private $ativo;
    private $tipoUsuario;
    private $statusIdentidade;
    private $dataVerificacaoIdentidade;
    private $protocoloIdentidade;
    private $idade;

    // Getters e Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getIdade() { return $this->idade; }
    public function setIdade($idade) { $this->idade = $idade; }

    /**
     * Calcula os anos completos a partir de uma data de nascimento,
     * considerando ano, mês e dia exatos.
     */
    public static function calcularIdadeCompleta(?string $dataNascimento): int
    {
        if (empty($dataNascimento)) {
            return 0;
        }
        try {
            $dataLimpa = trim(explode(' ', $dataNascimento)[0]);
            $nasc = DateTime::createFromFormat('!Y-m-d', $dataLimpa) ?: new DateTime($dataLimpa);
            $nasc->setTime(0, 0, 0);
            $hoje = new DateTime('today');
            $hoje->setTime(0, 0, 0);
            if ($nasc > $hoje) {
                return 0;
            }
            return (int)$hoje->diff($nasc)->y;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Retorna a idade completa calculada com base na data de nascimento do usuário.
     */
    public function getIdadeCalculada(): int
    {
        return self::calcularIdadeCompleta($this->dataNascimento);
    }

    public function getTipoUsuario() { return $this->tipoUsuario ?: 'adotante'; }
    public function setTipoUsuario($tipoUsuario) { $this->tipoUsuario = $tipoUsuario; }

    public function getStatusIdentidade() { return $this->statusIdentidade ?: 'pendente'; }
    public function setStatusIdentidade($status) { $this->statusIdentidade = $status; }

    public function getDataVerificacaoIdentidade() { return $this->dataVerificacaoIdentidade; }
    public function setDataVerificacaoIdentidade($data) { $this->dataVerificacaoIdentidade = $data; }

    public function getProtocoloIdentidade() { return $this->protocoloIdentidade; }
    public function setProtocoloIdentidade($protocolo) { $this->protocoloIdentidade = $protocolo; }

    public function getNome() { return $this->nome; }
    public function setNome($nome) { $this->nome = $nome; }

    public function getDataNascimento() { return $this->dataNascimento; }
    public function setDataNascimento($dataNascimento) { $this->dataNascimento = $dataNascimento; }

    public function getCpf() { return $this->cpf; }
    public function setCpf($cpf) { $this->cpf = $cpf; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getTelefone() { return $this->telefone; }
    public function setTelefone($telefone) { $this->telefone = $telefone; }

    public function getCep() { return $this->cep; }
    public function setCep($cep) { $this->cep = $cep; }

    public function getRua() { return $this->rua; }
    public function setRua($rua) { $this->rua = $rua; }

    public function getNumero() { return $this->numero; }
    public function setNumero($numero) { $this->numero = $numero; }

    public function getBairro() { return $this->bairro; }
    public function setBairro($bairro) { $this->bairro = $bairro; }

    public function getCidade() { return $this->cidade; }
    public function setCidade($cidade) { $this->cidade = $cidade; }

    public function getEstado() { return $this->estado; }
    public function setEstado($estado) { $this->estado = $estado; }

    public function getEndereco() {
        if ($this->endereco) return $this->endereco;
        $partes = array_filter([$this->rua, $this->numero ? "nº {$this->numero}" : null, $this->bairro, $this->cidade, $this->estado]);
        return implode(', ', $partes);
    }
    public function setEndereco($endereco) { $this->endereco = $endereco; }

    public function getFoto() { return $this->foto; }
    public function setFoto($foto) { $this->foto = $foto; }

    public function getSenha() { return $this->senha; }
    public function setSenha($senha) { $this->senha = $senha; }

    public function getAtivo() { return $this->ativo; }
    public function setAtivo($ativo) { $this->ativo = $ativo; }
}