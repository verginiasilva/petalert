<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'configuracoes';
$tituloPagina = 'Configurações & Administradores Oficiais';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações — PetAlert Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>⚙️ Configuração dos Administradores Oficiais</h2>
                <p>Gerenciamento e reconhecimento dos 3 administradores oficiais exclusivos do PetAlert no banco <code>petalert</code>.</p>
            </div>
        </div>

        <div id="alertaConfig" class="alert-adm"></div>

        <!-- QUADRO DOS 3 ADMINISTRADORES OFICIAIS -->
        <h3 style="margin-top:0; color:var(--text-main); font-size:1.15rem; margin-bottom:1rem;">🛡️ Administradores Oficiais do PetAlert</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.2rem; margin-bottom:2.5rem;">
            
            <!-- ADMIN 1: MARIA EDUARDA -->
            <div class="stat-card" style="border-top:4px solid #F59E0B;" id="cardMariaEduarda">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.8rem;">
                    <div>
                        <strong style="font-size:1.15rem; color:#1F2937; display:block;">1. Maria Eduarda</strong>
                        <span class="badge badge-warning" id="statusBadgeMaria" style="margin-top:4px;">⏳ Pendente de Dados Reais</span>
                    </div>
                    <div style="width:40px; height:40px; border-radius:50%; background:#FFFBEB; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">👩‍💼</div>
                </div>
                <div style="font-size:0.88rem; color:#4B5563; line-height:1.6;" id="infoMaria">
                    <p style="margin:0 0 0.5rem;"><strong>Status no Banco:</strong> <span style="color:#D97706;">Aguardando credenciais cadastrais reais na tabela <code>tb_usuario</code>.</span></p>
                    <p style="margin:0 0 0.8rem; font-size:0.82rem; color:var(--text-muted);">Conforme as regras do projeto, não foram gerados dados pessoais fictícios para a administradora.</p>
                    <button type="button" class="btn-primary btn-sm" id="btnCadastrarMaria" style="width:100%; justify-content:center;">➕ Cadastrar Credenciais Reais</button>
                </div>
            </div>

            <!-- ADMIN 2: MIGUEL LIMA -->
            <div class="stat-card" style="border-top:4px solid #10B981;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.8rem;">
                    <div>
                        <strong style="font-size:1.15rem; color:#1F2937; display:block;">2. Miguel Lima</strong>
                        <span class="badge badge-success" style="margin-top:4px;">⭐ Ativo Oficial</span>
                    </div>
                    <div style="width:40px; height:40px; border-radius:50%; background:#ECFDF5; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">👨‍💼</div>
                </div>
                <div style="font-size:0.88rem; color:#4B5563; line-height:1.6;">
                    <p style="margin:0 0 0.4rem;"><strong>ID no Banco:</strong> <code>id_usuario = 6</code></p>
                    <p style="margin:0 0 0.4rem;"><strong>E-mail:</strong> <code>miguel@alertemail.com</code></p>
                    <p style="margin:0 0 0.4rem;"><strong>CPF:</strong> <code>000.000.000-06</code></p>
                    <p style="margin:0 0 0.4rem;"><strong>Permissão:</strong> <code>tipo_usuario = 'admin'</code></p>
                    <p style="margin:0; font-size:0.82rem; color:#059669;">Registro identificado e com permissões administrativas ativas.</p>
                </div>
            </div>

            <!-- ADMIN 3: VERGINIA -->
            <div class="stat-card" style="border-top:4px solid #10B981;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.8rem;">
                    <div>
                        <strong style="font-size:1.15rem; color:#1F2937; display:block;">3. Verginia</strong>
                        <span class="badge badge-success" style="margin-top:4px;">⭐ Ativa Oficial</span>
                    </div>
                    <div style="width:40px; height:40px; border-radius:50%; background:#ECFDF5; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">👩‍💼</div>
                </div>
                <div style="font-size:0.88rem; color:#4B5563; line-height:1.6;">
                    <p style="margin:0 0 0.4rem;"><strong>Nome Real:</strong> Verginia dos Santos Silva</p>
                    <p style="margin:0 0 0.4rem;"><strong>IDs no Banco:</strong> <code>id_usuario = 73</code> e <code>74</code></p>
                    <p style="margin:0 0 0.4rem;"><strong>E-mail:</strong> <code>verginiadossantossilva14@gmail.com</code></p>
                    <p style="margin:0 0 0.4rem;"><strong>CPF:</strong> <code>512.579.888-67</code></p>
                    <p style="margin:0 0 0.4rem;"><strong>Permissão:</strong> <code>tipo_usuario = 'admin'</code></p>
                    <p style="margin:0; font-size:0.82rem; color:#059669;">Registro identificado e com permissões administrativas ativas.</p>
                </div>
            </div>
        </div>

        <!-- DIRETRIZES DE SEGURANÇA E AUDITORIA -->
        <div style="background:#FFFDF7; border:1px solid #FFE58F; border-radius:var(--radius-lg); padding:1.5rem;">
            <h3 style="margin-top:0; color:#832738; font-size:1.15rem;">🔐 Diretrizes de Segurança do PetAlert</h3>
            <ul style="margin:0.8rem 0 0; padding-left:1.3rem; color:#374151; line-height:1.8; font-size:0.95rem;">
                <li><strong>Proteção Backend Rigorosa:</strong> Todas as rotas administrativas exigem verificação ativa da sessão e do perfil <code>admin</code> no banco MySQL. Tentativas de acesso por usuários comuns retornam HTTP 403 Forbidden.</li>
                <li><strong>Proteção Contra Modificação no Navegador:</strong> A autorização é validada exclusivamente no servidor PHP a cada requisição, tornando ineficazes manipulações locais em cookies ou localStorage.</li>
                <li><strong>Tratamento de Dados Pessoais (LGPD):</strong> CPF, endereços completos e dados privados de contato não são expostos a usuários comuns.</li>
                <li><strong>Registro de Atividades:</strong> Toda alteração ou moderação realizada pelos administradores é registrada na tabela <code>tb_historico_admin</code> com identificação do autor, data, hora e IP.</li>
            </ul>
        </div>
    </div>

    <!-- MODAL PARA CADASTRAR MARIA EDUARDA (SEM DADOS FAKES) -->
    <div class="admin-modal" id="modalMaria">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Cadastrar Credenciais Oficiais de Maria Eduarda</h3>
                <button type="button" class="modal-close" id="btnFecharModalMaria">✕</button>
            </div>
            <form id="formMaria">
                <p style="color:var(--text-muted); font-size:0.9rem; margin:0 0 1rem;">
                    Informe os dados reais de <strong>Maria Eduarda</strong> para criar sua conta de administradora oficial na tabela <code>tb_usuario</code> com acesso imediato ao painel.
                </p>

                <div class="form-grid-2">
                    <div class="form-group-adm">
                        <label for="mariaNome">Nome Completo *</label>
                        <input type="text" id="mariaNome" value="Maria Eduarda" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="mariaEmail">E-mail Real *</label>
                        <input type="email" id="mariaEmail" placeholder="mariaeduarda@..." required>
                    </div>
                    <div class="form-group-adm">
                        <label for="mariaCpf">CPF Real * (apenas dígitos válidos)</label>
                        <input type="text" id="mariaCpf" maxlength="14" placeholder="000.000.000-00" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="mariaTelefone">Telefone / WhatsApp *</label>
                        <input type="text" id="mariaTelefone" placeholder="(00) 00000-0000" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="mariaDataNasc">Data de Nascimento *</label>
                        <input type="date" id="mariaDataNasc" required>
                    </div>
                    <div class="form-group-adm">
                        <label for="mariaSenha">Senha de Acesso Inicial *</label>
                        <input type="password" id="mariaSenha" minlength="6" placeholder="Mínimo 6 caracteres" required>
                    </div>
                </div>

                <div id="alertaModalMaria" class="alert-adm" style="margin-top:1rem;"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarMaria">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnSalvarMaria">Cadastrar Administradora</button>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const modalMaria = document.getElementById('modalMaria');
    const formMaria = document.getElementById('formMaria');
    const alertaConfig = document.getElementById('alertaConfig');
    const alertaModalMaria = document.getElementById('alertaModalMaria');

    function exibirAlerta(el, tipo, msg) {
        if (!el) return;
        el.className = `alert-adm ${tipo}`;
        el.textContent = msg;
        el.style.display = 'block';
        setTimeout(() => el.style.display = 'none', 6000);
    }

    async function verificarStatusMaria() {
        try {
            const res = await fetch('../index.php?rota=admin&acao=admins-oficiais');
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.status === 'sucesso') {
                const admins = json.dados || [];
                const maria = admins.find(a => a.nome_oficial.includes('Maria Eduarda'));
                if (maria && maria.id_usuario) {
                    document.getElementById('statusBadgeMaria').className = 'badge badge-success';
                    document.getElementById('statusBadgeMaria').textContent = '⭐ Ativa Oficial';
                    document.getElementById('infoMaria').innerHTML = `
                        <p style="margin:0 0 0.4rem;"><strong>ID no Banco:</strong> <code>id_usuario = ${maria.id_usuario}</code></p>
                        <p style="margin:0 0 0.4rem;"><strong>E-mail:</strong> <code>${maria.email || '—'}</code></p>
                        <p style="margin:0 0 0.4rem;"><strong>Permissão:</strong> <code>tipo_usuario = 'admin'</code></p>
                        <p style="margin:0; font-size:0.82rem; color:#059669;">Administradora cadastrada com sucesso e ativa.</p>
                    `;
                }
            }
        } catch (e) {}
    }

    document.getElementById('btnCadastrarMaria')?.addEventListener('click', () => {
        formMaria.reset();
        document.getElementById('mariaNome').value = 'Maria Eduarda';
        alertaModalMaria.style.display = 'none';
        modalMaria.classList.add('open');
    });

    document.getElementById('btnFecharModalMaria')?.addEventListener('click', () => modalMaria.classList.remove('open'));
    document.getElementById('btnCancelarMaria')?.addEventListener('click', () => modalMaria.classList.remove('open'));

    formMaria?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            nome: document.getElementById('mariaNome').value.trim(),
            email: document.getElementById('mariaEmail').value.trim(),
            cpf: document.getElementById('mariaCpf').value.replace(/\D/g, ''),
            telefone: document.getElementById('mariaTelefone').value.trim(),
            data_nascimento: document.getElementById('mariaDataNasc').value,
            senha: document.getElementById('mariaSenha').value.trim()
        };

        try {
            const res = await fetch('../index.php?rota=admin&acao=cadastrar-maria', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.status === 'sucesso') {
                modalMaria.classList.remove('open');
                exibirAlerta(alertaConfig, 'success', json.mensagem || 'Maria Eduarda cadastrada com sucesso!');
                await verificarStatusMaria();
            } else {
                exibirAlerta(alertaModalMaria, 'error', json.mensagem || 'Erro ao cadastrar Maria Eduarda.');
            }
        } catch (err) {
            exibirAlerta(alertaModalMaria, 'error', 'Erro ao conectar ao servidor.');
        }
    });

    verificarStatusMaria();
});
</script>
</body>
</html>
