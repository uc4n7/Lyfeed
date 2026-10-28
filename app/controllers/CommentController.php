<?php
/**
 * CommentController - add / edit / update / delete comments (owner only for edit/delete).
 */
class CommentController extends Controller
{
    private CommentModel $comments;

    public function __construct()
    {
        $this->comments = new CommentModel();
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePost();

        $isAjax  = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        $postId  = (int)($_POST['post_id'] ?? 0);
        $content = clean($_POST['content'] ?? '');

        if (!(new PostModel())->find($postId)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Post not found.']);
                exit;
            }
            $this->notFound();
        }
        if ($content === '' || mb_strlen($content) > 1000) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(422);
                echo json_encode(['ok' => false, 'error' => 'Comment is required (max 1000 characters).']);
                exit;
            }
            flash('danger', 'Comment is required (max 1000 characters).');
            redirect('feed/index');
        }

        $id = $this->comments->create($postId, current_user_id(), $content);

        // AJAX request: return the new comment as JSON (escape for safe client rendering)
        if ($isAjax) {
            $comment = $this->comments->find($id);
            // Escape HTML-sensitive fields before sending as JSON
            $comment['content']   = e($comment['content']);
            $comment['full_name'] = e($comment['full_name']);
            $comment['username']  = e($comment['username']);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'comment' => $comment]);
            exit;
        }

        $this->backToPost($postId);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $comment = $this->ownedComment((int)($_GET['id'] ?? 0));

        $this->view('feed/comment_edit', ['title' => 'Edit comment', 'comment' => $comment]);
    }

    public function update(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $comment = $this->ownedComment((int)($_POST['id'] ?? 0));

        $content = clean($_POST['content'] ?? '');
        if ($content === '' || mb_strlen($content) > 1000) {
            flash('danger', 'Comment is required (max 1000 characters).');
            redirect('comment/edit', ['id' => $comment['id']]);
        }

        $this->comments->update((int)$comment['id'], current_user_id(), $content);
        flash('success', 'Comment updated.');
        $this->backToPost((int)$comment['post_id']);
    }

    public function delete(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $comment = $this->ownedComment((int)($_POST['id'] ?? 0));

        $this->comments->delete((int)$comment['id'], current_user_id());
        flash('success', 'Comment deleted.');
        $this->backToPost((int)$comment['post_id']);
    }

    private function ownedComment(int $id): array
    {
        $comment = $this->comments->find($id);
        if (!$comment) {
            $this->notFound();
        }
        if ((int)$comment['user_id'] !== current_user_id()) {
            $this->forbidden();
        }
        return $comment;
    }

    /** Return to the feed, scrolled to the post that was commented on. */
    private function backToPost(int $postId): void
    {
        header('Location: ' . url('feed/index') . '#post-' . $postId);
        exit;
    }
}
