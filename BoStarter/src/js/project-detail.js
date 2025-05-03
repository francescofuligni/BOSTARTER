function showRewardImage() {
    var select = document.getElementById('codice_reward');
    var idx = select.selectedIndex;
    var option = select.options[idx];
    var img = option.getAttribute('data-img');
    var desc = option.getAttribute('data-desc');
    console.log({idx, img, desc, value: select.value});
    if (img && select.value) {
        document.getElementById('reward-img').src = img;
        document.getElementById('reward-desc').innerText = desc;
        document.getElementById('reward-image-preview').style.display = 'block';
    } else {
        document.getElementById('reward-image-preview').style.display = 'none';
    }
}
