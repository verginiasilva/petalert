const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const sendButton = document.getElementById('sendButton');
const doadorNomeEl = document.getElementById('doadorNome');
const doadorCargoEl = document.getElementById('doadorCargo');
const doadorAvatarEl = document.getElementById('doadorAvatar');
const petPillEl = document.getElementById('petPill');
const chatList = document.getElementById('chatList');
const chatPanel = document.getElementById('chatPanel');
const badgeSimulacao = document.getElementById('badgeSimulacao');
const btnAlternarModo = document.getElementById('btnAlternarModo');
const chatTituloPrincipal = document.getElementById('chatTituloPrincipal');

const petId = Number(new URLSearchParams(window.location.search).get('petId')) || null;
let currentUser = null;
let currentDoador = null;
let currentPet = null;
let modoConversa = 'doador'; // 'doador' | 'adotante'

function getCurrentUser() {
    try {
        const raw = localStorage.getItem('petalert_usuario') || sessionStorage.getItem('petalert_usuario');
        if (!raw) return null;

        const parsed = JSON.parse(raw);
        if (!parsed) return null;

        const userId = parsed.id_usuario ?? parsed.id ?? null;
        if (userId === null || userId === undefined || userId === '') {
            return null;
        }

        return {
            ...parsed,
            id: parsed.id ?? userId,
            id_usuario: parsed.id_usuario ?? userId
        };
    } catch (error) {
        return null;
    }
}

function escapeHtml(value = '') {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatTime(dateString) {
    const date = dateString ? new Date(dateString) : new Date();
    return new Intl.DateTimeFormat('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

function getAutorIncoming() {
    const nomeDoador = currentDoador?.nome || 'Tutor do pet';
    if (modoConversa === 'adotante') {
        return 'Interessado(a) na Adoção';
    }
    return `${nomeDoador} (Tutor)`;
}

function renderEmptyState() {
    if (!chatMessages) return;

    const nomeDoador = currentDoador?.nome || 'Tutor do pet';
    const nomePet = currentPet?.nome || 'pet';

    let mensagemInicial = '';
    if (modoConversa === 'doador') {
        mensagemInicial = `Olá! Sou o(a) ${nomeDoador}, tutor(a) responsável pelo(a) ${nomePet}. Estou à disposição para tirar dúvidas sobre ele(a) e conversar sobre a adoção responsável!`;
    } else {
        mensagemInicial = `Olá! Eu tenho bastante interesse em adotar o(a) ${nomePet} e gostaria de saber mais sobre a convivência e adaptação dele(a).`;
    }

    chatMessages.innerHTML = `
        <div class="message-row incoming">
            <div class="message-bubble">
                <div class="message-author">${escapeHtml(getAutorIncoming())}</div>
                <div>${escapeHtml(mensagemInicial)}</div>
                <span class="message-time">agora</span>
            </div>
        </div>
    `;
}

function renderMessages(messages = []) {
    if (!chatMessages) return;
    chatMessages.innerHTML = '';

    if (!messages.length) {
        renderEmptyState();
        return;
    }

    const usuarioAtual = currentUser?.id_usuario ?? currentUser?.id ?? null;

    messages.forEach((message) => {
        const mensagem = message.mensagem ?? message.message ?? '';
        const origem = Number(message.id_usuario_origem || message.id_usuario) === Number(usuarioAtual)
            ? 'outgoing'
            : 'incoming';

        const row = document.createElement('div');
        row.className = `message-row ${origem}`;

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';

        const author = document.createElement('div');
        author.className = 'message-author';
        author.textContent = origem === 'incoming' ? getAutorIncoming() : 'Você';

        const text = document.createElement('div');
        text.innerHTML = escapeHtml(mensagem).replace(/\n/g, '<br>');

        const time = document.createElement('span');
        time.className = 'message-time';
        time.textContent = formatTime(message.data_criacao || message.data_mensagem || message.time || new Date());

        bubble.appendChild(author);
        bubble.appendChild(text);
        bubble.appendChild(time);
        row.appendChild(bubble);
        chatMessages.appendChild(row);
    });

    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function mostrarListaConversas() {
    if (!chatList) return;
    chatList.classList.remove('hidden');
    if (chatPanel) chatPanel.classList.add('hidden');
}

function mostrarChat() {
    if (chatList) chatList.classList.add('hidden');
    if (chatPanel) chatPanel.classList.remove('hidden');
}

function renderConversationList(conversas = []) {
    if (!chatList) return;

    if (!conversas.length) {
        chatList.innerHTML = `
            <div class="empty-state" style="background:#fff; border:1px solid var(--border); border-radius:16px; padding:1.5rem; text-align:center;">
                <h3>Você ainda não iniciou nenhuma conversa sobre pets.</h3>
                <a href="../pets/index.html" class="primary-btn" style="display:inline-block; margin-top:1rem;">Ver pets disponíveis</a>
            </div>
        `;
        return;
    }

    chatList.innerHTML = conversas.map((item) => {
        const nomePet = item.nome_pet || 'Pet';
        const nomeDoador = item.nome_doador || 'Tutor';
        const ultimaMensagem = item.ultima_mensagem || 'Inicie a conversa';
        const data = item.ultima_data ? formatTime(item.ultima_data) : '';
        const idPet = item.id_pet || '';
        const avatar = (nomePet || nomeDoador).trim().charAt(0).toUpperCase() || 'P';

        return `
            <button type="button" class="conversation-item" data-pet-id="${escapeHtml(String(idPet))}">
                <div class="conversation-avatar">${escapeHtml(avatar)}</div>
                <div class="conversation-content">
                    <div class="conversation-header">
                        <span class="conversation-name">🐾 ${escapeHtml(nomePet)}</span>
                        <span class="conversation-meta">${escapeHtml(data)}</span>
                    </div>
                    <div class="conversation-meta" style="color:var(--primary-dark); font-weight:600;">Tutor: ${escapeHtml(nomeDoador)}</div>
                    <div class="conversation-preview">${escapeHtml(ultimaMensagem)}</div>
                </div>
            </button>
        `;
    }).join('');

    chatList.querySelectorAll('.conversation-item').forEach((button) => {
        button.addEventListener('click', () => {
            const idPet = button.dataset.petId;
            if (!idPet) return;
            window.location.href = `./index.html?petId=${encodeURIComponent(idPet)}`;
        });
    });
}

async function carregarConversas() {
    try {
        const resposta = await fetch('../index.php?rota=chat&acao=minhas-conversas', {
            headers: { Accept: 'application/json' }
        });

        if (!resposta.ok) {
            throw new Error('Falha ao carregar as conversas.');
        }

        const dados = await resposta.json();
        const conversas = Array.isArray(dados?.dados) ? dados.dados : [];
        renderConversationList(conversas);
    } catch (error) {
        console.error('Erro ao carregar conversas:', error);
        renderConversationList([]);
    }
}

function exibirModalAuthChat() {
    const modalAuth = document.getElementById('modalAuthChat');
    const btnEntrar = document.getElementById('btnChatEntrar');
    const btnCriarConta = document.getElementById('btnChatCriarConta');

    if (petId) {
        sessionStorage.setItem('petalert_pending_pet_id', String(petId));
    }
    sessionStorage.setItem('petalert_redirect_url', window.location.href);

    if (btnEntrar) {
        btnEntrar.href = `../login/index.html?redirect=${encodeURIComponent(window.location.href)}`;
    }
    if (btnCriarConta) {
        btnCriarConta.href = `../cadastro/index.html?redirect=${encodeURIComponent(window.location.href)}`;
    }

    if (modalAuth) {
        modalAuth.style.display = 'flex';
    } else {
        window.location.href = `../login/index.html?redirect=${encodeURIComponent(window.location.href)}`;
    }
}

async function carregarDadosPet() {
    if (!petId) {
        petPillEl.textContent = '🐾 Pet não informado';
        doadorNomeEl.textContent = 'Doador indisponível';
        doadorCargoEl.textContent = 'Não foi possível identificar o pet.';
        doadorAvatarEl.textContent = '?';
        return;
    }

    try {
        const usuarioAtual = currentUser?.id_usuario ?? currentUser?.id ?? '';
        const respostaContexto = await fetch(`../index.php?rota=chat&acao=pet-context&id_pet=${encodeURIComponent(petId)}&id_usuario=${encodeURIComponent(usuarioAtual)}`, {
            headers: { Accept: 'application/json' }
        });

        if (respostaContexto.status === 401) {
            exibirModalAuthChat();
            return;
        }

        if (!respostaContexto.ok) {
            throw new Error('Não foi possível identificar o pet.');
        }

        const contexto = await respostaContexto.json();
        currentPet = contexto?.pet || null;
        currentDoador = contexto?.doador || null;

        if (currentPet) {
            petPillEl.textContent = `🐾 ${currentPet.nome || 'Pet'}`;
        }

        atualizarVisualModo();
    } catch (error) {
        console.error('Erro ao carregar dados do pet:', error);
        petPillEl.textContent = '🐾 Pet';
        doadorNomeEl.textContent = 'Doador indisponível';
        doadorCargoEl.textContent = 'Não foi possível carregar os dados do doador.';
        doadorAvatarEl.textContent = 'D';
    }
}

function atualizarVisualModo() {
    const nomePet = currentPet?.nome || 'Pet';
    const nomeDoador = currentDoador?.nome || 'Tutor do pet';

    if (modoConversa === 'doador') {
        if (chatTituloPrincipal) chatTituloPrincipal.textContent = `💬 Conversando sobre ${nomePet}`;
        if (badgeSimulacao) badgeSimulacao.textContent = 'IA do PetAlert — conversa simulada com o doador';
        if (btnAlternarModo) btnAlternarModo.textContent = 'Mudar para Modo Adotante';

        if (doadorNomeEl) doadorNomeEl.textContent = nomeDoador;
        if (doadorCargoEl) doadorCargoEl.textContent = `Tutor(a) responsável pelo(a) ${nomePet}`;
        if (doadorAvatarEl) doadorAvatarEl.textContent = nomeDoador.trim().charAt(0).toUpperCase() || 'D';
    } else {
        if (chatTituloPrincipal) chatTituloPrincipal.textContent = `💬 Simulação de Adotante — ${nomePet}`;
        if (badgeSimulacao) badgeSimulacao.textContent = 'IA do PetAlert — conversa simulada com o adotante';
        if (btnAlternarModo) btnAlternarModo.textContent = 'Mudar para Modo Doador';

        if (doadorNomeEl) doadorNomeEl.textContent = 'Interessado(a) na Adoção';
        if (doadorCargoEl) doadorCargoEl.textContent = `Simulando pessoa interessada em adotar ${nomePet}`;
        if (doadorAvatarEl) doadorAvatarEl.textContent = 'A';
    }
}

// Carregar histórico separado por petId, tipo_conversa e usuário (Requisito 13)
async function carregarHistorico() {
    if (!petId) {
        renderEmptyState();
        return;
    }

    try {
        const usuarioAtual = currentUser?.id_usuario ?? currentUser?.id ?? '';
        const endpoint = `../index.php?rota=chat&id_pet=${encodeURIComponent(petId)}&tipo_conversa=${encodeURIComponent(modoConversa)}&id_usuario=${encodeURIComponent(usuarioAtual)}`;
        const resposta = await fetch(endpoint, {
            headers: { Accept: 'application/json' }
        });

        if (!resposta.ok) {
            throw new Error('Falha ao carregar histórico');
        }

        const dados = await resposta.json();
        const mensagens = dados && dados.status === 'sucesso' && Array.isArray(dados.dados) ? dados.dados : [];
        renderMessages(mensagens);
    } catch (error) {
        console.error('Erro ao carregar histórico:', error);
        renderEmptyState();
    }
}

function appendMessageBubble(author, text, direction = 'incoming') {
    const row = document.createElement('div');
    row.className = `message-row ${direction}`;

    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';

    const authorEl = document.createElement('div');
    authorEl.className = 'message-author';
    authorEl.textContent = author;

    const textEl = document.createElement('div');
    textEl.innerHTML = escapeHtml(String(text || '')).replace(/\n/g, '<br>');

    const timeEl = document.createElement('span');
    timeEl.className = 'message-time';
    timeEl.textContent = 'agora';

    bubble.appendChild(authorEl);
    bubble.appendChild(textEl);
    bubble.appendChild(timeEl);
    row.appendChild(bubble);
    chatMessages.appendChild(row);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Enviar mensagem respeitando contexto e separação
async function enviarMensagem() {
    const mensagem = chatInput.value.trim();
    if (!mensagem || !petId || !currentUser) {
        if (!currentUser) {
            window.location.href = `../login/index.html?redirect=${encodeURIComponent(window.location.href)}`;
        }
        return;
    }

    const usuarioAtual = currentUser.id_usuario ?? currentUser.id;
    const destinoDoador = currentDoador && currentDoador.id_usuario ? Number(currentDoador.id_usuario) : null;

    chatInput.value = '';
    chatInput.disabled = true;
    sendButton.disabled = true;
    sendButton.textContent = 'Enviando...';

    appendMessageBubble('Você', mensagem, 'outgoing');

    try {
        // 1. Salva a mensagem do usuário no banco com tipo_conversa (Requisito 13)
        await fetch('../index.php?rota=chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_usuario: usuarioAtual,
                id_pet: petId,
                id_usuario_origem: usuarioAtual,
                id_usuario_destino: destinoDoador,
                mensagem: mensagem,
                tipo_conversa: modoConversa
            })
        });

        // 2. Chama a IA com o contexto específico (doador ou adotante)
        const respostaIa = await fetch('../index.php?rota=ia', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: mensagem,
                petId: petId,
                contexto: modoConversa,
                usuarioId: usuarioAtual,
                history: [{ role: 'user', text: mensagem }]
            })
        });

        const dadoIa = await respostaIa.json().catch(() => ({}));
        const textoResposta = dadoIa?.resposta || dadoIa?.reply || dadoIa?.response || 'Não consegui obter uma resposta no momento.';

        // 3. Exibe o balão com o nome do personagem correto
        appendMessageBubble(getAutorIncoming(), textoResposta, 'incoming');

        // 4. Salva a resposta da IA no banco com o mesmo tipo_conversa
        await fetch('../index.php?rota=chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_usuario: destinoDoador ?? usuarioAtual,
                id_pet: petId,
                id_usuario_origem: destinoDoador ?? usuarioAtual,
                id_usuario_destino: usuarioAtual,
                mensagem: textoResposta,
                tipo_conversa: modoConversa
            })
        });

    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        appendMessageBubble('Sistema', 'Não foi possível enviar a mensagem no momento.', 'incoming');
    } finally {
        chatInput.disabled = false;
        sendButton.disabled = false;
        sendButton.textContent = 'Enviar';
        chatInput.focus();
    }
}

// Alternar entre Modo Doador e Modo Adotante (Requisito 8, 10 e 11)
if (btnAlternarModo) {
    btnAlternarModo.addEventListener('click', async () => {
        modoConversa = modoConversa === 'doador' ? 'adotante' : 'doador';
        atualizarVisualModo();
        await carregarHistorico();
    });
}

if (chatInput && sendButton) {
    chatInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            enviarMensagem();
        }
    });

    sendButton.addEventListener('click', enviarMensagem);
}

(async function init() {
    currentUser = getCurrentUser();
    if (!currentUser) {
        exibirModalAuthChat();
        return;
    }

    if (petId) {
        mostrarChat();
        await carregarDadosPet();
        await carregarHistorico();
    } else {
        mostrarListaConversas();
        await carregarConversas();
    }
})();
