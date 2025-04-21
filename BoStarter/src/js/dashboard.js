function filterOpenProjects() {
    const checkbox = document.getElementById('filterOpenProjects');
    const cards = document.querySelectorAll('.project-card');
    cards.forEach(card => {
        const badge = card.querySelector('.badge');
        const isOpen = badge && badge.textContent.trim().toLowerCase() === 'aperto';
        card.closest('.col-md-4').style.display = (checkbox.checked && !isOpen) ? 'none' : '';
    });
}
