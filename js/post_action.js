document.addEventListener('DOMContentLoaded', () => {
    // Menangani tombol Like
    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('disabled')) {
                window.location.href = 'login.php';
                return;
            }
            const post = button.closest('.post-box');
            const postId = post.getAttribute('data-post-id');
            const isLiked = button.classList.toggle('liked');
            const likeCountElem = document.getElementById(`like-count-${postId}`);
            fetch('like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ postId, like: isLiked })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data.message);
                button.classList.toggle('liked');
                likeCountElem.textContent = `${data.like_count} Likes`;
            })
            .catch(error => console.error('Error:', error));
        });
    });
    

    // Menangani tombol Comment
    document.querySelectorAll('.comment-button').forEach(button => {
        button.addEventListener('click', () => {
            const commentSection = button.closest('.post-box').querySelector('.comment-section');
            commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
        });
    });
    
    // Menangani tombol Share
    document.querySelectorAll('.share-button').forEach(button => {
        button.addEventListener('click', () => {
            const sharePopup = button.closest('.post-box').querySelector('.share-popup');
            sharePopup.style.display = sharePopup.style.display === 'none' ? 'block' : 'none';
        });
    });
    
    document.querySelectorAll('.comment-section form').forEach(form => {
        
            fetch('php_tools/add_comment.php', {
                method: 'POST',
                body: formData
            })
            .catch(error => console.error('Error:', error)); 
    });
    
    document.querySelectorAll('.comment-section').forEach(section => {
        const postId = section.querySelector('input[name="post_id"]').value;
        loadComments(postId);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.options-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const dropdown = this.nextElementSibling;
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
    });
});

document.querySelectorAll('.options-dropdown .fa-trash').forEach(icon => {
    icon.addEventListener('click', function() {
        const postBox = this.closest('.post-box');
        const postId = postBox.getAttribute('data-post-id');
        const confirmDelete = confirm('Are you sure you want to delete this post?');
        if (confirmDelete) {
            fetch(`delete_post.php?post_id=${postId}`)
                .then(response => response.text())
                .then(result => {
                    if (result === 'success') {
                        postBox.remove();
                    } else {
                        alert('Error deleting post.');
                    }
                });
        }
    });
});

