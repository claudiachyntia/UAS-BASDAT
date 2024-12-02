<div class="posts-container">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="post-box" data-post-id="<?= $row['id']; ?>">
                    <?php if (isset($_SESSION['user_id']) && $row['user_id'] == $_SESSION['user_id']): ?>
                        <div class="post-options">
                            <i class="fas fa-ellipsis-v options-icon"></i>
                            <div class="options-dropdown">
                                <a href="delete_post.php?post_id=<?= $row['id']; ?>&source_page=<?= $source_page; ?>" onclick="return confirm('Are you sure you want to delete this post?');">
                                    <i class="fas fa-trash"></i>
                                    <span>Delete Post</span>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="post-header">
                        <a href="profile.php?user_id=<?= $row['user_id']; ?>">
                            <?php 
                            $profilePic = !empty($row['profile_picture']) ?$row['profile_picture'] : 'uploads/default_profile_picture.jpg';
                            ?>
                            <img src="<?= $profilePic; ?>" alt="User Profile Picture" height="40" width="40">
                        </a>
                        <div>
                            <div class="post-author-name"><?= $row['username']; ?></div>
                            <div class="post-time"><?= date('d M Y H:i', strtotime($row['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="post-content">
                        <p><?= nl2br($row['content']); ?></p>
                        <!-- Display image if it exists -->
                        <?php if ($row['image']): ?>
                            <img src="uploads/<?= $row['image']; ?>" alt="Post Image" style="max-width:100%; height:auto;">
                        <?php endif; ?>
                    </div>
                    <div class="post-actions">
                        <div class="like-button <?= isset($user_id) ? ($row['is_liked'] ? 'liked' : '') : 'disabled'; ?>"><?= $row['like_count'].' '; ?><i class="fas fa-thumbs-up"></i></div>
                        <div class="comment-button" class="like-count"><?= $row['comment_count']; ?> <i class="fas fa-comment"></i> </div>
                        <div class="share-button" class="like-count"><i class="fas fa-share"></i></div>
                    </div>
                    <div class="comment-section" style="display: <?= isset($_GET['open_comments']) && $_GET['open_comments'] === 'true' ? 'block' : 'none'; ?>;">
                        <div class="post-box">
                            <form action="php_tools/add_comment.php?source_page=<?= urlencode($source_page); ?>" method="POST">
                                <div class="post-header">
                                    <img alt="Profile Picture" src="<?= $profilePicture; ?>" height="40" width="40">
                                    <input type="hidden" name="post_id" value="<?= $row['id']; ?>">
                                    <input type="text" name="comment_text" class="comment-input" placeholder="Write comment..." required>
                                    <div class="post-actions" style="margin-top: 20px;">
                                        <button type="submit" class="comment-input" style="background:none; border:none; color:inherit;">
                                            <div><i class="fas fa-pencil-alt"></i> Post</div>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="comment-list" data-post-id="<?= $row['id']; ?>">
                            <?php
                            $comments = getComments($row['id'], $con);
                            if (count($comments) > 0):
                                foreach ($comments as $comment): ?>
                                    <div class="comment-item">
                                        <div class="post-header">
                                            <a href="profile.php?user_id=<?= $row['user_id']; ?>">
                                                <img src="<?= $comment['profile_picture']; ?>" alt="<?= $comment['username']; ?>'s Profile Picture" height="40" width="40">
                                            </a>
                                            <div>
                                                <div class="comment-username"><?= $comment['username']; ?>
                                                    <div class="comment-time"><?= date('d M Y H:i', strtotime($comment['created_at'])); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="comment-text"><?= $comment['comment_text']; ?></p>
                                    </div>
                                <?php endforeach; 
                            else: ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="share-popup" style="display: none;">
                        <div class="share-options">
                        <?php
                        $post_id = $row['id']; 
                        $post_url = "https://wikiitrip.com/community.php?id=" . $post_id;
                        ?>
                        <div class="share-options">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($post_url); ?>" target="_blank">
                                <i class="fab fa-facebook"> Facebook</i>
                            </a>
                            <a href="https://wa.me/?text=<?= urlencode($post_url); ?>" target="_blank">
                                <i class="fab fa-whatsapp"> WhatsApp</i>
                            </a>
                            <a href="https://www.instagram.com/?url=<?= urlencode($post_url); ?>" target="_blank">
                                <i class="fab fa-instagram"> Instagram</i>
                            </a>
                        </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No search results for : '<?= $keyword; ?>'</p>
        <?php endif; ?>
    </div>
</div>