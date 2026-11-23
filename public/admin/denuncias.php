<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'denuncias';
$tituloPagina = 'Central Administrativa de Denúncias';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central de Denúncias — PetAlert</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>🚨 Central de Denúncias (tb_denuncia)</h2>
                <p>Análise de relatos de maus-tratos, fotos/vídeos, identificação de denunciante e denunciado, e resolução com auditoria.</p>
            </div>
        </div>

        <div id="alertaDenuncias" class="alert-adm"></div>

        <div class="toolbar-wrap">
            <select id="filtroStatusDenuncia" class="toolbar-select">
                <option value="">Todos os status</option>
                <option value="pendente">Pendentes</option>
                <option value="em_analise">Em Análise</option>
                <option value="resolvida">Resolvidas</option>
                <option value="improcedente">Improcedentes</option>
            </select>
            <span id="contadorDenuncias" style="font-weight:700; color:var(--text-muted); font-size:0.88rem; margin-left:auto;">Carregando...</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data & Hora</th>
                        <th>Denunciante</th>
                        <th>Denunciado / Alvo</th>
                        <th>Motivo / Tipo</th>
                        <th>Localização</th>
                        <th>Mídia</th>
                        <th>Status</th>
                        <th style="text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaDenuncias">
                    <tr><td colspan="9" style="text-align:center; padding:2rem;">Carregando denúncias do banco...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DE ANÁLISE / RESOLUÇÃO DA DENÚNCIA -->
    <div class="admin-modal" id="modalDenuncia">
        <div class="modal-box" style="max-width:700px;">
            <div class="modal-header">
                <h3 id="modalDenunciaTitulo">Análise da Denúncia</h3>
                <button type="button" class="modal-close" id="btnFecharModalDenuncia">✕</button>
            </div>
            <div id="detalhesDenuncia" style="margin-bottom:1.5rem; font-size:0.95rem; line-height:1.6;">
                <!-- Preenchido via JS -->
            </div>

            <form id="formStatusDenuncia">
                <input type="hidden" id="denunciaId">
                <div class="form-group-adm">
                    <label for="novoStatusDenuncia">Novo Status da Denúncia *</label>
                    <select id="novoStatusDenuncia" required>
                        <option value="pendente">Pendente (Aguardando verificação)</option>
                        <option value="em_analise">Em Análise (Investigação em andamento)</option>
                        <option value="resolvida">Resolvida (Providências concluídas)</option>
                        <option value="improcedente">Improcedente (Denúncia sem fundamento)</option>
                    </select>
                </div>

                <div class="form-group-adm">
                    <label for="obsDenuncia">Observações Administrativas / Parecer do Administrador</label>
                    <textarea id="obsDenuncia" rows="3" placeholder="Descreva as medidas tomadas, contato realizado ou conclusão do caso..."></textarea>
                </div>

                <div id="alertaModalDenuncia" class="alert-adm"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarDenuncia">Fechar</button>
                    <button type="submit" class="btn-primary" id="btnSalvarDenuncia">Salvar Parecer e Status</button>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let denuncias = [];
    const tabela = document.getElementById('tabelaDenuncias');
    const filtroStatus = document.getElementById('filtroStatusDenuncia');
    const contador = document.getElementById('contadorDenuncias');
    const alerta = document.getElementById('alertaDenuncias');

    const modal = document.getElementById('modalDenuncia');
    const form = document.getElementById('formStatusDenuncia');
    const alertaModal = document.getElementById('alertaModalDenuncia');

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

    async function carregarDenuncias() {
        tabela.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:2rem;">Carregando denúncias...</td></tr>';
        try {
            const res = await fetch('../index.php?rota=admin&acao=denuncias');
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.status !== 'sucesso') {
                exibirAlerta(alerta, 'error', json.mensagem || 'Erro ao carregar denúncias.');
                tabela.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:2rem; color:#DC2626;">Erro ao carregar denúncias.</td></tr>';
                return;
            }
            denuncias = Array.isArray(json.dados) ? json.dados : [];
            renderizar();
        } catch (e) {
            exibirAlerta(alerta, 'error', 'Erro ao conectar ao servidor.');
        }
    }

    function renderizar() {
        const st = (filtroStatus.value || '').trim().toLowerCase();
        const filtradas = denuncias.filter(d => {
            if (!st) return true;
            return (d.status || '').toLowerCase() === st;
        });

        contador.textContent = `${filtradas.length} de ${denuncias.length} denúncia(s)`;

        if (!filtradas.length) {
            tabela.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhuma denúncia encontrada para este status.</td></tr>';
            return;
        }

        tabela.innerHTML = filtradas.map(d => {
            const id = d.id_denuncia;
            const statusNorm = (d.status || 'pendente').toLowerCase();
            let badge = `<span class="badge badge-warning">Pendente</span>`;
            if (statusNorm === 'em_analise') badge = `<span class="badge badge-info">Em Análise</span>`;
            else if (statusNorm === 'resolvida') badge = `<span class="badge badge-success">Resolvida</span>`;
            else if (statusNorm === 'improcedente') badge = `<span class="badge badge-danger">Improcedente</span>`;

            let midiaHtml = `<span style="color:var(--text-muted); font-size:0.8rem;">Sem anexo</span>`;
            if (d.foto) {
                const src = d.foto.startsWith('http') ? d.foto : `../${d.foto.replace(/^\.\.\//, '')}`;
                midiaHtml = `<a href="${sanitizar(src)}" target="_blank" style="text-decoration:none; font-size:1.1rem;" title="Abrir foto da denúncia">📷 Foto</a>`;
            } else if (d.video) {
                const src = d.video.startsWith('http') ? d.video : `../${d.video.replace(/^\.\.\//, '')}`;
                midiaHtml = `<a href="${sanitizar(src)}" target="_blank" style="text-decoration:none; font-size:1.1rem;" title="Abrir vídeo da denúncia">🎥 Vídeo</a>`;
            }

            return `
                <tr>
                    <td><strong>#${id}</strong></td>
                    <td style="white-space:nowrap; font-size:0.85rem;">${sanitizar(d.data_criacao || '—')}</td>
                    <td>
                        <strong>${sanitizar(d.nome_denunciante || 'Anônimo')}</strong><br>
                        <small style="color:var(--text-muted);">${sanitizar(d.telefone_denunciante || d.email_denunciante || '')}</small>
                    </td>
                    <td>
                        <strong>${sanitizar(d.nome_tutor_denunciado || 'Usuário não identificado')}</strong><br>
                        <small style="color:var(--text-muted);">Pet: ${sanitizar(d.nome_pet || 'Não especificado')}</small>
                    </td>
                    <td>
                        <strong>${sanitizar(d.motivo || d.tipo || 'Geral')}</strong><br>
                        <small style="color:var(--text-muted); display:block; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${sanitizar(d.descricao || '')}</small>
                    </td>
                    <td style="font-size:0.85rem;">${sanitizar(d.localizacao || '—')}</td>
                    <td>${midiaHtml}</td>
                    <td>${badge}</td>
                    <td style="text-align:right;">
                        <button type="button" class="btn-primary btn-sm btn-analisar" data-id="${id}" title="Analisar e alterar status">🔍 Analisar</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    filtroStatus.addEventListener('change', renderizar);

    tabela.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-analisar');
        if (btn) {
            const id = Number(btn.dataset.id);
            const den = denuncias.find(d => Number(d.id_denuncia) === id);
            if (den) abrirModalAnalise(den);
        }
    });

    function abrirModalAnalise(d) {
        document.getElementById('denunciaId').value = d.id_denuncia;
        document.getElementById('modalDenunciaTitulo').textContent = `Analisar Denúncia #${d.id_denuncia}`;
        document.getElementById('novoStatusDenuncia').value = (d.status || 'pendente').toLowerCase();
        document.getElementById('obsDenuncia').value = '';

        let midiaPreview = '<p><em>Nenhuma foto ou vídeo anexado.</em></p>';
        if (d.foto) {
            const src = d.foto.startsWith('http') ? d.foto : `../${d.foto.replace(/^\.\.\//, '')}`;
            midiaPreview = `<div style="margin-top:0.5rem;"><img src="${sanitizar(src)}" style="max-width:100%; max-height:220px; border-radius:12px; border:1px solid #E5E7EB;" onerror="this.style.display='none'"></div>`;
        }

        document.getElementById('detalhesDenuncia').innerHTML = `
            <div style="background:#FFFDF7; border:1px solid #FFE58F; border-radius:14px; padding:1.2rem; margin-bottom:1rem;">
                <div style="margin-bottom:0.5rem;"><strong>Denunciante:</strong> ${sanitizar(d.nome_denunciante || 'Anônimo')} (${sanitizar(d.email_denunciante || 'Sem e-mail')} - ${sanitizar(d.telefone_denunciante || 'Sem telefone')})</div>
                <div style="margin-bottom:0.5rem;"><strong>Denunciado:</strong> ${sanitizar(d.nome_tutor_denunciado || 'Não identificado')} | <strong>Pet:</strong> ${sanitizar(d.nome_pet || 'Não informado')}</div>
                <div style="margin-bottom:0.5rem;"><strong>Motivo:</strong> ${sanitizar(d.motivo || d.tipo || 'Geral')}</div>
                <div style="margin-bottom:0.5rem;"><strong>Localização do Ocorrido:</strong> ${sanitizar(d.localizacao || 'Não informada')}</div>
                <div style="margin-bottom:0.5rem;"><strong>Data do Registro:</strong> ${sanitizar(d.data_criacao || '—')}</div>
                <div style="margin-top:0.8rem; padding-top:0.8rem; border-top:1px dashed #FDE68A;">
                    <strong>Relato da Denúncia:</strong>
                    <p style="margin:0.4rem 0 0; color:#374151;">${sanitizar(d.descricao || 'Sem descrição informada.')}</p>
                </div>
                ${midiaPreview}
            </div>
        `;

        alertaModal.style.display = 'none';
        modal.classList.add('open');
    }

    document.getElementById('btnFecharModalDenuncia')?.addEventListener('click', () => modal.classList.remove('open'));
    document.getElementById('btnCancelarDenuncia')?.addEventListener('click', () => modal.classList.remove('open'));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            id_denuncia: document.getElementById('denunciaId').value,
            status: document.getElementById('novoStatusDenuncia').value,
            observacoes: document.getElementById('obsDenuncia').value.trim()
        };

        try {
            const res = await fetch('../index.php?rota=admin&acao=denuncia-atualizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.status === 'sucesso') {
                modal.classList.remove('open');
                exibirAlerta(alerta, 'success', json.mensagem || 'Denúncia atualizada com sucesso!');
                await carregarDenuncias();
            } else {
                exibirAlerta(alertaModal, 'error', json.mensagem || 'Erro ao atualizar denúncia.');
            }
        } catch (err) {
            exibirAlerta(alertaModal, 'error', 'Erro de conexão com o servidor.');
        }
    });

    carregarDenuncias();
});
</script>
</body>
</html>
