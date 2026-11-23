/**
 * PetAlert — Interatividade da Landing Page
 * Gerencia menu responsivo, rolagem suave, destaque de navegação e carregamento dinâmico de pets reais.
 * Suporta execução tanto na raiz (/index.html) quanto dentro de /public/inicio/index.html.
 */

document.addEventListener('DOMContentLoaded', () => {
    // No InfinityFree: /htdocs/public/inicio/index.html => URL tem /public/inicio/
    // Na raiz: /htdocs/index.html => URL NÃO tem /public/
    const isRootPage = !window.location.pathname.includes('/public/');
    const pathPrefix = isRootPage ? 'public/' : '../';

    // 1. Limpar parâmetros antigos de logout/exclusão sem exibir alertas ao entrar
    const params = new URLSearchParams(window.location.search);
    const feedback = params.get('status');

    if (feedback === 'logout' || feedback === 'deletada' || feedback === 'conta_excluida') {
        params.delete('status');
        const novaUrl = window.location.pathname + (params.toString() ? `?${params.toString()}` : '') + window.location.hash;
        window.history.replaceState({}, document.title, novaUrl);
    }

    // 2. Menu Responsivo Mobile (Hambúrguer)
    const menuToggle = document.getElementById('lpMenuToggle');
    const menuNav = document.getElementById('menuNavegacao');
    const navLinks = document.querySelectorAll('.lp-nav-link');
    const header = document.getElementById('headerPrincipal');

    function fecharMenuMobile() {
        if (menuToggle && menuNav) {
            menuToggle.classList.remove('active');
            menuNav.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
        }
    }

    if (menuToggle && menuNav) {
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = menuToggle.classList.toggle('active');
            menuNav.classList.toggle('active');
            menuToggle.setAttribute('aria-expanded', String(isOpen));
        });

        // Fecha ao clicar fora do menu
        document.addEventListener('click', (e) => {
            if (!menuNav.contains(e.target) && !menuToggle.contains(e.target)) {
                fecharMenuMobile();
            }
        });

        // Fecha com tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                fecharMenuMobile();
            }
        });

        // Fecha ao clicar em um link interno
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                fecharMenuMobile();
            });
        });
    }

    // 3. Rolagem Suave com Compensação do Cabeçalho Fixo
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (!targetId || targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                const headerHeight = header ? header.offsetHeight : 70;
                const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                const offsetPosition = elementPosition - headerHeight - 12;

                window.scrollTo({
                    top: Math.max(0, offsetPosition),
                    behavior: 'smooth'
                });
            }
        });
    });

    // 4. Destaque do Link Ativo na Navegação Conforme a Rolagem (IntersectionObserver)
    const secoes = document.querySelectorAll('section[id]');
    if ('IntersectionObserver' in window && secoes.length > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '-20% 0px -70% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    navLinks.forEach(link => {
                        const href = link.getAttribute('href');
                        if (href === `#${id}`) {
                            link.classList.add('active');
                        } else if (href && href.startsWith('#')) {
                            link.classList.remove('active');
                        }
                    });
                }
            });
        }, observerOptions);

        secoes.forEach(sec => observer.observe(sec));
    }

    // 5. Carregamento Dinâmico de Pets Reais da API no Destaque
    const petsContainer = document.getElementById('lpPetsDestaqueContainer');
    const fallbackLogo = isRootPage ? 'public/img/PetAlert-Logo.png' : '../img/PetAlert-Logo.png';
    const linkPets = isRootPage ? 'public/pets/index.html' : '../pets/index.html';

    function formatarCaminhoFoto(caminho) {
        if (!caminho || typeof caminho !== 'string' || caminho.trim() === '') {
            return fallbackLogo;
        }
        caminho = caminho.trim();
        if (caminho.startsWith('http://') || caminho.startsWith('https://') || caminho.startsWith('data:')) {
            return caminho;
        }
        if (caminho.startsWith('../')) {
            return isRootPage ? caminho.replace(/^\.\.\//, '') : caminho;
        }
        if (caminho.startsWith('/')) {
            return (isRootPage ? '' : '..') + caminho;
        }
        return (isRootPage ? '' : '../') + caminho;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function carregarPetsDestaque() {
        if (!petsContainer) return;

        try {
            const apiEndpoint = isRootPage ? 'index.php?rota=pets' : '../index.php?rota=pets';
            const response = await fetch(apiEndpoint, { credentials: 'include' });
            if (!response.ok) return;

            const resultado = await response.json();
            const listaPets = Array.isArray(resultado) ? resultado : (resultado.dados || resultado.pets || []);

            if (Array.isArray(listaPets) && listaPets.length > 0) {
                let cardsHtml = '';
                listaPets.forEach(pet => {
                    const nome = escapeHtml(pet.nome || 'Amiguinho');
                    const especie = escapeHtml(pet.especie || 'Pet');
                    const porte = escapeHtml(pet.porte || 'Médio');
                    const raca = escapeHtml(pet.raca || pet.raça || 'SRD');
                    const idade = escapeHtml(pet.idade || 'Idade não informada');
                    const cidade = escapeHtml(pet.cidade || pet.cidade_doador || 'Local a combinar');
                    const estado = escapeHtml(pet.estado || pet.estado_doador || '');
                    const localTexto = estado ? `${cidade}, ${estado}` : cidade;
                    const descricao = escapeHtml(pet.descricao || pet.motivo_doacao || 'Esperando por um lar carinhoso e responsável.');
                    const fotoUrl = formatarCaminhoFoto(pet.foto);

                    cardsHtml += `
                        <article class="lp-pet-card">
                            <div class="lp-pet-img-wrap">
                                <img src="${fotoUrl}" alt="Foto de ${nome}" class="lp-pet-img" loading="lazy" onerror="this.src='${fallbackLogo}'">
                                <span class="lp-pet-badge">${especie} • ${porte}</span>
                            </div>
                            <div class="lp-pet-body">
                                <h3 class="lp-pet-name">${nome}</h3>
                                <p class="lp-pet-info">${descricao}</p>
                                <div class="lp-pet-meta">
                                    <span>📍 ${localTexto}</span>
                                    <span>🎂 ${idade}</span>
                                    <span>🐕 ${raca}</span>
                                </div>
                                <a href="${linkPets}" class="lp-btn lp-btn-sm lp-btn-outline">Conhecer ${nome}</a>
                            </div>
                        </article>
                    `;
                });

                petsContainer.innerHTML = cardsHtml;
            } else {
                petsContainer.innerHTML = `
                    <p class="lp-pets-empty" style="grid-column:1/-1;text-align:center;color:var(--lp-text-muted);padding:2rem;">
                        Nenhum pet disponível para adoção no momento.
                    </p>
                `;
            }
        } catch (error) {
            console.info('PetAlert Landing Page: exibindo cards padrão.');
        }
    }

    carregarPetsDestaque();
});
