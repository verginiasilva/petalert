document.addEventListener('DOMContentLoaded', () => {
    const formPet = document.getElementById('formPet');
    const btnPublicar = document.getElementById('publicar');
    const selectEspecie = document.getElementById('especie');
    const grupoEspecieOutra = document.getElementById('grupoEspecieOutra');
    const inputEspecieOutra = document.getElementById('especieOutra');
    const dropzoneFotos = document.getElementById('dropzoneFotos');
    const inputMultiFotos = document.getElementById('inputMultiFotos');
    const gridPreviaFotos = document.getElementById('gridPreviaFotos');
    const contadorFotos = document.getElementById('contadorFotos');
    const alertaLimiteFotos = document.getElementById('alertaLimiteFotos');
    const modalAuthDoar = document.getElementById('modalAuthDoarPet');
    const btnDoarCriarConta = document.getElementById('btnDoarCriarConta');
    const btnDoarEntrar = document.getElementById('btnDoarEntrar');

    const LIMITE_FOTOS = 5;
    // Lista de arquivos File selecionados
    let listaArquivosFotos = [];

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

    // 2. Gerenciamento de Upload de até 5 fotos
    if (dropzoneFotos && inputMultiFotos) {
        dropzoneFotos.addEventListener('click', () => {
            if (listaArquivosFotos.length >= LIMITE_FOTOS) {
                if (alertaLimiteFotos) alertaLimiteFotos.style.display = 'block';
                return;
            }
            inputMultiFotos.click();
        });

        // Suporte a Drag and Drop
        dropzoneFotos.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzoneFotos.style.borderColor = '#FF6B6B';
            dropzoneFotos.style.background = '#fff4ed';
        });

        dropzoneFotos.addEventListener('dragleave', () => {
            dropzoneFotos.style.borderColor = '#f0d5ca';
            dropzoneFotos.style.background = '#ffffff';
        });

        dropzoneFotos.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzoneFotos.style.borderColor = '#f0d5ca';
            dropzoneFotos.style.background = '#ffffff';
            if (e.dataTransfer && e.dataTransfer.files) {
                adicionarArquivosFotos(e.dataTransfer.files);
            }
        });

        inputMultiFotos.addEventListener('change', () => {
            if (inputMultiFotos.files) {
                adicionarArquivosFotos(inputMultiFotos.files);
            }
            inputMultiFotos.value = ''; // Permite selecionar o mesmo arquivo novamente se desejar
        });
    }

    function adicionarArquivosFotos(files) {
        const novosArquivos = Array.from(files);
        let fotosRestantes = LIMITE_FOTOS - listaArquivosFotos.length;

        if (fotosRestantes <= 0) {
            alert('Você já atingiu o limite de 5 fotos para este animal. Para adicionar outra, remova uma foto existente.');
            atualizarContadorFotos();
            return;
        }

        if (novosArquivos.length > fotosRestantes) {
            alert(`Você pode adicionar no máximo mais ${fotosRestantes} foto(s). Apenas as primeiras serão adicionadas para respeitar o limite de 5 fotos por pet.`);
        }

        const arquivosValidos = novosArquivos.slice(0, fotosRestantes).filter(file => {
            if (!file.type.startsWith('image/')) {
                alert(`O arquivo "${file.name}" não é uma imagem válida e foi ignorado.`);
                return false;
            }
            if (file.size > 8 * 1024 * 1024) {
                alert(`A imagem "${file.name}" ultrapassa o limite de 8MB e foi ignorada.`);
                return false;
            }
            return true;
        });

        listaArquivosFotos = listaArquivosFotos.concat(arquivosValidos);
        renderizarPreviasFotos();
    }

    function removerFoto(index) {
        listaArquivosFotos.splice(index, 1);
        renderizarPreviasFotos();
    }

    function atualizarContadorFotos() {
        const qtd = listaArquivosFotos.length;
        if (contadorFotos) {
            contadorFotos.textContent = `${qtd} de ${LIMITE_FOTOS} fotos adicionadas`;
        }

        if (alertaLimiteFotos) {
            alertaLimiteFotos.style.display = qtd >= LIMITE_FOTOS ? 'block' : 'none';
        }

        if (dropzoneFotos) {
            if (qtd >= LIMITE_FOTOS) {
                dropzoneFotos.style.opacity = '0.6';
                dropzoneFotos.style.pointerEvents = 'none';
            } else {
                dropzoneFotos.style.opacity = '1';
                dropzoneFotos.style.pointerEvents = 'auto';
            }
        }
    }

    function renderizarPreviasFotos() {
        if (!gridPreviaFotos) return;

        atualizarContadorFotos();

        if (listaArquivosFotos.length === 0) {
            gridPreviaFotos.style.display = 'none';
            gridPreviaFotos.innerHTML = '';
            return;
        }

        gridPreviaFotos.style.display = 'grid';
        gridPreviaFotos.innerHTML = '';

        listaArquivosFotos.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = `pet-preview-item ${index === 0 ? 'principal' : ''}`;

            const img = document.createElement('img');
            img.className = 'pet-preview-img';
            img.alt = `Prévia da foto ${index + 1}`;

            const reader = new FileReader();
            reader.onload = (e) => {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);

            const badge = document.createElement('span');
            badge.className = `pet-preview-badge ${index === 0 ? 'capa' : ''}`;
            badge.textContent = index === 0 ? '★ Foto Principal' : `Foto ${index + 1}`;

            const btnRemover = document.createElement('button');
            btnRemover.type = 'button';
            btnRemover.className = 'btn-remover-foto-previa';
            btnRemover.title = 'Remover esta foto';
            btnRemover.innerHTML = '✕';
            btnRemover.addEventListener('click', (e) => {
                e.stopPropagation();
                removerFoto(index);
            });

            item.appendChild(img);
            item.appendChild(badge);
            item.appendChild(btnRemover);
            gridPreviaFotos.appendChild(item);
        });
    }

    // 3. Preservação de Rascunho
    function salvarRascunho() {
        const dados = {
            nome: document.getElementById('nomePet')?.value || '',
            especie: selectEspecie?.value || '',
            especieOutra: inputEspecieOutra?.value || '',
            raca: document.getElementById('raca')?.value || '',
            sexo: document.getElementById('sexo')?.value || 'Macho',
            idade: document.getElementById('idade')?.value || '',
            porte: document.getElementById('porte')?.value || '',
            peso: document.getElementById('peso')?.value || '',
            cor: document.getElementById('cor')?.value || '',
            personalidade: document.getElementById('personalidade')?.value || '',
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
            if (r.sexo && document.getElementById('sexo')) document.getElementById('sexo').value = r.sexo;
            if (r.idade) document.getElementById('idade').value = r.idade;
            if (r.porte) document.getElementById('porte').value = r.porte;
            if (r.peso) document.getElementById('peso').value = r.peso;
            if (r.cor) document.getElementById('cor').value = r.cor;
            if (r.personalidade && document.getElementById('personalidade')) document.getElementById('personalidade').value = r.personalidade;
            if (r.motivo_doacao) document.getElementById('motivo_doacao').value = r.motivo_doacao;
            if (r.descricao) document.getElementById('descricao').value = r.descricao;
        } catch (e) {
            console.error('Erro ao restaurar rascunho:', e);
        }
    }

    if (formPet) {
        formPet.addEventListener('input', salvarRascunho);
        formPet.addEventListener('change', salvarRascunho);
    }

    restaurarRascunho();

    // 4. Modal de Autenticação Necessária
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

    if (!usuario) {
        exibirModalAuthDoar();
    }

    if (!btnPublicar) return;

    btnPublicar.addEventListener('click', async () => {
        const userLogado = obterUsuario();

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
        const sexo = document.getElementById('sexo')?.value || 'Macho';
        const idade = document.getElementById('idade')?.value.trim() || '';
        const porte = document.getElementById('porte')?.value || '';
        const peso = document.getElementById('peso')?.value.trim() || '';
        const cor = document.getElementById('cor')?.value.trim() || '';
        const personalidade = document.getElementById('personalidade')?.value.trim() || '';
        const motivoDoacao = document.getElementById('motivo_doacao')?.value.trim() || '';
        const descricao = document.getElementById('descricao')?.value.trim() || '';

        // Validações obrigatórias
        if (!nome) return alert('Por favor, informe o nome do pet.');
        if (!especie) return alert('Por favor, selecione ou especifique a espécie do animal.');
        if (!raca) return alert('Por favor, informe a raça do pet (digite SRD se for vira-lata).');
        if (!idade) return alert('Por favor, informe a idade em anos.');
        if (!porte) return alert('Por favor, selecione o porte do pet.');
        if (!peso) return alert('Por favor, informe o peso aproximado em kg.');
        if (!cor) return alert('Por favor, informe a cor do pet.');
        if (!personalidade) return alert('Por favor, informe a personalidade e comportamento do pet.');
        if (!motivoDoacao) return alert('O motivo da doação é obrigatório. Por favor, explique-o.');
        if (!descricao) return alert('Por favor, conte a história e biografia do animalzinho.');

        if (listaArquivosFotos.length === 0) {
            return alert('Por favor, adicione pelo menos uma foto do pet para o cadastro (você pode adicionar até 5 fotos).');
        }

        const formData = new FormData();
        formData.append('nome', nome);
        formData.append('especie', especie);
        formData.append('raca', raca);
        formData.append('sexo', sexo);
        formData.append('idade', idade);
        formData.append('porte', porte);
        formData.append('peso', peso);
        formData.append('cor', cor);
        formData.append('personalidade', personalidade);
        formData.append('motivo_doacao', motivoDoacao);
        formData.append('descricao', descricao);

        if (userLogado && (userLogado.id_usuario || userLogado.id)) {
            formData.append('id_usuario', userLogado.id_usuario || userLogado.id);
        }

        // Adiciona as até 5 fotos no FormData
        listaArquivosFotos.forEach((file) => {
            formData.append('fotos[]', file);
        });
        // Adiciona também 'foto' para retrocompatibilidade
        formData.append('foto', listaArquivosFotos[0]);

        btnPublicar.disabled = true;
        btnPublicar.innerText = `Publicando pet (${listaArquivosFotos.length} foto${listaArquivosFotos.length > 1 ? 's' : ''})...`;

        try {
            const endpoint = '../index.php?rota=pets';
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });

            if (response.status === 401) {
                exibirModalAuthDoar();
                return;
            }

            const result = await response.json().catch(() => ({}));

            if (response.ok && (result.sucesso || result.id || result.status === 'sucesso')) {
                sessionStorage.removeItem('petalert_draft_pet');
                const idCadastrado = result.id || result.id_pet;
                alert(result.mensagem || 'Pet cadastrado com sucesso! Redirecionando para a página do pet...');
                if (idCadastrado) {
                    window.location.href = `../pets/detalhes.html?id=${idCadastrado}`;
                } else {
                    window.location.href = '../pets/index.html';
                }
            } else {
                alert(result.mensagem || result.erro || 'Erro ao cadastrar pet no banco de dados. Verifique os dados informados.');
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro de comunicação com o servidor ao cadastrar o pet. Verifique sua conexão e tente novamente.');
        } finally {
            btnPublicar.disabled = false;
            btnPublicar.innerText = '🐾 Publicar Pet para Adoção';
        }
    });
});
