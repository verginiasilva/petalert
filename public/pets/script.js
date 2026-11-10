document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('petsContainer');
    const interessadosContainer = document.getElementById('interessadosContainer');
    const filtroInput = document.getElementById('filtroPet');
    const filtroCidade = document.getElementById('filtroCidade');
    const btnFiltrarPets = document.getElementById('btnFiltrarPets');
    const btnLimparFiltrosPets = document.getElementById('btnLimparFiltrosPets');
    const indicadorLocalizacao = document.getElementById('indicadorLocalizacao');
    const textoIndicadorLocalizacao = document.getElementById('textoIndicadorLocalizacao');
    const badgeQtdPets = document.getElementById('badgeQtdPets');
    const btnRemoverFiltroLocal = document.getElementById('btnRemoverFiltroLocal');
    const grupoBusca = document.getElementById('grupoBusca');
    const abasPerfilContainer = document.getElementById('abasPerfilContainer');
    const abaTodosPets = document.getElementById('abaTodosPets');
    const abaMeusPets = document.getElementById('abaMeusPets');
    const abaInteressados = document.getElementById('abaInteressados');

    const obterUsuario = () => {
        try {
            const raw = localStorage.getItem('petalert_usuario') || sessionStorage.getItem('petalert_usuario');
            if (!raw || raw === 'null') return null;
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    };

    let usuarioAtual = obterUsuario();
    let isDoador = Boolean(usuarioAtual && (usuarioAtual.tipo_usuario || '').toLowerCase() === 'doador');

    let abaAtiva = 'todos'; // 'todos' | 'meus' | 'interessados'
    let todosPets = [];
    let meusPetsLista = [];
    let interessadosLista = [];

    function atualizarVisibilidadeAbas() {
        if (isDoador && abasPerfilContainer) {
            abasPerfilContainer.style.display = 'flex';
        } else if (abasPerfilContainer) {
            abasPerfilContainer.style.display = 'none';
        }
    }

    atualizarVisibilidadeAbas();

    // Sincroniza estado de autenticação com o backend caso o usuário esteja em sessão PHP
    (async function sincronizarSessao() {
        try {
            const res = await fetch('../index.php?rota=auth&acao=me', { credentials: 'include' });
            if (res.ok) {
                const data = await res.json();
                const u = data?.dados || data?.usuario || (data?.id_usuario ? data : null);
                if (u && (u.id_usuario || u.id)) {
                    usuarioAtual = u;
                    isDoador = Boolean((u.tipo_usuario || '').toLowerCase() === 'doador');
                    localStorage.setItem('petalert_usuario', JSON.stringify(u));
                    sessionStorage.setItem('petalert_usuario', JSON.stringify(u));
                    atualizarVisibilidadeAbas();
                    if (isDoador && (window.location.hash === '#meus' || new URLSearchParams(window.location.search).get('aba') === 'meus')) {
                        if (abaMeusPets && abaAtiva !== 'meus') abaMeusPets.click();
                    }
                }
            }
        } catch (e) {}
    })();

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

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Renderiza cards de pets (modo público ou modo meus pets)
    function renderizarPets(pets, modoMeusPets = false) {
        if (!container) return;

        if (!pets || pets.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <h3>${modoMeusPets ? 'Você ainda não cadastrou pets para doação' : 'Nenhum pet encontrado'}</h3>
                    <p>${modoMeusPets ? 'Use o menu "Doar pet" para cadastrar um animal sob sua responsabilidade.' : 'Não encontramos nenhum animalzinho com esses filtros no momento.'}</p>
                    ${modoMeusPets ? '<a href="../doarPet/index.html" class="primary-btn" style="display:inline-block; margin-top:1rem;">Cadastrar pet agora</a>' : ''}
                </div>
            `;
            return;
        }

        container.innerHTML = pets.map(pet => {
            const idPet = pet.id_pet || pet.id;
            const nome = pet.nome || 'Sem nome';
            const especie = pet.especie || 'Pet';
            const raca = pet.raca || pet.raça || 'Não informada';
            const idade = pet.idade ? `${pet.idade} ano(s)` : 'Idade não informada';
            const porte = pet.porte || 'Porte não informado';
            const peso = pet.peso && parseFloat(pet.peso) > 0 ? `${pet.peso} kg` : null;
            const cor = pet.cor || null;
            const motivoDoacao = pet.motivo_doacao || null;
            const desc = pet.descricao || 'Animal dócil, saudável e pronto para receber um novo lar cheio de carinho.';
            const fotoUrl = formatarCaminhoFoto(pet.foto);
            const statusAdocao = (pet.status_adocao || 'disponivel').toLowerCase();
            const isDoado = statusAdocao === 'doado';

            return `
                <article class="pet-card" style="${isDoado ? 'opacity: 0.88; border-color: #fca5a5;' : ''}">
                    <div>
                        <div class="pet-card-header" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
                            <div>
                                <h3 style="margin:0; font-size:1.3rem;">${escapeHtml(nome)}</h3>
                                <span class="pet-badge">${escapeHtml(especie)}</span>
                            </div>
                            ${isDoado ? `
                                <span style="background:#fee2e2; color:#991b1b; font-weight:800; font-size:0.78rem; padding:4px 8px; border-radius:8px; border:1px solid #f87171;">
                                    🐾 Pet já doado
                                </span>
                            ` : `
                                <span style="background:#dcfce7; color:#15803d; font-weight:800; font-size:0.78rem; padding:4px 8px; border-radius:8px; border:1px solid #86efac;">
                                    Disponível
                                </span>
                            `}
                        </div>

                        <div class="pet-tags">
                            <span class="pet-tag">🏷️ ${escapeHtml(raca)}</span>
                            <span class="pet-tag">🎂 ${escapeHtml(idade)}</span>
                            <span class="pet-tag">📏 ${escapeHtml(porte)}</span>
                            ${peso ? `<span class="pet-tag">⚖️ ${escapeHtml(peso)}</span>` : ''}
                            ${cor ? `<span class="pet-tag">🎨 ${escapeHtml(cor)}</span>` : ''}
                        </div>

                        ${motivoDoacao ? `
                            <div style="background:#fff7ed; border-left:3px solid #f97316; padding:6px 10px; border-radius:6px; margin: 0.6rem 0; font-size:0.88rem; color:#9a3412;">
                                <strong>Motivo da Doação:</strong> ${escapeHtml(motivoDoacao)}
                            </div>
                        ` : ''}

                        <p class="pet-desc">${escapeHtml(desc)}</p>
                    </div>

                    <img src="${escapeHtml(fotoUrl)}" alt="Foto de ${escapeHtml(nome)}" class="card-pet-img pet-photo" style="margin-bottom: 1rem; border-radius: 12px; width:100%; height:200px; object-fit:cover;" onerror="this.onerror=null; this.src='../img/PetAlert-Logo.png';">

                    <div class="pet-card-actions">
                        ${modoMeusPets ? `
                            <div class="pet-card-actions-row">
                                <button type="button" class="btn-editar-pet" data-pet-id="${idPet}" title="Editar dados deste pet">
                                    ✏️ Editar Pet
                                </button>
                                <button type="button" class="btn-excluir-pet" data-pet-id="${idPet}" title="Excluir este pet permanentemente">
                                    🗑️ Excluir Pet
                                </button>
                            </div>
                            <div style="display:flex; gap:0.55rem; width:100%;">
                                ${!isDoado ? `
                                    <button type="button" class="primary-btn btn-classificar-doado" data-pet-id="${idPet}" style="flex:1; background: linear-gradient(135deg, #10b981, #059669); font-size:0.84rem; padding:0.6rem 0.6rem; white-space:nowrap;">
                                        ✓ Já doado
                                    </button>
                                ` : `
                                    <button type="button" class="secondary-btn" disabled style="flex:1; opacity:0.6; cursor:not-allowed; font-size:0.84rem; padding:0.6rem 0.6rem;">
                                        ✓ Concluído
                                    </button>
                                `}
                                <button type="button" class="secondary-btn pet-chat-btn" data-pet-id="${idPet}" style="flex:1; font-size:0.84rem; padding:0.6rem 0.6rem;">
                                    💬 Chat (${escapeHtml(nome)})
                                </button>
                            </div>
                        ` : `
                            ${!isDoado ? `
                                <button type="button" class="secondary-btn pet-chat-btn" data-pet-id="${idPet}">
                                    Chat com doador
                                </button>
                            ` : `
                                <button type="button" class="secondary-btn" disabled style="opacity:0.65; cursor:not-allowed;">
                                    Adoção Concluída
                                </button>
                            `}
                        `}
                    </div>
                </article>
            `;
        }).join('');

    const modalLogin = document.getElementById('modalLoginNecessario');
    const btnModalEntrar = document.getElementById('btnModalEntrar');
    const btnModalCriarConta = document.getElementById('btnModalCriarConta');
    const btnModalFechar = document.getElementById('btnModalFechar');
    const modalLoginSubtexto = document.getElementById('modalLoginSubtexto');

    // Elementos do Modal de Edição de Pet
    const modalEditarPet = document.getElementById('modalEditarPet');
    const formEditarPet = document.getElementById('formEditarPet');
    const btnFecharModalEditar = document.getElementById('btnFecharModalEditar');
    const btnCancelarEditarPet = document.getElementById('btnCancelarEditarPet');
    const editPetId = document.getElementById('editPetId');
    const editNomePet = document.getElementById('editNomePet');
    const editEspecie = document.getElementById('editEspecie');
    const editRaca = document.getElementById('editRaca');
    const editIdade = document.getElementById('editIdade');
    const editPorte = document.getElementById('editPorte');
    const editPeso = document.getElementById('editPeso');
    const editCor = document.getElementById('editCor');
    const editMotivoDoacao = document.getElementById('editMotivoDoacao');
    const editDescricao = document.getElementById('editDescricao');
    const editFoto = document.getElementById('editFoto');
    const editFotoPreview = document.getElementById('editFotoPreview');
    const msgErroModalEditar = document.getElementById('msgErroModalEditar');
    const feedbackPetAlerta = document.getElementById('feedbackPetAlerta');

    function exibirFeedbackPet(mensagem, tipo = 'sucesso') {
        if (!feedbackPetAlerta) return;
        feedbackPetAlerta.style.display = 'flex';
        if (tipo === 'sucesso') {
            feedbackPetAlerta.style.background = '#dcfce7';
            feedbackPetAlerta.style.color = '#15803d';
            feedbackPetAlerta.style.border = '1px solid #86efac';
            feedbackPetAlerta.innerHTML = `<span>✅ ${escapeHtml(mensagem)}</span><button type="button" onclick="this.parentElement.style.display='none'" style="background:none;border:none;cursor:pointer;font-weight:700;color:#15803d;">✕</button>`;
        } else {
            feedbackPetAlerta.style.background = '#fee2e2';
            feedbackPetAlerta.style.color = '#991b1b';
            feedbackPetAlerta.style.border = '1px solid #f87171';
            feedbackPetAlerta.innerHTML = `<span>⚠️ ${escapeHtml(mensagem)}</span><button type="button" onclick="this.parentElement.style.display='none'" style="background:none;border:none;cursor:pointer;font-weight:700;color:#991b1b;">✕</button>`;
        }
        window.scrollTo({ top: feedbackPetAlerta.offsetTop - 70, behavior: 'smooth' });
        setTimeout(() => {
            if (feedbackPetAlerta) feedbackPetAlerta.style.display = 'none';
        }, 7000);
    }

    function fecharModalEdicao() {
        if (modalEditarPet) modalEditarPet.style.display = 'none';
        if (msgErroModalEditar) msgErroModalEditar.style.display = 'none';
        if (formEditarPet) formEditarPet.reset();
    }

    if (btnFecharModalEditar) btnFecharModalEditar.onclick = fecharModalEdicao;
    if (btnCancelarEditarPet) btnCancelarEditarPet.onclick = fecharModalEdicao;
    if (modalEditarPet) {
        modalEditarPet.addEventListener('click', (e) => {
            if (e.target === modalEditarPet) fecharModalEdicao();
        });
    }

    // Preview de foto ao selecionar nova imagem
    if (editFoto && editFotoPreview) {
        editFoto.onchange = (e) => {
            const file = e.target.files && e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    editFotoPreview.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
    }

    // Submissão do Formulário de Edição de Pet
    if (formEditarPet) {
        formEditarPet.onsubmit = async (e) => {
            e.preventDefault();
            const petId = editPetId ? editPetId.value : '';
            if (!petId) return;

            const btnSalvar = document.getElementById('btnSalvarEditarPet');
            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.textContent = 'Salvando...';
            }
            if (msgErroModalEditar) msgErroModalEditar.style.display = 'none';

            try {
                const formData = new FormData(formEditarPet);
                formData.append('_method', 'PUT');

                const res = await fetch(`../index.php?rota=pets&id=${encodeURIComponent(petId)}`, {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });
                const json = await res.json().catch(() => ({}));

                if (res.ok && json.status === 'sucesso') {
                    // Atualiza o item local no array sem duplicar nem recriar ID
                    const idx = meusPetsLista.findIndex(p => String(p.id_pet || p.id) === String(petId));
                    if (idx !== -1) {
                        meusPetsLista[idx].nome = editNomePet.value;
                        meusPetsLista[idx].especie = editEspecie.value;
                        meusPetsLista[idx].raca = editRaca.value;
                        meusPetsLista[idx].raça = editRaca.value;
                        meusPetsLista[idx].idade = editIdade.value;
                        meusPetsLista[idx].porte = editPorte.value;
                        meusPetsLista[idx].peso = editPeso.value;
                        meusPetsLista[idx].cor = editCor.value;
                        meusPetsLista[idx].motivo_doacao = editMotivoDoacao.value;
                        meusPetsLista[idx].descricao = editDescricao.value;
                        if (json.foto) {
                            meusPetsLista[idx].foto = json.foto;
                        } else if (editFotoPreview && editFotoPreview.src && !editFotoPreview.src.includes('PetAlert-Logo.png')) {
                            meusPetsLista[idx].foto = editFotoPreview.src;
                        }
                    }

                    fecharModalEdicao();
                    renderizarPets(meusPetsLista, true);
                    exibirFeedbackPet(json.mensagem || 'Pet atualizado com sucesso!', 'sucesso');
                } else {
                    const msg = json.mensagem || 'Não foi possível atualizar as informações do pet.';
                    if (msgErroModalEditar) {
                        msgErroModalEditar.textContent = msg;
                        msgErroModalEditar.style.display = 'block';
                    } else {
                        alert(msg);
                    }
                }
            } catch (err) {
                console.error('Erro ao atualizar pet:', err);
                if (msgErroModalEditar) {
                    msgErroModalEditar.textContent = 'Erro de comunicação ao salvar alterações.';
                    msgErroModalEditar.style.display = 'block';
                }
            } finally {
                if (btnSalvar) {
                    btnSalvar.disabled = false;
                    btnSalvar.textContent = '💾 Salvar Alterações';
                }
            }
        };
    }

    function exibirModalLogin(petId, nomePet = '') {
        const destinoChat = `../chatDoador/index.html?petId=${encodeURIComponent(petId)}`;
        sessionStorage.setItem('petalert_pending_pet_id', String(petId));
        sessionStorage.setItem('petalert_redirect_url', destinoChat);

        if (modalLoginSubtexto && nomePet) {
            modalLoginSubtexto.textContent = `Após entrar na sua conta, você retornará diretamente para conversar sobre o(a) ${nomePet}.`;
        }

        if (btnModalEntrar) {
            btnModalEntrar.onclick = () => {
                window.location.href = `../login/index.html?redirect=${encodeURIComponent(destinoChat)}`;
            };
        }

        if (btnModalCriarConta) {
            btnModalCriarConta.onclick = () => {
                window.location.href = `../cadastro/index.html?redirect=${encodeURIComponent(destinoChat)}`;
            };
        }

        if (modalLogin) {
            modalLogin.style.display = 'flex';
        }
    }

    if (btnModalFechar && modalLogin) {
        btnModalFechar.addEventListener('click', () => {
            modalLogin.style.display = 'none';
        });
    }

    if (modalLogin) {
        modalLogin.addEventListener('click', (e) => {
            if (e.target === modalLogin) {
                modalLogin.style.display = 'none';
            }
        });
    }

    // Listeners para Botão Editar Pet (Requisito 2)
    document.querySelectorAll('.btn-editar-pet').forEach((button) => {
        button.addEventListener('click', () => {
            const petId = button.dataset.petId;
            if (!petId) return;

            const pet = meusPetsLista.find(p => String(p.id_pet || p.id) === String(petId));
            if (!pet) return;

            // Preenche o formulário com os dados atuais do pet
            if (editPetId) editPetId.value = pet.id_pet || pet.id;
            if (editNomePet) editNomePet.value = pet.nome || '';
            if (editEspecie) {
                const esp = pet.especie || 'Cachorro';
                editEspecie.value = ['Cachorro', 'Gato', 'Coelho', 'Pássaro', 'Hamster'].includes(esp) ? esp : 'Outros';
            }
            if (editRaca) editRaca.value = pet.raca || pet.raça || '';
            if (editIdade) editIdade.value = pet.idade || '';
            if (editPorte) editPorte.value = pet.porte || 'Médio';
            if (editPeso) editPeso.value = pet.peso || '';
            if (editCor) editCor.value = pet.cor || '';
            if (editMotivoDoacao) editMotivoDoacao.value = pet.motivo_doacao || '';
            if (editDescricao) editDescricao.value = pet.descricao || '';
            if (editFotoPreview) editFotoPreview.src = formatarCaminhoFoto(pet.foto);
            if (editFoto) editFoto.value = '';
            if (msgErroModalEditar) msgErroModalEditar.style.display = 'none';

            if (modalEditarPet) {
                modalEditarPet.style.display = 'flex';
            }
        });
    });

    // Listeners para Botão Excluir Pet (Requisito 3)
    document.querySelectorAll('.btn-excluir-pet').forEach((button) => {
        button.addEventListener('click', async () => {
            const petId = button.dataset.petId;
            if (!petId) return;

            const pet = meusPetsLista.find(p => String(p.id_pet || p.id) === String(petId));
            const nomePet = pet ? pet.nome : 'este pet';

            const confirmacao = confirm(`Tem certeza de que deseja excluir este pet (${nomePet})? Essa ação não poderá ser desfeita.`);
            if (!confirmacao) {
                return; // Se cancelar, não fazer nenhuma alteração
            }

            button.disabled = true;
            button.textContent = 'Excluindo...';

            try {
                // Envia requisição DELETE ao backend com validação de sessão e ownership
                const res = await fetch(`../index.php?rota=pets&id=${encodeURIComponent(petId)}`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `_method=DELETE&id=${encodeURIComponent(petId)}`
                });

                const json = await res.json().catch(() => ({}));

                if (res.ok && json.status === 'sucesso') {
                    // Remove do array em memória
                    meusPetsLista = meusPetsLista.filter(p => String(p.id_pet || p.id) !== String(petId));

                    // Atualiza a visualização imediatamente
                    renderizarPets(meusPetsLista, true);
                    exibirFeedbackPet(json.mensagem || `Pet "${nomePet}" excluído com sucesso!`, 'sucesso');
                } else {
                    const msgErro = json.mensagem || 'Não foi possível excluir o pet. Verifique suas permissões.';
                    exibirFeedbackPet(msgErro, 'erro');
                    button.disabled = false;
                    button.textContent = '🗑️ Excluir Pet';
                }
            } catch (err) {
                console.error('Erro ao excluir pet:', err);
                exibirFeedbackPet('Erro de conexão ao tentar excluir o pet.', 'erro');
                button.disabled = false;
                button.textContent = '🗑️ Excluir Pet';
            }
        });
    });

    // Listeners para Chat
    document.querySelectorAll('.pet-chat-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const petId = button.dataset.petId || '';
            const destinoChat = `../chatDoador/index.html?petId=${encodeURIComponent(petId)}`;
            const card = button.closest('.pet-card');
            const nomePet = card ? card.querySelector('h3')?.textContent?.trim() : '';

            if (!usuarioAtual) {
                exibirModalLogin(petId, nomePet);
                return;
            }

            sessionStorage.setItem('petalert_pending_pet_id', petId);
            window.location.href = destinoChat;
        });
    });

        // Listeners para Classificar como doado (Requisito 23)
        document.querySelectorAll('.btn-classificar-doado').forEach((button) => {
            button.addEventListener('click', async () => {
                const petId = button.dataset.petId;
                if (!petId) return;

                if (!confirm('Deseja realmente classificar este pet como DOADO?\n\nEle será retirado da lista de pets disponíveis para adoção pública e o histórico será preservado.')) {
                    return;
                }

                button.disabled = true;
                button.textContent = 'Atualizando status...';

                try {
                    const res = await fetch('../index.php?rota=pets&acao=classificar-doado', {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_pet: petId })
                    });
                    const json = await res.json().catch(() => ({}));

                    if (res.ok && json.status === 'sucesso') {
                        exibirFeedbackPet(json.mensagem || 'Pet classificado como doado com sucesso!', 'sucesso');
                        await carregarMeusPets();
                    } else {
                        exibirFeedbackPet(json.mensagem || 'Não foi possível classificar o pet como doado.', 'erro');
                        button.disabled = false;
                        button.textContent = '✓ Já doado';
                    }
                } catch (err) {
                    console.error('Erro ao classificar doado:', err);
                    exibirFeedbackPet('Erro de conexão ao atualizar status do pet.', 'erro');
                    button.disabled = false;
                    button.textContent = '✓ Já doado';
                }
            });
        });
    }

    // Renderiza Interessados nos pets do doador (Requisito 20)
    function renderizarInteressados(lista) {
        if (!interessadosContainer) return;

        if (!lista || lista.length === 0) {
            interessadosContainer.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <h3>Nenhum interessado registrado ainda</h3>
                    <p>Assim que pessoas interessadas iniciarem conversas sobre os seus pets cadastrados, elas aparecerão listadas aqui para você combinar a adoção.</p>
                </div>
            `;
            return;
        }

        interessadosContainer.innerHTML = lista.map(item => {
            const nomeInteressado = item.nome_interessado || 'Adotante Interessado';
            const nomePet = item.nome_pet || 'Pet';
            const fotoPet = formatarCaminhoFoto(item.foto_pet);
            const mensagem = item.ultima_mensagem || 'Iniciou contato sobre este animalzinho.';
            const dataContato = item.data_contato ? new Date(item.data_contato).toLocaleString('pt-BR') : '';
            const email = item.email_interessado || '';
            const telefone = item.telefone_interessado || '';
            const local = [item.cidade_interessado, item.estado_interessado].filter(Boolean).join('/');

            return `
                <article class="pet-card" style="border-left: 4px solid var(--primary-dark);">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem;">
                            <h3 style="margin:0; font-size:1.2rem;">👤 ${escapeHtml(nomeInteressado)}</h3>
                            <span style="font-size:0.75rem; color:var(--muted);">${escapeHtml(dataContato)}</span>
                        </div>

                        <div style="background:#fffaf5; border:1px solid #fed7aa; padding:10px; border-radius:10px; margin-bottom:0.8rem;">
                            <strong style="color:var(--primary-dark);">Interesse no Pet:</strong> ${escapeHtml(nomePet)}
                            ${local ? `<div style="font-size:0.85rem; color:var(--muted); margin-top:2px;">📍 Região: ${escapeHtml(local)}</div>` : ''}
                            ${email ? `<div style="font-size:0.85rem; color:var(--muted);">✉️ ${escapeHtml(email)}</div>` : ''}
                            ${telefone ? `<div style="font-size:0.85rem; color:var(--muted);">📞 ${escapeHtml(telefone)}</div>` : ''}
                        </div>

                        <p style="font-size:0.92rem; color:var(--text); line-height:1.5; background:#ffffff; border:1px dashed var(--border); padding:8px 12px; border-radius:8px;">
                            💬 <em>"${escapeHtml(mensagem)}"</em>
                        </p>
                    </div>

                    <div style="margin-top:auto; padding-top:1rem;">
                        <a href="../chatDoador/index.html?petId=${encodeURIComponent(item.id_pet)}" class="primary-btn" style="display:inline-block; width:100%; text-align:center; text-decoration:none; font-size:0.95rem;">
                            Abrir Conversa do Pet ${escapeHtml(nomePet)}
                        </a>
                    </div>
                </article>
            `;
        }).join('');
    }

    // Carregar todos os pets disponíveis
    async function carregarTodosPets() {
        if (!container) return;
        container.innerHTML = '<div class="empty-state"><p>Carregando pets do banco de dados...</p></div>';

        try {
            const res = await fetch('../index.php?rota=pets', { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`Falha HTTP ${res.status}`);
            const data = await res.json();
            todosPets = Array.isArray(data) ? data : (data?.dados || []);
            renderizarPets(todosPets, false);
        } catch (err) {
            console.error('Erro ao carregar todos pets:', err);
            container.innerHTML = `
                <div class="empty-state" style="color: #b91c1c;">
                    <h3>Erro ao carregar os pets</h3>
                    <p>Verifique se o XAMPP está ativo no servidor local.</p>
                </div>
            `;
        }
    }

    // Carregar pets do próprio doador (Requisito 19)
    async function carregarMeusPets() {
        if (!container) return;
        container.innerHTML = '<div class="empty-state"><p>Carregando seus pets cadastrados...</p></div>';

        try {
            const res = await fetch('../index.php?rota=pets&acao=meus-pets', { credentials: 'include', headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`Falha HTTP ${res.status}`);
            const data = await res.json();
            meusPetsLista = Array.isArray(data) ? data : (data?.dados || []);
            renderizarPets(meusPetsLista, true);
        } catch (err) {
            console.error('Erro ao carregar meus pets:', err);
            container.innerHTML = `
                <div class="empty-state">
                    <h3>Não foi possível carregar seus pets</h3>
                    <p>Faça login como doador para gerenciar seus animais cadastrados.</p>
                </div>
            `;
        }
    }

    // Carregar interessados (Requisito 20)
    async function carregarInteressados() {
        if (!interessadosContainer) return;
        interessadosContainer.innerHTML = '<div class="empty-state" style="grid-column: 1 / -1;"><p>Consultando pessoas interessadas nos seus pets...</p></div>';

        try {
            const res = await fetch('../index.php?rota=pets&acao=interessados', { credentials: 'include', headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`Falha HTTP ${res.status}`);
            const data = await res.json();
            interessadosLista = Array.isArray(data) ? data : (data?.dados || []);
            renderizarInteressados(interessadosLista);
        } catch (err) {
            console.error('Erro ao carregar interessados:', err);
            interessadosContainer.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <h3>Nenhum interessado carregado</h3>
                    <p>Não foi possível carregar a lista de contatos no momento.</p>
                </div>
            `;
        }
    }

    // Alternância de Abas para Doador
    if (abaTodosPets) {
        abaTodosPets.addEventListener('click', () => {
            abaAtiva = 'todos';
            abaTodosPets.className = 'primary-btn';
            abaMeusPets.className = 'secondary-btn';
            abaInteressados.className = 'secondary-btn';
            if (grupoBusca) grupoBusca.style.display = 'block';
            if (container) container.style.display = 'grid';
            if (interessadosContainer) interessadosContainer.style.display = 'none';
            carregarTodosPets();
        });
    }

    if (abaMeusPets) {
        abaMeusPets.addEventListener('click', () => {
            abaAtiva = 'meus';
            abaTodosPets.className = 'secondary-btn';
            abaMeusPets.className = 'primary-btn';
            abaInteressados.className = 'secondary-btn';
            if (grupoBusca) grupoBusca.style.display = 'none';
            if (container) container.style.display = 'grid';
            if (interessadosContainer) interessadosContainer.style.display = 'none';
            carregarMeusPets();
        });
    }

    if (abaInteressados) {
        abaInteressados.addEventListener('click', () => {
            abaAtiva = 'interessados';
            abaTodosPets.className = 'secondary-btn';
            abaMeusPets.className = 'secondary-btn';
            abaInteressados.className = 'primary-btn';
            if (grupoBusca) grupoBusca.style.display = 'none';
            if (container) container.style.display = 'none';
            if (interessadosContainer) interessadosContainer.style.display = 'grid';
            carregarInteressados();
        });
    }

    // Busca em tempo real (para a aba de todos os pets)
    if (filtroInput) {
        filtroInput.addEventListener('input', (e) => {
            const termo = e.target.value.toLowerCase().trim();
            if (!termo) {
                renderizarPets(todosPets, false);
                return;
            }

            const filtrados = todosPets.filter(pet => {
                const nome = (pet.nome || '').toLowerCase();
                const raca = (pet.raca || pet.raça || '').toLowerCase();
                const especie = (pet.especie || '').toLowerCase();
                const porte = (pet.porte || '').toLowerCase();
                const cor = (pet.cor || '').toLowerCase();
                const motivo = (pet.motivo_doacao || '').toLowerCase();

                return nome.includes(termo) ||
                       raca.includes(termo) ||
                       especie.includes(termo) ||
                       porte.includes(termo) ||
                       cor.includes(termo) ||
                       motivo.includes(termo);
            });

            renderizarPets(filtrados, false);
        });
    }

    // Inicialização: Se a URL tiver hash #meus ou query param ?aba=meus e for doador, ativa aba Meus Pets
    const urlParams = new URLSearchParams(window.location.search);
    const querAbaMeus = window.location.hash === '#meus' || urlParams.get('aba') === 'meus';

    if (querAbaMeus && isDoador && abaMeusPets) {
        abaMeusPets.click();
    } else {
        carregarTodosPets();
    }
});
