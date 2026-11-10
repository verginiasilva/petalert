document.addEventListener('DOMContentLoaded', () => {
    let usuarios = [];
    let usuarioExcluirId = null;

    const tabela = document.getElementById('tabelaUsuarios');
    const filtroBusca = document.getElementById('filtroBusca');
    const filtroPerfil = document.getElementById('filtroPerfil');
    const filtroStatus = document.getElementById('filtroStatus');
    const contador = document.getElementById('contadorUsuarios');
    const alertaGeral = document.getElementById('alertaGeral');

    const modalForm = document.getElementById('modalFormUsuario');
    const formUsuario = document.getElementById('formUsuario');
    const modalDetalhes = document.getElementById('modalDetalhes');
    const modalExcluir = document.getElementById('modalExcluir');
    const alertaModal = document.getElementById('alertaModal');
    const alertaExcluir = document.getElementById('alertaExcluir');

    function exibirAlerta(el, tipo, msg) {
        if (!el) return;
        el.className = `alert-adm ${tipo}`;
        el.textContent = msg;
        el.style.display = 'block';
        setTimeout(() => {
            el.style.display = 'none';
        }, 5000);
    }

    function sanitizar(txt) {
        const d = document.createElement('div');
        d.textContent = txt ?? '';
        return d.innerHTML;
    }

    function formatarCpf(cpf) {
        const c = String(cpf || '').replace(/\D/g, '');
        if (c.length === 11) {
            return c.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }
        return cpf || '—';
    }

    async function carregarUsuarios() {
        tabela.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:2rem;">Carregando usuários...</td></tr>';
        try {
            const res = await fetch('../index.php?rota=admin&acao=usuarios');
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.status !== 'sucesso') {
                exibirAlerta(alertaGeral, 'error', json.mensagem || 'Erro ao carregar lista de usuários.');
                tabela.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:2rem; color:#DC2626;">Acesso negado ou erro ao carregar.</td></tr>';
                return;
            }
            usuarios = Array.isArray(json.dados) ? json.dados : [];
            renderizarTabela();
        } catch (e) {
            exibirAlerta(alertaGeral, 'error', 'Erro de conexão com o servidor.');
        }
    }

    function renderizarTabela() {
        const termo = (filtroBusca.value || '').trim().toLowerCase();
        const perfil = (filtroPerfil.value || '').trim().toLowerCase();
        const status = filtroStatus.value;

        const filtrados = usuarios.filter(u => {
            const nome = (u.nome || '').toLowerCase();
            const email = (u.email || '').toLowerCase();
            const cpf = (u.cpf || '').toLowerCase();
            const tipo = (u.tipo_usuario || '').toLowerCase();
            const ativo = String(u.ativo ?? 1);

            const bateTermo = !termo || nome.includes(termo) || email.includes(termo) || cpf.includes(termo);
            const batePerfil = !perfil || (perfil === 'admin' ? ['admin', 'administrador'].includes(tipo) : tipo === perfil);
            const bateStatus = status === '' || ativo === status;

            return bateTermo && batePerfil && bateStatus;
        });

        contador.textContent = `${filtrados.length} de ${usuarios.length} usuário(s)`;

        if (!filtrados.length) {
            tabela.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhum usuário localizado com os filtros selecionados.</td></tr>';
            return;
        }

        tabela.innerHTML = filtrados.map(u => {
            const id = u.id_usuario;
            const tipo = (u.tipo_usuario || 'adotante').toLowerCase();
            const isAdmin = ['admin', 'administrador'].includes(tipo);
            const isAtivo = Number(u.ativo) === 1;

            let badgePerfil = `<span class="badge badge-info">${sanitizar(tipo)}</span>`;
            if (isAdmin) badgePerfil = `<span class="badge badge-admin">⭐ Admin</span>`;
            else if (tipo === 'doador') badgePerfil = `<span class="badge badge-warning">🤝 Doador</span>`;
            else badgePerfil = `<span class="badge badge-info">🏡 Adotante</span>`;

            const badgeStatus = isAtivo 
                ? `<span class="badge badge-success">Ativo</span>`
                : `<span class="badge badge-danger">Bloqueado</span>`;

            let fotoHtml = `<div style="width:36px; height:36px; border-radius:50%; background:#F3F4F6; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">👤</div>`;
            if (u.foto && u.foto !== '0') {
                const src = u.foto.startsWith('http') ? u.foto : `../${u.foto.replace(/^\.\.\//, '')}`;
                fotoHtml = `<img src="${sanitizar(src)}" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:1px solid #E5E7EB;" onerror="this.src='../img/usuario.png'">`;
            }

            return `
                <tr>
                    <td><strong>#${id}</strong></td>
                    <td>${fotoHtml}</td>
                    <td><strong>${sanitizar(u.nome)}</strong></td>
                    <td>${sanitizar(u.email)}</td>
                    <td>${sanitizar(formatarCpf(u.cpf))}</td>
                    <td>${sanitizar(u.telefone || '—')}</td>
                    <td>${badgePerfil}</td>
                    <td>${badgeStatus}</td>
                    <td style="text-align:right; white-space:nowrap;">
                        <button type="button" class="btn-secondary btn-sm btn-detalhes" data-id="${id}" title="Ver detalhes completos">👁️</button>
                        <button type="button" class="btn-secondary btn-sm btn-editar" data-id="${id}" title="Editar cadastro">✏️</button>
                        <button type="button" class="${isAtivo ? 'btn-danger-sm' : 'btn-success-sm'} btn-sm btn-status" data-id="${id}" data-ativo="${u.ativo}" title="${isAtivo ? 'Bloquear conta' : 'Desbloquear conta'}">
                            ${isAtivo ? '🔒 Bloquear' : '🔓 Ativar'}
                        </button>
                        <button type="button" class="btn-danger-sm btn-sm btn-excluir" data-id="${id}" data-nome="${sanitizar(u.nome)}" title="Excluir usuário">🗑️</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Eventos de filtro
    filtroBusca.addEventListener('input', renderizarTabela);
    filtroPerfil.addEventListener('change', renderizarTabela);
    filtroStatus.addEventListener('change', renderizarTabela);

    // Delegação de cliques na tabela
    tabela.addEventListener('click', async (e) => {
        const btnDetalhes = e.target.closest('.btn-detalhes');
        const btnEditar = e.target.closest('.btn-editar');
        const btnStatus = e.target.closest('.btn-status');
        const btnExcluir = e.target.closest('.btn-excluir');

        if (btnDetalhes) {
            const id = Number(btnDetalhes.dataset.id);
            const user = usuarios.find(u => Number(u.id_usuario) === id);
            if (user) abrirDetalhes(user);
        } else if (btnEditar) {
            const id = Number(btnEditar.dataset.id);
            const user = usuarios.find(u => Number(u.id_usuario) === id);
            if (user) abrirFormEdicao(user);
        } else if (btnStatus) {
            const id = Number(btnStatus.dataset.id);
            await alternarStatusUsuario(id);
        } else if (btnExcluir) {
            const id = Number(btnExcluir.dataset.id);
            const nome = btnExcluir.dataset.nome;
            abrirModalExclusao(id, nome);
        }
    });

    // Visualizar detalhes completos
    function abrirDetalhes(u) {
        const corpo = document.getElementById('corpoDetalhes');
        corpo.innerHTML = `
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid #F3EDE2;">
                <div style="font-size:2.5rem;">👤</div>
                <div>
                    <h4 style="margin:0; font-size:1.2rem; color:var(--text-main);">${sanitizar(u.nome)}</h4>
                    <span style="font-size:0.85rem; color:var(--text-muted);">Cadastrado em: ${sanitizar(u.data_criacao || '—')}</span>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.8rem;">
                <div><strong>ID:</strong> #${u.id_usuario}</div>
                <div><strong>Perfil:</strong> ${sanitizar(u.tipo_usuario)}</div>
                <div><strong>CPF:</strong> ${sanitizar(formatarCpf(u.cpf))}</div>
                <div><strong>Data de Nascimento:</strong> ${sanitizar(u.data_nascimento || '—')} (${u.idade || '—'} anos)</div>
                <div><strong>E-mail:</strong> ${sanitizar(u.email)}</div>
                <div><strong>Telefone:</strong> ${sanitizar(u.telefone || '—')}</div>
                <div><strong>Status da Conta:</strong> ${Number(u.ativo) === 1 ? '🟢 Ativo' : '🔴 Bloqueado'}</div>
                <div><strong>Status de Identidade:</strong> ${sanitizar(u.status_identidade || 'pendente')}</div>
                <div style="grid-column: 1 / -1;"><strong>Endereço Completo:</strong> ${sanitizar(u.endereco || `${u.rua}, ${u.numero} - ${u.bairro} - ${u.cidade}/${u.estado} - CEP ${u.cep}`)}</div>
            </div>
        `;
        modalDetalhes.classList.add('open');
    }

    document.getElementById('btnFecharDetalhes')?.addEventListener('click', () => modalDetalhes.classList.remove('open'));
    document.getElementById('btnFecharDetalhes2')?.addEventListener('click', () => modalDetalhes.classList.remove('open'));

    // Alternar status ativo/bloqueado
    async function alternarStatusUsuario(id) {
        if (!confirm('Deseja realmente alterar o status (bloquear/desbloquear) deste usuário?')) return;
        try {
            const res = await fetch('../index.php?rota=admin&acao=usuario-toggle-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_usuario: id })
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.status === 'sucesso') {
                exibirAlerta(alertaGeral, 'success', json.mensagem);
                await carregarUsuarios();
            } else {
                exibirAlerta(alertaGeral, 'error', json.mensagem || 'Não foi possível alterar o status do usuário.');
            }
        } catch (e) {
            exibirAlerta(alertaGeral, 'error', 'Erro ao conectar ao servidor.');
        }
    }

    // Modal de Cadastro
    document.getElementById('btnNovoUsuario')?.addEventListener('click', () => {
        formUsuario.reset();
        document.getElementById('formIdUsuario').value = '';
        document.getElementById('modalFormTitulo').textContent = 'Cadastrar Novo Usuário';
        document.getElementById('labelSenha').textContent = 'Senha de Acesso *';
        document.getElementById('formSenha').required = true;
        alertaModal.style.display = 'none';
        modalForm.classList.add('open');
    });

    // Modal de Edição
    function abrirFormEdicao(u) {
        formUsuario.reset();
        document.getElementById('formIdUsuario').value = u.id_usuario;
        document.getElementById('modalFormTitulo').textContent = `Editar Usuário #${u.id_usuario}`;
        document.getElementById('labelSenha').textContent = 'Nova Senha (deixe em branco para manter a atual)';
        document.getElementById('formSenha').required = false;

        document.getElementById('formNome').value = u.nome || '';
        document.getElementById('formEmail').value = u.email || '';
        document.getElementById('formCpf').value = u.cpf || '';
        document.getElementById('formTelefone').value = u.telefone || '';
        document.getElementById('formDataNascimento').value = u.data_nascimento || '';
        document.getElementById('formTipo').value = (u.tipo_usuario || 'adotante').toLowerCase();
        document.getElementById('formCep').value = u.cep || '';
        document.getElementById('formRua').value = u.rua || '';
        document.getElementById('formNumero').value = u.numero || '';
        document.getElementById('formBairro').value = u.bairro || '';
        document.getElementById('formCidade').value = u.cidade || '';
        document.getElementById('formEstado').value = u.estado || '';

        alertaModal.style.display = 'none';
        modalForm.classList.add('open');
    }

    document.getElementById('btnFecharForm')?.addEventListener('click', () => modalForm.classList.remove('open'));
    document.getElementById('btnCancelarForm')?.addEventListener('click', () => modalForm.classList.remove('open'));

    // Submissão do Formulário de Usuário (Criar / Editar)
    formUsuario?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('formIdUsuario').value;
        const isEdicao = Boolean(id);

        const payload = {
            id_usuario: id,
            id: id,
            nome: document.getElementById('formNome').value.trim(),
            email: document.getElementById('formEmail').value.trim(),
            cpf: document.getElementById('formCpf').value.replace(/\D/g, ''),
            telefone: document.getElementById('formTelefone').value.trim(),
            data_nascimento: document.getElementById('formDataNascimento').value,
            tipo_usuario: document.getElementById('formTipo').value,
            cep: document.getElementById('formCep').value.trim(),
            rua: document.getElementById('formRua').value.trim(),
            numero: document.getElementById('formNumero').value.trim(),
            bairro: document.getElementById('formBairro').value.trim(),
            cidade: document.getElementById('formCidade').value.trim(),
            estado: document.getElementById('formEstado').value.trim().toUpperCase(),
            senha: document.getElementById('formSenha').value.trim()
        };

        const endpoint = isEdicao 
            ? '../index.php?rota=usuarios&acao=admin-atualizar'
            : '../index.php?rota=usuarios&acao=admin-criar';

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json().catch(() => ({}));

            if (res.ok && (json.status === 'sucesso' || json.status === 'ok')) {
                modalForm.classList.remove('open');
                exibirAlerta(alertaGeral, 'success', json.mensagem || 'Usuário salvo com sucesso!');
                await carregarUsuarios();
            } else {
                exibirAlerta(alertaModal, 'error', json.mensagem || 'Erro ao salvar usuário.');
            }
        } catch (err) {
            exibirAlerta(alertaModal, 'error', 'Erro ao conectar ao servidor.');
        }
    });

    // Exclusão Segura
    function abrirModalExclusao(id, nome) {
        usuarioExcluirId = id;
        document.getElementById('textoExclusao').innerHTML = `Tem certeza que deseja excluir o usuário <strong>${sanitizar(nome)}</strong> (ID #${id})?<br><br><small style="color:#6B7280;">O sistema validará chaves estrangeiras e vínculos com animais, chats, denúncias e doações para garantir a integridade dos dados.</small>`;
        alertaExcluir.style.display = 'none';
        modalExcluir.classList.add('open');
    }

    document.getElementById('btnFecharExcluir')?.addEventListener('click', () => modalExcluir.classList.remove('open'));
    document.getElementById('btnCancelarExcluir')?.addEventListener('click', () => modalExcluir.classList.remove('open'));

    document.getElementById('btnConfirmarExcluir')?.addEventListener('click', async () => {
        if (!usuarioExcluirId) return;

        try {
            const res = await fetch('../index.php?rota=usuarios&acao=admin-excluir', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_usuario: usuarioExcluirId })
            });
            const json = await res.json().catch(() => ({}));

            if (res.ok && (json.status === 'sucesso' || json.status === 'ok')) {
                modalExcluir.classList.remove('open');
                exibirAlerta(alertaGeral, 'success', json.mensagem || 'Usuário excluído com sucesso.');
                await carregarUsuarios();
            } else {
                exibirAlerta(alertaExcluir, 'error', json.mensagem || 'Não foi possível excluir o usuário.');
            }
        } catch (e) {
            exibirAlerta(alertaExcluir, 'error', 'Erro ao conectar ao servidor.');
        }
    });

    // Carga inicial
    carregarUsuarios();
});
