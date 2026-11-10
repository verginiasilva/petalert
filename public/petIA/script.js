document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('mensagemIa');
    const button = document.getElementById('enviarIa');
    const chatMessages = document.getElementById('chatMessages');
    const limparBtn = document.getElementById('limparHistoricoBtn');
    const chips = document.querySelectorAll('.chip');

    const STORAGE_KEY = 'petalert_session_chat_history';

    // Recupera histórico da sessão
    function obterHistorico() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    // Salva histórico na sessão
    function salvarHistorico(history) {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(history));
        } catch (e) {}
    }

    // Formatador completo de Markdown para respostas ricas da Pet.IA
    function formatarMarkdown(text) {
        if (!text) return '';

        // 1. Escapar HTML para segurança
        let raw = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // 2. Headings (###, ##, #)
        raw = raw.replace(/^### (.*$)/gim, '<h4 style="margin:0.7rem 0 0.35rem; font-size:1.05rem; font-weight:800; color:var(--primary-dark);">$1</h4>');
        raw = raw.replace(/^## (.*$)/gim, '<h3 style="margin:0.8rem 0 0.4rem; font-size:1.15rem; font-weight:800; color:var(--text);">$1</h3>');
        raw = raw.replace(/^# (.*$)/gim, '<h2 style="margin:0.9rem 0 0.45rem; font-size:1.25rem; font-weight:800; color:var(--text);">$1</h2>');

        // 3. Negrito e Itálico
        raw = raw.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>');
        raw = raw.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        raw = raw.replace(/\*(.*?)\*/g, '<em>$1</em>');
        raw = raw.replace(/`([^`]+)`/g, '<code style="background:rgba(0,0,0,0.06); padding:2px 5px; border-radius:4px; font-size:0.9em;">$1</code>');

        // 4. Listas com marcadores (bullet points)
        const lines = raw.split('\n');
        let inUl = false;
        let inOl = false;
        let outputLines = [];

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];

            // Item de lista não ordenada: • , - , *
            const bulletMatch = line.match(/^(\s*)(?:[•\-\*])\s+(.+)$/);
            // Item de lista numerada: 1. , 2.
            const orderedMatch = line.match(/^(\s*)\d+\.\s+(.+)$/);

            if (bulletMatch) {
                if (inOl) {
                    outputLines.push('</ol>');
                    inOl = false;
                }
                if (!inUl) {
                    outputLines.push('<ul style="margin:0.4rem 0 0.6rem 1.2rem; padding:0;">');
                    inUl = true;
                }
                outputLines.push(`<li style="margin-bottom:0.25rem;">${bulletMatch[2]}</li>`);
            } else if (orderedMatch) {
                if (inUl) {
                    outputLines.push('</ul>');
                    inUl = false;
                }
                if (!inOl) {
                    outputLines.push('<ol style="margin:0.4rem 0 0.6rem 1.2rem; padding:0;">');
                    inOl = true;
                }
                outputLines.push(`<li style="margin-bottom:0.25rem;">${orderedMatch[2]}</li>`);
            } else {
                if (inUl) {
                    outputLines.push('</ul>');
                    inUl = false;
                }
                if (inOl) {
                    outputLines.push('</ol>');
                    inOl = false;
                }
                outputLines.push(line);
            }
        }

        if (inUl) outputLines.push('</ul>');
        if (inOl) outputLines.push('</ol>');

        let html = outputLines.join('\n');
        // Quebras duplas em parágrafos e simples em <br>
        html = html
            .replace(/\n\n+/g, '<br><br>')
            .replace(/\n/g, '<br>');

        // Limpeza de tags listas adjacentes a <br>
        html = html
            .replace(/<\/ul><br>/g, '</ul>')
            .replace(/<\/ol><br>/g, '</ol>')
            .replace(/<br><ul>/g, '<ul>')
            .replace(/<br><ol>/g, '<ol>');

        return html;
    }

    // Adiciona uma mensagem na interface
    function adicionarMensagemUI(role, texto) {
        const isUser = (role === 'user');
        const row = document.createElement('div');
        row.className = `msg-row ${isUser ? 'user' : 'bot'}`;

        const avatar = document.createElement('div');
        avatar.className = 'msg-avatar';
        avatar.textContent = isUser ? '👤' : '🐾';

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';

        if (isUser) {
            bubble.textContent = texto;
        } else {
            bubble.innerHTML = formatarMarkdown(texto);
        }

        row.appendChild(avatar);
        row.appendChild(bubble);
        chatMessages.appendChild(row);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Renderiza o histórico salvo ao carregar a página
    function carregarHistoricoSalvo() {
        const historico = obterHistorico();
        if (historico.length > 0) {
            historico.forEach(item => {
                adicionarMensagemUI(item.role, item.text);
            });
        }
    }

    // Exibir balão de carregamento (digitando...)
    function mostrarDigitando() {
        const row = document.createElement('div');
        row.className = 'msg-row bot';
        row.id = 'loadingBubble';

        const avatar = document.createElement('div');
        avatar.className = 'msg-avatar';
        avatar.textContent = '🐾';

        const bubble = document.createElement('div');
        bubble.className = 'typing-indicator';
        bubble.innerHTML = `
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span style="font-size: 0.85rem; color: var(--muted); margin-left: 6px;">Pet.IA está pensando...</span>
        `;

        row.appendChild(avatar);
        row.appendChild(bubble);
        chatMessages.appendChild(row);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function removerDigitando() {
        const el = document.getElementById('loadingBubble');
        if (el) el.remove();
    }

    // Envio da mensagem para a API oficial do Gemini no backend
    async function enviarMensagem(mensagemCustomizada = null) {
        const mensagem = (mensagemCustomizada !== null ? mensagemCustomizada : input.value).trim();

        // Requisito 12: Impedir envio de mensagem vazia
        if (!mensagem) {
            input.focus();
            return;
        }

        // Requisito 12: Evitar envios duplicados
        if (button.disabled) return;

        input.value = '';
        input.disabled = true;
        button.disabled = true;
        button.innerHTML = '<span>Pensando...</span> ⏳';

        // 1. Adicionar mensagem do usuário na tela
        adicionarMensagemUI('user', mensagem);

        // 2. Atualizar histórico
        const historico = obterHistorico();
        historico.push({ role: 'user', text: mensagem });
        salvarHistorico(historico);

        // 3. Mostrar indicador de carregamento (Requisito 11)
        mostrarDigitando();

        try {
            const endpoint = '../index.php?rota=ia';
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: mensagem,
                    history: historico
                })
            });

            removerDigitando();

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                throw new Error(errData.error || errData.resposta || 'Não foi possível se comunicar com o servidor da Pet.IA.');
            }

            const data = await response.json();
            const respostaTexto = data.reply || data.resposta || data.response || 'Desculpe, não consegui formular uma resposta no momento.';

            // 4. Exibir resposta na tela e salvar histórico
            adicionarMensagemUI('model', respostaTexto);
            historico.push({ role: 'model', text: respostaTexto });
            salvarHistorico(historico);

        } catch (error) {
            removerDigitando();
            console.error('Erro Pet.IA:', error);

            // Requisito 8: Mensagem amigável de erro/indisponibilidade
            const mensagemErro = `⚠️ Desculpe, tive uma instabilidade temporária ao consultar os servidores da inteligência artificial. Por favor, tente novamente em instantes!\n\nSe preferir, você pode consultar informações sobre adoção na aba **Pets** ou falar com clínicas na aba **Veterinário**.`;
            adicionarMensagemUI('model', mensagemErro);
        } finally {
            input.disabled = false;
            button.disabled = false;
            button.innerHTML = '<span>Enviar</span> ➔';
            input.focus();
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    // Eventos
    button.addEventListener('click', () => enviarMensagem());

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            enviarMensagem();
        }
    });

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            const pergunta = chip.dataset.ask;
            if (pergunta) {
                enviarMensagem(pergunta);
            }
        });
    });

    limparBtn.addEventListener('click', () => {
        if (confirm('Deseja limpar o histórico da conversa nesta sessão?')) {
            sessionStorage.removeItem(STORAGE_KEY);
            chatMessages.innerHTML = `
                <div class="msg-row bot">
                    <div class="msg-avatar">🐶</div>
                    <div class="msg-bubble">
                        Histórico limpo! 🐾 Como posso ajudar você agora?
                    </div>
                </div>
            `;
        }
    });

    // Inicia carregando o histórico anterior da sessão
    carregarHistoricoSalvo();
    input.focus();
});
