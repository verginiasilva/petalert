document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formDenuncia');
    const btnSubmit = document.getElementById('btnEnviarDenuncia');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const motivo = document.getElementById('motivo')?.value.trim() || '';
        const localizacao = document.getElementById('localizacao')?.value.trim() || '';
        const descricao = document.getElementById('descricao')?.value.trim() || '';
        const fotoInput = document.getElementById('foto');
        const videoInput = document.getElementById('video');

        if (!motivo) {
            alert('Por favor, informe o motivo da denúncia.');
            return;
        }

        if (!localizacao) {
            alert('Por favor, informe o local / endereço da ocorrência.');
            return;
        }

        if (!descricao) {
            alert('Por favor, descreva detalhadamente a situação.');
            return;
        }

        const temFoto = fotoInput && fotoInput.files && fotoInput.files.length > 0;
        const temVideo = videoInput && videoInput.files && videoInput.files.length > 0;

        // REGRA OBRIGATÓRIA: Foto OU Vídeo (ou ambos)
        if (!temFoto && !temVideo) {
            alert('Atenção: É obrigatório anexar pelo menos uma mídia comprobatória (Foto ou Vídeo) para registrar a denúncia.');
            return;
        }

        const formData = new FormData();
        formData.append('motivo', motivo);
        formData.append('tipo', motivo);
        formData.append('localizacao', localizacao);
        formData.append('descricao', descricao);

        if (temFoto) {
            formData.append('foto', fotoInput.files[0]);
        }

        if (temVideo) {
            formData.append('video', videoInput.files[0]);
        }

        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Enviando Denúncia...';
        }

        try {
            const endpoint = '../index.php?rota=denuncias';
            const res = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            const json = await res.json().catch(() => ({}));

            if (res.ok && (json.status === 'sucesso' || json.sucesso || json.id)) {
                alert(json.mensagem || 'Denúncia enviada com sucesso! As informações e mídias foram salvas para apuração.');
                form.reset();
            } else {
                alert(json.mensagem || json.erro || 'Erro ao registrar denúncia. Verifique os campos e mídias.');
            }
        } catch (err) {
            console.error('Erro:', err);
            alert('Erro de conexão com o servidor. Verifique se o XAMPP está ativo.');
        } finally {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Registrar Denúncia';
            }
        }
    });
});
