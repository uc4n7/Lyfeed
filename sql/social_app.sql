


CREATE DATABASE IF NOT EXISTS social_app
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE social_app;

DROP TABLE IF EXISTS follows;
DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;


-- Accounts

CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(30)  NOT NULL,
    password      VARCHAR(255) NOT NULL, -- hashed with password_hash()
    full_name     VARCHAR(100) NOT NULL,
    bio           TEXT NULL,
    profile_image VARCHAR(255) NULL, -- filename in public/assets/uploads/
    cover_image   VARCHAR(255) NULL, -- cover filename in public/assets/uploads/
    cover_position INT NOT NULL DEFAULT 50, -- cover crop, 0 = top, 100 = bottom
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts 
CREATE TABLE posts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    content    TEXT NOT NULL,
    image      VARCHAR(255) NULL,
    visibility ENUM('public', 'private') NOT NULL DEFAULT 'public',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_posts_user (user_id),
    KEY idx_posts_created (created_at),
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Comments on posts

CREATE TABLE comments (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id    INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    parent_id  INT UNSIGNED NULL DEFAULT NULL,
    content    TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comments_post (post_id),
    KEY idx_comments_user (user_id),
    KEY idx_comments_parent (parent_id),
    CONSTRAINT fk_comments_post FOREIGN KEY (post_id)
        REFERENCES posts (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id)
        REFERENCES comments (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Likes. UNIQUE(post_id, user_id) allows one like per user per post.

CREATE TABLE likes (
    id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_likes_post_user (post_id, user_id),
    KEY idx_likes_user (user_id),
    CONSTRAINT fk_likes_post FOREIGN KEY (post_id)
        REFERENCES posts (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_likes_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Follows. follower_id follows followed_id
CREATE TABLE follows (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    follower_id INT UNSIGNED NOT NULL,
    followed_id INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_follows (follower_id, followed_id),
    KEY idx_follows_follower (follower_id),
    KEY idx_follows_followed (followed_id),
    CONSTRAINT fk_follows_follower FOREIGN KEY (follower_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_follows_followed FOREIGN KEY (followed_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


