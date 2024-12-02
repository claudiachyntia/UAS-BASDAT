document.getElementById('bookmarkButton').addEventListener('click', function() {
    var button = this;
    var tourismId = button.getAttribute('data-tourism-id');
    var action = button.textContent.trim() === 'Save' ? 'save' : 'unsave';

    fetch('bookmark_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=' + encodeURIComponent(action) + '&tourism_id=' + encodeURIComponent(tourismId)
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'saved') {
            button.innerHTML = '<i class="fa-solid fa-bookmark"></i> Unsave'; // Ganti teks dengan ikon dan kata 'Unsave'
        } else if (data === 'unsaved') {
            button.innerHTML = '<i class="fa-solid fa-bookmark"></i> Save'; // Ganti teks dengan ikon dan kata 'Save'
        } else {
            window.location.href = 'login.php';
        }
    })
    .catch(error => {
        window.location.href = 'login.php';
    });
});
