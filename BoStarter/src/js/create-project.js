function addReward() {
    const section = document.getElementById('rewards-section');
    const group = section.querySelector('.reward-group').cloneNode(true);
    group.querySelectorAll('input').forEach(input => input.value = '');
    section.appendChild(group);
}
