<?php
// Queries for the posts table.
class PostModel
{
    private PDO $db;

    // Post plus author, like count, comment count, and whether the viewer liked it.
    private const BASE_SELECT =
        'SELECT p.id, p.user_id, p.content, p.image, p.visibility, p.created_at,
                u.username, u.full_name, u.profile_image,
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id)    AS like_count,
                (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                (SELECT COUNT(*) FROM likes l2 WHERE l2.post_id = p.id AND l2.user_id = :viewerLike) AS liked_by_me
         FROM posts p
         JOIN users u ON u.id = p.user_id';

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id, int $viewerId = 0): ?array
    {
        if ($viewerId > 0) {
            $stmt = $this->db->prepare(
                self::BASE_SELECT . ' WHERE p.id = :id
                AND (p.visibility = \'public\' OR p.user_id = :viewerUser OR EXISTS (SELECT 1 FROM follows f WHERE f.follower_id = :viewerFollow AND f.followed_id = p.user_id))'
            );
            $stmt->execute([
                ':id'           => $id,
                ':viewerLike'   => $viewerId,
                ':viewerUser'   => $viewerId,
                ':viewerFollow' => $viewerId,
            ]);
            return $stmt->fetch() ?: null;
        }

        $stmt = $this->db->prepare(self::BASE_SELECT . ' WHERE p.id = :id AND p.visibility = \'public\'');
        $stmt->execute([':id' => $id, ':viewerLike' => 0]);
        return $stmt->fetch() ?: null;
    }

    // Posts visible to viewer: public OR author is viewer OR viewer follows author.
    public function feed(int $viewerId, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE (p.visibility = \'public\' OR p.user_id = :viewerUser OR EXISTS (SELECT 1 FROM follows f WHERE f.follower_id = :viewerFollow AND f.followed_id = p.user_id))
            ORDER BY p.created_at DESC, p.id DESC LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':viewerLike', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':viewerUser', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':viewerFollow', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(int $viewerId = 0): int
    {
        if ($viewerId > 0) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM posts p
                 WHERE (p.visibility = \'public\' OR p.user_id = :viewerUser OR EXISTS (SELECT 1 FROM follows f WHERE f.follower_id = :viewerFollow AND f.followed_id = p.user_id))'
            );
            $stmt->execute([
                ':viewerUser'   => $viewerId,
                ':viewerFollow' => $viewerId,
            ]);
            return (int)$stmt->fetchColumn();
        }
        return (int)$this->db->query("SELECT COUNT(*) FROM posts WHERE visibility = 'public'")->fetchColumn();
    }

    public function byUser(int $userId, int $viewerId): array
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE p.user_id = :uid
            AND (p.visibility = \'public\' OR p.user_id = :viewerUser OR EXISTS (SELECT 1 FROM follows f WHERE f.follower_id = :viewerFollow AND f.followed_id = p.user_id))
            ORDER BY p.created_at DESC, p.id DESC'
        );
        $stmt->execute([
            ':uid'          => $userId,
            ':viewerLike'   => $viewerId,
            ':viewerUser'   => $viewerId,
            ':viewerFollow' => $viewerId,
        ]);
        return $stmt->fetchAll();
    }

    public function countByUser(int $userId, int $viewerId = 0): int
    {
        if ($viewerId > 0) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM posts p WHERE p.user_id = :uid
                 AND (p.visibility = \'public\' OR p.user_id = :viewerUser OR EXISTS (SELECT 1 FROM follows f WHERE f.follower_id = :viewerFollow AND f.followed_id = p.user_id))'
            );
            $stmt->execute([
                ':uid'          => $userId,
                ':viewerUser'   => $viewerId,
                ':viewerFollow' => $viewerId,
            ]);
            return (int)$stmt->fetchColumn();
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM posts WHERE user_id = :uid AND visibility = 'public'");
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    // Match post text, author name, or username.
    public function search(string $term, int $viewerId, int $limit = 20): array
    {
        $like = '%' . addcslashes($term, '%_\\') . '%';
        $cleanUser = ltrim($term, '@');
        $likeUser = '%' . addcslashes($cleanUser, '%_\\') . '%';

        $stmt = $this->db->prepare(
            self::BASE_SELECT . ' WHERE (p.content LIKE :q OR u.full_name LIKE :qName OR u.username LIKE :qUser)
            AND (p.visibility = \'public\' OR p.user_id = :viewerUser OR EXISTS (SELECT 1 FROM follows f WHERE f.follower_id = :viewerFollow AND f.followed_id = p.user_id))
            ORDER BY p.created_at DESC LIMIT :lim'
        );
        $stmt->bindValue(':viewerLike', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':viewerUser', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':viewerFollow', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':q', $like);
        $stmt->bindValue(':qName', $like);
        $stmt->bindValue(':qUser', $likeUser);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $content, ?string $image, string $visibility = 'public'): int
    {
        $visibility = $visibility === 'private' ? 'private' : 'public';
        $stmt = $this->db->prepare(
            'INSERT INTO posts (user_id, content, image, visibility) VALUES (:uid, :content, :image, :vis)'
        );
        $stmt->execute([':uid' => $userId, ':content' => $content, ':image' => $image, ':vis' => $visibility]);
        return (int)$this->db->lastInsertId();
    }

    // Update only if $userId owns the post.
    public function update(int $id, int $userId, string $content, ?string $image, string $visibility = 'public'): bool
    {
        $visibility = $visibility === 'private' ? 'private' : 'public';
        $stmt = $this->db->prepare(
            'UPDATE posts SET content = :content, image = :image, visibility = :vis WHERE id = :id AND user_id = :uid'
        );
        return $stmt->execute([':content' => $content, ':image' => $image, ':vis' => $visibility, ':id' => $id, ':uid' => $userId]);
    }

    // Delete only if $userId owns the post.
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = :id AND user_id = :uid');
        return $stmt->execute([':id' => $id, ':uid' => $userId]);
    }
}
