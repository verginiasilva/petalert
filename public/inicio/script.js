document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const feedback = params.get('status');

    if (feedback === 'logout') {
        alert('Que pena que você está saindo! Sentiremos sua falta e os pets também. 🐾');
    }

    if (feedback === 'deletada' || feedback === 'conta_excluida') {
        alert('Sua conta foi removida com sucesso. Esperamos vê-lo em breve. 🐾');
    }
});
