document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('tipo').addEventListener('change', function () {
        const codiceContainer = document.getElementById('codice-container');
        if (this.value === 'AMMINISTRATORE') {
            codiceContainer.classList.remove('d-none');
        } else {
            codiceContainer.classList.add('d-none');
            document.getElementById('codice_sicurezza').value = '';
        }
    });
    
    async function generaCodice() {
        const array = new Uint8Array(8);
        crypto.getRandomValues(array);
        const codice = Array.from(array, byte => (byte % 36).toString(36)).join('');
        document.getElementById('codice_sicurezza').value = codice.toUpperCase();
    }
});