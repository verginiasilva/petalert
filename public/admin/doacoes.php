<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'doacoes';
$tituloPagina = 'Gerenciamento de Doações';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Doações — PetAlert</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tabs-doacoes {
            display: flex;
            gap: 10px;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
        }
        .tab-btn {
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-muted);
            padding: 0.5rem 1rem;
            cursor: pointer;
            border-radius: var(--radius-sm);
            transition: all 0.2s;
        }
        .tab-btn.active {
            background: var(--pastel-yellow);
            color: #B45309;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.15);
        }
        .painel-insumos {
            display: grid;
            grid-template-columns: minmax(260px, 360px) 1fr;
            gap: 1.2rem;
        }
        .form-insumo-card {
            background: #FFFDF7;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.1rem;
            align-self: start;
        }
        .form-insumo-card h3 {
            margin: 0 0 1rem;
            font-size: 1.05rem;
            color: #92400E;
        }
        @media (max-width: 900px) {
            .painel-insumos {
                grid-template-columns: 1fr;
            }
            .tabs-doacoes {
                flex-wrap: wrap;
            }
        }
        @media (max-width: 640px) {
            .tabs-doacoes {
                flex-direction: column;
            }
            .tab-btn {
                width: 100%;
                text-align: center;
            }
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
            .modal-footer {
                flex-direction: column;
            }
            .modal-footer button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>💛 Gestão de Doações — PetAlert</h2>
                <p>Acompanhamento segregado de doações financeiras (PIX) e doações de animais para adoção responsável.</p>
            </div>
            <div id="boxTotalArrecadado" style="background:#FFFBEB; border:1px solid #FDE68A; padding:0.6rem 1.2rem; border-radius:var(--radius-sm); font-size:0.95rem; font-weight:700; color:#B45309;">
                Total Arrecadado Confirmado: <span id="txtTotalArrecadado">R$ 0,00</span>
            </div>
        </div>

        <div class="tabs-doacoes">
            <button type="button" class="tab-btn active" id="tabFinanceiras">💰 1. Doações Financeiras (<span id="contFin">0</span>)</button>
            <button type="button" class="tab-btn" id="tabAnimais">🐾 2. Doações de Animais (<span id="contAnim">0</span>)</button>
            <button type="button" class="tab-btn" id="tabInsumos">📦 3. Doações de Insumos (<span id="contInsumos">0</span>)</button>
        </div>

        <!-- PAINEL 1: DOAÇÕES FINANCEIRAS -->
        <div id="painelFinanceiras">
            <div class="toolbar-wrap">
                <input type="text" id="filtroFinanceiras" class="toolbar-input" placeholder="🔍 Filtrar por doador, e-mail ou valor...">
                <select id="filtroStatusPagamento" class="toolbar-select">
                    <option value="">Todos os status</option>
                    <option value="confirmado">Confirmados / Pagos</option>
                    <option value="aguardando_pagamento">Aguardando Pagamento</option>
                </select>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Doador Responsável</th>
                            <th>Contato / E-mail</th>
                            <th>Valor Doado</th>
                            <th>Tipo / Objeto</th>
                            <th>Status de Pagamento</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaFinanceiras">
                        <tr><td colspan="7" style="text-align:center; padding:2rem;">Carregando doações financeiras...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAINEL 2: DOAÇÕES DE ANIMAIS -->
        <div id="painelAnimais" style="display:none;">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Pet</th>
                            <th>Espécie / Raça</th>
                            <th>Doador Responsável</th>
                            <th>Motivo da Doação</th>
                            <th>Data da Disponibilização</th>
                            <th>Status Atual</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaAnimais">
                        <tr><td colspan="6" style="text-align:center; padding:2rem;">Carregando doações de animais...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAINEL 3: DOAÇÕES DE INSUMOS -->
        <div id="painelInsumos" style="display:none;">
            <div class="painel-insumos">
                <div class="form-insumo-card">
                    <h3>📦 Cadastrar item de insumo</h3>
                    <div id="alertInsumo" class="alert-adm"></div>

                    <form id="formInsumo">
                        <div class="form-group-adm">
                            <label for="insumoNome">Nome do doador *</label>
                            <select id="insumoNome" required>
                                <option value="anonimo" data-id="0" data-phone="">Anônimo</option>
                            </select>
                        </div>
                        <div class="form-group-adm">
                            <label for="insumoIdUsuario">ID do usuário</label>
                            <input type="text" id="insumoIdUsuario" value="Anônimo" data-id-user="0" readonly>
                        </div>
                        <div class="form-group-adm">
                            <label for="insumoIdInsumo">ID do insumo</label>
                            <input type="text" id="insumoIdInsumo" value="Será gerado ao cadastrar" readonly>
                        </div>
                        <div class="form-group-adm">
                            <label for="insumoTelefone">Número de telefone</label>
                            <input type="tel" id="insumoTelefone" placeholder="Ex.: (14) 99999-9999">
                        </div>
                        <div class="form-group-adm">
                            <label for="insumoItem">Item ou Material *</label>
                            <input type="text" id="insumoItem" placeholder="Ex.: Ração para cães" required>
                        </div>
                        <div class="form-group-adm">
                            <label for="insumoQuantidade">Quantidade / Tamanho (opcional)</label>
                            <input type="text" id="insumoQuantidade" placeholder="Ex.: 2 pacotes de 15kg">
                        </div>
                        <button type="submit" class="btn-primary" id="btnCadastrarInsumo" style="width:100%; justify-content:center;">
                            ➕ Cadastrar Item
                        </button>
                    </form>
                </div>

                <div>
                    <div class="table-responsive" style="margin-bottom:1.2rem;">
                        <h3 style="font-size:1rem; margin:0 0 0.7rem;">Itens cadastrados pelo administrador</h3>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Doador</th>
                                    <th>ID Usuário</th>
                                    <th>Telefone</th>
                                    <th>Item</th>
                                    <th>Quantidade</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaInsumos">
                                <tr><td colspan="8" style="text-align:center; padding:2rem;">Carregando itens de insumos...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <h3 style="font-size:1rem; margin:0 0 0.7rem;">Doações registradas pelos usuários</h3>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Doador</th>
                                    <th>Item</th>
                                    <th>Quantidade</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaInsumosPublicados">
                                <tr><td colspan="6" style="text-align:center; padding:2rem;">Carregando doações de insumos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-modal" id="modalEditarInsumo" role="dialog" aria-modal="true" aria-labelledby="tituloEditarInsumo">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="tituloEditarInsumo">Editar insumo</h3>
                <button type="button" class="modal-close" id="fecharModalInsumo" aria-label="Fechar">&times;</button>
            </div>

            <form id="formEditarInsumo">
                <input type="hidden" id="editarInsumoId">

                <div class="form-grid-2">
                    <div class="form-group-adm">
                        <label for="editarInsumoNome">Nome do doador *</label>
                        <select id="editarInsumoNome" required>
                            <option value="anonimo" data-id="0" data-phone="">Anônimo</option>
                        </select>
                    </div>
                    <div class="form-group-adm">
                        <label for="editarInsumoIdUsuario">ID do usuário</label>
                        <input type="text" id="editarInsumoIdUsuario" value="Anônimo" readonly>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group-adm">
                        <label for="editarInsumoTelefone">Número de telefone</label>
                        <input type="tel" id="editarInsumoTelefone" placeholder="Ex.: (14) 99999-9999">
                    </div>
                    <div class="form-group-adm">
                        <label for="editarInsumoItem">Item ou Material *</label>
                        <input type="text" id="editarInsumoItem" required>
                    </div>
                </div>

                <div class="form-group-adm">
                    <label for="editarInsumoQuantidade">Quantidade / Tamanho (opcional)</label>
                    <input type="text" id="editarInsumoQuantidade" placeholder="Ex.: 2 pacotes de 15kg">
                </div>

                <div id="alertEditarInsumo" class="alert-adm"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="cancelarEditarInsumo">Cancelar</button>
                    <button type="submit" class="btn-primary" id="salvarEditarInsumo">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    let doacoesFin = [];
    let doacoesAnim = [];
    let doacoesInsumos = [];
    let doacoesInsumosPublicados = [];
    let usuariosInsumos = [];

    const tabFin = document.getElementById('tabFinanceiras');
    const tabAnim = document.getElementById('tabAnimais');
    const tabInsumos = document.getElementById('tabInsumos');
    const pnlFin = document.getElementById('painelFinanceiras');
    const pnlAnim = document.getElementById('painelAnimais');
    const pnlInsumos = document.getElementById('painelInsumos');

    const tabFinCorpo = document.getElementById('tabelaFinanceiras');
    const tabAnimCorpo = document.getElementById('tabelaAnimais');
    const tabInsumosCorpo = document.getElementById('tabelaInsumos');
    const tabInsumosPublicadosCorpo = document.getElementById('tabelaInsumosPublicados');
    const contFin = document.getElementById('contFin');
    const contAnim = document.getElementById('contAnim');
    const contInsumos = document.getElementById('contInsumos');
    const txtTotal = document.getElementById('txtTotalArrecadado');

    const filtroTexto = document.getElementById('filtroFinanceiras');
    const filtroStatus = document.getElementById('filtroStatusPagamento');

    function sanitizar(txt) {
        const d = document.createElement('div');
        d.textContent = txt ?? '';
        return d.innerHTML;
    }

    tabFin.addEventListener('click', () => {
        tabFin.classList.add('active');
        tabAnim.classList.remove('active');
        tabInsumos.classList.remove('active');
        pnlFin.style.display = 'block';
        pnlAnim.style.display = 'none';
        pnlInsumos.style.display = 'none';
    });

    tabAnim.addEventListener('click', () => {
        tabAnim.classList.add('active');
        tabFin.classList.remove('active');
        tabInsumos.classList.remove('active');
        pnlFin.style.display = 'none';
        pnlAnim.style.display = 'block';
        pnlInsumos.style.display = 'none';
    });

    tabInsumos.addEventListener('click', () => {
        tabInsumos.classList.add('active');
        tabFin.classList.remove('active');
        tabAnim.classList.remove('active');
        pnlFin.style.display = 'none';
        pnlAnim.style.display = 'none';
        pnlInsumos.style.display = 'block';
    });

    async function carregarDoacoes() {
        try {
            const res = await fetch('../index.php?rota=admin&acao=doacoes');
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.status !== 'sucesso') {
                tabFinCorpo.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#DC2626;">Erro ao carregar doações.</td></tr>';
                return;
            }

            const dados = json.dados || {};
            doacoesFin = dados.financeiras || [];
            doacoesAnim = dados.animais || [];
            doacoesInsumos = dados.insumos || [];
            doacoesInsumosPublicados = dados.insumos_publicados || [];
            usuariosInsumos = dados.usuarios || [];

            contFin.textContent = doacoesFin.length;
            contAnim.textContent = doacoesAnim.length;
            contInsumos.textContent = doacoesInsumos.length + doacoesInsumosPublicados.length;

            // Calcular total arrecadado confirmado (somente status 'pago' ou 'confirmado')
            let somaConfirmada = 0;
            doacoesFin.forEach(d => {
                const st = (d.status_pagamento || '').toLowerCase();
                if (['pago', 'confirmado'].includes(st)) {
                    somaConfirmada += Number(d.valor_doado || 0);
                }
            });

            txtTotal.textContent = somaConfirmada.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            renderizarFinanceiras();
            renderizarAnimais();
            renderizarInsumos();
            renderizarInsumosPublicados();
            preencherUsuariosInsumos();
        } catch (e) {
            tabFinCorpo.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#DC2626;">Erro ao conectar com o servidor.</td></tr>';
        }
    }

    function renderizarFinanceiras() {
        const termo = (filtroTexto.value || '').trim().toLowerCase();
        const stFiltro = (filtroStatus.value || '').trim().toLowerCase();

        const filtradas = doacoesFin.filter(d => {
            const doador = (d.nome_doador || d.nome_usuario || '').toLowerCase();
            const email = (d.email_usuario || '').toLowerCase();
            const val = String(d.valor_doado || '');
            const st = (d.status_pagamento || '').toLowerCase();

            const bateTermo = !termo || doador.includes(termo) || email.includes(termo) || val.includes(termo);
            const bateStatus = !stFiltro || (stFiltro === 'confirmado' ? ['pago', 'confirmado'].includes(st) : st === stFiltro);

            return bateTermo && bateStatus;
        });

        if (!filtradas.length) {
            tabFinCorpo.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhuma doação financeira localizada.</td></tr>';
            return;
        }

        tabFinCorpo.innerHTML = filtradas.map(d => {
            const st = (d.status_pagamento || 'pendente').toLowerCase();
            const isPago = ['pago', 'confirmado'].includes(st);
            const badge = isPago
                ? `<span class="badge badge-success">✓ Confirmado / Pago</span>`
                : `<span class="badge badge-warning">⏳ Aguardando Pagamento</span>`;

            const valorNum = Number(d.valor_doado || 0);
            const valorFmt = valorNum > 0 ? valorNum.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : (d.objeto_doado || 'Insumo');

            return `
                <tr>
                    <td><strong>#${d.id_doacao}</strong></td>
                    <td style="font-size:0.85rem; white-space:nowrap;">${sanitizar(d.data_doacao || d.data_criacao || '—')}</td>
                    <td><strong>${sanitizar(d.nome_doador || d.nome_usuario || 'Doador Anônimo')}</strong></td>
                    <td>${sanitizar(d.email_usuario || d.telefone_usuario || '—')}</td>
                    <td><strong style="color:${isPago ? '#059669' : '#D97706'}; font-size:1.05rem;">${sanitizar(valorFmt)}</strong></td>
                    <td>${sanitizar(d.tipo_doacao || d.objeto_doado || 'PIX')}</td>
                    <td>${badge}</td>
                </tr>
            `;
        }).join('');
    }

    function renderizarAnimais() {
        if (!doacoesAnim.length) {
            tabAnimCorpo.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhum registro de animal doado para adoção.</td></tr>';
            return;
        }

        tabAnimCorpo.innerHTML = doacoesAnim.map(a => {
            const st = (a.status_adocao || 'disponivel').toLowerCase();
            const badge = ['doado', 'adotado', 'adotada'].includes(st)
                ? `<span class="badge badge-success">❤️ Adotado</span>`
                : `<span class="badge badge-warning">🟢 Disponível</span>`;

            return `
                <tr>
                    <td><strong>${sanitizar(a.nome_pet || 'Pet #' + a.id_pet)}</strong></td>
                    <td>${sanitizar(a.especie || '—')} / ${sanitizar(a.raca || 'SRD')}</td>
                    <td><strong>${sanitizar(a.nome_doador || 'Tutor #' + a.id_usuario)}</strong></td>
                    <td><small style="color:#4B5563;">${sanitizar(a.motivo_doacao || 'Não especificado')}</small></td>
                    <td style="font-size:0.85rem; white-space:nowrap;">${sanitizar(a.data_doacao || '—')}</td>
                    <td>${badge}</td>
                </tr>
            `;
        }).join('');
    }

    function renderizarInsumos() {
        if (!doacoesInsumos.length) {
            tabInsumosCorpo.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhum item de insumo cadastrado.</td></tr>';
            return;
        }

        tabInsumosCorpo.innerHTML = doacoesInsumos.map(i => `
            <tr>
                <td><strong>#${i.id_doacao}</strong></td>
                <td>${sanitizar(i.nome_doador || i.nome_usuario || 'Usuário anônimo')}</td>
                <td>${i.id_usuario ? `<strong>#${i.id_usuario}</strong>` : '0'}</td>
                <td>${sanitizar(i.contato || '—')}</td>
                <td>${sanitizar(i.objeto_doado || '—')}</td>
                <td>${sanitizar(i.quantidade || '—')}</td>
                <td style="white-space:nowrap;">${sanitizar(i.data_doacao || '—')}</td>
                <td>
                    <div style="display:flex; gap:0.45rem; flex-wrap:wrap;">
                        <button type="button" class="btn-secondary btn-sm" data-action="editar" data-id="${i.id_doacao}" data-item="${sanitizar(i.objeto_doado || '')}" data-quantidade="${sanitizar(i.quantidade || '')}" data-nome="${sanitizar(i.nome_doador || i.nome_usuario || 'Anônimo')}" data-user-id="${i.id_usuario || 0}" data-phone="${sanitizar(i.contato || '')}">✏️ Editar</button>
                        <button type="button" class="btn-danger-sm" data-action="excluir" data-id="${i.id_doacao}" data-item="${sanitizar(i.objeto_doado || '')}">🗑️ Excluir</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function preencherUsuariosInsumos() {
        const select = document.getElementById('insumoNome');
        select.innerHTML = '<option value="anonimo" data-id="0" data-phone="">Anônimo</option>' +
            usuariosInsumos.map(u =>
                `<option value="${sanitizar(u.nome)}" data-id="${u.id_usuario}" data-phone="${sanitizar(u.telefone || '')}">
                    ${sanitizar(u.nome)}
                </option>`
            ).join('');

        const selectEditar = document.getElementById('editarInsumoNome');
        selectEditar.innerHTML = '<option value="anonimo" data-id="0" data-phone="">Anônimo</option>' +
            usuariosInsumos.map(u =>
                `<option value="${sanitizar(u.nome)}" data-id="${u.id_usuario}" data-phone="${sanitizar(u.telefone || '')}">
                    ${sanitizar(u.nome)}
                </option>`
            ).join('');
    }

    const insumoNome = document.getElementById('insumoNome');
    insumoNome.addEventListener('change', () => {
        const option = insumoNome.options[insumoNome.selectedIndex];
        const idUsuario = Number(option.dataset.id || 0);
        const insumoIdUsuario = document.getElementById('insumoIdUsuario');
        insumoIdUsuario.value = idUsuario > 0
            ? `#${idUsuario}`
            : 'Anônimo';
        insumoIdUsuario.dataset.idUser = String(idUsuario);
        document.getElementById('insumoTelefone').value = option.dataset.phone || '';
    });

    const editarInsumoNome = document.getElementById('editarInsumoNome');
    editarInsumoNome.addEventListener('change', () => {
        const option = editarInsumoNome.options[editarInsumoNome.selectedIndex];
        const idUsuario = Number(option.dataset.id || 0);
        const campoId = document.getElementById('editarInsumoIdUsuario');
        campoId.value = idUsuario > 0 ? `#${idUsuario}` : 'Anônimo';
        campoId.dataset.idUser = String(idUsuario);
        document.getElementById('editarInsumoTelefone').value = option.dataset.phone || '';
    });

    function fecharModalInsumo() {
        document.getElementById('modalEditarInsumo').classList.remove('open');
    }

    document.getElementById('fecharModalInsumo').addEventListener('click', fecharModalInsumo);
    document.getElementById('cancelarEditarInsumo').addEventListener('click', fecharModalInsumo);
    document.getElementById('modalEditarInsumo').addEventListener('click', event => {
        if (event.target.id === 'modalEditarInsumo') fecharModalInsumo();
    });

    document.getElementById('formEditarInsumo').addEventListener('submit', async event => {
        event.preventDefault();

        const id = Number(document.getElementById('editarInsumoId').value);
        const item = document.getElementById('editarInsumoItem').value.trim();
        const quantidade = document.getElementById('editarInsumoQuantidade').value.trim();
        const telefone = document.getElementById('editarInsumoTelefone').value.trim();
        const nomeSelect = document.getElementById('editarInsumoNome');
        const idUsuario = Number(document.getElementById('editarInsumoIdUsuario').dataset.idUser || 0);
        const nomeDoador = nomeSelect.value === 'anonimo'
            ? 'Usuário anônimo'
            : nomeSelect.value;
        const alertEditar = document.getElementById('alertEditarInsumo');
        const botaoSalvar = document.getElementById('salvarEditarInsumo');

        if (!item) {
            document.getElementById('editarInsumoItem').focus();
            return;
        }

        botaoSalvar.disabled = true;
        botaoSalvar.textContent = 'Salvando...';
        alertEditar.style.display = 'none';

        try {
            const res = await fetch('../index.php?rota=admin&acao=insumo-editar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    id_doacao: id,
                    item,
                    quantidade,
                    nome_doador: nomeDoador,
                    id_usuario: idUsuario,
                    telefone
                })
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.status !== 'sucesso') {
                throw new Error(json.mensagem || 'Não foi possível editar o insumo.');
            }

            fecharModalInsumo();
            await carregarDoacoes();
        } catch (error) {
            alertEditar.className = 'alert-adm';
            alertEditar.style.background = '#FEF2F2';
            alertEditar.style.color = '#991B1B';
            alertEditar.style.border = '1px solid #FECACA';
            alertEditar.textContent = error.message;
            alertEditar.style.display = 'block';
        } finally {
            botaoSalvar.disabled = false;
            botaoSalvar.textContent = 'Salvar alterações';
        }
    });

    function renderizarInsumosPublicados() {
        if (!doacoesInsumosPublicados.length) {
            tabInsumosPublicadosCorpo.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhuma doação de insumo registrada pelos usuários.</td></tr>';
            return;
        }

        tabInsumosPublicadosCorpo.innerHTML = doacoesInsumosPublicados.map(i => {
            const status = (i.status_pagamento || 'confirmado').toLowerCase() === 'confirmado'
                ? '<span class="badge badge-success">✓ Publicado</span>'
                : `<span class="badge badge-warning">${sanitizar(i.status_pagamento)}</span>`;

            return `
                <tr>
                    <td><strong>#${i.id_doacao}</strong></td>
                    <td><strong>${sanitizar(i.nome_doador || i.nome_usuario || 'Usuário anônimo')}</strong></td>
                    <td>${sanitizar(i.objeto_doado || '—')}</td>
                    <td>${sanitizar(i.quantidade || '—')}</td>
                    <td style="white-space:nowrap;">${sanitizar(i.data_doacao || '—')}</td>
                    <td>${status}</td>
                </tr>
            `;
        }).join('');
    }

    const formInsumo = document.getElementById('formInsumo');
    const alertInsumo = document.getElementById('alertInsumo');
    const btnCadastrarInsumo = document.getElementById('btnCadastrarInsumo');

    formInsumo.addEventListener('submit', async event => {
        event.preventDefault();

        const item = document.getElementById('insumoItem').value.trim();
        const quantidade = document.getElementById('insumoQuantidade').value.trim();
        const nomeDoador = document.getElementById('insumoNome').value;
        const idUsuario = Number(document.getElementById('insumoIdUsuario').dataset.idUser || 0);
        const telefone = document.getElementById('insumoTelefone').value.trim();
        if (!item) {
            document.getElementById('insumoItem').focus();
            return;
        }

        btnCadastrarInsumo.disabled = true;
        btnCadastrarInsumo.textContent = 'Cadastrando...';
        alertInsumo.style.display = 'none';

        try {
            const res = await fetch('../index.php?rota=admin&acao=insumo-cadastrar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    item,
                    quantidade,
                    nome_doador: nomeDoador === 'anonimo' ? 'Usuário anônimo' : nomeDoador,
                    id_usuario: idUsuario,
                    telefone
                })
            });
            const json = await res.json().catch(() => ({}));

            if (!res.ok || json.status !== 'sucesso') {
                throw new Error(json.mensagem || 'Não foi possível cadastrar a doação.');
            }

            alertInsumo.className = 'alert-adm success';
            alertInsumo.textContent = json.mensagem;
            alertInsumo.style.display = 'block';
            document.getElementById('insumoIdInsumo').value = `#${json.id_doacao}`;
            formInsumo.reset();
            document.getElementById('insumoIdUsuario').value = 'Anônimo';
            document.getElementById('insumoIdUsuario').dataset.idUser = '0';
            document.getElementById('insumoNome').selectedIndex = 0;
            await carregarDoacoes();
        } catch (error) {
            alertInsumo.className = 'alert-adm';
            alertInsumo.style.background = '#FEF2F2';
            alertInsumo.style.color = '#991B1B';
            alertInsumo.style.border = '1px solid #FECACA';
            alertInsumo.textContent = error.message;
            alertInsumo.style.display = 'block';
        } finally {
            btnCadastrarInsumo.disabled = false;
            btnCadastrarInsumo.textContent = '➕ Cadastrar Item';
        }
    });

    tabInsumosCorpo.addEventListener('click', async event => {
        const botao = event.target.closest('button[data-action]');
        if (!botao) return;

        const id = Number(botao.dataset.id);
        const itemAtual = botao.dataset.item;
        const quantidadeAtual = botao.dataset.quantidade || '';
        const acao = botao.dataset.action;

        if (acao === 'editar') {
            document.getElementById('editarInsumoId').value = String(id);
            document.getElementById('editarInsumoItem').value = itemAtual;
            document.getElementById('editarInsumoQuantidade').value = quantidadeAtual;
            document.getElementById('editarInsumoTelefone').value = botao.dataset.phone || '';

            const idUsuario = Number(botao.dataset.userId || 0);
            const nomeDoador = botao.dataset.nome || 'Anônimo';
            const select = document.getElementById('editarInsumoNome');
            let optionEncontrada = Array.from(select.options).find(option =>
                Number(option.dataset.id || 0) === idUsuario
            );
            if (!optionEncontrada) optionEncontrada = select.options[0];
            select.value = optionEncontrada.value;
            document.getElementById('editarInsumoIdUsuario').value = idUsuario > 0 ? `#${idUsuario}` : 'Anônimo';
            document.getElementById('editarInsumoIdUsuario').dataset.idUser = String(idUsuario);

            document.getElementById('alertEditarInsumo').style.display = 'none';
            document.getElementById('modalEditarInsumo').classList.add('open');
            document.getElementById('editarInsumoItem').focus();
            return;
        }

        if (acao === 'excluir') {
            if (!window.confirm(`Excluir o insumo "${itemAtual}"?`)) return;

            botao.disabled = true;
            try {
                const res = await fetch('../index.php?rota=admin&acao=insumo-excluir', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ id_doacao: id })
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok || json.status !== 'sucesso') {
                    throw new Error(json.mensagem || 'Não foi possível excluir o insumo.');
                }
                alert(json.mensagem);
                await carregarDoacoes();
            } catch (error) {
                alert(error.message);
            }
        }
    });

    filtroTexto.addEventListener('input', renderizarFinanceiras);
    filtroStatus.addEventListener('change', renderizarFinanceiras);

    carregarDoacoes();
});
</script>
</body>
</html>
