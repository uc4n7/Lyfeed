<?php
// Queries for user follows.
class FollowModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function isFollowing(int $followerId, int $followedId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM follows WHERE follower_id = :fid AND followed_id = :uid LIMIT 1');
        $stmt->execute([':fid' => $followerId, ':uid' => $followedId]);
        return (bool)$stmt->fetchColumn();
    }

    public function add(int $followerId, int $followedId): bool
    {
        if ($followerId === $followedId) {
            return false;
        }
        $stmt = $this->db->prepare('INSERT IGNORE INTO follows (follower_id, followed_id) VALUES (:fid, :uid)');
        return $stmt->execute([':fid' => $followerId, ':uid' => $followedId]);
    }

    public function remove(int $followerId, int $followedId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM follows WHERE follower_id = :fid AND followed_id = :uid');
        return $stmt->execute([':fid' => $followerId, ':uid' => $followedId]);
    }

    public function countFollowers(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM follows WHERE followed_id = :uid');
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function countFollowing(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM follows WHERE follower_id = :uid');
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get list of users following $userId
     * @return array<int, array>
     */
    public function getFollowers(int $userId, ?int $viewerId = null): array
    {
        $viewerCheck = $viewerId !== null
            ? 'EXISTS(SELECT 1 FROM follows WHERE follower_id = :vid AND followed_id = u.id) AS is_following'
            : '0 AS is_following';

        $sql = "SELECT u.id, u.username, u.full_name, u.bio, u.profile_image, {$viewerCheck}
                FROM follows f
                JOIN users u ON u.id = f.follower_id
                WHERE f.followed_id = :uid
                ORDER BY f.created_at DESC";

        $params = [':uid' => $userId];
        if ($viewerId !== null) {
            $params[':vid'] = $viewerId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get list of users followed by $userId
     * @return array<int, array>
     */
    public function getFollowing(int $userId, ?int $viewerId = null): array
    {
        $viewerCheck = $viewerId !== null
            ? 'EXISTS(SELECT 1 FROM follows WHERE follower_id = :vid AND followed_id = u.id) AS is_following'
            : '0 AS is_following';

        $sql = "SELECT u.id, u.username, u.full_name, u.bio, u.profile_image, {$viewerCheck}
                FROM follows f
                JOIN users u ON u.id = f.followed_id
                WHERE f.follower_id = :uid
                ORDER BY f.created_at DESC";

        $params = [':uid' => $userId];
        if ($viewerId !== null) {
            $params[':vid'] = $viewerId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
