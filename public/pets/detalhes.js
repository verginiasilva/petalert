/**
 * PetAlert — Visualização Detalhada, Galeria e Biografia do Pet
 */

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const petId = params.get('id') || params.get('id_pet');
    const container = document.getElementById('petDetalhesContainer');
    const breadcrumbNome = document.getElementById('breadcrumbNomePet');

    // Lightbox modal elements
    const lightboxModal = document.getElementById('lightboxModal');
    const lightboxImg = document.getElementById('lightboxImg');
    const btnFecharLightbox = document.getElementById('btnFecharLightbox');

    if (!petId) {
        window.location.href = 'index.html';
        return;
    }

    let petAtual = null;
    let fotosPet = [];
    let indiceFotoAtual = 0;

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatarCaminhoFoto(caminho) {
        if (!caminho || typeof caminho !== 'string' || caminho.trim() === '') {
            return '../img/PetAlert-Logo.png';
        }
        caminho = caminho.trim();
        if (caminho.startsWith('http://') || caminho.startsWith('https://') || caminho.startsWith('data:')) {
            return caminho;
        }
        if (caminho.startsWith('../')) {
            return caminho;
        }
        if (caminho.startsWith('/')) {
            return '..' + caminho;
        }
        return '../' + caminho;
    }

    function obterUsuario() {
        try {
            const raw = localStorage.getItem('petalert_usuario') || sessionStorage.getItem('petalert_usuario');
            if (!raw || raw === 'null') return null;
            const parsed = JSON.parse(raw);
            if (!parsed) return null;
            if (!parsed.id_usuario && parsed.id) parsed.id_usuario = parsed.id;
            if (!parsed.id && parsed.id_usuario) parsed.id = parsed.id_usuario;
            return parsed;
        } catch (e) {
            return null;
        }
    }

    // Gerenciamento da Galeria de Fotos
    function atualizarFotoDestaque(index, animar = true) {
        if (fotosPet.length === 0) return;
        if (index < 0) index = fotosPet.length - 1;
        if (index >= fotosPet.length) index = 0;

        indiceFotoAtual = index;

        const mainImg = document.getElementById('petGaleriaPrincipalImg');
        const badgeContador = document.getElementById('galeriaContadorBadge');
        const thumbs = document.querySelectorAll('.pet-gallery-thumb-item');

        if (mainImg) {
            if (animar) {
                mainImg.style.opacity = '0.4';
                setTimeout(() => {
                    mainImg.src = fotosPet[indiceFotoAtual];
                    mainImg.style.opacity = '1';
                }, 120);
            } else {
                mainImg.src = fotosPet[indiceFotoAtual];
            }
        }

        if (badgeContador) {
            badgeContador.textContent = `Foto ${indiceFotoAtual + 1} de ${fotosPet.length}`;
        }

        // Atualiza a borda da miniatura ativa
        thumbs.forEach((th, i) => {
            if (i === indiceFotoAtual) {
                th.classList.add('active');
                th.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                th.classList.remove('active');
            }
        });
    }

    function abrirLightbox(src) {
        if (lightboxModal && lightboxImg) {
            lightboxImg.src = src || fotosPet[indiceFotoAtual];
            lightboxModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function fecharLightbox() {
        if (lightboxModal) {
            lightboxModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    if (btnFecharLightbox) {
        btnFecharLightbox.addEventListener('click', fecharLightbox);
    }
    if (lightboxModal) {
        lightboxModal.addEventListener('click', (e) => {
            if (e.target === lightboxModal) fecharLightbox();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (lightboxModal && lightboxModal.style.display === 'flex') {
            if (e.key === 'Escape') fecharLightbox();
            if (e.key === 'ArrowRight') {
                atualizarFotoDestaque(indiceFotoAtual + 1);
                lightboxImg.src = fotosPet[indiceFotoAtual];
            }
            if (e.key === 'ArrowLeft') {
                atualizarFotoDestaque(indiceFotoAtual - 1);
                lightboxImg.src = fotosPet[indiceFotoAtual];
            }
        } else if (fotosPet.length > 1) {
            if (e.key === 'ArrowRight') atualizarFotoDestaque(indiceFotoAtual + 1);
            if (e.key === 'ArrowLeft') atualizarFotoDestaque(indiceFotoAtual - 1);
        }
    });

    async function carregarDetalhesPet() {
        try {
            const response = await fetch(`../index.php?rota=pets&id=${petId}`, { credentials: 'include' });
            if (!response.ok) {
                container.innerHTML = `
                    <div style="text-align:center; padding:3rem 1rem;">
                        <span style="font-size:3rem;">🐾</span>
                        <h2 style="margin:1rem 0 0.5rem 0;">Pet não encontrado</h2>
                        <p style="color:var(--muted); margin-bottom:1.5rem;">O animal que você procura pode já ter sido adotado ou o link está incorreto.</p>
                        <a href="index.html" class="primary-btn">Voltar para a lista de pets</a>
                    </div>
                `;
                return;
            }

            const pet = await response.json();
            petAtual = pet;

            const nome = pet.nome || 'Amiguinho sem nome';
            const especie = pet.especie || 'Pet';
            const raca = pet.raca || pet.raça || 'SRD (Sem raça definida)';
            const idade = pet.idade ? `${pet.idade} ano(s)` : 'Idade não informada';
            const porte = pet.porte || 'Médio';
            const peso = pet.peso && parseFloat(pet.peso) > 0 ? `${pet.peso} kg` : 'Não informado';
            const cor = pet.cor || 'Não informada';
            const sexo = pet.sexo || 'Macho';
            const personalidade = pet.personalidade || 'Dócil, carinhoso, brincalhão e amoroso';
            const motivoDoacao = pet.motivo_doacao || 'Doação responsável para encontrar uma família dedicada.';
            const biografia = pet.descricao || 'Este animalzinho está à espera de um lar com muito carinho, respeito e acolhimento.';
            const statusAdocao = (pet.status_adocao || 'disponivel').toLowerCase();
            const isDoado = (statusAdocao === 'doado');

            const nomeDoador = pet.nome_responsavel || 'Protetor Responsável';
            const cidadeDoador = pet.cidade || 'Local a combinar';
            const estadoDoador = pet.estado ? ` - ${pet.estado}` : '';
            const localizacao = `${cidadeDoador}${estadoDoador}`;

            // Atualiza o título da aba e o breadcrumb
            document.title = `PetAlert | ${nome} (${especie} para Adoção)`;
            if (breadcrumbNome) {
                breadcrumbNome.textContent = nome;
            }

            // Normaliza a lista de fotos (até 5)
            let rawFotos = Array.isArray(pet.fotos) && pet.fotos.length > 0 ? pet.fotos : [pet.foto || '../img/PetAlert-Logo.png'];
            fotosPet = rawFotos.filter(Boolean).map(formatarCaminhoFoto);
            if (fotosPet.length === 0) {
                fotosPet = ['../img/PetAlert-Logo.png'];
            }

            // Renderização da página na ordem exata solicitada:
            // 1. Galeria de fotos em destaque
            // 2. Nome do animal e informações principais
            // 3. Biografia e história do pet
            // 4. Características e informações adicionais
            // 5. Motivo da doação
            // 6. Informações do doador
            // 7. Botão para demonstrar interesse na adoção ou conversar com o doador

            container.innerHTML = `
                <!-- 1. GALERIA DE FOTOS EM DESTAQUE -->
                <section class="pet-gallery-section" aria-label="Galeria de fotos de ${escapeHtml(nome)}">
                    <div class="pet-gallery-stage" id="petGaleriaStage">
                        <span class="gallery-counter-badge" id="galeriaContadorBadge">Foto 1 de ${fotosPet.length}</span>
                        
                        <button type="button" class="btn-gallery-zoom" id="btnAmpliarFoto" title="Ampliar foto em tela cheia">
                            🔍 <span>Ampliar</span>
                        </button>

                        ${fotosPet.length > 1 ? `
                            <button type="button" class="gallery-nav-btn prev" id="btnGaleriaPrev" aria-label="Foto anterior">❮</button>
                            <button type="button" class="gallery-nav-btn next" id="btnGaleriaNext" aria-label="Próxima foto">❯</button>
                        ` : ''}

                        <img 
                            src="${escapeHtml(fotosPet[0])}" 
                            alt="Foto de ${escapeHtml(nome)}" 
                            class="pet-gallery-main-img" 
                            id="petGaleriaPrincipalImg"
                            onerror="this.src='../img/PetAlert-Logo.png';"
                        >
                    </div>

                    <!-- Miniaturas Clicáveis para Navegação -->
                    ${fotosPet.length > 1 ? `
                        <div class="pet-gallery-thumbs" id="petGaleriaThumbs">
                            ${fotosPet.map((foto, idx) => `
                                <div class="pet-gallery-thumb-item ${idx === 0 ? 'active' : ''}" data-index="${idx}" title="Ver foto ${idx + 1}">
                                    <img src="${escapeHtml(foto)}" alt="Miniatura ${idx + 1}" class="pet-gallery-thumb-img" onerror="this.src='../img/PetAlert-Logo.png';">
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                </section>

                <!-- 2. NOME DO ANIMAL E INFORMAÇÕES PRINCIPAIS -->
                <div class="pet-header-info">
                    <div class="pet-title-block">
                        <h1>${escapeHtml(nome)}</h1>
                        <div class="pet-badges-row">
                            <span class="badge-info">🐾 ${escapeHtml(especie)}</span>
                            <span class="badge-info">🚻 ${escapeHtml(sexo)}</span>
                            <span class="badge-info">🏷️ ${escapeHtml(raca)}</span>
                            <span class="badge-info">🎂 ${escapeHtml(idade)}</span>
                            <span class="badge-info">📏 Porte ${escapeHtml(porte)}</span>
                        </div>
                    </div>

                    <div>
                        ${isDoado ? `
                            <span class="badge-status-doado">🐾 Adoção Concluída</span>
                        ` : `
                            <span class="badge-status-disponivel">✓ Disponível para Adoção</span>
                        `}
                    </div>
                </div>

                <!-- 3. BIOGRAFIA E HISTÓRIA DO PET ("Conheça o pet") -->
                <section class="pet-section-block">
                    <h2 class="pet-section-title">
                        <span class="icon">📖</span>
                        <span>Conheça a história de ${escapeHtml(nome)}</span>
                    </h2>
                    <div class="pet-bio-card">
                        <p class="pet-bio-text">${escapeHtml(biografia)}</p>
                    </div>
                </section>

                <!-- 4. CARACTERÍSTICAS E INFORMAÇÕES ADICIONAIS -->
                <section class="pet-section-block">
                    <h2 class="pet-section-title">
                        <span class="icon">📋</span>
                        <span>Características e Detalhes</span>
                    </h2>

                    <div class="pet-features-grid">
                        <div class="pet-feature-item">
                            <div class="pet-feature-label">🐾 Espécie</div>
                            <div class="pet-feature-value">${escapeHtml(especie)}</div>
                        </div>

                        <div class="pet-feature-item">
                            <div class="pet-feature-label">🏷️ Raça</div>
                            <div class="pet-feature-value">${escapeHtml(raca)}</div>
                        </div>

                        <div class="pet-feature-item">
                            <div class="pet-feature-label">🚻 Sexo</div>
                            <div class="pet-feature-value">${escapeHtml(sexo)}</div>
                        </div>

                        <div class="pet-feature-item">
                            <div class="pet-feature-label">🎂 Idade</div>
                            <div class="pet-feature-value">${escapeHtml(idade)}</div>
                        </div>

                        <div class="pet-feature-item">
                            <div class="pet-feature-label">📏 Porte</div>
                            <div class="pet-feature-value">${escapeHtml(porte)}</div>
                        </div>

                        <div class="pet-feature-item">
                            <div class="pet-feature-label">⚖️ Peso</div>
                            <div class="pet-feature-value">${escapeHtml(peso)}</div>
                        </div>

                        <div class="pet-feature-item">
                            <div class="pet-feature-label">🎨 Cor da Pelagem</div>
                            <div class="pet-feature-value">${escapeHtml(cor)}</div>
                        </div>

                        <div class="pet-feature-item">
                            <div class="pet-feature-label">📍 Localização</div>
                            <div class="pet-feature-value">${escapeHtml(localizacao)}</div>
                        </div>
                    </div>

                    <!-- Personalidade e Comportamento -->
                    <div class="pet-personality-card">
                        <strong>💛 Personalidade e Comportamento do Pet:</strong>
                        <p>${escapeHtml(personalidade)}</p>
                    </div>
                </section>

                <!-- 5. MOTIVO DA DOAÇÃO -->
                <section class="pet-section-block">
                    <h2 class="pet-section-title">
                        <span class="icon">🤝</span>
                        <span>Motivo da Doação</span>
                    </h2>
                    <div class="pet-donation-reason-card">
                        <p>${escapeHtml(motivoDoacao)}</p>
                    </div>
                </section>

                <!-- 6. INFORMAÇÕES DO DOADOR -->
                <section class="pet-section-block">
                    <h2 class="pet-section-title">
                        <span class="icon">👤</span>
                        <span>Informações do Responsável</span>
                    </h2>
                    <div class="pet-donor-card">
                        <div class="pet-donor-profile">
                            <div class="donor-avatar">🐾</div>
                            <div class="donor-info">
                                <h3>${escapeHtml(nomeDoador)} <span class="donor-badge">Doador Verificado</span></h3>
                                <p>📍 ${escapeHtml(localizacao)}</p>
                            </div>
                        </div>
                        <div style="font-size:0.88rem; color:var(--muted); line-height:1.4;">
                            Intermediado com segurança pela plataforma PetAlert.
                        </div>
                    </div>
                </section>

                <!-- 7. BOTÃO PARA DEMONSTRAR INTERESSE NA ADOÇÃO OU CONVERSAR COM O DOADOR -->
                <div class="pet-actions-area">
                    ${!isDoado ? `
                        <button type="button" class="btn-adotar-destaque" id="btnConversarDoador">
                            <span>💬 Conversar com o doador sobre a adoção de ${escapeHtml(nome)}</span>
                        </button>
                    ` : `
                        <button type="button" class="btn-adotar-destaque" disabled style="opacity:0.6; cursor:not-allowed; background:#9ca3af; box-shadow:none;">
                            <span>🐾 Este pet já foi adotado</span>
                        </button>
                    `}
                    <a href="index.html" class="btn-voltar-pets">
                        <span>← Ver outros animais</span>
                    </a>
                </div>
            `;

            // Vincula Eventos da Galeria
            const mainImg = document.getElementById('petGaleriaPrincipalImg');
            const btnZoom = document.getElementById('btnAmpliarFoto');
            const btnPrev = document.getElementById('btnGaleriaPrev');
            const btnNext = document.getElementById('btnGaleriaNext');
            const thumbItems = document.querySelectorAll('.pet-gallery-thumb-item');

            if (mainImg) {
                mainImg.addEventListener('click', () => abrirLightbox(fotosPet[indiceFotoAtual]));
            }
            if (btnZoom) {
                btnZoom.addEventListener('click', () => abrirLightbox(fotosPet[indiceFotoAtual]));
            }
            if (btnPrev) {
                btnPrev.addEventListener('click', () => atualizarFotoDestaque(indiceFotoAtual - 1));
            }
            if (btnNext) {
                btnNext.addEventListener('click', () => atualizarFotoDestaque(indiceFotoAtual + 1));
            }

            thumbItems.forEach(thumb => {
                thumb.addEventListener('click', () => {
                    const idx = parseInt(thumb.dataset.index, 10);
                    atualizarFotoDestaque(idx);
                });
            });

            // Vincula Ação de Conversar com o Doador (Chat)
            const btnConversar = document.getElementById('btnConversarDoador');
            if (btnConversar) {
                btnConversar.addEventListener('click', () => {
                    const user = obterUsuario();
                    if (!user) {
                        alert(`Para conversar com o doador sobre o(a) ${nome}, é necessário entrar na sua conta ou se cadastrar no PetAlert.`);
                        const redirectUrl = encodeURIComponent(window.location.href);
                        window.location.href = `../login/index.html?redirect=${redirectUrl}`;
                        return;
                    }

                    // Se logado, direciona para o chat do pet
                    const doadorId = pet.id_usuario || '';
                    window.location.href = `../chatDoador/index.html?petId=${petId}&doadorId=${doadorId}&nomePet=${encodeURIComponent(nome)}`;
                });
            }

        } catch (error) {
            console.error('Erro ao carregar pet:', error);
            container.innerHTML = `
                <div style="text-align:center; padding:3rem 1rem;">
                    <span style="font-size:3rem;">⚠️</span>
                    <h2 style="margin:1rem 0 0.5rem 0;">Erro ao carregar detalhes</h2>
                    <p style="color:var(--muted); margin-bottom:1.5rem;">Não foi possível carregar as informações do pet no momento. Verifique sua conexão.</p>
                    <a href="index.html" class="primary-btn">Voltar para a lista de pets</a>
                </div>
            `;
        }
    }

    carregarDetalhesPet();
});
