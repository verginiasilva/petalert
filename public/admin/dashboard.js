document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('../index.php?rota=admin&acao=dashboard');
        const json = await res.json().catch(() => ({}));
        if (!res.ok || json.status !== 'sucesso') {
            console.error('Falha ao carregar métricas:', json);
            return;
        }

        const d = json.dados || {};

        // Atualizar cards com dados reais
        const el = (id, val) => {
            const dom = document.getElementById(id);
            if (dom) dom.textContent = val;
        };

        el('statUsuarios', d.total_usuarios ?? 0);
        el('statDescUsuarios', `${d.total_admins ?? 0} administrador(es) ativo(s)`);
        el('statAdotantes', d.total_adotantes ?? 0);
        el('statDoadores', d.total_doadores ?? 0);
        el('statPets', d.total_pets ?? 0);
        el('statDisponiveis', d.pets_disponiveis ?? 0);
        el('statAdotados', d.pets_adotados ?? 0);
        el('statDenuncias', d.denuncias_pendentes ?? 0);
        el('statDescDenuncias', `${d.total_denuncias ?? 0} denúncia(s) total`);
        
        const valorFormatado = Number(d.soma_doacoes_financeiras || 0).toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
        el('statDoacoesValor', valorFormatado);
        el('statDescDoacoes', `${d.total_doacoes_financeiras ?? 0} doação(ões) financeiras`);

        el('statClinicas', d.total_clinicas ?? 0);
        el('statAceites', d.total_aceites_privacidade ?? 0);

        // Gráficos com Chart.js
        const series = Array.isArray(d.series_graficos) ? d.series_graficos : [];
        const labels = series.map(s => s.rotulo);
        const dataCadastros = series.map(s => s.cadastros);
        const dataAdocoes = series.map(s => s.adocoes);
        const dataDoacoes = series.map(s => s.doacoes);

        if (typeof Chart !== 'undefined') {
            // Gráfico 1: Cadastros e Adoções
            const ctx1 = document.getElementById('chartCadastros');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: labels.length ? labels : ['Mês 1', 'Mês 2', 'Mês 3'],
                        datasets: [
                            {
                                label: 'Novos Cadastros',
                                data: dataCadastros.length ? dataCadastros : [0, 0, 0],
                                borderColor: '#E05370',
                                backgroundColor: 'rgba(224, 83, 112, 0.1)',
                                tension: 0.3,
                                fill: true,
                                borderWidth: 2.5
                            },
                            {
                                label: 'Adoções Concluídas',
                                data: dataAdocoes.length ? dataAdocoes : [0, 0, 0],
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.3,
                                fill: true,
                                borderWidth: 2.5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            }

            // Gráfico 2: Doações Financeiras
            const ctx2 = document.getElementById('chartDoacoes');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: labels.length ? labels : ['Mês 1', 'Mês 2', 'Mês 3'],
                        datasets: [{
                            label: 'Total Arrecadado (R$)',
                            data: dataDoacoes.length ? dataDoacoes : [0, 0, 0],
                            backgroundColor: '#F59E0B',
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        }
    } catch (e) {
        console.error('Erro ao renderizar dashboard:', e);
    }
});
