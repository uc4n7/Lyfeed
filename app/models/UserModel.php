<?php
/**
 * UserModel - data access for the `users` table. Database logic only.
 */
class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, full_name, bio, profile_image, cover_image, cover_position, created_at FROM users WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Includes the password hash - used only for login verification. */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        return $stmt->fetch() ?: null;
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(string $username, string $passwordHash, string $fullName, ?string $profileImage): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, password, full_name, profile_image)
             VALUES (:username, :password, :full_name, :profile_image)'
        );
        $stmt->execute([
            ':username'      => $username,
            ':password'      => $passwordHash,
            ':full_name'     => $fullName,
            ':profile_image' => $profileImage,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateProfile(int $id, string $fullName, ?string $bio, ?string $profileImage, ?string $coverImage = null, int $coverPosition = 50): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET full_name = :full_name, bio = :bio, profile_image = :profile_image, cover_image = :cover_image, cover_position = :cover_position WHERE id = :id'
        );
        return $stmt->execute([
            ':full_name'       => $fullName,
            ':bio'             => $bio,
            ':profile_image'   => $profileImage,
            ':cover_image'     => $coverImage,
            ':cover_position'  => $coverPosition,
            ':id'              => $id,
        ]);
    }

    /** Newest members other than the given user (for the "Discover" row / suggestions). */
    public function others(int $exceptId, int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, full_name, profile_image, cover_image, cover_position
             FROM users
             WHERE id <> :id
             ORDER BY created_at DESC, id DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':id', $exceptId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Search by full name or username. LIKE wildcards in user input are escaped. */
    public function search(string $term, int $limit = 20): array
    {
        $like = '%' . addcslashes($term, '%_\\') . '%';
        $stmt = $this->db->prepare(
            'SELECT id, username, full_name, bio, profile_image
             FROM users
             WHERE username LIKE :q1 OR full_name LIKE :q2
             ORDER BY full_name ASC
             LIMIT :lim'
        );
        $stmt->bindValue(':q1', $like);
        $stmt->bindValue(':q2', $like);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Update password hash (for rehashing on login). */
    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        return $stmt->execute([':password' => $passwordHash, ':id' => $id]);
    }
}
