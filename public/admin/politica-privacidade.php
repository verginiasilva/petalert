<?php
require_once __DIR__ . '/_guard.php';
$adminUsuario = petalert_exigir_admin_pagina();
$paginaAtual = 'politica-privacidade';
$tituloPagina = 'Política de Privacidade & Conformidade LGPD';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade — PetAlert Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tabs-lgpd {
            display: flex;
            gap: 10px;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
        }
        .tab-btn-lgpd {
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-muted);
            padding: 0.5rem 1rem;
            cursor: pointer;
            border-radius: var(--radius-sm);
            transition: all 0.2s;
        }
        .tab-btn-lgpd.active {
            background: var(--pastel-pink);
            color: #832738;
            box-shadow: 0 2px 6px rgba(255, 141, 161, 0.25);
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>

    <div class="content-card">
        <div class="content-header">
            <div>
                <h2>🔒 Política de Privacidade & Governança LGPD</h2>
                <p>Diretrizes de proteção de dados (Lei nº 13.709/2018) e auditoria de aceites registrados na base de dados.</p>
            </div>
            <a href="../politica-privacidade/index.html" target="_blank" class="btn-secondary" style="font-size:0.85rem;">
                🌐 Visualizar Página Pública
            </a>
        </div>

        <div class="tabs-lgpd">
            <button type="button" class="tab-btn-lgpd active" id="tabAceitesLgpd">📋 1. Registros de Aceite no Banco (<span id="contAceites">0</span>)</button>
            <button type="button" class="tab-btn-lgpd" id="tabTextoLgpd">📜 2. Texto Oficial da Política (10/11/2026)</button>
        </div>

        <!-- ABA 1: REGISTROS DE ACEITE (tb_privacidade_aceite) -->
        <div id="painelAceites">
            <div style="background:#FFFDF7; border:1px solid #FDE68A; border-radius:var(--radius-md); padding:1rem 1.4rem; margin-bottom:1.5rem; font-size:0.92rem; color:#92400E;">
                <strong>Auditoria Legal:</strong> Cada usuário cadastrado possui o registro de consentimento e ciência da versão vigente da Política de Privacidade com data, hora, IP de origem e protocolo de integridade.
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Aceite</th>
                            <th>Data & Horário</th>
                            <th>Usuário</th>
                            <th>E-mail</th>
                            <th>CPF</th>
                            <th>Perfil</th>
                            <th>Versão da Política</th>
                            <th>IP Origem</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaAceites">
                        <tr><td colspan="8" style="text-align:center; padding:2rem;">Carregando registros de aceite...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ABA 2: TEXTO OFICIAL DA POLÍTICA -->
        <div id="painelTexto" style="display:none; line-height:1.7; color:#374151; font-size:0.95rem; max-width:900px;">
            <div style="background:#FFF0F3; border-left:4px solid #FF8DA1; padding:1.2rem; border-radius:0 12px 12px 0; margin-bottom:1.5rem;">
                <h3 style="margin:0 0 0.4rem; color:#832738;">POLÍTICA DE PRIVACIDADE – PETALERT</h3>
                <strong>Última atualização: 10 de novembro de 2026</strong> | Em conformidade com a LGPD (Lei nº 13.709/2018).
            </div>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.1. Apresentação</h4>
            <p>A presente Política de Privacidade explica como o PetAlert coleta, utiliza, armazena e protege os dados pessoais dos usuários durante a utilização da plataforma. O tratamento das informações deverá respeitar a Lei Geral de Proteção de Dados Pessoais (LGPD – Lei nº 13.709/2018).</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.2. Quais informações coletamos?</h4>
            <p>Durante o cadastro e a utilização do PetAlert, poderão ser coletadas: Dados de identificação (Nome completo, CPF, Data de nascimento, Foto de perfil voluntária); Dados de contato (E-mail, Telefone/WhatsApp); Dados de localização (CEP, Rua, Número, Bairro, Cidade, Estado); Dados de acesso (Senha criptografada bcrypt); Informações relacionadas aos animais anunciados; e conversas do chat e denúncias.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.3. Para que utilizamos os dados?</h4>
            <p>Criar e identificar contas; verificar requisitos de idade mínima (16 anos); autenticação e recuperação de senha; evitar fraudes e contas duplicadas; funcionamento regular da plataforma; apresentar animais por geolocalização; comunicação entre doador e adotante; suporte e administração.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.4. Quem pode visualizar os dados pessoais?</h4>
            <p>Os dados pessoais como CPF, endereço completo, telefone e data de nascimento são de acesso restrito administrativo e nunca exibidos publicamente a outros usuários comuns. Os administradores acessam apenas o necessário para exercer suas funções.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.5. Armazenamento das informações</h4>
            <p>Os dados são armazenados no banco de dados MySQL <code>petalert</code> com senhas em formato de hash bcrypt seguro. São adotadas medidas técnicas e organizacionais de proteção contra vazamentos e acessos indevidos.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.6. Compartilhamento de informações</h4>
            <p>Os dados pessoais jamais são comercializados. Compartilhamento ocorre somente para funcionamento indispensável do serviço ou mediante requisição legal legítima de autoridades.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.7. Utilização de CPF e data de nascimento</h4>
            <p>O CPF é utilizado para validação inequívoca e prevenção de duplicidades; a data de nascimento assegura a idade mínima de 16 anos. Informações de uso confidencial e protegido.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.8. Dados de localização</h4>
            <p>O endereço residencial completo (rua e número) não é exposto publicamente para terceiros. Apenas cidade/estado são utilizados para geolocalização de pets e clínicas.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.9. Inteligência Artificial – Pet.IA</h4>
            <p>No envio de dúvidas de cuidados animais para ferramentas de IA, é aplicada a minimização rigorosa de dados: jamais são enviados CPF, senhas, endereços ou dados cadastrais pessoais dos usuários.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.10. Conversas e denúncias</h4>
            <p>Históricos de chat e denúncias são registrados para fins de segurança, moderação e resolução de conflitos, com acesso reservado à administração autorizada.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.11. Direitos dos titulares (Art. 18 LGPD)</h4>
            <p>Confirmação de tratamento, acesso, correção, anonimização, portabilidade, revogação do consentimento e eliminação de dados pessoais conforme os parâmetros legais.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.12. Retenção e exclusão dos dados</h4>
            <p>Conservação estritamente necessária para finalidades de atendimento e obrigações legais (Marco Civil da Internet). Eliminação segura após término da finalidade ou retenção legal.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.13. Proteção de menores de idade</h4>
            <p>Idade mínima obrigatória de 16 anos para cadastro, atendendo ao ECA e à LGPD.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.14. Alterações nesta política</h4>
            <p>Revisões comunicadas formalmente na plataforma aos usuários.</p>

            <h4 style="color:var(--primary-dark); font-size:1.15rem; margin-top:1.5rem;">12.15. Contato e solicitações</h4>
            <p>Canal oficial de atendimento do PetAlert para requisições de titulares de dados.</p>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const tabAceites = document.getElementById('tabAceitesLgpd');
    const tabTexto = document.getElementById('tabTextoLgpd');
    const pnlAceites = document.getElementById('painelAceites');
    const pnlTexto = document.getElementById('painelTexto');
    const tabela = document.getElementById('tabelaAceites');
    const contAceites = document.getElementById('contAceites');

    function sanitizar(txt) {
        const d = document.createElement('div');
        d.textContent = txt ?? '';
        return d.innerHTML;
    }

    tabAceites.addEventListener('click', () => {
        tabAceites.classList.add('active');
        tabTexto.classList.remove('active');
        pnlAceites.style.display = 'block';
        pnlTexto.style.display = 'none';
    });

    tabTexto.addEventListener('click', () => {
        tabTexto.classList.add('active');
        tabAceites.classList.remove('active');
        pnlAceites.style.display = 'none';
        pnlTexto.style.display = 'block';
    });

    async function carregarAceites() {
        try {
            const res = await fetch('../index.php?rota=admin&acao=politica-privacidade');
            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.status !== 'sucesso') {
                tabela.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#DC2626;">Erro ao carregar registros de aceite.</td></tr>';
                return;
            }

            const dados = Array.isArray(json.dados) ? json.dados : [];
            contAceites.textContent = dados.length;

            if (!dados.length) {
                tabela.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">Nenhum aceite registrado até o momento.</td></tr>';
                return;
            }

            tabela.innerHTML = dados.map(a => {
                const cpfFmt = String(a.cpf || '').replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                return `
                    <tr>
                        <td><strong>#${a.id_aceite}</strong></td>
                        <td style="font-size:0.85rem; white-space:nowrap;">${sanitizar(a.data_hora_aceite || '—')}</td>
                        <td>
                            <strong>${sanitizar(a.nome)}</strong><br>
                            <small style="color:var(--text-muted);">ID #${a.id_usuario}</small>
                        </td>
                        <td>${sanitizar(a.email)}</td>
                        <td>${sanitizar(cpfFmt || '—')}</td>
                        <td><span class="badge badge-info">${sanitizar(a.tipo_usuario || 'adotante')}</span></td>
                        <td><span class="badge badge-success">v${sanitizar(a.versao_politica || '2026-11-10')}</span></td>
                        <td style="font-family:monospace; font-size:0.8rem; color:var(--text-muted);">${sanitizar(a.ip_origem || '127.0.0.1')}</td>
                    </tr>
                `;
            }).join('');
        } catch (e) {
            tabela.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#DC2626;">Erro de conexão com o servidor.</td></tr>';
        }
    }

    carregarAceites();
});
</script>
</body>
</html>
