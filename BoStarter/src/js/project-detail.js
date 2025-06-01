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

    document.getElementById('add-skill').addEventListener('click', function() {
        const container = document.getElementById('skills-container');
        const skillRow = document.querySelector('.skill-row').cloneNode(true);
        container.appendChild(skillRow);
    });
});

function showRewardImage() {
    var select = document.getElementById('codice_reward');
    var idx = select.selectedIndex;
    var option = select.options[idx];
    var img = option.getAttribute('data-img');
    var desc = option.getAttribute('data-desc');
    if (img && select.value) {
        const rewardImg = document.getElementById('reward-img');
        rewardImg.src = img;
        rewardImg.setAttribute('data-img', img);
        document.getElementById('reward-desc').innerText = desc;
        document.getElementById('reward-image-preview').style.display = 'block';
    } else {
        document.getElementById('reward-image-preview').style.display = 'none';
    }
}