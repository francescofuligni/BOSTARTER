document.addEventListener('DOMContentLoaded', () => {
    // Gestione del tipo di utente
    const typeSelect = document.getElementById('type');
    const secCodeContainer = document.getElementById('security_code_container');
    const secCode = document.getElementById('security_code');

    typeSelect.addEventListener('change', function () {
        if (this.value === 'AMMINISTRATORE') {
            secCodeContainer.classList.remove('d-none');
        } else {
            secCodeContainer.classList.add('d-none');
            secCode.value = '';
        }
    });
});

async function generateCode() {
    const data = new TextEncoder().encode(Date.now().toString() + Math.random());
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    document.getElementById('security_code').value = hashHex.substring(0, 8).toUpperCase();
}