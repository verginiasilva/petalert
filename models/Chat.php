<?php

class Chat
{
    private ?int $id_chat = null;
    private ?int $id_usuario = null;
    private ?int $id_pet = null;
    private ?int $id_usuario_origem = null;
    private ?int $id_usuario_destino = null;
    private ?string $mensagem = null;
    private ?string $data_criacao = null;
    private ?string $tipo_conversa = null;

    public function getId(): ?int { return $this->id_chat; }
    public function setId(?int $id_chat): void { $this->id_chat = $id_chat; }

    public function getIdUsuario(): ?int { return $this->id_usuario; }
    public function setIdUsuario(?int $id_usuario): void { $this->id_usuario = $id_usuario; }

    public function getIdPet(): ?int { return $this->id_pet; }
    public function setIdPet(?int $id_pet): void { $this->id_pet = $id_pet; }

    public function getIdUsuarioOrigem(): ?int { return $this->id_usuario_origem; }
    public function setIdUsuarioOrigem(?int $id_usuario_origem): void { $this->id_usuario_origem = $id_usuario_origem; }

    public function getIdUsuarioDestino(): ?int { return $this->id_usuario_destino; }
    public function setIdUsuarioDestino(?int $id_usuario_destino): void { $this->id_usuario_destino = $id_usuario_destino; }

    public function getMensagem(): ?string { return $this->mensagem; }
    public function setMensagem(?string $mensagem): void { $this->mensagem = $mensagem; }

    public function getDataCriacao(): ?string { return $this->data_criacao; }
    public function setDataCriacao(?string $data_criacao): void { $this->data_criacao = $data_criacao; }

    public function getTipoConversa(): ?string { return $this->tipo_conversa ?: 'doador'; }
    public function setTipoConversa(?string $tipo_conversa): void { $this->tipo_conversa = $tipo_conversa; }
}
?>