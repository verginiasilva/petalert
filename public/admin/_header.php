<?php
$paginaAtual = $paginaAtual ?? 'dashboard';
$adminNome = htmlspecialchars($adminUsuario['nome'] ?? 'Administrador', ENT_QUOTES, 'UTF-8');
$adminEmail = htmlspecialchars($adminUsuario['email'] ?? '', ENT_QUOTES, 'UTF-8');
$iniciais = mb_substr($adminNome, 0, 1, 'UTF-8');
if (strpos($adminNome, ' ') !== false) {
    $partes = explode(' ', $adminNome);
    $iniciais = mb_substr($partes[0], 0, 1, 'UTF-8') . mb_substr(end($partes), 0, 1, 'UTF-8');
}
?>
<!-- SIDEBAR LATERAL COM OS 11 ITENS ADMINISTRATIVOS -->
<aside class="admin-sidebar" id="adminSidebar">
    <a class="sidebar-brand" href="index.php">
        <img src="../img/PetAlert-Logo.png" alt="Logo PetAlert">
        <span class="sidebar-brand-text">PetAlert</span>
        <span class="sidebar-brand-badge">ADMIN</span>
    </a>

    <div class="sidebar-user">
        <div class="sidebar-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name" title="<?= $adminNome ?>"><?= $adminNome ?></div>
            <div class="sidebar-user-role">Administrador Oficial</div>
        </div>
    </div>

    <nav class="sidebar-menu" aria-label="Menu administrativo">
        <div class="menu-label">Principal</div>
        <a href="index.php" class="sidebar-item <?= $paginaAtual === 'dashboard' ? 'active' : '' ?>">
            <span class="item-icon">📊</span>
            <span>1. Dashboard</span>
        </a>

        <div class="menu-label">Gestão da Plataforma</div>
        <a href="usuarios.php" class="sidebar-item <?= $paginaAtual === 'usuarios' ? 'active' : '' ?>">
            <span class="item-icon">👥</span>
            <span>2. Usuários</span>
        </a>
        <a href="pets.php" class="sidebar-item <?= $paginaAtual === 'pets' ? 'active' : '' ?>">
            <span class="item-icon">🐾</span>
            <span>3. Animais</span>
        </a>
        <a href="denuncias.php" class="sidebar-item <?= $paginaAtual === 'denuncias' ? 'active' : '' ?>">
            <span class="item-icon">🚨</span>
            <span>4. Denúncias</span>
        </a>
        <a href="doacoes.php" class="sidebar-item <?= $paginaAtual === 'doacoes' ? 'active' : '' ?>">
            <span class="item-icon">💛</span>
            <span>5. Doações</span>
        </a>
        <a href="veterinarios.php" class="sidebar-item <?= $paginaAtual === 'veterinarios' ? 'active' : '' ?>">
            <span class="item-icon">🏥</span>
            <span>6. Clínicas Veterinárias</span>
        </a>

        <div class="menu-label">Auditoria & Segurança</div>
        <a href="relatorios.php" class="sidebar-item <?= $paginaAtual === 'relatorios' ? 'active' : '' ?>">
            <span class="item-icon">📈</span>
            <span>7. Relatórios</span>
        </a>
        <a href="historico.php" class="sidebar-item <?= $paginaAtual === 'historico' ? 'active' : '' ?>">
            <span class="item-icon">📜</span>
            <span>8. Histórico</span>
        </a>
        <a href="politica-privacidade.php" class="sidebar-item <?= $paginaAtual === 'politica-privacidade' ? 'active' : '' ?>">
            <span class="item-icon">🔒</span>
            <span>9. Política de Privacidade</span>
        </a>
        <a href="configuracoes.php" class="sidebar-item <?= $paginaAtual === 'configuracoes' ? 'active' : '' ?>">
            <span class="item-icon">⚙️</span>
            <span>10. Configurações</span>
        </a>

        <div class="menu-label">Sessão</div>
        <a href="logout.php" class="sidebar-item" style="color:#DC2626;" onclick="return confirm('Deseja realmente encerrar a sessão administrativa?');">
            <span class="item-icon">🚪</span>
            <span>11. Sair</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        © 2026 PetAlert Admin<br>Área Exclusiva de Gestão
    </div>
</aside>

<!-- ESTRUTURA PRINCIPAL (MAIN WRAPPER) -->
<div class="admin-main">
    <header class="admin-topbar">
        <div class="topbar-left">
            <button type="button" class="topbar-toggle" id="btnToggleSidebar" aria-label="Abrir menu lateral">☰</button>
            <h1 class="topbar-title"><?= htmlspecialchars($tituloPagina ?? 'Painel Administrativo', ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <div class="topbar-right">
            <a href="../inicio/index.html" class="btn-site-public" target="_blank" title="Ver site público do PetAlert">
                <span>🌐</span> Ver Site Público
            </a>
            <div style="font-size:0.85rem; font-weight:700; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:8px; height:8px; background:#10B981; border-radius:50%;"></span>
                Online: <strong style="color:var(--text-main);"><?= $adminNome ?></strong>
            </div>
        </div>
    </header>
    <main class="admin-body">
