<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'veterinarios';
$tituloPagina = 'Gerenciamento de Clínicas Veterinárias';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínicas Veterinárias — PetAlert</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>🏥 Clínicas Veterinárias Parceiras (tb_veterinario)</h2>
                <p>Cadastro, consulta, atualização de contatos, CRMV e endereços das clínicas disponíveis no sistema.</p>
            </div>
            <button type="button" class="btn-primary" id="btnNovaClinica">➕ Cadastrar Nova Clínica</button>
        </div>

        <div id="alertaClinicas" class="alert-adm"></div>

        <div class="toolbar-wrap">
            <input type="text" id="filtroNomeClinica" class="toolbar-input" placeholder="🔍 Buscar por nome da clínica ou CRMV...">
            <input type="text" id="filtroCidadeClinica" class="toolbar-input" placeholder="📍 Filtrar por cidade...">
            <span id="contadorClinicas" style="font-weight:700; color:var(--text-muted); font-size:0.88rem; margin-left:auto;">Carregando...</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome da Clínica</th>
                        <th>Cidade / UF</th>
                        <th>Telefone</th>
                        <th>CRMV</th>
                        <th>Horário de Atendimento</th>
                        <th style="text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaClinicas">
                    <tr><td colspan="7" style="text-align:center; padding:2rem;">Carregando clínicas do banco...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: CADASTRAR / EDITAR CLÍNICA -->
    <div class="admin-modal" id="modalClinica">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modalClinicaTitulo">Cadastrar Clínica Veterinária</h3>
                <button type="button" class="modal-close" id="btnFecharModalClinica">✕</button>
            </div>
            <form id="formClinica">
                <input type="hidden" id="clinicaId" value="">
                <div class="form-grid-2">
                    <div class="form-group-adm">
                        <label for="clinicaNome">Nome da Clínica / Hospital *</label>
                        <input type="text" id="clinicaNome" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="clinicaTelefone">Telefone / WhatsApp</label>
                        <input type="text" id="clinicaTelefone">
                    </div>
                    <div class="form-group-adm">
                        <label for="clinicaCrmv">CRMV do Responsável</label>
                        <input type="text" id="clinicaCrmv" placeholder="Ex.: 12345-SP">
                    </div>
                    <div class="form-group-adm">
                        <label for="clinicaEmail">E-mail de Contato</label>
                        <input type="email" id="clinicaEmail">
                    </div>
                    <div class="form-group-adm">
                        <label for="clinicaCep">CEP</label>
                        <input type="text" id="clinicaCep">
                    </div>
                    <div class="form-group-adm">
                        <label for="clinicaEndereco">Endereço Completo</label>
                        <input type="text" id="clinicaEndereco">
                    </div>
                    <div class="form-group-adm">
                        <label for="clinicaCidade">Cidade *</label>
                        <input type="text" id="clinicaCidade" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="clinicaEstado">Estado (UF) *</label>
                        <input type="text" id="clinicaEstado" maxlength="2" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="clinicaHorario">Horário de Atendimento</label>
                        <input type="text" id="clinicaHorario" placeholder="Ex.: Seg a Sex 08h-18h | 24 Horas">
                    </div>
                    <div class="form-group-adm">
                        <label for="clinicaSite">Site / Rede Social</label>
                        <input type="text" id="clinicaSite" placeholder="https://...">
                    </div>
                </div>

                <div id="alertaModalClinica" class="alert-adm" style="margin-top:1rem;"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarClinica">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnSalvarClinica">Salvar Clínica</button>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let clinicas = [];
    const tabela = document.getElementById('tabelaClinicas');
    const filtroNome = document.getElementById('filtroNomeClinica');
    const filtroCidade = document.getElementById('filtroCidadeClinica');
    const contador = document.getElementById('contadorClinicas');
    const alerta = document.getElementById('alertaClinicas');

    const modal = document.getElementById('modalClinica');
    const form = document.getElementById('formClinica');
    const alertaModal = document.getElementById('alertaModalClinica');

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

    async function carregarClinicas() {
        tabela.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem;">Carregando clínicas...</td></tr>';
        try {
            const res = await fetch('../index.php?rota=admin&acao=veterinarios');
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.status !== 'sucesso') {
                exibirAlerta(alerta, 'error', json.mensagem || 'Erro ao carregar clínicas.');
                tabela.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem; color:#DC2626;">Erro ao carregar clínicas.</td></tr>';
                return;
            }
            clinicas = Array.isArray(json.dados) ? json.dados : [];
            renderizar();
        } catch (e) {
            exibirAlerta(alerta, 'error', 'Erro ao conectar ao servidor.');
        }
    }

    function renderizar() {
        const termo = (filtroNome.value || '').trim().toLowerCase();
        const cid = (filtroCidade.value || '').trim().toLowerCase();

        const filtradas = clinicas.filter(c => {
            const nome = (c.nome || '').toLowerCase();
            const crmv = (c.crmv || '').toLowerCase();
            const cidade = (c.cidade || '').toLowerCase();

            const bateTermo = !termo || nome.includes(termo) || crmv.includes(termo);
            const bateCidade = !cid || cidade.includes(cid);

            return bateTermo && bateCidade;
        });

        contador.textContent = `${filtradas.length} de ${clinicas.length} clínica(s)`;

        if (!filtradas.length) {
            tabela.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhuma clínica localizada com estes filtros.</td></tr>';
            return;
        }

        tabela.innerHTML = filtradas.map(c => {
            const id = c.id_veterinario;
            return `
                <tr>
                    <td><strong>#${id}</strong></td>
                    <td><strong>${sanitizar(c.nome)}</strong></td>
                    <td>${sanitizar(c.cidade || '—')}/${sanitizar(c.estado || '')}</td>
                    <td>${sanitizar(c.telefone || '—')}</td>
                    <td><span class="badge badge-info">${sanitizar(c.crmv || '—')}</span></td>
                    <td style="font-size:0.85rem;">${sanitizar(c.horario_atendimento || 'Comercial')}</td>
                    <td style="text-align:right; white-space:nowrap;">
                        <button type="button" class="btn-secondary btn-sm btn-edit-cli" data-id="${id}" title="Editar informações">✏️</button>
                        <button type="button" class="btn-danger-sm btn-sm btn-del-cli" data-id="${id}" data-nome="${sanitizar(c.nome)}" title="Excluir clínica">🗑️</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    filtroNome.addEventListener('input', renderizar);
    filtroCidade.addEventListener('input', renderizar);

    document.getElementById('btnNovaClinica')?.addEventListener('click', () => {
        form.reset();
        document.getElementById('clinicaId').value = '';
        document.getElementById('modalClinicaTitulo').textContent = 'Cadastrar Nova Clínica';
        alertaModal.style.display = 'none';
        modal.classList.add('open');
    });

    tabela.addEventListener('click', async (e) => {
        const btnEdit = e.target.closest('.btn-edit-cli');
        const btnDel = e.target.closest('.btn-del-cli');

        if (btnEdit) {
            const id = Number(btnEdit.dataset.id);
            const cli = clinicas.find(c => Number(c.id_veterinario) === id);
            if (cli) {
                form.reset();
                document.getElementById('clinicaId').value = cli.id_veterinario;
                document.getElementById('modalClinicaTitulo').textContent = `Editar Clínica #${cli.id_veterinario}`;
                document.getElementById('clinicaNome').value = cli.nome || '';
                document.getElementById('clinicaTelefone').value = cli.telefone || '';
                document.getElementById('clinicaCrmv').value = cli.crmv || '';
                document.getElementById('clinicaEmail').value = cli.email || '';
                document.getElementById('clinicaCep').value = cli.cep || '';
                document.getElementById('clinicaEndereco').value = cli.endereco || '';
                document.getElementById('clinicaCidade').value = cli.cidade || '';
                document.getElementById('clinicaEstado').value = cli.estado || '';
                document.getElementById('clinicaHorario').value = cli.horario_atendimento || '';
                document.getElementById('clinicaSite').value = cli.site || '';
                alertaModal.style.display = 'none';
                modal.classList.add('open');
            }
        } else if (btnDel) {
            const id = Number(btnDel.dataset.id);
            const nome = btnDel.dataset.nome;
            if (confirm(`Deseja realmente remover o cadastro da clínica "${nome}" (ID #${id})?`)) {
                try {
                    const res = await fetch('../index.php?rota=admin&acao=veterinario-excluir', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_veterinario: id })
                    });
                    const json = await res.json().catch(() => ({}));
                    if (res.ok && json.status === 'sucesso') {
                        exibirAlerta(alerta, 'success', json.mensagem || 'Clínica removida com sucesso!');
                        await carregarClinicas();
                    } else {
                        exibirAlerta(alerta, 'error', json.mensagem || 'Erro ao remover clínica.');
                    }
                } catch (err) {
                    exibirAlerta(alerta, 'error', 'Erro de conexão.');
                }
            }
        }
    });

    document.getElementById('btnFecharModalClinica')?.addEventListener('click', () => modal.classList.remove('open'));
    document.getElementById('btnCancelarClinica')?.addEventListener('click', () => modal.classList.remove('open'));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            id_veterinario: document.getElementById('clinicaId').value,
            nome: document.getElementById('clinicaNome').value.trim(),
            telefone: document.getElementById('clinicaTelefone').value.trim(),
            crmv: document.getElementById('clinicaCrmv').value.trim(),
            email: document.getElementById('clinicaEmail').value.trim(),
            cep: document.getElementById('clinicaCep').value.trim(),
            endereco: document.getElementById('clinicaEndereco').value.trim(),
            cidade: document.getElementById('clinicaCidade').value.trim(),
            estado: document.getElementById('clinicaEstado').value.trim().toUpperCase(),
            horario_atendimento: document.getElementById('clinicaHorario').value.trim(),
            site: document.getElementById('clinicaSite').value.trim()
        };

        try {
            const res = await fetch('../index.php?rota=admin&acao=veterinario-salvar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.status === 'sucesso') {
                modal.classList.remove('open');
                exibirAlerta(alerta, 'success', json.mensagem || 'Clínica salva com sucesso!');
                await carregarClinicas();
            } else {
                exibirAlerta(alertaModal, 'error', json.mensagem || 'Erro ao salvar clínica.');
            }
        } catch (err) {
            exibirAlerta(alertaModal, 'error', 'Erro ao conectar ao servidor.');
        }
    });

    carregarClinicas();
});
</script>
</body>
</html>
