<?php
/**
 * PostModel - data access for the `posts` table. Database logic only.
 */
class PostModel
{
    private PDO $db;

    /** Shared SELECT: post + author info + like count + comment count + whether viewer liked it. */
    private const BASE_SELECT =
        'SELECT p.id, p.user_id, p.content, p.image, p.created_at,
                u.username, u.full_name, u.profile_image,
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id)    AS like_count,
                (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                (SELECT COUNT(*) FROM likes l2 WHERE l2.post_id = p.id AND l2.user_id = :viewer) AS liked_by_me
         FROM posts p
         JOIN users u ON u.id = p.user_id';

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id, int $viewerId = 0): ?array
    {
        $stmt = $this->db->prepare(self::BASE_SELECT . ' WHERE p.id = :id');
        $stmt->execute([':viewer' => $viewerId, ':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Newsfeed: all posts, latest first, paginated. */
    public function feed(int $viewerId, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' ORDER BY p.created_at DESC, p.id DESC LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':viewer', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    }

    public function byUser(int $userId, int $viewerId): array
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE p.user_id = :uid ORDER BY p.created_at DESC, p.id DESC'
        );
        $stmt->execute([':viewer' => $viewerId, ':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM posts WHERE user_id = :uid');
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /** Search posts by keyword in content or author name / username. */
    public function search(string $term, int $viewerId, int $limit = 20): array
    {
        $like = '%' . addcslashes($term, '%_\\') . '%';
        $cleanUser = ltrim($term, '@');
        $likeUser = '%' . addcslashes($cleanUser, '%_\\') . '%';

        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE (p.content LIKE :q OR u.full_name LIKE :qName OR u.username LIKE :qUser) ORDER BY p.created_at DESC LIMIT :lim'
        );
        $stmt->bindValue(':viewer', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':q', $like);
        $stmt->bindValue(':qName', $like);
        $stmt->bindValue(':qUser', $likeUser);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $content, ?string $image): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO posts (user_id, content, image) VALUES (:uid, :content, :image)'
        );
        $stmt->execute([':uid' => $userId, ':content' => $content, ':image' => $image]);
        return (int)$this->db->lastInsertId();
    }

    /** Defense-in-depth: only the owning user can update their own post. */
    public function update(int $id, int $userId, string $content, ?string $image): bool
    {
        $stmt = $this->db->prepare('UPDATE posts SET content = :content, image = :image WHERE id = :id AND user_id = :uid');
        return $stmt->execute([':content' => $content, ':image' => $image, ':id' => $id, ':uid' => $userId]);
    }

    /** Defense-in-depth: only the owning user can delete their own post. */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = :id AND user_id = :uid');
        return $stmt->execute([':id' => $id, ':uid' => $userId]);
    }
}
