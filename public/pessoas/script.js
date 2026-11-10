document.addEventListener('DOMContentLoaded', () => {
    let pessoas = [];
    const painelAdmin = document.getElementById('painelAdminPessoas');
    const painelNegado = document.getElementById('painelAcessoNegado');
    const erroDetalheMsg = document.getElementById('erroDetalheMsg');
    const filtroTexto = document.getElementById('filtroTexto');
    const filtroTipo = document.getElementById('filtroTipo');
    const contador = document.getElementById('contadorPessoas');
    const tabelaCorpo = document.getElementById('tabelaCorpo');
    const loading = document.getElementById('loading');
    const tabelaWrapper = document.getElementById('tabelaWrapper');
    const btnRecarregar = document.getElementById('btnRecarregarLista');

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatarTelefone(tel) {
        if (!tel) return '—';
        const num = tel.replace(/\D/g, '');
        if (num.length === 11) {
            return num.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (num.length === 10) {
            return num.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        return tel;
    }

    function formatarData(dataStr) {
        if (!dataStr) return '—';
        const partes = dataStr.split(' ')[0].split('-');
        if (partes.length === 3) {
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }
        return dataStr;
    }

    function obterUsuario() {
        try {
            const raw = localStorage.getItem('petalert_usuario') || sessionStorage.getItem('petalert_usuario');
            if (!raw || raw === 'null') return null;
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function checarAdmin(u) {
        if (!u) return false;
        const tipo = (u.tipo_usuario || '').toLowerCase();
        return Boolean(tipo === 'admin' || tipo === 'administrador' || u.is_admin);
    }

    function mostrarAcessoNegado(mensagem) {
        if (painelAdmin) painelAdmin.style.display = 'none';
        if (painelNegado) painelNegado.style.display = 'block';
        if (erroDetalheMsg) {
            erroDetalheMsg.textContent = JSON.stringify({
                "status": "erro",
                "erro": mensagem || "Acesso não autorizado"
            }, null, 2);
        }
    }

    async function validarAcessoEIniciar() {
        const usuarioLocal = obterUsuario();
        if (!checarAdmin(usuarioLocal)) {
            mostrarAcessoNegado("Acesso não autorizado");
            return;
        }

        // Validação rigorosa no backend
        try {
            const resAuth = await fetch('../index.php?rota=auth&acao=me');
            if (!resAuth.ok) {
                mostrarAcessoNegado("Acesso não autorizado");
                return;
            }
            const jsonAuth = await resAuth.json().catch(() => ({}));
            const dados = jsonAuth.dados || {};
            const tipoBackend = (dados.tipo_usuario || '').toLowerCase();
            const isAdminBackend = Boolean(tipoBackend === 'admin' || tipoBackend === 'administrador' || dados.is_admin);

            if (!isAdminBackend) {
                mostrarAcessoNegado("Acesso não autorizado");
                return;
            }

            // Acesso autenticado e verificado como administrador
            if (painelNegado) painelNegado.style.display = 'none';
            if (painelAdmin) painelAdmin.style.display = 'block';
            buscarPessoas();
        } catch (e) {
            // Em caso de falha de conexão com a rota me, a própria rota de pessoas protege com HTTP 403
            if (painelNegado) painelNegado.style.display = 'none';
            if (painelAdmin) painelAdmin.style.display = 'block';
            buscarPessoas();
        }
    }

    async function buscarPessoas() {
        if (loading) loading.style.display = 'block';
        if (tabelaWrapper) tabelaWrapper.style.display = 'none';

        try {
            const res = await fetch('../index.php?rota=usuarios');

            if (res.status === 403) {
                const errJson = await res.json().catch(() => ({}));
                mostrarAcessoNegado(errJson.erro || errJson.mensagem || "Acesso não autorizado");
                return;
            }

            const json = await res.json().catch(() => ({}));

            if (res.ok && json.status === 'sucesso' && Array.isArray(json.dados)) {
                pessoas = json.dados;
                renderizar();
            } else {
                if (tabelaCorpo) {
                    tabelaCorpo.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem; color:#b91c1c;">Não foi possível consultar os dados das pessoas cadastradas.</td></tr>`;
                }
            }
        } catch (err) {
            console.error('Erro ao consultar pessoas:', err);
            if (tabelaCorpo) {
                tabelaCorpo.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem; color:#b91c1c;">Erro de conexão com o servidor ao consultar o banco de dados.</td></tr>`;
            }
        } finally {
            if (loading) loading.style.display = 'none';
            if (tabelaWrapper && pessoas.length > 0) tabelaWrapper.style.display = 'block';
        }
    }

    function renderizar() {
        if (!tabelaCorpo) return;

        const termo = (filtroTexto?.value || '').trim().toLowerCase();
        const tipoFiltro = (filtroTipo?.value || '').trim().toLowerCase();

        const filtradas = pessoas.filter(p => {
            const nome = (p.nome || '').toLowerCase();
            const email = (p.email || '').toLowerCase();
            const cidade = (p.cidade || '').toLowerCase();
            const tipo = (p.tipo_usuario || 'adotante').toLowerCase();

            const bateTexto = !termo || nome.includes(termo) || email.includes(termo) || cidade.includes(termo);
            const bateTipo = !tipoFiltro || tipo === tipoFiltro;

            return bateTexto && bateTipo;
        });

        if (contador) {
            contador.textContent = `${filtradas.length} de ${pessoas.length} pessoa(s) no MySQL`;
        }

        if (filtradas.length === 0) {
            tabelaCorpo.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align:center; padding:2.5rem; color:var(--muted);">
                        Nenhuma pessoa encontrada para os filtros aplicados.
                    </td>
                </tr>
            `;
            return;
        }

        tabelaCorpo.innerHTML = filtradas.map(p => {
            const tipo = (p.tipo_usuario || 'adotante').toLowerCase();
            let badgeClass = 'adotante';
            let badgeLabel = '❤️ Adotante';
            if (tipo === 'admin' || tipo === 'administrador') {
                badgeClass = 'admin';
                badgeLabel = '🛡️ Admin';
            } else if (tipo === 'doador') {
                badgeClass = 'doador';
                badgeLabel = '🐾 Doador';
            }

            const cidadeEstado = (p.cidade && p.estado) ? `${p.cidade}/${p.estado}` : (p.cidade || p.estado || '—');
            const dataCad = formatarData(p.data_criacao || '');
            const tel = formatarTelefone(p.telefone || '');
            const foto = p.foto ? `<span style="margin-right:6px;">📷</span>` : ``;

            return `
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.85rem 1rem; font-weight: 600;">
                        ${foto}${escapeHtml(p.nome || 'Usuário')}
                    </td>
                    <td style="padding: 0.85rem 1rem;">
                        <span class="perfil-badge-tipo ${badgeClass}" style="font-size:0.75rem; padding: 3px 8px; border-radius: 99px;">
                            ${badgeLabel}
                        </span>
                    </td>
                    <td style="padding: 0.85rem 1rem; color: var(--muted);">${escapeHtml(p.email || '—')}</td>
                    <td style="padding: 0.85rem 1rem;">${tel}</td>
                    <td style="padding: 0.85rem 1rem;">${escapeHtml(cidadeEstado)}</td>
                    <td style="padding: 0.85rem 1rem; color: var(--muted);">${dataCad}</td>
                    <td style="padding: 0.85rem 1rem; color: #15803d; font-weight: 700;">● Ativo</td>
                </tr>
            `;
        }).join('');
    }

    if (btnRecarregar) {
        btnRecarregar.addEventListener('click', buscarPessoas);
    }

    if (filtroTexto) {
        filtroTexto.addEventListener('input', renderizar);
    }

    if (filtroTipo) {
        filtroTipo.addEventListener('change', renderizar);
    }

    validarAcessoEIniciar();
});
