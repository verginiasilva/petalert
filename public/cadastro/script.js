document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('cadastroForm');
    const btn = document.getElementById('cadastrar');
    const loginLink = document.getElementById('linkLogin');
    const toggleSenhaBtn = document.getElementById('toggleSenha');
    const senhaInput = document.getElementById('senha');
    const cepInput = document.getElementById('cep');
    const cepStatus = document.getElementById('cepStatus');
    const cpfInput = document.getElementById('cpf');
    const telInput = document.getElementById('telefone');
    const dataNascimentoInput = document.getElementById('dataNascimento');
    const idadeFeedback = document.getElementById('idadeFeedback');

    // Elementos da Foto de Perfil
    const fotoPerfilInput = document.getElementById('fotoPerfilInput');
    const btnSelecionarFoto = document.getElementById('btnSelecionarFoto');
    const btnRemoverFoto = document.getElementById('btnRemoverFoto');
    const fotoPreview = document.getElementById('fotoPerfilPreview');
    const fotoPlaceholder = document.getElementById('fotoPerfilPlaceholder');
    const fotoErroMsg = document.getElementById('fotoErroMsg');

    let fotoArquivoSelecionado = null;

    // 1. Mostrar / Ocultar Senha
    if (toggleSenhaBtn && senhaInput) {
        toggleSenhaBtn.addEventListener('click', () => {
            const isPassword = senhaInput.type === 'password';
            senhaInput.type = isPassword ? 'text' : 'password';
            toggleSenhaBtn.textContent = isPassword ? '🙈' : '👁️';
        });
    }

    if (loginLink) {
        const redirect = new URLSearchParams(window.location.search).get('redirect') || sessionStorage.getItem('petalert_redirect_url');
        if (redirect) {
            loginLink.href = `../login/index.html?redirect=${encodeURIComponent(redirect)}`;
        }
    }

    // 2. Foto de Perfil no Cadastro (Seleção, Prévia, Troca e Remoção)
    if (btnSelecionarFoto && fotoPerfilInput) {
        btnSelecionarFoto.addEventListener('click', () => {
            fotoPerfilInput.click();
        });

        fotoPerfilInput.addEventListener('change', (e) => {
            if (fotoErroMsg) {
                fotoErroMsg.style.display = 'none';
                fotoErroMsg.textContent = '';
            }

            const file = e.target.files && e.target.files[0];
            if (!file) return;

            // Validação de tipo de arquivo (JPG, JPEG, PNG, WEBP)
            const tiposValidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            const extValidas = ['jpg', 'jpeg', 'png', 'webp'];
            const fileExt = file.name.split('.').pop().toLowerCase();

            if (!tiposValidos.includes(file.type.toLowerCase()) && !extValidas.includes(fileExt)) {
                if (fotoErroMsg) {
                    fotoErroMsg.textContent = 'Formato inválido. Selecione apenas imagens JPG, JPEG, PNG ou WEBP.';
                    fotoErroMsg.style.display = 'block';
                }
                fotoPerfilInput.value = '';
                return;
            }

            // Validação de tamanho máximo (5 MB)
            const maxBytes = 5 * 1024 * 1024;
            if (file.size > maxBytes) {
                if (fotoErroMsg) {
                    fotoErroMsg.textContent = 'A imagem selecionada ultrapassa o tamanho máximo permitido de 5 MB.';
                    fotoErroMsg.style.display = 'block';
                }
                fotoPerfilInput.value = '';
                return;
            }

            // Sucesso: Carrega prévia
            fotoArquivoSelecionado = file;
            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                if (fotoPreview) {
                    fotoPreview.src = loadEvent.target.result;
                    fotoPreview.style.display = 'block';
                }
                if (fotoPlaceholder) {
                    fotoPlaceholder.style.display = 'none';
                }
                if (btnSelecionarFoto) {
                    btnSelecionarFoto.textContent = '🔄 Trocar foto';
                }
                if (btnRemoverFoto) {
                    btnRemoverFoto.style.display = 'inline-flex';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    if (btnRemoverFoto) {
        btnRemoverFoto.addEventListener('click', () => {
            fotoArquivoSelecionado = null;
            if (fotoPerfilInput) fotoPerfilInput.value = '';
            if (fotoPreview) {
                fotoPreview.src = '';
                fotoPreview.style.display = 'none';
            }
            if (fotoPlaceholder) {
                fotoPlaceholder.style.display = 'block';
            }
            if (btnSelecionarFoto) {
                btnSelecionarFoto.textContent = '📷 Adicionar foto';
            }
            btnRemoverFoto.style.display = 'none';
            if (fotoErroMsg) {
                fotoErroMsg.style.display = 'none';
                fotoErroMsg.textContent = '';
            }
        });
    }

    // 3. Máscara de CPF
    if (cpfInput) {
        cpfInput.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 9) {
                v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
            } else if (v.length > 6) {
                v = v.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            } else if (v.length > 3) {
                v = v.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            }
            e.target.value = v;
        });
    }

    // 4. Máscara de Telefone
    if (telInput) {
        telInput.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 10) {
                v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (v.length > 5) {
                v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = v;
        });
    }

    // 5. Validação Automática de Idade e Data de Nascimento (Mínimo 16 anos)
    const hojeDate = new Date();
    const anoHoje = hojeDate.getFullYear();
    const mesHoje = String(hojeDate.getMonth() + 1).padStart(2, '0');
    const diaHoje = String(hojeDate.getDate()).padStart(2, '0');
    const hojeIso = `${anoHoje}-${mesHoje}-${diaHoje}`;

    if (dataNascimentoInput) {
        // Impede seleção de datas futuras no datepicker nativo
        dataNascimentoInput.max = hojeIso;

        function calcularIdade(dataStr) {
            if (!dataStr) return null;
            const partes = dataStr.split('-');
            if (partes.length !== 3) return null;

            const ano = parseInt(partes[0], 10);
            const mes = parseInt(partes[1], 10) - 1;
            const dia = parseInt(partes[2], 10);

            const nasc = new Date(ano, mes, dia);
            if (isNaN(nasc.getTime()) || nasc.getFullYear() !== ano || nasc.getMonth() !== mes || nasc.getDate() !== dia) {
                return { invalida: true };
            }

            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);
            nasc.setHours(0, 0, 0, 0);

            // Data futura ou igual a hoje
            if (nasc >= hoje) {
                return { futura: true };
            }

            // Ano limite razoável
            if (ano < 1900) {
                return { invalida: true };
            }

            let idade = hoje.getFullYear() - nasc.getFullYear();
            const m = hoje.getMonth() - nasc.getMonth();
            if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) {
                idade--;
            }

            return { idade, nasc, hoje };
        }

        function validarIdadeFrontend() {
            if (!idadeFeedback) return true;

            const dataStr = dataNascimentoInput.value.trim();
            if (!dataStr) {
                idadeFeedback.style.display = 'none';
                idadeFeedback.textContent = '';
                return null;
            }

            const resultado = calcularIdade(dataStr);

            if (!resultado || resultado.invalida) {
                idadeFeedback.textContent = 'A data de nascimento informada é inválida.';
                idadeFeedback.style.color = '#dc2626';
                idadeFeedback.style.display = 'block';
                return false;
            }

            if (resultado.futura) {
                idadeFeedback.textContent = 'A data de nascimento informada é inválida ou está no futuro.';
                idadeFeedback.style.color = '#dc2626';
                idadeFeedback.style.display = 'block';
                return false;
            }

            if (resultado.idade < 16) {
                idadeFeedback.textContent = 'Você precisa ter pelo menos 16 anos para se cadastrar na nossa plataforma.';
                idadeFeedback.style.color = '#dc2626';
                idadeFeedback.style.display = 'block';
                return false;
            }

            idadeFeedback.textContent = `✓ Idade confirmada: ${resultado.idade} anos. Você pode continuar com o cadastro.`;
            idadeFeedback.style.color = '#16a34a';
            idadeFeedback.style.display = 'block';
            return true;
        }

        dataNascimentoInput.addEventListener('input', validarIdadeFrontend);
        dataNascimentoInput.addEventListener('change', validarIdadeFrontend);
    }

    // 6. Busca Automática de CEP (ViaCEP)
    const buscarCep = async (cepRaw) => {
        const cep = cepRaw.replace(/\D/g, '');
        if (cep.length !== 8) return;

        if (cepStatus) {
            cepStatus.textContent = 'Buscando endereço pelo CEP...';
            cepStatus.style.color = 'var(--primary-dark)';
        }

        try {
            const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await res.json();

            if (data.erro) {
                if (cepStatus) {
                    cepStatus.textContent = 'CEP não encontrado. Digite o endereço manualmente.';
                    cepStatus.style.color = '#e74c3c';
                }
                return;
            }

            const ruaInput = document.getElementById('rua');
            const bairroInput = document.getElementById('bairro');
            const cidadeInput = document.getElementById('cidade');
            const estadoInput = document.getElementById('estado');
            const numeroInput = document.getElementById('numero');

            if (ruaInput && data.logradouro) ruaInput.value = data.logradouro;
            if (bairroInput && data.bairro) bairroInput.value = data.bairro;
            if (cidadeInput && data.localidade) cidadeInput.value = data.localidade;
            if (estadoInput && data.uf) estadoInput.value = data.uf;

            if (cepStatus) {
                cepStatus.textContent = 'Endereço preenchido com sucesso! Digite o número da casa.';
                cepStatus.style.color = '#27ae60';
            }

            if (numeroInput) {
                numeroInput.focus();
            }
        } catch (err) {
            console.error('Erro ao consultar ViaCEP:', err);
            if (cepStatus) {
                cepStatus.textContent = 'Não foi possível buscar o CEP automaticamente. Digite o endereço.';
                cepStatus.style.color = 'var(--muted)';
            }
        }
    };

    if (cepInput) {
        cepInput.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '').slice(0, 8);
            if (v.length > 5) {
                v = v.replace(/(\d{5})(\d{1,3})/, '$1-$2');
            }
            e.target.value = v;
            if (v.replace(/\D/g, '').length === 8) {
                buscarCep(v);
            }
        });

        cepInput.addEventListener('blur', (e) => {
            buscarCep(e.target.value);
        });
    }

    // Função para cálculo matemático de CPF no frontend
    function validarCpfMath(cpf) {
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        for (let t = 9; t < 11; t++) {
            let d = 0;
            for (let c = 0; c < t; c++) {
                d += parseInt(cpf.charAt(c), 10) * ((t + 1) - c);
            }
            d = ((10 * d) % 11) % 10;
            if (parseInt(cpf.charAt(t), 10) !== d) return false;
        }
        return true;
    }

    const feedbackBox = document.getElementById('cadastroFeedback');
    function exibirFeedback(mensagem, tipo = 'erro') {
        if (!feedbackBox) {
            alert(mensagem);
            return;
        }
        feedbackBox.textContent = mensagem;
        feedbackBox.className = `feedback-alerta ${tipo}`;
        feedbackBox.style.display = 'flex';
        feedbackBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function ocultarFeedback() {
        if (feedbackBox) {
            feedbackBox.style.display = 'none';
        }
    }

    // 7. Submissão do Formulário de Cadastro com Validação de Idade e Foto
    if (btn) {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            ocultarFeedback();

            const nome = document.getElementById('nome')?.value.trim() || '';
            const dataNascimento = document.getElementById('dataNascimento')?.value.trim() || '';
            const cpf = document.getElementById('cpf')?.value.trim() || '';
            const email = document.getElementById('email')?.value.trim() || '';
            const telefone = document.getElementById('telefone')?.value.trim() || '';
            const cep = document.getElementById('cep')?.value.trim() || '';
            const rua = document.getElementById('rua')?.value.trim() || '';
            const numero = document.getElementById('numero')?.value.trim() || '';
            const bairro = document.getElementById('bairro')?.value.trim() || '';
            const cidade = document.getElementById('cidade')?.value.trim() || '';
            const estado = document.getElementById('estado')?.value.trim() || '';
            const senha = document.getElementById('senha')?.value.trim() || '';
            const perfil = document.getElementById('perfil')?.value || 'adotante';

            // 1. Validação de Nome
            if (!nome) return exibirFeedback('Por favor, informe seu nome completo.');
            const partesNome = nome.replace(/\s+/g, ' ').trim().split(' ');
            if (partesNome.length < 2 || nome.length < 4) {
                return exibirFeedback('Por favor, informe seu nome completo (nome e sobrenome).');
            }

            // 2. Validação Rigorosa de Data de Nascimento e Idade Mínima de 16 Anos
            if (!dataNascimento) return exibirFeedback('Por favor, informe sua data de nascimento.');

            const partesData = dataNascimento.split('-');
            if (partesData.length !== 3) {
                return exibirFeedback('A data de nascimento informada é inválida ou está no futuro.');
            }

            const anoN = parseInt(partesData[0], 10);
            const mesN = parseInt(partesData[1], 10) - 1;
            const diaN = parseInt(partesData[2], 10);
            const dtNasc = new Date(anoN, mesN, diaN);
            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);
            dtNasc.setHours(0, 0, 0, 0);

            if (isNaN(dtNasc.getTime()) || dtNasc.getFullYear() !== anoN || dtNasc.getMonth() !== mesN || dtNasc.getDate() !== diaN) {
                return exibirFeedback('A data de nascimento informada é inválida ou está no futuro.');
            }

            if (dtNasc >= hoje) {
                return exibirFeedback('A data de nascimento informada é inválida ou está no futuro.');
            }

            if (anoN < 1900) {
                return exibirFeedback('A data de nascimento informada é inválida.');
            }

            let idadeCalculada = hoje.getFullYear() - dtNasc.getFullYear();
            const diffMes = hoje.getMonth() - dtNasc.getMonth();
            if (diffMes < 0 || (diffMes === 0 && hoje.getDate() < dtNasc.getDate())) {
                idadeCalculada--;
            }

            if (idadeCalculada < 16) {
                return exibirFeedback('Você precisa ter pelo menos 16 anos para se cadastrar na nossa plataforma.');
            }

            // 3. Validação de CPF
            if (!cpf) return exibirFeedback('Por favor, informe seu CPF.');
            const cpfNumeros = cpf.replace(/\D/g, '');
            if (cpfNumeros.length !== 11) {
                return exibirFeedback('O CPF informado deve conter 11 dígitos.');
            }
            if (!validarCpfMath(cpfNumeros)) {
                return exibirFeedback('O CPF informado possui dígitos verificadores inválidos pelos cálculos matemáticos.');
            }

            // 4. Demais campos obrigatórios
            if (!email) return exibirFeedback('Por favor, informe seu e-mail.');
            if (!telefone) return exibirFeedback('Por favor, informe seu telefone / WhatsApp.');
            if (!cep) return exibirFeedback('Por favor, informe o seu CEP.');
            if (!rua) return exibirFeedback('Por favor, informe sua rua / logradouro.');
            if (!numero) return exibirFeedback('Por favor, informe o número da sua casa.');
            if (!senha) return exibirFeedback('Por favor, crie uma senha segura.');

            const aceitePolitica = document.getElementById('aceitePolitica');
            if (aceitePolitica && !aceitePolitica.checked) {
                return exibirFeedback('É necessário ler e estar ciente da Política de Privacidade para prosseguir com o cadastro.');
            }

            // 5. Montagem do FormData (Suporta upload de foto e dados textuais)
            const formData = new FormData();
            formData.append('nome', nome);
            formData.append('data_nascimento', dataNascimento);
            formData.append('dataNascimento', dataNascimento);
            formData.append('cpf', cpfNumeros);
            formData.append('email', email);
            formData.append('telefone', telefone);
            formData.append('cep', cep);
            formData.append('rua', rua);
            formData.append('numero', numero);
            formData.append('bairro', bairro);
            formData.append('cidade', cidade);
            formData.append('estado', estado);
            formData.append('senha', senha);
            formData.append('perfil', perfil);

            if (fotoArquivoSelecionado) {
                formData.append('foto', fotoArquivoSelecionado);
            }

            btn.disabled = true;
            btn.classList.add('btn-carregando');
            btn.textContent = 'Validando dados e cadastrando...';
            exibirFeedback('Processando cadastro com verificação de idade e identidade...', 'processando');

            try {
                const res = await fetch('../index.php?rota=auth&acao=cadastro', {
                    method: 'POST',
                    body: formData
                });

                const json = await res.json().catch(() => ({}));

                if (res.ok && (json.status === 'sucesso' || json.usuario || json.id)) {
                    const statusId = json.status_identidade || 'pendente';
                    const msgSucesso = json.mensagem || 'Cadastro realizado com sucesso! Você já pode fazer login.';

                    exibirFeedback(msgSucesso, statusId === 'verificado' ? 'sucesso' : 'aviso');

                    setTimeout(() => {
                        if (form) form.reset();
                        if (fotoPreview) {
                            fotoPreview.src = '';
                            fotoPreview.style.display = 'none';
                        }
                        if (fotoPlaceholder) {
                            fotoPlaceholder.style.display = 'block';
                        }
                        if (btnRemoverFoto) {
                            btnRemoverFoto.style.display = 'none';
                        }
                        if (btnSelecionarFoto) {
                            btnSelecionarFoto.textContent = '📷 Adicionar foto';
                        }
                        fotoArquivoSelecionado = null;

                        const redirect = new URLSearchParams(window.location.search).get('redirect') || sessionStorage.getItem('petalert_redirect_url');
                        const destinoLogin = redirect ? `../login/index.html?redirect=${encodeURIComponent(redirect)}` : '../login/index.html';
                        window.location.href = destinoLogin;
                    }, 2200);
                } else {
                    const msgErro = json.mensagem || json.erro || 'Não foi possível concluir o cadastro. Verifique os dados informados.';
                    exibirFeedback(msgErro, 'erro');
                }
            } catch (err) {
                console.error(err);
                exibirFeedback('Erro de comunicação com o servidor. Verifique sua conexão com o XAMPP.', 'erro');
            } finally {
                btn.disabled = false;
                btn.classList.remove('btn-carregando');
                btn.textContent = 'Cadastrar';
            }
        });
    }
});
