<?php
// Toggle like/unlike. Returns JSON for AJAX, otherwise redirects to the post.
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

        $uid    = current_user_id();
        $postId = (int)($_POST['post_id'] ?? 0);
        if (!(new PostModel())->find($postId, $uid)) {
            if ($isAjax) {
                $this->json(['ok' => false, 'error' => 'Post not found.'], 404);
            }
            $this->notFound();
        }

        $likes = new LikeModel();

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

        $redirect = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url('feed/index') . '#post-' . $postId;
        header('Location: ' . $redirect);
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
