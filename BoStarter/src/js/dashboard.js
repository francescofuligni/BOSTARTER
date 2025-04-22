function filterOpenProjects() {
    const onlyOpen = document.getElementById('filterOpenProjects').checked;
    const cards = document.querySelectorAll('#all-projects .project-card-container');

    cards.forEach(card => {
        const status = card.getAttribute('data-status');
        if (onlyOpen && status !== 'aperto') {
            card.style.display = 'none';
        } else {
            card.style.display = 'block';
        }
    });
}