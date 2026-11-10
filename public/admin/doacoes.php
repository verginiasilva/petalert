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
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    let doacoesFin = [];
    let doacoesAnim = [];

    const tabFin = document.getElementById('tabFinanceiras');
    const tabAnim = document.getElementById('tabAnimais');
    const pnlFin = document.getElementById('painelFinanceiras');
    const pnlAnim = document.getElementById('painelAnimais');

    const tabFinCorpo = document.getElementById('tabelaFinanceiras');
    const tabAnimCorpo = document.getElementById('tabelaAnimais');
    const contFin = document.getElementById('contFin');
    const contAnim = document.getElementById('contAnim');
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
        pnlFin.style.display = 'block';
        pnlAnim.style.display = 'none';
    });

    tabAnim.addEventListener('click', () => {
        tabAnim.classList.add('active');
        tabFin.classList.remove('active');
        pnlFin.style.display = 'none';
        pnlAnim.style.display = 'block';
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

            contFin.textContent = doacoesFin.length;
            contAnim.textContent = doacoesAnim.length;

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

    filtroTexto.addEventListener('input', renderizarFinanceiras);
    filtroStatus.addEventListener('change', renderizarFinanceiras);

    carregarDoacoes();
});
</script>
</body>
</html>
