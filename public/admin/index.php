<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'dashboard';
$tituloPagina = 'Dashboard Geral';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo — PetAlert</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <!-- BOAS-VINDAS AO ADMINISTRADOR -->
    <div style="background: linear-gradient(135deg, #FFF9E6, #FFF0F3); border: 1px solid #FFE58F; border-radius: var(--radius-lg); padding: 1.4rem 1.8rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="margin: 0 0 0.35rem; color: #832738; font-size: 1.4rem;">Olá, <?= $adminNome ?>! 👋</h2>
            <p style="margin: 0; color: #6B7280; font-size: 0.95rem;">
                Bem-vindo ao painel central do <strong>PetAlert</strong>. Dados e métricas sincronizados em tempo real com o banco de dados <code>petalert</code>.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="usuarios.php" class="btn-primary" style="font-size:0.85rem;">➕ Novo Usuário</a>
            <a href="veterinarios.php" class="btn-secondary" style="font-size:0.85rem;">🏥 Nova Clínica</a>
        </div>
    </div>

    <!-- CARDS DE MÉTRICAS REAIS DO BANCO -->
    <div class="dashboard-grid" id="cardsMetricas">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total de Usuários</span>
                <span class="stat-icon">👥</span>
            </div>
            <h3 class="stat-value" id="statUsuarios">—</h3>
            <span class="stat-desc" id="statDescUsuarios">Carregando dados...</span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Adotantes</span>
                <span class="stat-icon">🏡</span>
            </div>
            <h3 class="stat-value" id="statAdotantes">—</h3>
            <span class="stat-desc">Perfil de adoção ativo</span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Doadores</span>
                <span class="stat-icon">🤝</span>
            </div>
            <h3 class="stat-value" id="statDoadores">—</h3>
            <span class="stat-desc">Perfil de doação ativo</span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total de Animais</span>
                <span class="stat-icon">🐾</span>
            </div>
            <h3 class="stat-value" id="statPets">—</h3>
            <span class="stat-desc" id="statDescPets">Registrados no sistema</span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Pets Disponíveis</span>
                <span class="stat-icon">🟢</span>
            </div>
            <h3 class="stat-value" id="statDisponiveis" style="color:#059669;">—</h3>
            <span class="stat-desc">Aguardando novo lar</span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Pets Adotados</span>
                <span class="stat-icon">❤️</span>
            </div>
            <h3 class="stat-value" id="statAdotados">—</h3>
            <span class="stat-desc">Adoções bem-sucedidas</span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Denúncias Pendentes</span>
                <span class="stat-icon" style="background:#FEE2E2;">🚨</span>
            </div>
            <h3 class="stat-value" id="statDenuncias" style="color:#DC2626;">—</h3>
            <span class="stat-desc" id="statDescDenuncias">Necessitam de análise</span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Doações Financeiras</span>
                <span class="stat-icon">💰</span>
            </div>
            <h3 class="stat-value" id="statDoacoesValor" style="color:#D97706; font-size:1.6rem;">—</h3>
            <span class="stat-desc" id="statDescDoacoes">Total arrecadado confirmado</span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Clínicas Veterinárias</span>
                <span class="stat-icon">🏥</span>
            </div>
            <h3 class="stat-value" id="statClinicas">—</h3>
            <span class="stat-desc">Parceiras cadastradas</span>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Aceites de Privacidade</span>
                <span class="stat-icon">🔒</span>
            </div>
            <h3 class="stat-value" id="statAceites" style="color:#4F46E5;">—</h3>
            <span class="stat-desc">LGPD – Versão 2026-11-10</span>
        </div>
    </div>

    <!-- SEÇÃO DE GRÁFICOS DINÂMICOS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="content-card" style="margin-bottom: 0;">
            <div class="content-header">
                <div>
                    <h2>📈 Evolução de Cadastros e Adoções</h2>
                    <p>Histórico mensal real registrado no banco</p>
                </div>
            </div>
            <div style="position: relative; height: 260px;">
                <canvas id="chartCadastros"></canvas>
            </div>
        </div>

        <div class="content-card" style="margin-bottom: 0;">
            <div class="content-header">
                <div>
                    <h2>💛 Doações Financeiras (R$)</h2>
                    <p>Valores arrecadados confirmados por mês</p>
                </div>
            </div>
            <div style="position: relative; height: 260px;">
                <canvas id="chartDoacoes"></canvas>
            </div>
        </div>
    </div>

    <!-- ATALHOS RÁPIDOS DE ADMINISTRAÇÃO -->
    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>⚡ Módulos de Gerenciamento Rápido</h2>
                <p>Acesse as funcionalidades administrativas exclusivas</p>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <a href="usuarios.php" class="stat-card" style="text-decoration:none; color:inherit;">
                <span style="font-size:1.8rem; margin-bottom:0.5rem; display:block;">👥</span>
                <strong style="font-size:1.05rem;">Gerenciar Usuários</strong>
                <span style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">CRUD, bloqueio e dados completos</span>
            </a>
            <a href="pets.php" class="stat-card" style="text-decoration:none; color:inherit;">
                <span style="font-size:1.8rem; margin-bottom:0.5rem; display:block;">🐾</span>
                <strong style="font-size:1.05rem;">Gerenciar Animais</strong>
                <span style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Filtros, tutores e status de adoção</span>
            </a>
            <a href="denuncias.php" class="stat-card" style="text-decoration:none; color:inherit;">
                <span style="font-size:1.8rem; margin-bottom:0.5rem; display:block;">🚨</span>
                <strong style="font-size:1.05rem;">Central de Denúncias</strong>
                <span style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Análise, mídias e resolução de casos</span>
            </a>
            <a href="doacoes.php" class="stat-card" style="text-decoration:none; color:inherit;">
                <span style="font-size:1.8rem; margin-bottom:0.5rem; display:block;">💛</span>
                <strong style="font-size:1.05rem;">Consultar Doações</strong>
                <span style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Financeiras (PIX) e de animais</span>
            </a>
            <a href="veterinarios.php" class="stat-card" style="text-decoration:none; color:inherit;">
                <span style="font-size:1.8rem; margin-bottom:0.5rem; display:block;">🏥</span>
                <strong style="font-size:1.05rem;">Clínicas Veterinárias</strong>
                <span style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Cadastro, edição e contatos</span>
            </a>
            <a href="politica-privacidade.php" class="stat-card" style="text-decoration:none; color:inherit;">
                <span style="font-size:1.8rem; margin-bottom:0.5rem; display:block;">🔒</span>
                <strong style="font-size:1.05rem;">Privacidade & LGPD</strong>
                <span style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">Termos oficiais e registros de aceite</span>
            </a>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script src="dashboard.js"></script>
</body>
</html>
