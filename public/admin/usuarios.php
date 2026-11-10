<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'usuarios';
$tituloPagina = 'Gerenciamento de Usuários';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários — PetAlert</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>👥 Gerenciamento de Usuários (tb_usuario)</h2>
                <p>Listagem, busca por nome, CPF e e-mail, filtros, detalhes, bloqueio de contas e CRUD completo.</p>
            </div>
            <button type="button" class="btn-primary" id="btnNovoUsuario">➕ Cadastrar Novo Usuário</button>
        </div>

        <div id="alertaGeral" class="alert-adm"></div>

        <div class="toolbar-wrap">
            <input type="text" id="filtroBusca" class="toolbar-input" placeholder="🔍 Buscar por nome, CPF ou e-mail...">
            <select id="filtroPerfil" class="toolbar-select">
                <option value="">Todos os perfis</option>
                <option value="adotante">Adotantes</option>
                <option value="doador">Doadores</option>
                <option value="admin">Administradores</option>
            </select>
            <select id="filtroStatus" class="toolbar-select">
                <option value="">Todos os status</option>
                <option value="1">Ativos</option>
                <option value="0">Bloqueados / Inativos</option>
            </select>
            <span id="contadorUsuarios" style="font-weight:700; color:var(--text-muted); font-size:0.88rem; margin-left:auto;">Carregando...</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nome Completo</th>
                        <th>E-mail</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th style="text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaUsuarios">
                    <tr><td colspan="9" style="text-align:center; padding:2rem;">Carregando usuários do banco...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: VISUALIZAR DADOS COMPLETOS -->
    <div class="admin-modal" id="modalDetalhes">
        <div class="modal-box">
            <div class="modal-header">
                <h3>📋 Detalhes Completos do Usuário</h3>
                <button type="button" class="modal-close" id="btnFecharDetalhes">✕</button>
            </div>
            <div id="corpoDetalhes" style="display:flex; flex-direction:column; gap:0.8rem; font-size:0.95rem;">
                <!-- Preenchido via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnFecharDetalhes2">Fechar</button>
            </div>
        </div>
    </div>

    <!-- MODAL: FORMULÁRIO DE CADASTRO / EDIÇÃO -->
    <div class="admin-modal" id="modalFormUsuario">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modalFormTitulo">Cadastrar Usuário</h3>
                <button type="button" class="modal-close" id="btnFecharForm">✕</button>
            </div>
            <form id="formUsuario">
                <input type="hidden" id="formIdUsuario" value="">
                <div class="form-grid-2">
                    <div class="form-group-adm">
                        <label for="formNome">Nome Completo *</label>
                        <input type="text" id="formNome" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="formEmail">E-mail *</label>
                        <input type="email" id="formEmail" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="formCpf">CPF * (apenas números)</label>
                        <input type="text" id="formCpf" maxlength="14" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="formTelefone">Telefone / WhatsApp *</label>
                        <input type="text" id="formTelefone" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="formDataNascimento">Data de Nascimento *</label>
                        <input type="date" id="formDataNascimento" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="formTipo">Tipo de Usuário *</label>
                        <select id="formTipo" required>
                            <option value="adotante">Adotante</option>
                            <option value="doador">Doador</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group-adm">
                        <label for="formCep">CEP *</label>
                        <input type="text" id="formCep" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="formRua">Rua / Logradouro *</label>
                        <input type="text" id="formRua" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="formNumero">Número *</label>
                        <input type="text" id="formNumero" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="formBairro">Bairro</label>
                        <input type="text" id="formBairro">
                    </div>
                    <div class="form-group-adm">
                        <label for="formCidade">Cidade *</label>
                        <input type="text" id="formCidade" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="formEstado">Estado (UF) *</label>
                        <input type="text" id="formEstado" maxlength="2" required>
                    </div>
                    <div class="form-group-adm" style="grid-column: 1 / -1;">
                        <label for="formSenha" id="labelSenha">Senha de Acesso</label>
                        <input type="password" id="formSenha" placeholder="Preencha para definir ou alterar senha">
                    </div>
                </div>

                <div id="alertaModal" class="alert-adm" style="margin-top:1rem;"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarForm">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnSalvarUsuario">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: CONFIRMAÇÃO DE EXCLUSÃO -->
    <div class="admin-modal" id="modalExcluir">
        <div class="modal-box" style="max-width:480px;">
            <div class="modal-header">
                <h3 style="color:#DC2626;">Confirmar Exclusão de Usuário</h3>
                <button type="button" class="modal-close" id="btnFecharExcluir">✕</button>
            </div>
            <p id="textoExclusao" style="color:#4B5563; line-height:1.5;">Tem certeza que deseja excluir este usuário?</p>
            <div id="alertaExcluir" class="alert-adm"></div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelarExcluir">Cancelar</button>
                <button type="button" class="btn-danger-sm" id="btnConfirmarExcluir" style="padding:0.65rem 1.2rem; font-size:0.9rem;">Excluir Definitivamente</button>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script src="usuarios.js"></script>
</body>
</html>
