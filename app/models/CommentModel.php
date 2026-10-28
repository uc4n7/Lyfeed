<?php
/**
 * CommentModel - data access for the `comments` table. Database logic only.
 */
class CommentModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username, u.full_name, u.profile_image
             FROM comments c JOIN users u ON u.id = c.user_id
             WHERE c.id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Comments for a set of posts, oldest first. Returned grouped by post_id. */
    public function forPosts(array $postIds): array
    {
        if (!$postIds) {
            return [];
        }
        $postIds      = array_map('intval', $postIds);
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $stmt = $this->db->prepare(
            "SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username, u.full_name, u.profile_image
             FROM comments c JOIN users u ON u.id = c.user_id
             WHERE c.post_id IN ($placeholders)
             ORDER BY c.created_at ASC, c.id ASC"
        );
        $stmt->execute($postIds);

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[$row['post_id']][] = $row;
        }
        return $grouped;
    }

    public function create(int $postId, int $userId, string $content): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (post_id, user_id, content) VALUES (:pid, :uid, :content)'
        );
        $stmt->execute([':pid' => $postId, ':uid' => $userId, ':content' => $content]);
        return (int)$this->db->lastInsertId();
    }

    /** Defense-in-depth: only the owning user can update their own comment. */
    public function update(int $id, int $userId, string $content): bool
    {
        $stmt = $this->db->prepare('UPDATE comments SET content = :content WHERE id = :id AND user_id = :uid');
        return $stmt->execute([':content' => $content, ':id' => $id, ':uid' => $userId]);
    }

    /** Defense-in-depth: only the owning user can delete their own comment. */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = :id AND user_id = :uid');
        return $stmt->execute([':id' => $id, ':uid' => $userId]);
    }
}
