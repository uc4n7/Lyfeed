<?php
/**
 * LikeModel - data access for the `likes` table. One like per user per post.
 */
class LikeModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function hasLiked(int $postId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM likes WHERE post_id = :pid AND user_id = :uid LIMIT 1');
        $stmt->execute([':pid' => $postId, ':uid' => $userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function count(int $postId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE post_id = :pid');
        $stmt->execute([':pid' => $postId]);
        return (int)$stmt->fetchColumn();
    }

    /** INSERT IGNORE + UNIQUE(post_id, user_id) guarantees a single like per user per post. */
    public function add(int $postId, int $userId): bool
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO likes (post_id, user_id) VALUES (:pid, :uid)');
        return $stmt->execute([':pid' => $postId, ':uid' => $userId]);
    }

    public function remove(int $postId, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM likes WHERE post_id = :pid AND user_id = :uid');
        return $stmt->execute([':pid' => $postId, ':uid' => $userId]);
    }
}
