<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'pets';
$tituloPagina = 'Gerenciamento de Animais';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Animais — PetAlert</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>🐾 Gerenciamento de Animais (tb_animal)</h2>
                <p>Visualização de todos os pets, fotos, tutores responsáveis, filtros por espécie/porte, edição e moderação de anúncios.</p>
            </div>
        </div>

        <div id="alertaPets" class="alert-adm"></div>

        <div class="toolbar-wrap">
            <input type="text" id="filtroBuscaPet" class="toolbar-input" placeholder="🔍 Buscar por nome do pet, raça ou tutor...">
            <select id="filtroEspecie" class="toolbar-select">
                <option value="">Todas as espécies</option>
                <option value="cachorro">Cachorro</option>
                <option value="gato">Gato</option>
                <option value="outro">Outro</option>
            </select>
            <select id="filtroPorte" class="toolbar-select">
                <option value="">Todos os portes</option>
                <option value="pequeno">Pequeno</option>
                <option value="medio">Médio</option>
                <option value="grande">Grande</option>
            </select>
            <select id="filtroStatusAdocao" class="toolbar-select">
                <option value="">Todos os status</option>
                <option value="disponivel">Disponíveis</option>
                <option value="adotado">Adotados / Doados</option>
            </select>
            <span id="contadorPets" style="font-weight:700; color:var(--text-muted); font-size:0.88rem; margin-left:auto;">Carregando...</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>Espécie / Raça</th>
                        <th>Porte / Idade</th>
                        <th>Tutor Responsável</th>
                        <th>Status</th>
                        <th style="text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaPets">
                    <tr><td colspan="8" style="text-align:center; padding:2rem;">Carregando animais do banco...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: DETALHES E EDIÇÃO DO PET -->
    <div class="admin-modal" id="modalPet">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modalPetTitulo">Editar Informações do Pet</h3>
                <button type="button" class="modal-close" id="btnFecharModalPet">✕</button>
            </div>
            <form id="formPet">
                <input type="hidden" id="petId">
                <div class="form-grid-2">
                    <div class="form-group-adm">
                        <label for="petNome">Nome do Pet *</label>
                        <input type="text" id="petNome" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="petEspecie">Espécie *</label>
                        <select id="petEspecie" required>
                            <option value="cachorro">Cachorro</option>
                            <option value="gato">Gato</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="form-group-adm">
                        <label for="petRaca">Raça</label>
                        <input type="text" id="petRaca">
                    </div>
                    <div class="form-group-adm">
                        <label for="petPorte">Porte</label>
                        <select id="petPorte">
                            <option value="pequeno">Pequeno</option>
                            <option value="medio">Médio</option>
                            <option value="grande">Grande</option>
                        </select>
                    </div>
                    <div class="form-group-adm">
                        <label for="petIdade">Idade (anos)</label>
                        <input type="number" id="petIdade" min="0" max="30">
                    </div>
                    <div class="form-group-adm">
                        <label for="petStatus">Status de Adoção</label>
                        <select id="petStatus">
                            <option value="disponivel">Disponível para Adoção</option>
                            <option value="adotado">Adotado / Concluído</option>
                        </select>
                    </div>
                    <div class="form-group-adm" style="grid-column: 1 / -1;">
                        <label for="petDescricao">Descrição / História</label>
                        <textarea id="petDescricao" rows="3"></textarea>
                    </div>
                </div>

                <div id="alertaModalPet" class="alert-adm" style="margin-top:1rem;"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarPet">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnSalvarPet">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let pets = [];
    const tabela = document.getElementById('tabelaPets');
    const filtroBusca = document.getElementById('filtroBuscaPet');
    const filtroEspecie = document.getElementById('filtroEspecie');
    const filtroPorte = document.getElementById('filtroPorte');
    const filtroStatus = document.getElementById('filtroStatusAdocao');
    const contador = document.getElementById('contadorPets');
    const alerta = document.getElementById('alertaPets');

    const modal = document.getElementById('modalPet');
    const form = document.getElementById('formPet');
    const alertaModal = document.getElementById('alertaModalPet');

    function sanitizar(txt) {
        const d = document.createElement('div');
        d.textContent = txt ?? '';
        return d.innerHTML;
    }

    function exibirAlerta(el, tipo, msg) {
        if (!el) return;
        el.className = `alert-adm ${tipo}`;
        el.textContent = msg;
        el.style.display = 'block';
        setTimeout(() => el.style.display = 'none', 5000);
    }

    async function carregarPets() {
        tabela.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:2rem;">Carregando animais...</td></tr>';
        try {
            const res = await fetch('../index.php?rota=admin&acao=pets');
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.status !== 'sucesso') {
                exibirAlerta(alerta, 'error', json.mensagem || 'Erro ao carregar lista de animais.');
                tabela.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:2rem; color:#DC2626;">Erro ao carregar animais.</td></tr>';
                return;
            }
            pets = Array.isArray(json.dados) ? json.dados : [];
            renderizar();
        } catch (e) {
            exibirAlerta(alerta, 'error', 'Erro ao conectar ao servidor.');
        }
    }

    function renderizar() {
        const termo = (filtroBusca.value || '').trim().toLowerCase();
        const especie = (filtroEspecie.value || '').trim().toLowerCase();
        const porte = (filtroPorte.value || '').trim().toLowerCase();
        const status = (filtroStatus.value || '').trim().toLowerCase();

        const filtrados = pets.filter(p => {
            const nome = (p.nome || '').toLowerCase();
            const raca = (p.raca || '').toLowerCase();
            const tutor = (p.nome_tutor || '').toLowerCase();
            const esp = (p.especie || '').toLowerCase();
            const por = (p.porte || '').toLowerCase();
            const st = (p.status_adocao || 'disponivel').toLowerCase();

            const bateTermo = !termo || nome.includes(termo) || raca.includes(termo) || tutor.includes(termo);
            const bateEspecie = !especie || esp === especie;
            const batePorte = !porte || por === porte;
            const bateStatus = !status || (status === 'disponivel' ? ['disponivel','disponível'].includes(st) : ['doado','adotado','adotada'].includes(st));

            return bateTermo && bateEspecie && batePorte && bateStatus;
        });

        contador.textContent = `${filtrados.length} de ${pets.length} animal(is)`;

        if (!filtrados.length) {
            tabela.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhum animal localizado.</td></tr>';
            return;
        }

        tabela.innerHTML = filtrados.map(p => {
            const id = p.id_pet;
            const isAdotado = ['doado','adotado','adotada'].includes((p.status_adocao || '').toLowerCase());
            const badgeStatus = isAdotado
                ? `<span class="badge badge-success">❤️ Adotado</span>`
                : `<span class="badge badge-warning">🟢 Disponível</span>`;

            let fotoHtml = `<div style="width:40px; height:40px; border-radius:10px; background:#F3F4F6; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">🐾</div>`;
            if (p.foto) {
                const src = p.foto.startsWith('http') ? p.foto : `../${p.foto.replace(/^\.\.\//, '')}`;
                fotoHtml = `<img src="${sanitizar(src)}" style="width:40px; height:40px; border-radius:10px; object-fit:cover; border:1px solid #E5E7EB;" onerror="this.src='../img/pets.png'">`;
            }

            return `
                <tr>
                    <td><strong>#${id}</strong></td>
                    <td>${fotoHtml}</td>
                    <td><strong>${sanitizar(p.nome)}</strong></td>
                    <td>${sanitizar(p.especie || '—')} / ${sanitizar(p.raca || 'SRD')}</td>
                    <td>${sanitizar(p.porte || 'Médio')} / ${p.idade ? p.idade + ' ano(s)' : '—'}</td>
                    <td>
                        <div><strong>${sanitizar(p.nome_tutor || 'Tutor não vinculado')}</strong></div>
                        <small style="color:var(--text-muted);">${sanitizar(p.cidade_tutor ? p.cidade_tutor + '/' + p.estado_tutor : '')}</small>
                    </td>
                    <td>${badgeStatus}</td>
                    <td style="text-align:right; white-space:nowrap;">
                        <button type="button" class="btn-secondary btn-sm btn-edit-pet" data-id="${id}" title="Editar informações">✏️</button>
                        <button type="button" class="${isAdotado ? 'btn-secondary' : 'btn-success-sm'} btn-sm btn-status-pet" data-id="${id}" data-status="${isAdotado ? 'disponivel' : 'adotado'}" title="${isAdotado ? 'Marcar como Disponível' : 'Marcar como Adotado'}">
                            ${isAdotado ? 'Reativar' : 'Marcar Adotado'}
                        </button>
                        <button type="button" class="btn-danger-sm btn-sm btn-del-pet" data-id="${id}" data-nome="${sanitizar(p.nome)}" title="Excluir anúncio inadequado">🗑️</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    filtroBusca.addEventListener('input', renderizar);
    filtroEspecie.addEventListener('change', renderizar);
    filtroPorte.addEventListener('change', renderizar);
    filtroStatus.addEventListener('change', renderizar);

    tabela.addEventListener('click', async (e) => {
        const btnEdit = e.target.closest('.btn-edit-pet');
        const btnStatus = e.target.closest('.btn-status-pet');
        const btnDel = e.target.closest('.btn-del-pet');

        if (btnEdit) {
            const id = Number(btnEdit.dataset.id);
            const pet = pets.find(p => Number(p.id_pet) === id);
            if (pet) abrirModalEdicao(pet);
        } else if (btnStatus) {
            const id = Number(btnStatus.dataset.id);
            const novoSt = btnStatus.dataset.status;
            await alterarStatusPet(id, novoSt);
        } else if (btnDel) {
            const id = Number(btnDel.dataset.id);
            const nome = btnDel.dataset.nome;
            await excluirPet(id, nome);
        }
    });

    function abrirModalEdicao(p) {
        document.getElementById('petId').value = p.id_pet;
        document.getElementById('petNome').value = p.nome || '';
        document.getElementById('petEspecie').value = (p.especie || 'cachorro').toLowerCase();
        document.getElementById('petRaca').value = p.raca || '';
        document.getElementById('petPorte').value = (p.porte || 'medio').toLowerCase();
        document.getElementById('petIdade').value = p.idade || '';
        document.getElementById('petStatus').value = ['doado','adotado','adotada'].includes((p.status_adocao || '').toLowerCase()) ? 'adotado' : 'disponivel';
        document.getElementById('petDescricao').value = p.descricao || '';
        alertaModal.style.display = 'none';
        modal.classList.add('open');
    }

    document.getElementById('btnFecharModalPet')?.addEventListener('click', () => modal.classList.remove('open'));
    document.getElementById('btnCancelarPet')?.addEventListener('click', () => modal.classList.remove('open'));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            id_pet: document.getElementById('petId').value,
            nome: document.getElementById('petNome').value.trim(),
            especie: document.getElementById('petEspecie').value,
            raca: document.getElementById('petRaca').value.trim(),
            porte: document.getElementById('petPorte').value,
            idade: document.getElementById('petIdade').value,
            status_adocao: document.getElementById('petStatus').value,
            descricao: document.getElementById('petDescricao').value.trim()
        };

        try {
            const res = await fetch('../index.php?rota=admin&acao=pet-salvar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.status === 'sucesso') {
                modal.classList.remove('open');
                exibirAlerta(alerta, 'success', json.mensagem || 'Pet atualizado com sucesso!');
                await carregarPets();
            } else {
                exibirAlerta(alertaModal, 'error', json.mensagem || 'Erro ao salvar pet.');
            }
        } catch (err) {
            exibirAlerta(alertaModal, 'error', 'Erro de conexão com o servidor.');
        }
    });

    async function alterarStatusPet(id, novoStatus) {
        try {
            const res = await fetch('../index.php?rota=admin&acao=pet-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_pet: id, status_adocao: novoStatus })
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.status === 'sucesso') {
                exibirAlerta(alerta, 'success', json.mensagem);
                await carregarPets();
            } else {
                exibirAlerta(alerta, 'error', json.mensagem || 'Erro ao alterar status.');
            }
        } catch (e) {
            exibirAlerta(alerta, 'error', 'Erro de conexão.');
        }
    }

    async function excluirPet(id, nome) {
        if (!confirm(`Deseja realmente excluir o anúncio do pet "${nome}" (ID #${id})?\n\nEsta ação é recomendada apenas para anúncios inadequados ou com violação das diretrizes.`)) {
            return;
        }

        try {
            const res = await fetch('../index.php?rota=admin&acao=pet-excluir', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_pet: id })
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.status === 'sucesso') {
                exibirAlerta(alerta, 'success', json.mensagem || 'Anúncio excluído com sucesso.');
                await carregarPets();
            } else {
                exibirAlerta(alerta, 'error', json.mensagem || 'Erro ao excluir anúncio.');
            }
        } catch (e) {
            exibirAlerta(alerta, 'error', 'Erro ao conectar ao servidor.');
        }
    }

    carregarPets();
});
</script>
</body>
</html>
