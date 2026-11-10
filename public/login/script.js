document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const identificadorInput = document.getElementById('identificador') || document.getElementById('cpfInput');
    const senhaInput = document.getElementById('senha');
    const btn = document.getElementById('entrar');
    const toggleSenhaBtn = document.getElementById('toggleSenha');
    const linkCadastro = document.getElementById('linkCadastro');
    const mensagemErro = document.getElementById('mensagemErro');
    const textoErro = document.getElementById('textoErro');

    // Suporte fluido para CPF (com máscara se apenas números) ou E-mail
    if (identificadorInput) {
        identificadorInput.addEventListener('input', (e) => {
            const raw = e.target.value;
            // Se for apenas dígitos e pontuação de CPF, aplica formatação automática
            if (/^[0-9.\-\s]*$/.test(raw)) {
                let v = raw.replace(/\D/g, '');
                if (v.length > 11) v = v.substring(0, 11);

                if (v.length > 9) {
                    v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
                } else if (v.length > 6) {
                    v = v.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
                } else if (v.length > 3) {
                    v = v.replace(/(\d{3})(\d{1,3})/, '$1.$2');
                }
                e.target.value = v;
            }
            ocultarErro();
        });
    }

    if (senhaInput) {
        senhaInput.addEventListener('input', ocultarErro);
    }

    // Toggle Mostrar/Ocultar Senha
    if (toggleSenhaBtn && senhaInput) {
        toggleSenhaBtn.addEventListener('click', () => {
            const isPassword = senhaInput.type === 'password';
            senhaInput.type = isPassword ? 'text' : 'password';
            toggleSenhaBtn.textContent = isPassword ? '🙈' : '👁️';
        });
    }

    function exibirErro(msg) {
        if (mensagemErro && textoErro) {
            textoErro.textContent = msg;
            mensagemErro.style.display = 'flex';
        } else {
            alert(msg);
        }
    }

    function ocultarErro() {
        if (mensagemErro) {
            mensagemErro.style.display = 'none';
        }
    }

    const getRedirectTarget = (usuario, jsonRedirect) => {
        const isAdmin = Boolean(usuario?.is_admin || ['admin', 'administrador'].includes(String(usuario?.tipo_usuario || '').toLowerCase()));
        const params = new URLSearchParams(window.location.search);
        const redirect = params.get('redirect');

        if (isAdmin) {
            if (redirect && redirect.includes('admin/')) {
                return redirect;
            }
            return '../admin/index.php';
        }

        // Usuário comum: não deve ser redirecionado para a área restrita admin
        if (redirect && !redirect.includes('admin/')) {
            return redirect;
        }

        const pendingPetId = sessionStorage.getItem('petalert_pending_pet_id');
        if (pendingPetId) {
            return `../chatDoador/index.html?petId=${encodeURIComponent(pendingPetId)}`;
        }
        const redirectUrl = sessionStorage.getItem('petalert_redirect_url');
        if (redirectUrl && !redirectUrl.includes('admin/')) {
            return redirectUrl;
        }

        return jsonRedirect || '../pets/index.html';
    };

    const salvarUsuarioLocal = (usuario) => {
        const normalizado = {
            id: usuario?.id ?? usuario?.id_usuario ?? null,
            id_usuario: usuario?.id_usuario ?? usuario?.id ?? null,
            nome: usuario?.nome || '',
            email: usuario?.email || '',
            cpf: usuario?.cpf || '',
            tipo_usuario: usuario?.tipo_usuario || 'adotante',
            is_admin: Boolean(usuario?.is_admin || ['admin', 'administrador'].includes(String(usuario?.tipo_usuario || '').toLowerCase())),
            foto: usuario?.foto || '',
            cidade: usuario?.cidade || '',
            estado: usuario?.estado || '',
            cep: usuario?.cep || ''
        };
        localStorage.setItem('petalert_usuario', JSON.stringify(normalizado));
        sessionStorage.setItem('petalert_usuario', JSON.stringify(normalizado));
    };

    if (linkCadastro) {
        linkCadastro.addEventListener('click', (e) => {
            e.preventDefault();
            const redirect = getRedirectTarget();
            window.location.href = `../cadastro/index.html?redirect=${encodeURIComponent(redirect)}`;
        });
    }

    // Ação de Login (Suporta CPF ou E-mail com a senha)
    async function realizarLogin() {
        const identificadorVal = identificadorInput ? identificadorInput.value.trim() : '';
        const senha = senhaInput ? senhaInput.value.trim() : '';

        if (!identificadorVal) {
            exibirErro('Por favor, informe seu CPF ou e-mail.');
            identificadorInput?.focus();
            return;
        }

        if (!senha) {
            exibirErro('Por favor, informe sua senha.');
            senhaInput?.focus();
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Entrando...';
        ocultarErro();

        try {
            const response = await fetch('../index.php?rota=auth&acao=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    identifier: identificadorVal,
                    senha: senha
                })
            });

            const json = await response.json().catch(() => ({}));

            if (!response.ok || json.status === 'erro') {
                exibirErro(json.mensagem || 'CPF/e-mail ou senha inválidos');
                return;
            }

            // Salva dados na sessão local
            salvarUsuarioLocal(json.usuario);

            // Redireciona com sucesso
            const destino = getRedirectTarget(json.usuario, json.redirect);
            window.location.href = destino;

        } catch (error) {
            console.error('Erro no login:', error);
            exibirErro('Não foi possível conectar ao servidor. Verifique o XAMPP e tente novamente.');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Entrar na Conta';
        }
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            realizarLogin();
        });
    }

    if (btn) {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            realizarLogin();
        });
    }
});
