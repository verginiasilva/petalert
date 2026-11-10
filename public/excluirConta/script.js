document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formExcluirContaDireto');
    const senhaInput = document.getElementById('senhaAtualExclusao');
    const textoInput = document.getElementById('textoConfirmacao');
    const checkTermo = document.getElementById('checkTermo');
    const btnConfirmar = document.getElementById('btnConfirmarExclusaoDireto');

    // Toggle senha
    document.querySelectorAll('.btn-toggle-pass').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            if (target) {
                const isPass = target.type === 'password';
                target.type = isPass ? 'text' : 'password';
                btn.textContent = isPass ? '🙈' : '👁️';
            }
        });
    });

    const obterUsuario = () => {
        try {
            const raw = localStorage.getItem('petalert_usuario') || sessionStorage.getItem('petalert_usuario');
            if (!raw || raw === 'null') return null;
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    };

    const usuario = obterUsuario();
    if (!usuario) {
        alert('Você precisa estar autenticado para acessar a área de exclusão de conta.');
        window.location.href = `../login/index.html?redirect=${encodeURIComponent(window.location.href)}`;
        return;
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const senha = senhaInput?.value.trim() || '';
            const confirmacao = textoInput?.value.trim().toUpperCase() || '';
            const aceito = checkTermo?.checked;

            if (!senha) {
                alert('Informe sua senha atual para confirmação de identidade.');
                senhaInput?.focus();
                return;
            }

            if (confirmacao !== 'EXCLUIR') {
                alert('Por favor, digite a palavra EXCLUIR para confirmar.');
                textoInput?.focus();
                return;
            }

            if (!aceito) {
                alert('Confirme o termo de ciência para prosseguir com a exclusão.');
                return;
            }

            btnConfirmar.disabled = true;
            btnConfirmar.textContent = 'Processando exclusão permanente...';

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
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Confirmar Exclusão';
                }
            } catch (err) {
                console.error(err);
                alert('Erro de comunicação com o servidor ao excluir conta.');
                btnConfirmar.disabled = false;
                btnConfirmar.textContent = 'Confirmar Exclusão';
            }
        });
    }
});
