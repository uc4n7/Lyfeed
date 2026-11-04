<?php
// Queries for the comments table with support for nested replies.
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
            'SELECT c.id, c.post_id, c.user_id, c.parent_id, c.content, c.created_at,
                    u.username, u.full_name, u.profile_image,
                    pu.username AS parent_username, pu.full_name AS parent_full_name
             FROM comments c
             JOIN users u ON u.id = c.user_id
             LEFT JOIN comments pc ON pc.id = c.parent_id
             LEFT JOIN users pu ON pu.id = pc.user_id
             WHERE c.id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Comments for these posts, structured into top-level comments and nested replies.
    public function forPosts(array $postIds): array
    {
        if (!$postIds) {
            return [];
        }
        $postIds      = array_map('intval', $postIds);
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $stmt = $this->db->prepare(
            "SELECT c.id, c.post_id, c.user_id, c.parent_id, c.content, c.created_at,
                    u.username, u.full_name, u.profile_image,
                    pu.username AS parent_username, pu.full_name AS parent_full_name
             FROM comments c
             JOIN users u ON u.id = c.user_id
             LEFT JOIN comments pc ON pc.id = c.parent_id
             LEFT JOIN users pu ON pu.id = pc.user_id
             WHERE c.post_id IN ($placeholders)
             ORDER BY c.created_at ASC, c.id ASC"
        );
        $stmt->execute($postIds);

        $byPost = [];
        $all = [];
        foreach ($stmt->fetchAll() as $row) {
            $row['replies'] = [];
            $all[$row['id']] = $row;
        }

        foreach ($all as $id => &$comment) {
            $pid = $comment['parent_id'];
            if ($pid && isset($all[$pid])) {
                $all[$pid]['replies'][] = &$comment;
            } else {
                $byPost[$comment['post_id']][] = &$comment;
            }
        }
        unset($comment);

        return $byPost;
    }

    public function create(int $postId, int $userId, string $content, ?int $parentId = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (post_id, user_id, parent_id, content) VALUES (:pid, :uid, :parent_id, :content)'
        );
        $stmt->execute([
            ':pid'       => $postId,
            ':uid'       => $userId,
            ':parent_id' => $parentId,
            ':content'   => $content,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Update only if $userId owns the comment.
    public function update(int $id, int $userId, string $content): bool
    {
        $stmt = $this->db->prepare('UPDATE comments SET content = :content WHERE id = :id AND user_id = :uid');
        return $stmt->execute([':content' => $content, ':id' => $id, ':uid' => $userId]);
    }

    // Delete only if $userId owns the comment.
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = :id AND user_id = :uid');
        return $stmt->execute([':id' => $id, ':uid' => $userId]);
    }
}
