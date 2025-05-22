document.addEventListener('DOMContentLoaded', function() {
    var zoomImgs = document.querySelectorAll('.img-thumbnail[data-toggle="modal"]');
    var modalImg = document.getElementById('imgZoomModalImg');
    zoomImgs.forEach(function(img) {
        img.addEventListener('click', function() {
            modalImg.src = this.getAttribute('data-img');
        });
    });
    $('#imgZoomModal').on('hidden.bs.modal', function () {
        modalImg.src = '';
    });
});

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