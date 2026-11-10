document.addEventListener('DOMContentLoaded', () => {
    const formPet = document.getElementById('formPet');
    const btnPublicar = document.getElementById('publicar');
    const selectEspecie = document.getElementById('especie');
    const grupoEspecieOutra = document.getElementById('grupoEspecieOutra');
    const inputEspecieOutra = document.getElementById('especieOutra');
    const inputFoto = document.getElementById('foto');
    const fotoPreview = document.getElementById('fotoPreview');
    const modalAuthDoar = document.getElementById('modalAuthDoarPet');
    const btnDoarCriarConta = document.getElementById('btnDoarCriarConta');
    const btnDoarEntrar = document.getElementById('btnDoarEntrar');

    const obterUsuario = () => {
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
    };

    const usuario = obterUsuario();

    // 1. Alternar campo de outra espécie
    if (selectEspecie && grupoEspecieOutra) {
        selectEspecie.addEventListener('change', () => {
            if (selectEspecie.value === 'Outros') {
                grupoEspecieOutra.style.display = 'block';
                inputEspecieOutra?.setAttribute('required', 'required');
                inputEspecieOutra?.focus();
            } else {
                grupoEspecieOutra.style.display = 'none';
                inputEspecieOutra?.removeAttribute('required');
            }
            salvarRascunho();
        });
    }

    // 2. Prévia da foto
    if (inputFoto && fotoPreview) {
        inputFoto.addEventListener('change', () => {
            const file = inputFoto.files && inputFoto.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    fotoPreview.src = e.target.result;
                    fotoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                fotoPreview.src = '';
                fotoPreview.style.display = 'none';
            }
        });
    }

    // 3. Preservação de Rascunho (Requisito 35 - Manter dados preenchidos antes do login)
    function salvarRascunho() {
        const dados = {
            nome: document.getElementById('nomePet')?.value || '',
            especie: selectEspecie?.value || '',
            especieOutra: inputEspecieOutra?.value || '',
            raca: document.getElementById('raca')?.value || '',
            idade: document.getElementById('idade')?.value || '',
            porte: document.getElementById('porte')?.value || '',
            peso: document.getElementById('peso')?.value || '',
            cor: document.getElementById('cor')?.value || '',
            motivo_doacao: document.getElementById('motivo_doacao')?.value || '',
            descricao: document.getElementById('descricao')?.value || ''
        };
        sessionStorage.setItem('petalert_draft_pet', JSON.stringify(dados));
    }

    function restaurarRascunho() {
        const raw = sessionStorage.getItem('petalert_draft_pet');
        if (!raw) return;
        try {
            const r = JSON.parse(raw);
            if (r.nome) document.getElementById('nomePet').value = r.nome;
            if (r.especie && selectEspecie) {
                selectEspecie.value = r.especie;
                if (r.especie === 'Outros' && grupoEspecieOutra && inputEspecieOutra) {
                    grupoEspecieOutra.style.display = 'block';
                    inputEspecieOutra.value = r.especieOutra || '';
                }
            }
            if (r.raca) document.getElementById('raca').value = r.raca;
            if (r.idade) document.getElementById('idade').value = r.idade;
            if (r.porte) document.getElementById('porte').value = r.porte;
            if (r.peso) document.getElementById('peso').value = r.peso;
            if (r.cor) document.getElementById('cor').value = r.cor;
            if (r.motivo_doacao) document.getElementById('motivo_doacao').value = r.motivo_doacao;
            if (r.descricao) document.getElementById('descricao').value = r.descricao;
        } catch (e) {
            console.error('Erro ao restaurar rascunho:', e);
        }
    }

    // Monitora alterações para salvar rascunho continuamente
    if (formPet) {
        formPet.addEventListener('input', salvarRascunho);
        formPet.addEventListener('change', salvarRascunho);
    }

    // Restaura rascunho salvo ao carregar
    restaurarRascunho();

    // 4. Modal de Autenticação Necessária (Requisito 35)
    function exibirModalAuthDoar() {
        salvarRascunho();
        sessionStorage.setItem('petalert_redirect_url', '../doarPet/index.html');

        if (btnDoarCriarConta) {
            btnDoarCriarConta.onclick = () => {
                salvarRascunho();
                window.location.href = '../cadastro/index.html?perfil=doador&redirect=../doarPet/index.html';
            };
        }

        if (btnDoarEntrar) {
            btnDoarEntrar.onclick = () => {
                salvarRascunho();
                window.location.href = '../login/index.html?redirect=../doarPet/index.html';
            };
        }

        if (modalAuthDoar) {
            modalAuthDoar.style.display = 'flex';
        }
    }

    // Se o visitante acessar a página de doar pet sem estar logado, exibe o modal imediatamente
    if (!usuario) {
        exibirModalAuthDoar();
    }

    if (!btnPublicar) return;

    btnPublicar.addEventListener('click', async () => {
        const userLogado = obterUsuario();

        // Bloqueio rigoroso de autenticação no frontend
        if (!userLogado) {
            exibirModalAuthDoar();
            return;
        }

        const nome = document.getElementById('nomePet')?.value.trim() || '';
        let especie = selectEspecie?.value || '';
        if (especie === 'Outros') {
            especie = inputEspecieOutra?.value.trim() || 'Outros';
        }
        const raca = document.getElementById('raca')?.value.trim() || '';
        const idade = document.getElementById('idade')?.value.trim() || '';
        const porte = document.getElementById('porte')?.value || '';
        const peso = document.getElementById('peso')?.value.trim() || '';
        const cor = document.getElementById('cor')?.value.trim() || '';
        const motivoDoacao = document.getElementById('motivo_doacao')?.value.trim() || '';
        const descricao = document.getElementById('descricao')?.value.trim() || '';

        // Validações obrigatórias
        if (!nome) return alert('Por favor, informe o nome do pet.');
        if (!especie) return alert('Por favor, selecione ou especifique a espécie do animal.');
        if (!raca) return alert('Por favor, informe a raça do pet.');
        if (!idade) return alert('Por favor, informe a idade em anos.');
        if (!porte) return alert('Por favor, selecione o porte do pet.');
        if (!peso) return alert('Por favor, informe o peso em kg.');
        if (!cor) return alert('Por favor, informe a cor do pet.');
        if (!motivoDoacao) return alert('O motivo da doação é obrigatório. Por favor, preencha-o.');
        if (!descricao) return alert('Por favor, conte uma breve história sobre o pet.');

        const formData = new FormData();
        formData.append('nome', nome);
        formData.append('especie', especie);
        formData.append('raca', raca);
        formData.append('idade', idade);
        formData.append('porte', porte);
        formData.append('peso', peso);
        formData.append('cor', cor);
        formData.append('motivo_doacao', motivoDoacao);
        formData.append('descricao', descricao);

        if (inputFoto && inputFoto.files && inputFoto.files[0]) {
            formData.append('foto', inputFoto.files[0]);
        } else {
            return alert('Por favor, selecione uma foto do pet para o cadastro.');
        }

        btnPublicar.disabled = true;
        btnPublicar.innerText = 'Cadastrando Pet...';

        try {
            const endpoint = '../index.php?rota=pets';
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            if (response.status === 401) {
                exibirModalAuthDoar();
                return;
            }

            const result = await response.json().catch(() => ({}));

            if (response.ok && (result.sucesso || result.id)) {
                // Remove rascunho após publicação com sucesso para não duplicar
                sessionStorage.removeItem('petalert_draft_pet');
                alert('Pet cadastrado com sucesso! Redirecionando para a lista de pets...');
                window.location.href = '../pets/index.html';
            } else {
                alert(result.erro || result.mensagem || 'Erro ao cadastrar pet no banco de dados.');
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao conectar com o servidor para cadastrar o pet.');
        } finally {
            btnPublicar.disabled = false;
            btnPublicar.innerText = 'Publicar Pet para Adoção';
        }
    });
});
