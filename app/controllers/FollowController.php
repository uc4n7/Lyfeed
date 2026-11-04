<?php
// Toggle follow/unfollow for a user. Supports AJAX and regular POST redirect.
class FollowController extends Controller
{
    public function toggle(): void
    {
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if (!is_logged_in()) {
            if ($isAjax) {
                $this->json(['ok' => false, 'error' => 'Please log in to follow users.'], 401);
            }
            $this->requireLogin();
        }
        $this->requirePost();

        $targetId = (int)($_POST['user_id'] ?? 0);
        $currentId = current_user_id();

        if ($targetId <= 0 || $targetId === $currentId) {
            if ($isAjax) {
                $this->json(['ok' => false, 'error' => 'Invalid user.'], 422);
            }
            flash('warning', 'You cannot follow yourself.');
            redirect('profile/show', ['id' => $currentId]);
        }

        $users = new UserModel();
        $targetUser = $users->findById($targetId);
        if (!$targetUser) {
            if ($isAjax) {
                $this->json(['ok' => false, 'error' => 'User not found.'], 404);
            }
            $this->notFound();
        }

        $follows = new FollowModel();
        if ($follows->isFollowing($currentId, $targetId)) {
            $follows->remove($currentId, $targetId);
            $following = false;
            $msg = 'You unfollowed ' . $targetUser['full_name'] . '.';
        } else {
            $follows->add($currentId, $targetId);
            $following = true;
            $msg = 'You are now following ' . $targetUser['full_name'] . '.';
        }

        if ($isAjax) {
            $this->json([
                'ok'             => true,
                'following'      => $following,
                'followerCount'  => $follows->countFollowers($targetId),
                'followingCount' => $follows->countFollowing($targetId),
                'message'        => $msg,
            ]);
        }

        flash('success', $msg);
        $tab = $_POST['tab'] ?? '';
        $params = ['id' => $targetId];
        if ($tab !== '') {
            $params['tab'] = $tab;
        }
        redirect('profile/show', $params);
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
