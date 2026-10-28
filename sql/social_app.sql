-- ============================================================
-- LyfeFeed Social Networking Web Application
-- Database: social_app   (MySQL 5.7+ / MariaDB 10.3+)
-- Sample users all use the password:  password123
-- ============================================================

CREATE DATABASE IF NOT EXISTS social_app
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE social_app;

DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;

-- ------------------------------------------------------------
-- users
-- ------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(30)  NOT NULL,
    password      VARCHAR(255) NOT NULL,           -- password_hash() output
    full_name     VARCHAR(100) NOT NULL,
    bio           TEXT NULL,
    profile_image VARCHAR(255) NULL,               -- file name inside public/assets/uploads/
    cover_image   VARCHAR(255) NULL,               -- banner background inside public/assets/uploads/
    cover_position INT NOT NULL DEFAULT 50,        -- vertical position percentage (0-100)
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--post 
CREATE TABLE posts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    content    TEXT NOT NULL,
    image      VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_posts_user (user_id),
    KEY idx_posts_created (created_at),
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- comments
-- ------------------------------------------------------------
CREATE TABLE comments (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id    INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    content    TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comments_post (post_id),
    KEY idx_comments_user (user_id),
    CONSTRAINT fk_comments_post FOREIGN KEY (post_id)
        REFERENCES posts (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- likes  (one like per user per post enforced by UNIQUE key)
-- ------------------------------------------------------------
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

