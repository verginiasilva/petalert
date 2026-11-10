<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'relatorios';
$tituloPagina = 'Relatórios Executivos & Auditoria';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios — PetAlert</title>
    <link rel="stylesheet" href="style.css">
    <style>
        @media print {
            .admin-sidebar, .admin-topbar, .btn-print-hide { display: none !important; }
            .admin-main { margin-left: 0 !important; }
            body { background: #fff !important; }
            .content-card { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>📈 Relatórios Executivos & Estatísticas da Plataforma</h2>
                <p>Consolidação oficial de dados, volumetria de proteção animal e demonstrativos para apresentação institucional.</p>
            </div>
            <button type="button" class="btn-primary btn-print-hide" onclick="window.print();">🖨️ Imprimir / Salvar PDF</button>
        </div>

        <div style="background:#FFFDF7; border:1px solid #FDE68A; border-radius:var(--radius-md); padding:1rem 1.4rem; margin-bottom:2rem; font-size:0.92rem; color:#92400E;">
            <strong>Emissor do Relatório:</strong> Administrador <?= $adminNome ?> | <strong>Data de Emissão:</strong> <?= date('d/m/Y H:i:s') ?> | <strong>Banco de Dados:</strong> <code>petalert</code>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.5rem; margin-bottom:2rem;">
            <!-- BLOCO 1: USUÁRIOS -->
            <div style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:1.2rem; background:#fff;">
                <h3 style="margin-top:0; color:var(--primary-dark); font-size:1.1rem; border-bottom:1px solid #F3EDE2; padding-bottom:0.5rem;">👥 Quadro Geral de Usuários</h3>
                <ul style="list-style:none; padding:0; margin:0; line-height:2.2; font-size:0.95rem;">
                    <li style="display:flex; justify-content:space-between;"><span>Total de Usuários Cadastrados:</span><strong id="relTotalUsers">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Adotantes Ativos:</span><strong id="relAdotantes">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Doadores Cadastrados:</span><strong id="relDoadores">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Administradores Oficiais:</span><strong id="relAdmins">—</strong></li>
                </ul>
            </div>

            <!-- BLOCO 2: ANIMAIS -->
            <div style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:1.2rem; background:#fff;">
                <h3 style="margin-top:0; color:#059669; font-size:1.1rem; border-bottom:1px solid #F3EDE2; padding-bottom:0.5rem;">🐾 Proteção Animal & Adoções</h3>
                <ul style="list-style:none; padding:0; margin:0; line-height:2.2; font-size:0.95rem;">
                    <li style="display:flex; justify-content:space-between;"><span>Total de Animais Cadastrados:</span><strong id="relTotalPets">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Animais Disponíveis para Adoção:</span><strong id="relPetsDisp" style="color:#059669;">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Animais Adotados / Concluídos:</span><strong id="relPetsAdot" style="color:#E05370;">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Taxa Geral de Efetivação:</span><strong id="relTaxaAdocao">—</strong></li>
                </ul>
            </div>

            <!-- BLOCO 3: DENÚNCIAS & IMPACTO -->
            <div style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:1.2rem; background:#fff;">
                <h3 style="margin-top:0; color:#DC2626; font-size:1.1rem; border-bottom:1px solid #F3EDE2; padding-bottom:0.5rem;">🚨 Central de Denúncias</h3>
                <ul style="list-style:none; padding:0; margin:0; line-height:2.2; font-size:0.95rem;">
                    <li style="display:flex; justify-content:space-between;"><span>Total de Denúncias Recebidas:</span><strong id="relTotalDen">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Casos Pendentes / Em Análise:</span><strong id="relDenPend" style="color:#DC2626;">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Casos Resolvidos:</span><strong id="relDenRes" style="color:#059669;">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Índice de Resolução:</span><strong id="relIndiceResolucao">—</strong></li>
                </ul>
            </div>

            <!-- BLOCO 4: ARRECADAÇÃO E DOAÇÕES -->
            <div style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:1.2rem; background:#fff;">
                <h3 style="margin-top:0; color:#D97706; font-size:1.1rem; border-bottom:1px solid #F3EDE2; padding-bottom:0.5rem;">💰 Apoio Financeiro & Doações</h3>
                <ul style="list-style:none; padding:0; margin:0; line-height:2.2; font-size:0.95rem;">
                    <li style="display:flex; justify-content:space-between;"><span>Total Arrecadado Confirmado:</span><strong id="relValorTotal" style="color:#D97706;">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Transações Financeiras Registradas:</span><strong id="relQtdDoacoes">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Doações de Animais Realizadas:</span><strong id="relDoaPets">—</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Clínicas Veterinárias Parceiras:</span><strong id="relClinicas">—</strong></li>
                </ul>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('../index.php?rota=admin&acao=dashboard');
        const json = await res.json().catch(() => ({}));
        if (!res.ok || json.status !== 'sucesso') return;
        const d = json.dados || {};

        document.getElementById('relTotalUsers').textContent = d.total_usuarios || 0;
        document.getElementById('relAdotantes').textContent = d.total_adotantes || 0;
        document.getElementById('relDoadores').textContent = d.total_doadores || 0;
        document.getElementById('relAdmins').textContent = d.total_admins || 0;

        document.getElementById('relTotalPets').textContent = d.total_pets || 0;
        document.getElementById('relPetsDisp').textContent = d.pets_disponiveis || 0;
        document.getElementById('relPetsAdot').textContent = d.pets_adotados || 0;
        const taxa = (d.total_pets > 0) ? Math.round(((d.pets_adotados || 0) / d.total_pets) * 100) : 0;
        document.getElementById('relTaxaAdocao').textContent = `${taxa}%`;

        document.getElementById('relTotalDen').textContent = d.total_denuncias || 0;
        document.getElementById('relDenPend').textContent = d.denuncias_pendentes || 0;
        document.getElementById('relDenRes').textContent = d.denuncias_resolvidas || 0;
        const indRes = (d.total_denuncias > 0) ? Math.round(((d.denuncias_resolvidas || 0) / d.total_denuncias) * 100) : 0;
        document.getElementById('relIndiceResolucao').textContent = `${indRes}%`;

        const valor = Number(d.soma_doacoes_financeiras || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        document.getElementById('relValorTotal').textContent = valor;
        document.getElementById('relQtdDoacoes').textContent = d.total_doacoes_financeiras || 0;
        document.getElementById('relDoaPets').textContent = d.total_doacoes_animais || 0;
        document.getElementById('relClinicas').textContent = d.total_clinicas || 0;
    } catch (e) {
        console.error('Erro ao carregar relatório:', e);
    }
});
</script>
</body>
</html>
