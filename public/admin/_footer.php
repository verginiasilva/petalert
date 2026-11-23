    </main>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('btnToggleSidebar');
        const sidebar = document.getElementById('adminSidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 900 && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            });
        }
    });
</script>
