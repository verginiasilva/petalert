/**
 * PetAlert - Meu Perfil, Configurações, Alteração de Senha, Logout e Exclusão de Conta (Requisito 34)
 */

document.addEventListener('DOMContentLoaded', async () => {
    // Elementos principais
    const avatarLetra = document.getElementById('avatarLetra');
    const heroNome = document.getElementById('heroNome');
    const heroEmail = document.getElementById('heroEmail');
    const heroBadgePerfil = document.getElementById('heroBadgePerfil');
    const heroMembroDesde = document.getElementById('heroMembroDesde');
    const feedbackAlerta = document.getElementById('feedbackAlerta');

    // Elementos de Verificação de Identidade
    const heroBadgeIdentidade = document.getElementById('heroBadgeIdentidade');
    const cardStatusIdentidade = document.getElementById('cardStatusIdentidade');
    const iconeStatusIdentidade = document.getElementById('iconeStatusIdentidade');
    const tituloStatusIdentidade = document.getElementById('tituloStatusIdentidade');
    const descStatusIdentidade = document.getElementById('descStatusIdentidade');

    // Abas
    const tabs = document.querySelectorAll('.perfil-tab');
    const tabContents = document.querySelectorAll('.perfil-tab-content');

    // Visualização e Edição
    const painelVisualizacao = document.getElementById('painelVisualizacao');
    const painelEdicao = document.getElementById('painelEdicao');
    const btnIniciarEdicao = document.getElementById('btnIniciarEdicao');
    const btnCancelarEdicao = document.getElementById('btnCancelarEdicao');
    const btnDescartarEdicao = document.getElementById('btnDescartarEdicao');
    const formEditarPerfil = document.getElementById('formEditarPerfil');

    // Campos de visualização
    const viewNome = document.getElementById('viewNome');
    const viewCpf = document.getElementById('viewCpf');
    const viewDataNasc = document.getElementById('viewDataNasc');
    const viewEmail = document.getElementById('viewEmail');
    const viewTelefone = document.getElementById('viewTelefone');
    const viewCep = document.getElementById('viewCep');
    const viewRua = document.getElementById('viewRua');
    const viewNumero = document.getElementById('viewNumero');
    const viewBairro = document.getElementById('viewBairro');
    const viewCidadeEstado = document.getElementById('viewCidadeEstado');
    const viewEnderecoCompleto = document.getElementById('viewEnderecoCompleto');

    // Campos de edição
    const editNome = document.getElementById('editNome');
    const editCpf = document.getElementById('editCpf');
    const editDataNasc = document.getElementById('editDataNasc');
    const editEmail = document.getElementById('editEmail');
    const editTelefone = document.getElementById('editTelefone');
    const editCep = document.getElementById('editCep');
    const editCepStatus = document.getElementById('editCepStatus');
    const editRua = document.getElementById('editRua');
    const editNumero = document.getElementById('editNumero');
    const editBairro = document.getElementById('editBairro');
    const editCidade = document.getElementById('editCidade');
    const editEstado = document.getElementById('editEstado');
    const editTipoUsuario = document.getElementById('editTipoUsuario');

    // Configurações e Resumo
    const configIdUsuario = document.getElementById('configIdUsuario');
    const configTipoPerfil = document.getElementById('configTipoPerfil');
    const configDataCadastro = document.getElementById('configDataCadastro');

    // Formulário Alterar Senha
    const formAlterarSenha = document.getElementById('formAlterarSenha');
    const senhaAtualInput = document.getElementById('senhaAtual');
    const novaSenhaInput = document.getElementById('novaSenha');
    const confirmarNovaSenhaInput = document.getElementById('confirmarNovaSenha');

    // Modais
    const modalConfirmarLogout = document.getElementById('modalConfirmarLogout');
    const btnSairHeader = document.getElementById('btnSairHeader');
    const btnSairConta = document.getElementById('btnSairConta');
    const btnCancelarLogout = document.getElementById('btnCancelarLogout');
    const btnConfirmarLogout = document.getElementById('btnConfirmarLogout');

    const modalExcluirConta = document.getElementById('modalExcluirConta');
    const btnAbrirModalExcluir = document.getElementById('btnAbrirModalExcluir');
    const btnCancelarExcluir = document.getElementById('btnCancelarExclusao');
    const formConfirmarExclusao = document.getElementById('formConfirmarExclusao');
    const senhaConfirmacaoExclusao = document.getElementById('senhaConfirmacaoExclusao');
    const textoConfirmacaoExclusao = document.getElementById('textoConfirmacaoExclusao');
    const checkTermoExclusao = document.getElementById('checkTermoExclusao');

    let dadosUsuarioAtual = null;

    // 1. Funções Auxiliares de Formatação e Feedback
    function exibirAlerta(msg, tipo = 'sucesso') {
        if (!feedbackAlerta) return;
        feedbackAlerta.textContent = msg;
        feedbackAlerta.className = `feedback-alerta ${tipo}`;
        feedbackAlerta.style.display = 'flex';
        window.scrollTo({ top: feedbackAlerta.offsetTop - 80, behavior: 'smooth' });

        if (tipo === 'sucesso') {
            setTimeout(() => {
                feedbackAlerta.style.display = 'none';
            }, 6000);
        }
    }

    function ocultarAlerta() {
        if (feedbackAlerta) feedbackAlerta.style.display = 'none';
    }

    function formatarCpf(cpf) {
        if (!cpf) return 'Não informado';
        const num = cpf.replace(/\D/g, '');
        if (num.length === 11) {
            return num.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }
        return cpf;
    }

    function formatarTelefone(tel) {
        if (!tel) return 'Não informado';
        const num = tel.replace(/\D/g, '');
        if (num.length === 11) {
            return num.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (num.length === 10) {
            return num.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        return tel;
    }

    function formatarData(dataStr) {
        if (!dataStr) return 'Não informada';
        const partes = dataStr.split(' ')[0].split('-');
        if (partes.length === 3) {
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }
        return dataStr;
    }

    // 2. Carregar Dados do Usuário Autenticado
    async function carregarDadosPerfil() {
        try {
            const res = await fetch('../index.php?rota=auth&acao=me');
            if (res.status === 401) {
                // Usuário não autenticado: redireciona para login
                window.location.href = `../login/index.html?redirect=${encodeURIComponent(window.location.href)}`;
                return;
            }

            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.status !== 'sucesso' || !json.dados) {
                exibirAlerta('Erro ao carregar dados do usuário autenticado.', 'erro');
                return;
            }

            dadosUsuarioAtual = json.dados;
            preencherInterface(dadosUsuarioAtual);
        } catch (err) {
            console.error('Erro de conexão ao buscar perfil:', err);
            // Fallback para storage se conexão falhar
            const localRaw = localStorage.getItem('petalert_usuario') || sessionStorage.getItem('petalert_usuario');
            if (localRaw) {
                dadosUsuarioAtual = JSON.parse(localRaw);
                preencherInterface(dadosUsuarioAtual);
            } else {
                window.location.href = '../login/index.html';
            }
        }
    }

    function preencherInterface(u) {
        const nome = u.nome || 'Usuário';
        const email = u.email || '';
        const tipo = (u.tipo_usuario || 'adotante').toLowerCase();
        const inicial = nome.trim().charAt(0).toUpperCase() || 'U';

        // Cabeçalho e Foto de Perfil
        const avatarFotoImg = document.getElementById('avatarFotoImg');
        const fotoUsuario = u.foto || null;
        if (fotoUsuario) {
            let srcFoto = fotoUsuario;
            if (!srcFoto.startsWith('http://') && !srcFoto.startsWith('https://') && !srcFoto.startsWith('data:')) {
                srcFoto = srcFoto.startsWith('../') ? srcFoto : '../' + srcFoto.replace(/^\//, '');
            }
            if (avatarFotoImg) {
                avatarFotoImg.src = srcFoto;
                avatarFotoImg.style.display = 'block';
                avatarFotoImg.onerror = () => {
                    avatarFotoImg.style.display = 'none';
                    if (avatarLetra) {
                        avatarLetra.textContent = inicial;
                        avatarLetra.style.display = 'block';
                    }
                };
            }
            if (avatarLetra) avatarLetra.style.display = 'none';
        } else {
            if (avatarFotoImg) avatarFotoImg.style.display = 'none';
            if (avatarLetra) {
                avatarLetra.textContent = inicial;
                avatarLetra.style.display = 'block';
            }
        }

        if (heroNome) heroNome.textContent = nome;
        if (heroEmail) heroEmail.textContent = email;
        const isAdmin = Boolean(tipo === 'admin' || tipo === 'administrador' || u.is_admin);

        if (heroBadgePerfil) {
            if (isAdmin) {
                heroBadgePerfil.textContent = '🛡️ Administrador';
                heroBadgePerfil.className = 'perfil-badge-tipo admin';
            } else {
                heroBadgePerfil.textContent = tipo === 'doador' ? '🐾 Doador' : '❤️ Adotante';
                heroBadgePerfil.className = `perfil-badge-tipo ${tipo}`;
            }
        }
        // Abas especiais para Doador
        const tabBtnMeusPets = document.getElementById('tabBtnMeusPets');
        if (tabBtnMeusPets) {
            tabBtnMeusPets.style.display = tipo === 'doador' ? 'inline-flex' : 'none';
        }

        // Aba exclusiva para Administradores: Pessoas Cadastradas
        const tabBtnPessoas = document.getElementById('tabBtnPessoas');
        if (tabBtnPessoas) {
            tabBtnPessoas.style.display = isAdmin ? 'inline-flex' : 'none';
        }

        if (heroMembroDesde && u.data_criacao) {
            heroMembroDesde.textContent = `📅 Membro desde ${formatarData(u.data_criacao)}`;
        }

        // Painel de Visualização
        if (viewNome) viewNome.textContent = nome;
        if (viewCpf) viewCpf.textContent = formatarCpf(u.cpf);
        if (viewDataNasc) viewDataNasc.textContent = formatarData(u.data_nascimento);
        if (viewEmail) viewEmail.textContent = email;
        if (viewTelefone) viewTelefone.textContent = formatarTelefone(u.telefone);
        if (viewCep) viewCep.textContent = u.cep || 'Não informado';
        if (viewRua) viewRua.textContent = u.rua || 'Não informado';
        if (viewNumero) viewNumero.textContent = u.numero || 'S/N';
        if (viewBairro) viewBairro.textContent = u.bairro || 'Não informado';
        if (viewCidadeEstado) {
            viewCidadeEstado.textContent = (u.cidade && u.estado) ? `${u.cidade} - ${u.estado}` : (u.cidade || u.estado || 'Não informado');
        }
        if (viewEnderecoCompleto) {
            viewEnderecoCompleto.textContent = u.endereco || [u.rua, u.numero ? `nº ${u.numero}` : null, u.bairro, u.cidade, u.estado].filter(Boolean).join(', ') || 'Não informado';
        }

        // Situação da Verificação de Identidade
        atualizarStatusIdentidadeUI(u.status_identidade);

        // Configurações
        if (configIdUsuario) configIdUsuario.textContent = `#${u.id_usuario || u.id || '—'}`;
        if (configTipoPerfil) {
            if (isAdmin) {
                configTipoPerfil.textContent = 'Administrador do Sistema (Acesso Total)';
            } else {
                configTipoPerfil.textContent = tipo === 'doador' ? 'Doador (Tutor de Pets)' : 'Adotante (Interessado em Adoção)';
            }
        }
        if (configDataCadastro) configDataCadastro.textContent = formatarData(u.data_criacao);

        // Atualiza localStorage
        localStorage.setItem('petalert_usuario', JSON.stringify(u));
        sessionStorage.setItem('petalert_usuario', JSON.stringify(u));
    }

    function atualizarStatusIdentidadeUI(status) {
        status = (status || 'pendente').toLowerCase();

        const statusMap = {
            verificado: {
                classe: 'verificado',
                badgeIcone: '🟢',
                badgeTexto: 'Identidade Verificada',
                cardIcone: '✅',
                cardTitulo: 'Identidade Confirmada',
                cardDesc: 'Seu CPF, Nome Completo e Data de Nascimento foram conferidos e confirmados com sucesso junto ao serviço oficial de identidade.'
            },
            pendente: {
                classe: 'pendente',
                badgeIcone: '🟡',
                badgeTexto: 'Verificação Pendente',
                cardIcone: '⏳',
                cardTitulo: 'Verificação de Identidade: Pendente',
                cardDesc: 'Seus dados cadastrais foram pré-validados no sistema e aguardam a ativação do endpoint oficial de verificação.'
            },
            falha: {
                classe: 'falha',
                badgeIcone: '🔴',
                badgeTexto: 'Falha na Verificação',
                cardIcone: '⚠️',
                cardTitulo: 'Incompatibilidade de Dados Cadastrais',
                cardDesc: 'Os dados informados (CPF, Nome ou Data de Nascimento) não coincidem com o registro oficial. Edite suas informações com dados compatíveis.'
            },
            nao_verificado: {
                classe: 'nao_verificado',
                badgeIcone: '⚪',
                badgeTexto: 'Não Verificado',
                cardIcone: 'ℹ️',
                cardTitulo: 'Identidade Não Verificada',
                cardDesc: 'Esta conta ainda não possui verificação cadastral de identidade registrada.'
            }
        };

        const info = statusMap[status] || statusMap.pendente;

        if (heroBadgeIdentidade) {
            heroBadgeIdentidade.className = `badge-identidade ${info.classe}`;
            heroBadgeIdentidade.textContent = `${info.badgeIcone} ${info.badgeTexto}`;
        }

        if (cardStatusIdentidade) {
            cardStatusIdentidade.className = `identidade-status-card ${info.classe}`;
        }
        if (iconeStatusIdentidade) iconeStatusIdentidade.textContent = info.cardIcone;
        if (tituloStatusIdentidade) tituloStatusIdentidade.textContent = info.cardTitulo;
        if (descStatusIdentidade) descStatusIdentidade.textContent = info.cardDesc;
    }

    function popularCamposEdicao() {
        if (!dadosUsuarioAtual) return;
        const u = dadosUsuarioAtual;

        if (editNome) editNome.value = u.nome || '';
        if (editCpf) editCpf.value = formatarCpf(u.cpf || '');
        if (editDataNasc) editDataNasc.value = u.data_nascimento ? u.data_nascimento.split(' ')[0] : '';
        if (editEmail) editEmail.value = u.email || '';
        if (editTelefone) editTelefone.value = formatarTelefone(u.telefone || '');
        if (editCep) editCep.value = u.cep || '';
        if (editRua) editRua.value = u.rua || '';
        if (editNumero) editNumero.value = u.numero || '';
        if (editBairro) editBairro.value = u.bairro || '';
        if (editCidade) editCidade.value = u.cidade || '';
        if (editEstado) editEstado.value = u.estado || '';
        if (editTipoUsuario) editTipoUsuario.value = (u.tipo_usuario || 'adotante').toLowerCase();
    }

    // 3. Alternância de Abas
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            tabContents.forEach(c => c.style.display = 'none');

            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');
            const targetId = tab.dataset.tab;
            const content = document.getElementById(targetId);
            if (content) content.style.display = 'block';

            ocultarAlerta();
        });
    });

    // Suporte para hash na URL (#seguranca ou #config ou #pessoas)
    const hash = window.location.hash;
    if (hash === '#seguranca') {
        const tabSeg = document.getElementById('tabBtnSeguranca');
        if (tabSeg) tabSeg.click();
    } else if (hash === '#config' || hash === '#excluir') {
        const tabConf = document.getElementById('tabBtnConfig');
        if (tabConf) tabConf.click();
        if (hash === '#excluir' && modalExcluirConta) {
            modalExcluirConta.style.display = 'flex';
        }
    } else if (hash === '#pessoas') {
        const tabPessoas = document.getElementById('tabBtnPessoas');
        if (tabPessoas && tabPessoas.style.display !== 'none') {
            tabPessoas.click();
        } else {
            const tabDados = document.getElementById('tabBtnDados');
            if (tabDados) tabDados.click();
        }
    }

    // 4. Edição de Informações
    if (btnIniciarEdicao) {
        btnIniciarEdicao.addEventListener('click', () => {
            popularCamposEdicao();
            painelVisualizacao.style.display = 'none';
            painelEdicao.style.display = 'block';
            ocultarAlerta();
        });
    }

    const fecharModoEdicao = () => {
        painelEdicao.style.display = 'none';
        painelVisualizacao.style.display = 'block';
    };

    if (btnCancelarEdicao) btnCancelarEdicao.addEventListener('click', fecharModoEdicao);
    if (btnDescartarEdicao) btnDescartarEdicao.addEventListener('click', fecharModoEdicao);

    // Consulta automática ViaCEP no campo de edição
    if (editCep) {
        editCep.addEventListener('input', async (e) => {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 8) v = v.slice(0, 8);
            if (v.length > 5) v = v.replace(/(\d{5})(\d{1,3})/, '$1-$2');
            e.target.value = v;

            const cepLimpo = v.replace(/\D/g, '');
            if (cepLimpo.length === 8) {
                if (editCepStatus) {
                    editCepStatus.textContent = 'Buscando CEP...';
                    editCepStatus.style.color = 'var(--primary-dark)';
                }
                try {
                    const res = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
                    const data = await res.json();
                    if (!data.erro) {
                        if (editRua && data.logradouro) editRua.value = data.logradouro;
                        if (editBairro && data.bairro) editBairro.value = data.bairro;
                        if (editCidade && data.localidade) editCidade.value = data.localidade;
                        if (editEstado && data.uf) editEstado.value = data.uf;
                        if (editCepStatus) {
                            editCepStatus.textContent = 'Endereço encontrado!';
                            editCepStatus.style.color = '#15803d';
                        }
                        if (editNumero) editNumero.focus();
                    } else if (editCepStatus) {
                        editCepStatus.textContent = 'CEP não encontrado.';
                        editCepStatus.style.color = '#b91c1c';
                    }
                } catch (err) {
                    console.error('Erro ViaCEP:', err);
                }
            }
        });
    }

    // Máscara de CPF na edição
    if (editCpf) {
        editCpf.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 9) v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
            else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            else if (v.length > 3) v = v.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            e.target.value = v;
        });
    }

    // Máscara de Telefone na edição
    if (editTelefone) {
        editTelefone.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 10) v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            else if (v.length > 5) v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            else if (v.length > 2) v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            e.target.value = v;
        });
    }

    // Salvar Edição de Informações Pessoais com Revalidação de Identidade
    if (formEditarPerfil) {
        formEditarPerfil.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nomeLimpo = editNome?.value.trim() || '';
            const cpfLimpo = editCpf?.value.replace(/\D/g, '') || '';
            const dataNascLimpa = editDataNasc?.value ? editDataNasc.value.split(' ')[0] : '';

            // Detecta se houve alteração em campos de identidade
            const cpfAtual = (dadosUsuarioAtual?.cpf || '').replace(/\D/g, '');
            const nomeAtual = (dadosUsuarioAtual?.nome || '').trim().toLowerCase();
            const dataAtual = (dadosUsuarioAtual?.data_nascimento || '').split(' ')[0];

            const mudouIdentidade = (
                cpfLimpo !== cpfAtual ||
                nomeLimpo.toLowerCase() !== nomeAtual ||
                dataNascLimpa !== dataAtual
            );

            // Validação local prévia se alterou campos de identidade
            if (mudouIdentidade) {
                const partesNome = nomeLimpo.replace(/\s+/g, ' ').split(' ');
                if (partesNome.length < 2 || nomeLimpo.length < 4) {
                    exibirAlerta('Para alterar sua identidade, informe seu nome completo (nome e sobrenome).', 'erro');
                    return;
                }

                if (cpfLimpo) {
                    if (cpfLimpo.length !== 11) {
                        exibirAlerta('O CPF deve possuir exatamente 11 dígitos.', 'erro');
                        return;
                    }
                    if (/^(\d)\1{10}$/.test(cpfLimpo)) {
                        exibirAlerta('O CPF informado possui dígitos repetidos inválidos.', 'erro');
                        return;
                    }
                    for (let t = 9; t < 11; t++) {
                        let d = 0;
                        for (let c = 0; c < t; c++) {
                            d += parseInt(cpfLimpo.charAt(c), 10) * ((t + 1) - c);
                        }
                        d = ((10 * d) % 11) % 10;
                        if (parseInt(cpfLimpo.charAt(t), 10) !== d) {
                            exibirAlerta('O CPF informado possui dígitos verificadores matematicamente inválidos.', 'erro');
                            return;
                        }
                    }
                }

                if (dataNascLimpa) {
                    const hoje = new Date().toISOString().split('T')[0];
                    const ano = parseInt(dataNascLimpa.split('-')[0], 10);
                    if (dataNascLimpa >= hoje || ano < 1900) {
                        exibirAlerta('A data de nascimento informada é inválida ou está no futuro.', 'erro');
                        return;
                    }
                }
            }

            const payload = {
                nome: nomeLimpo,
                cpf: cpfLimpo,
                data_nascimento: dataNascLimpa,
                email: editEmail?.value.trim() || '',
                telefone: editTelefone?.value.trim() || '',
                cep: editCep?.value.trim() || '',
                rua: editRua?.value.trim() || '',
                numero: editNumero?.value.trim() || '',
                bairro: editBairro?.value.trim() || '',
                cidade: editCidade?.value.trim() || '',
                estado: editEstado?.value.trim().toUpperCase() || '',
                tipo_usuario: editTipoUsuario?.value || 'adotante'
            };

            const btnSalvar = document.getElementById('btnSalvarPerfil');
            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.textContent = mudouIdentidade ? 'Validando identidade e salvando...' : 'Salvando alterações...';
            }

            try {
                const res = await fetch('../index.php?rota=usuarios', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const json = await res.json().catch(() => ({}));

                if (res.ok && (json.status === 'sucesso' || json.dados)) {
                    exibirAlerta(json.mensagem || 'Informações pessoais salvas com sucesso!', 'sucesso');
                    if (json.dados) {
                        dadosUsuarioAtual = json.dados;
                        preencherInterface(dadosUsuarioAtual);
                    } else {
                        await carregarDadosPerfil();
                    }
                    fecharModoEdicao();
                    if (listaPessoasGeral.length > 0) {
                        carregarListaPessoas();
                    }
                } else {
                    // Se falhar (ex: dados incompatíveis na API ou CPF já cadastrado)
                    exibirAlerta(json.mensagem || 'Erro ao salvar alterações no perfil. Seus dados anteriores foram preservados.', 'erro');
                }
            } catch (err) {
                console.error(err);
                exibirAlerta('Falha de comunicação com o servidor ao atualizar dados.', 'erro');
            } finally {
                if (btnSalvar) {
                    btnSalvar.disabled = false;
                    btnSalvar.textContent = '💾 Salvar informações';
                }
            }
        });
    }

    // 5. Alteração de Senha Segura
    // Toggle de visibilidade para todos os campos de senha
    document.querySelectorAll('.btn-toggle-pass').forEach(btnToggle => {
        btnToggle.addEventListener('click', () => {
            const targetId = btnToggle.dataset.target;
            const inputTarget = document.getElementById(targetId);
            if (inputTarget) {
                const isPass = inputTarget.type === 'password';
                inputTarget.type = isPass ? 'text' : 'password';
                btnToggle.textContent = isPass ? '🙈' : '👁️';
            }
        });
    });

    if (formAlterarSenha) {
        formAlterarSenha.addEventListener('submit', async (e) => {
            e.preventDefault();

            const senhaAtual = senhaAtualInput?.value.trim() || '';
            const novaSenha = novaSenhaInput?.value.trim() || '';
            const confirmarNovaSenha = confirmarNovaSenhaInput?.value.trim() || '';

            if (!senhaAtual || !novaSenha || !confirmarNovaSenha) {
                exibirAlerta('Por favor, preencha todos os campos para alterar sua senha.', 'erro');
                return;
            }

            if (novaSenha !== confirmarNovaSenha) {
                exibirAlerta('A confirmação da nova senha não confere com a nova senha digitada.', 'erro');
                return;
            }

            if (novaSenha.length < 6) {
                exibirAlerta('A nova senha deve possuir pelo menos 6 caracteres.', 'erro');
                return;
            }

            const btnSalvarSenha = document.getElementById('btnSalvarSenha');
            if (btnSalvarSenha) {
                btnSalvarSenha.disabled = true;
                btnSalvarSenha.textContent = 'Atualizando senha...';
            }

            try {
                const res = await fetch('../index.php?rota=usuarios&acao=alterar-senha', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        senha_atual: senhaAtual,
                        nova_senha: novaSenha,
                        confirmar_senha: confirmarNovaSenha
                    })
                });

                const json = await res.json().catch(() => ({}));

                if (res.ok && json.status === 'sucesso') {
                    exibirAlerta(json.mensagem || 'Senha alterada com sucesso! Guarde sua nova credencial.', 'sucesso');
                    formAlterarSenha.reset();
                } else {
                    exibirAlerta(json.mensagem || 'Erro ao alterar senha. Verifique a senha atual informada.', 'erro');
                }
            } catch (err) {
                console.error(err);
                exibirAlerta('Erro de conexão com o servidor ao alterar senha.', 'erro');
            } finally {
                if (btnSalvarSenha) {
                    btnSalvarSenha.disabled = false;
                    btnSalvarSenha.textContent = '🔐 Atualizar Senha';
                }
            }
        });
    }

    // 6. Botão "Sair da conta" (Logout)
    const abrirModalLogout = () => {
        if (modalConfirmarLogout) modalConfirmarLogout.style.display = 'flex';
    };

    const fecharModalLogout = () => {
        if (modalConfirmarLogout) modalConfirmarLogout.style.display = 'none';
    };

    if (btnSairHeader) btnSairHeader.addEventListener('click', abrirModalLogout);
    if (btnSairConta) btnSairConta.addEventListener('click', abrirModalLogout);
    if (btnCancelarLogout) btnCancelarLogout.addEventListener('click', fecharModalLogout);

    if (modalConfirmarLogout) {
        modalConfirmarLogout.addEventListener('click', (e) => {
            if (e.target === modalConfirmarLogout) fecharModalLogout();
        });
    }

    if (btnConfirmarLogout) {
        btnConfirmarLogout.addEventListener('click', async () => {
            btnConfirmarLogout.disabled = true;
            btnConfirmarLogout.textContent = 'Saindo...';
            try {
                await fetch('../index.php?rota=auth&acao=logout', { method: 'POST' });
            } catch (e) {
                console.error('Logout error:', e);
            }
            localStorage.removeItem('petalert_usuario');
            sessionStorage.removeItem('petalert_usuario');
            window.location.href = '../inicio/index.html?status=logout';
        });
    }

    // 7. Excluir Minha Conta Permanentemente (Requisito 34)
    if (btnAbrirModalExcluir) {
        btnAbrirModalExcluir.addEventListener('click', () => {
            if (modalExcluirConta) {
                if (formConfirmarExclusao) formConfirmarExclusao.reset();
                modalExcluirConta.style.display = 'flex';
            }
        });
    }

    const fecharModalExcluir = () => {
        if (modalExcluirConta) modalExcluirConta.style.display = 'none';
    };

    if (btnCancelarExcluir) btnCancelarExcluir.addEventListener('click', fecharModalExcluir);

    if (modalExcluirConta) {
        modalExcluirConta.addEventListener('click', (e) => {
            if (e.target === modalExcluirConta) fecharModalExcluir();
        });
    }

    if (formConfirmarExclusao) {
        formConfirmarExclusao.addEventListener('submit', async (e) => {
            e.preventDefault();

            const senha = senhaConfirmacaoExclusao?.value.trim() || '';
            const confirmacaoTexto = textoConfirmacaoExclusao?.value.trim().toUpperCase() || '';
            const termoAceito = checkTermoExclusao?.checked;

            if (!senha) {
                alert('Para sua segurança, informe sua senha atual para confirmar a exclusão.');
                senhaConfirmacaoExclusao?.focus();
                return;
            }

            if (confirmacaoTexto !== 'EXCLUIR') {
                alert('Digite exatamente a palavra EXCLUIR para confirmar a ação.');
                textoConfirmacaoExclusao?.focus();
                return;
            }

            if (!termoAceito) {
                alert('Você precisa aceitar os termos de ciência da exclusão irreversível.');
                return;
            }

            const btnExecutar = document.getElementById('btnExecutarExclusao');
            if (btnExecutar) {
                btnExecutar.disabled = true;
                btnExecutar.textContent = 'Excluindo conta permanentemente...';
            }

            try {
                const res = await fetch('../index.php?rota=usuarios&acao=excluir', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        senha: senha,
                        confirmacao: 'EXCLUIR',
                        confirmacao_termo: true
                    })
                });

                const json = await res.json().catch(() => ({}));

                if (res.ok && json.status === 'sucesso') {
                    localStorage.removeItem('petalert_usuario');
                    sessionStorage.removeItem('petalert_usuario');
                    alert('Sua conta e dados vinculados foram permanentemente excluídos do PetAlert.');
                    window.location.href = '../inicio/index.html?status=conta_excluida';
                } else {
                    alert(json.mensagem || 'Não foi possível excluir a conta. Verifique sua senha atual.');
                    if (btnExecutar) {
                        btnExecutar.disabled = false;
                        btnExecutar.textContent = 'Excluir minha conta definitivamente';
                    }
                }
            } catch (err) {
                console.error(err);
                alert('Erro de comunicação com o servidor ao solicitar exclusão da conta.');
                if (btnExecutar) {
                    btnExecutar.disabled = false;
                    btnExecutar.textContent = 'Excluir minha conta definitivamente';
                }
            }
        });
    }

    // --- UPLOAD E ALTERAÇÃO DE FOTO DE PERFIL ---
    const inputFotoPerfil = document.getElementById('inputFotoPerfil');
    const modalAlterarFotoPerfil = document.getElementById('modalAlterarFotoPerfil');
    const previewNovaFotoPerfil = document.getElementById('previewNovaFotoPerfil');
    const btnCancelarTrocaFoto = document.getElementById('btnCancelarTrocaFoto');
    const btnConfirmarSalvarFoto = document.getElementById('btnConfirmarSalvarFoto');
    const msgErroFotoPerfil = document.getElementById('msgErroFotoPerfil');

    let arquivoFotoSelecionado = null;

    if (inputFotoPerfil) {
        inputFotoPerfil.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            // Validação de tipo
            const tiposValidos = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            if (!tiposValidos.includes(file.type)) {
                alert('Por favor, selecione uma imagem no formato JPG, JPEG, PNG ou WEBP.');
                inputFotoPerfil.value = '';
                return;
            }

            // Validação de tamanho (máximo 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('A imagem selecionada é muito grande. O limite máximo é de 5MB.');
                inputFotoPerfil.value = '';
                return;
            }

            arquivoFotoSelecionado = file;

            // Carrega prévia
            const reader = new FileReader();
            reader.onload = (evt) => {
                if (previewNovaFotoPerfil) {
                    previewNovaFotoPerfil.src = evt.target.result;
                }
                if (msgErroFotoPerfil) {
                    msgErroFotoPerfil.style.display = 'none';
                }
                if (modalAlterarFotoPerfil) {
                    modalAlterarFotoPerfil.style.display = 'flex';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    function fecharModalFotoPerfil() {
        if (modalAlterarFotoPerfil) modalAlterarFotoPerfil.style.display = 'none';
        if (inputFotoPerfil) inputFotoPerfil.value = '';
        arquivoFotoSelecionado = null;
        if (msgErroFotoPerfil) msgErroFotoPerfil.style.display = 'none';
    }

    if (btnCancelarTrocaFoto) {
        btnCancelarTrocaFoto.addEventListener('click', fecharModalFotoPerfil);
    }

    if (modalAlterarFotoPerfil) {
        modalAlterarFotoPerfil.addEventListener('click', (e) => {
            if (e.target === modalAlterarFotoPerfil) fecharModalFotoPerfil();
        });
    }

    if (btnConfirmarSalvarFoto) {
        btnConfirmarSalvarFoto.addEventListener('click', async () => {
            if (!arquivoFotoSelecionado) {
                alert('Nenhum arquivo de foto selecionado.');
                return;
            }

            btnConfirmarSalvarFoto.disabled = true;
            btnConfirmarSalvarFoto.textContent = 'Enviando foto...';
            if (msgErroFotoPerfil) msgErroFotoPerfil.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('foto', arquivoFotoSelecionado);

                const res = await fetch('../index.php?rota=auth&acao=alterar-foto', {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });

                const json = await res.json().catch(() => ({}));

                if (res.ok && json.status === 'sucesso') {
                    // Atualiza objeto em memória e storage
                    if (dadosUsuarioAtual) {
                        dadosUsuarioAtual.foto = json.foto;
                        localStorage.setItem('petalert_usuario', JSON.stringify(dadosUsuarioAtual));
                        sessionStorage.setItem('petalert_usuario', JSON.stringify(dadosUsuarioAtual));
                    }

                    // Atualiza avatar imediatamente na interface
                    preencherInterface(dadosUsuarioAtual);

                    fecharModalFotoPerfil();
                    exibirAlerta(json.mensagem || 'Foto de perfil atualizada com sucesso!', 'sucesso');
                } else {
                    const erro = json.mensagem || 'Não foi possível salvar a nova foto de perfil.';
                    if (msgErroFotoPerfil) {
                        msgErroFotoPerfil.textContent = erro;
                        msgErroFotoPerfil.style.display = 'block';
                    } else {
                        alert(erro);
                    }
                }
            } catch (err) {
                console.error('Erro ao enviar foto de perfil:', err);
                if (msgErroFotoPerfil) {
                    msgErroFotoPerfil.textContent = 'Erro de comunicação ao enviar a foto.';
                    msgErroFotoPerfil.style.display = 'block';
                } else {
                    alert('Erro de comunicação ao enviar a foto.');
                }
            } finally {
                btnConfirmarSalvarFoto.disabled = false;
                btnConfirmarSalvarFoto.textContent = '💾 Salvar Foto';
            }
        });
    }

    // --- LISTAGEM E CONSULTA DE PESSOAS CADASTRADAS (READ) ---
    let listaPessoasGeral = [];
    const tabBtnPessoas = document.getElementById('tabBtnPessoas');
    const btnRecarregarPessoas = document.getElementById('btnRecarregarPessoas');
    const filtroNomePessoa = document.getElementById('filtroNomePessoa');
    const filtroTipoPessoa = document.getElementById('filtroTipoPessoa');
    const contadorPessoas = document.getElementById('contadorPessoas');
    const corpoTabelaPessoas = document.getElementById('corpoTabelaPessoas');
    const loadingPessoas = document.getElementById('loadingPessoas');

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function carregarListaPessoas() {
        if (!corpoTabelaPessoas) return;

        const tipoLogado = (dadosUsuarioAtual?.tipo_usuario || '').toLowerCase();
        const isAdmin = Boolean(tipoLogado === 'admin' || tipoLogado === 'administrador' || dadosUsuarioAtual?.is_admin);

        if (!isAdmin) {
            corpoTabelaPessoas.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem; color:#b91c1c;"><strong>🔒 Acesso não autorizado (HTTP 403)</strong><br>A lista de pessoas cadastradas é privada e restrita a administradores.</td></tr>`;
            if (contadorPessoas) contadorPessoas.textContent = 'Acesso restrito a administradores (403)';
            return;
        }

        if (loadingPessoas) loadingPessoas.style.display = 'block';

        try {
            const res = await fetch('../index.php?rota=usuarios');

            if (res.status === 403) {
                const json = await res.json().catch(() => ({}));
                const msg = json.erro || json.mensagem || 'Acesso não autorizado';
                corpoTabelaPessoas.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem; color:#b91c1c;"><strong>🔒 ${escapeHtml(msg)}</strong><br>A lista de pessoas cadastradas é privada e restrita a administradores.</td></tr>`;
                if (contadorPessoas) contadorPessoas.textContent = 'Acesso restrito (403)';
                return;
            }

            const json = await res.json().catch(() => ({}));

            if (res.ok && json.status === 'sucesso' && Array.isArray(json.dados)) {
                listaPessoasGeral = json.dados;
                renderizarListaPessoas();
            } else {
                corpoTabelaPessoas.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:1.5rem; color:#b91c1c;">Não foi possível carregar a listagem de pessoas do banco de dados.</td></tr>`;
            }
        } catch (err) {
            console.error('Erro ao listar pessoas:', err);
            corpoTabelaPessoas.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:1.5rem; color:#b91c1c;">Erro de conexão com o servidor ao consultar pessoas.</td></tr>`;
        } finally {
            if (loadingPessoas) loadingPessoas.style.display = 'none';
        }
    }

    function renderizarListaPessoas() {
        if (!corpoTabelaPessoas) return;

        const termo = (filtroNomePessoa?.value || '').trim().toLowerCase();
        const tipoFiltro = (filtroTipoPessoa?.value || '').trim().toLowerCase();

        const filtradas = listaPessoasGeral.filter(p => {
            const nome = (p.nome || '').toLowerCase();
            const email = (p.email || '').toLowerCase();
            const cidade = (p.cidade || '').toLowerCase();
            const tipo = (p.tipo_usuario || 'adotante').toLowerCase();

            const bateTexto = !termo || nome.includes(termo) || email.includes(termo) || cidade.includes(termo);
            const bateTipo = !tipoFiltro || tipo === tipoFiltro;

            return bateTexto && bateTipo;
        });

        if (contadorPessoas) {
            contadorPessoas.textContent = `${filtradas.length} de ${listaPessoasGeral.length} pessoa(s)`;
        }

        if (filtradas.length === 0) {
            corpoTabelaPessoas.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align:center; padding:2rem; color:var(--muted);">
                        Nenhuma pessoa encontrada com os filtros informados.
                    </td>
                </tr>
            `;
            return;
        }

        corpoTabelaPessoas.innerHTML = filtradas.map(p => {
            const tipo = (p.tipo_usuario || 'adotante').toLowerCase();
            const badgeClass = tipo === 'doador' ? 'doador' : 'adotante';
            const badgeLabel = tipo === 'doador' ? '🐾 Doador' : '❤️ Adotante';
            const cidadeEstado = (p.cidade && p.estado) ? `${p.cidade}/${p.estado}` : (p.cidade || p.estado || '—');
            const dataCad = formatarData(p.data_criacao || '');
            const tel = formatarTelefone(p.telefone || '');
            const foto = p.foto ? `<span style="margin-right:6px;">📷</span>` : ``;

            return `
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.75rem 0.9rem; font-weight: 600;">
                        ${foto}${escapeHtml(p.nome || 'Usuário')}
                    </td>
                    <td style="padding: 0.75rem 0.9rem;">
                        <span class="perfil-badge-tipo ${badgeClass}" style="font-size:0.75rem; padding: 2px 8px;">
                            ${badgeLabel}
                        </span>
                    </td>
                    <td style="padding: 0.75rem 0.9rem; color: var(--muted);">${escapeHtml(p.email || '—')}</td>
                    <td style="padding: 0.75rem 0.9rem;">${tel}</td>
                    <td style="padding: 0.75rem 0.9rem;">${escapeHtml(cidadeEstado)}</td>
                    <td style="padding: 0.75rem 0.9rem; color: var(--muted);">${dataCad}</td>
                    <td style="padding: 0.75rem 0.9rem; color: #15803d; font-weight: 700;">● Ativo</td>
                </tr>
            `;
        }).join('');
    }

    if (tabBtnPessoas) {
        tabBtnPessoas.addEventListener('click', (e) => {
            const tipoLogado = (dadosUsuarioAtual?.tipo_usuario || '').toLowerCase();
            const isAdmin = Boolean(tipoLogado === 'admin' || tipoLogado === 'administrador' || dadosUsuarioAtual?.is_admin);
            if (!isAdmin) {
                e.preventDefault();
                e.stopPropagation();
                exibirFeedback('Acesso não autorizado: Esta seção é restrita a administradores.', 'erro');
                const tabDados = document.getElementById('tabBtnDados');
                if (tabDados) tabDados.click();
                return;
            }
            carregarListaPessoas();
        });
    }

    if (btnRecarregarPessoas) {
        btnRecarregarPessoas.addEventListener('click', () => {
            carregarListaPessoas();
        });
    }

    if (filtroNomePessoa) {
        filtroNomePessoa.addEventListener('input', renderizarListaPessoas);
    }

    if (filtroTipoPessoa) {
        filtroTipoPessoa.addEventListener('change', renderizarListaPessoas);
    }

    // Inicialização
    await carregarDadosPerfil();
});
