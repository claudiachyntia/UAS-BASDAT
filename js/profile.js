const showPostsButton = document.getElementById('show-posts');
const showLikesButton = document.getElementById('show-likes');
const postsSection = document.getElementById('activities');
const likesSection = document.getElementById('reviews');

showPostsButton.addEventListener('click', (event) => {
    postsSection.style.display = 'block'; // Tampilkan Posts
    likesSection.style.display = 'none'; // Sembunyikan Likes
});

showLikesButton.addEventListener('click', (event) => {
    postsSection.style.display = 'none'; // Sembunyikan Posts
    likesSection.style.display = 'block'; // Tampilkan Likes
});

var modal = document.getElementById("uploadModal");
var closeBtn = document.getElementsByClassName("close")[0];
var editIcon = document.getElementById("editProfilePicture");
var profilePictureElement = document.getElementById("profilePicture");


editIcon.addEventListener("click", function(e) {
    e.preventDefault();
    modal.style.display = "block";
});

closeBtn.onclick = function() {
    modal.style.display = "none";
}
window.onclick = function(event) {
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

