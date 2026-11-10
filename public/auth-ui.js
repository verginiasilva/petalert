/**
 * PetAlert - Controle Global de Interface de Autenticação e Navegação
 * Gerencia a visibilidade do botão 'Entrar', identificação do usuário logado e logout.
 */
document.addEventListener('DOMContentLoaded', () => {
    const obterUsuario = () => {
        try {
            const raw = localStorage.getItem('petalert_usuario') || sessionStorage.getItem('petalert_usuario');
            if (!raw || raw === 'null') return null;

            const usuario = JSON.parse(raw);
            if (!usuario) return null;

            if (!usuario.id_usuario && usuario.id) usuario.id_usuario = usuario.id;
            if (!usuario.id && usuario.id_usuario) usuario.id = usuario.id_usuario;

            return usuario;
        } catch (error) {
            return null;
        }
    };

    const nav = document.querySelector('.site-nav');
    const header = document.querySelector('.site-header');
    const usuario = obterUsuario();

    // 1. Ajuste da Barra de Navegação Principal
    if (nav) {
        // Remove duplicidades prévias de painéis
        const painelExistente = document.getElementById('petalert-user-panel');
        if (painelExistente) painelExistente.remove();

        const entrarExistente = nav.querySelector('a[href*="login"]');

        if (!usuario) {
            // Visitante NÃO Autenticado: Garante a presença do botão "Entrar" visível
            if (!entrarExistente) {
                const entrarLink = document.createElement('a');
                entrarLink.href = '../login/index.html';
                entrarLink.textContent = 'Entrar';
                entrarLink.className = 'nav-entrar-btn';
                entrarLink.id = 'navLoginLink';
                nav.appendChild(entrarLink);
            } else {
                entrarExistente.textContent = 'Entrar';
                entrarExistente.classList.add('nav-entrar-btn');
                entrarExistente.style.display = 'inline-block';
            }

            // Destaca se estiver na página de login
            if (window.location.pathname.includes('/login/')) {
                const loginLink = nav.querySelector('a[href*="login"]');
                if (loginLink) loginLink.classList.add('active');
            }
        } else {
            // Usuário AUTENTICADO: Oculta/Substitui o botão "Entrar" pelas ações do usuário logado
            if (entrarExistente) {
                entrarExistente.style.display = 'none';
            }

            const painel = document.createElement('div');
            painel.id = 'petalert-user-panel';
            painel.className = 'user-nav-panel';

            // Exibe identificação do perfil (Adotante, Doador ou Admin)
            const tipoUsuario = (usuario.tipo_usuario || 'adotante').toLowerCase();
            const isAdmin = Boolean(tipoUsuario === 'admin' || tipoUsuario === 'administrador' || usuario.is_admin);
            const primeiroNome = usuario.nome ? usuario.nome.split(' ')[0] : 'Usuário';
            
            let badgeCor = 'background:#e0e7ff; color:#4338ca;';
            let badgeLabel = tipoUsuario;
            if (isAdmin) {
                badgeCor = 'background:#fee2e2; color:#991b1b; border:1px solid #fecaca;';
                badgeLabel = 'Admin';
            } else if (tipoUsuario === 'doador') {
                badgeCor = 'background:#dcfce7; color:#15803d;';
                badgeLabel = 'Doador';
            }

            // Formata foto do usuário para navbar
            let navFotoHtml = '👤';
            if (usuario.foto) {
                let fotoUrl = usuario.foto;
                if (!fotoUrl.startsWith('http://') && !fotoUrl.startsWith('https://') && !fotoUrl.startsWith('data:')) {
                    fotoUrl = fotoUrl.startsWith('../') ? fotoUrl : '../' + fotoUrl.replace(/^\//, '');
                }
                navFotoHtml = `<img src="${fotoUrl}" alt="Avatar" style="width:26px; height:26px; border-radius:50%; object-fit:cover; margin-right:6px; border:1px solid #fed7aa;" onerror="this.outerHTML='👤'">`;
            }

            painel.innerHTML = `
                <a href="../perfil/index.html" class="user-nav-label" title="Acessar Meu Perfil e Configurações" style="text-decoration:none; display:inline-flex; align-items:center;">
                    ${navFotoHtml} Olá, <strong>${primeiroNome}</strong>
                    <span style="font-size:0.72rem; ${badgeCor} padding:2px 7px; border-radius:99px; text-transform:uppercase; font-weight:800; margin-left:4px;">
                        ${badgeLabel}
                    </span>
                </a>
                ${isAdmin ? `
                    <a href="../admin/index.php" class="nav-admin-btn" style="background:linear-gradient(135deg, #ede9fe, #ddd6fe); color:#5b21b6; font-size:0.88rem; font-weight:800; padding:0.4rem 0.75rem; border-radius:8px; border:1px solid #c4b5fd; text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="Painel Administrativo — PetAlert">
                        Painel Administrativo
                    </a>
                ` : ''}
                ${tipoUsuario === 'doador' ? `
                    <a href="../pets/index.html#meus" class="nav-meus-pets-btn" style="background:linear-gradient(135deg, var(--primary-soft, #ffedd5), #fef3c7); color:var(--primary-dark, #c2410c); font-size:0.88rem; font-weight:800; padding:0.4rem 0.75rem; border-radius:8px; border:1px solid #fed7aa; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                        🐾 Meus Pets
                    </a>
                ` : ''}
                <a href="../perfil/index.html" class="nav-perfil-btn" style="color:var(--text); font-size:0.88rem; font-weight:700; padding:0.4rem 0.6rem; border-radius:8px; border:1px solid var(--border);">
                    Meu Perfil
                </a>
                <button type="button" class="btn-nav-sair" id="btnNavSair" title="Encerrar sessão no PetAlert">
                    Sair
                </button>
            `;

            nav.appendChild(painel);

            // Requisito 17: Se for ADOTANTE, não mostrar link de Cadastrar Pet / Doar Pet
            if (tipoUsuario === 'adotante') {
                const doarLink = nav.querySelector('a[href*="doarPet"]');
                if (doarLink) {
                    doarLink.style.display = 'none';
                }
            }

            // Oculta links/botões restritos de administração para quem NÃO é admin
            if (!isAdmin) {
                document.querySelectorAll('a[href*="pessoas"], a[href*="admin/"], .somente-admin, .admin-only').forEach(el => {
                    el.style.display = 'none';
                });
            }

            const sairBtn = painel.querySelector('#btnNavSair');
            if (sairBtn) {
                sairBtn.addEventListener('click', async () => {
                    if (!confirm('Deseja realmente sair da sua conta PetAlert?')) {
                        return;
                    }
                    try {
                        await fetch('../index.php?rota=auth&acao=logout', { method: 'POST' });
                    } catch (e) {
                        console.error('Erro ao chamar logout no servidor:', e);
                    }
                    localStorage.removeItem('petalert_usuario');
                    sessionStorage.removeItem('petalert_usuario');
                    window.location.href = '../inicio/index.html?status=logout';
                });
            }
        }
    }

    // 2. Proteção do fluxo da página "Doar Pet": Exigir autenticação e perfil DOADOR
    if (window.location.pathname.includes('/doarPet/')) {
        if (usuario && (usuario.tipo_usuario || '').toLowerCase() === 'adotante') {
            alert('Atenção: Seu perfil cadastrado é ADOTANTE.\n\nO cadastro de animais para doação é exclusivo para usuários com perfil DOADOR.');
            window.location.href = '../pets/index.html';
            return;
        }

        const btnPublicar = document.getElementById('publicar');
        const modalAuth = document.getElementById('modalAuthDoarPet');
        if (btnPublicar && !usuario) {
            btnPublicar.addEventListener('click', (e) => {
                const userCheck = obterUsuario();
                if (!userCheck) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    if (modalAuth) {
                        modalAuth.style.display = 'flex';
                    } else {
                        window.location.href = `../login/index.html?redirect=${encodeURIComponent(window.location.href)}`;
                    }
                }
            }, true);
        }
    }

    // 3. Menu Hambúrguer Responsivo para Celulares
    if (header && nav && !document.getElementById('mobileMenuToggle')) {
        const toggleBtn = document.createElement('button');
        toggleBtn.id = 'mobileMenuToggle';
        toggleBtn.type = 'button';
        toggleBtn.className = 'mobile-menu-toggle';
        toggleBtn.setAttribute('aria-label', 'Abrir menu de navegação');
        toggleBtn.innerHTML = '☰';

        toggleBtn.addEventListener('click', () => {
            nav.classList.toggle('mobile-open');
            toggleBtn.innerHTML = nav.classList.contains('mobile-open') ? '✕' : '☰';
        });

        // Insere o botão hambúrguer ao lado da marca no cabeçalho
        const brand = header.querySelector('.brand');
        if (brand && brand.nextSibling) {
            header.insertBefore(toggleBtn, nav);
        } else {
            header.appendChild(toggleBtn);
        }
    }
});
