<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'historico';
$tituloPagina = 'Histórico Administrativo & Auditoria';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico Administrativo — PetAlert</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>📜 Histórico de Ações Administrativas (tb_historico_admin)</h2>
                <p>Auditoria em tempo real de todas as operações realizadas pelos administradores (Maria Eduarda, Miguel Lima e Verginia).</p>
            </div>
        </div>

        <div class="toolbar-wrap">
            <select id="filtroModuloHist" class="toolbar-select">
                <option value="">Todos os módulos</option>
                <option value="usuarios">Usuários</option>
                <option value="animais">Animais</option>
                <option value="denuncias">Denúncias</option>
                <option value="veterinarios">Clínicas Veterinárias</option>
                <option value="sistema">Sistema & Segurança</option>
            </select>
            <span id="contadorHist" style="font-weight:700; color:var(--text-muted); font-size:0.88rem; margin-left:auto;">Carregando...</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID Log</th>
                        <th>Data & Horário</th>
                        <th>Administrador Responsável</th>
                        <th>Módulo</th>
                        <th>Ação Realizada</th>
                        <th>Registro Afetado</th>
                        <th>Detalhes da Operação</th>
                        <th>IP Origem</th>
                    </tr>
                </thead>
                <tbody id="tabelaHistorico">
                    <tr><td colspan="8" style="text-align:center; padding:2rem;">Carregando histórico administrativo...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    let logs = [];
    const tabela = document.getElementById('tabelaHistorico');
    const filtroModulo = document.getElementById('filtroModuloHist');
    const contador = document.getElementById('contadorHist');

    function sanitizar(txt) {
        const d = document.createElement('div');
        d.textContent = txt ?? '';
        return d.innerHTML;
    }

    async function carregarHistorico() {
        try {
            const res = await fetch('../index.php?rota=admin&acao=historico');
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.status !== 'sucesso') {
                tabela.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#DC2626;">Erro ao carregar histórico.</td></tr>';
                return;
            }
            logs = Array.isArray(json.dados) ? json.dados : [];
            renderizar();
        } catch (e) {
            tabela.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#DC2626;">Erro de conexão com o servidor.</td></tr>';
        }
    }

    function renderizar() {
        const mod = (filtroModulo.value || '').trim().toLowerCase();
        const filtrados = logs.filter(l => {
            if (!mod) return true;
            return (l.modulo || '').toLowerCase() === mod;
        });

        contador.textContent = `${filtrados.length} registro(s) de auditoria`;

        if (!filtrados.length) {
            tabela.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhuma atividade registrada para este filtro.</td></tr>';
            return;
        }

        tabela.innerHTML = filtrados.map(l => {
            const modNorm = (l.modulo || '').toLowerCase();
            let badgeMod = `<span class="badge badge-info">${sanitizar(l.modulo)}</span>`;
            if (modNorm === 'usuarios') badgeMod = `<span class="badge badge-admin">👥 Usuários</span>`;
            else if (modNorm === 'animais') badgeMod = `<span class="badge badge-success">🐾 Animais</span>`;
            else if (modNorm === 'denuncias') badgeMod = `<span class="badge badge-danger">🚨 Denúncias</span>`;
            else if (modNorm === 'veterinarios') badgeMod = `<span class="badge badge-warning">🏥 Clínicas</span>`;
            else if (modNorm === 'sistema') badgeMod = `<span class="badge badge-info">⚙️ Sistema</span>`;

            return `
                <tr>
                    <td><strong>#${l.id_log}</strong></td>
                    <td style="font-size:0.85rem; white-space:nowrap;">${sanitizar(l.data_hora || '—')}</td>
                    <td>
                        <strong>${sanitizar(l.nome_admin)}</strong><br>
                        <small style="color:var(--text-muted);">Admin ID #${l.id_usuario}</small>
                    </td>
                    <td>${badgeMod}</td>
                    <td><strong>${sanitizar(l.acao)}</strong></td>
                    <td>${l.id_registro ? `<span class="badge badge-info">ID #${l.id_registro}</span>` : '—'}</td>
                    <td style="font-size:0.88rem; color:#374151;">${sanitizar(l.detalhes || 'Sem detalhes adicionais')}</td>
                    <td style="font-size:0.8rem; color:var(--text-muted); font-family:monospace;">${sanitizar(l.ip || '—')}</td>
                </tr>
            `;
        }).join('');
    }

    filtroModulo.addEventListener('change', renderizar);
    carregarHistorico();
});
</script>
</body>
</html>
