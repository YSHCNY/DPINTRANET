<?php
require_once '../app/core/Model.php';

class PasswordResetOtp extends Model
{
    public function createForUser(int $userId, string $otpHash, string $expiresAt): bool
    {
        $this->invalidateUserOtps($userId);

        $stmt = $this->db->prepare(
            'INSERT INTO password_reset_otps (user_id, otp_hash, attempts, expires_at, created_at) VALUES (?, ?, 0, ?, NOW())'
        );

        return $stmt->execute([$userId, $otpHash, $expiresAt]);
    }

    public function getActiveOtpForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM password_reset_otps WHERE user_id = ? AND verified_at IS NULL AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1'
        );

        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM password_reset_otps WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function incrementAttempts(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE password_reset_otps SET attempts = attempts + 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function markVerified(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE password_reset_otps SET verified_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function deleteByUserId(int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM password_reset_otps WHERE user_id = ?');
        return $stmt->execute([$userId]);
    }

    public function invalidateUserOtps(int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE password_reset_otps SET verified_at = NOW() WHERE user_id = ? AND verified_at IS NULL');
        return $stmt->execute([$userId]);
    }
}
