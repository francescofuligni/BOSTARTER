document.addEventListener('DOMContentLoaded', () => {
    const tipoSelect = document.getElementById('tipo');
    const codiceContainer = document.getElementById('codice-container');
    const codiceInput = document.getElementById('codice_sicurezza');

    tipoSelect.addEventListener('change', function () {
        if (this.value === 'AMMINISTRATORE') {
            codiceContainer.classList.remove('d-none');
        } else {
            codiceContainer.classList.add('d-none');
            codiceInput.value = '';
        }
    });
});

async function generaCodice() {
    const data = new TextEncoder().encode(Date.now().toString() + Math.random());
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    document.getElementById('codice_sicurezza').value = hashHex.substring(0, 8).toUpperCase();
}