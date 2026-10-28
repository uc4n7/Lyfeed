<?php
/**
 * LikeController - like / unlike toggle. Works with AJAX (JSON) and plain form posts.
 */
class LikeController extends Controller
{
    public function toggle(): void
    {
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        if (!is_logged_in()) {
            if ($isAjax) {
                $this->json(['ok' => false, 'error' => 'Please log in.'], 401);
            }
            $this->requireLogin();
        }
        $this->requirePost();

        $postId = (int)($_POST['post_id'] ?? 0);
        if (!(new PostModel())->find($postId)) {
            if ($isAjax) {
                $this->json(['ok' => false, 'error' => 'Post not found.'], 404);
            }
            $this->notFound();
        }

        $likes = new LikeModel();
        $uid   = current_user_id();

        if ($likes->hasLiked($postId, $uid)) {
            $likes->remove($postId, $uid);
            $liked = false;
        } else {
            $likes->add($postId, $uid);
            $liked = true;
        }

        if ($isAjax) {
            $this->json(['ok' => true, 'liked' => $liked, 'count' => $likes->count($postId)]);
        }

        header('Location: ' . url('feed/index') . '#post-' . $postId);
        exit;
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
