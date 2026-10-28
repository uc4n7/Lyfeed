<?php
/**
 * PostController - create / edit / update / delete posts (owner only).
 */
class PostController extends Controller
{
    private PostModel $posts;

    public function __construct()
    {
        $this->posts = new PostModel();
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePost();

        $content = clean($_POST['content'] ?? '');
        if ($content === '' || mb_strlen($content) > 2000) {
            flash('danger', 'Post content is required (max 2000 characters).');
            redirect('feed/index');
        }

        [$image, $error] = handle_image_upload('image');
        if ($error) {
            flash('danger', $error);
            redirect('feed/index');
        }

        $this->posts->create(current_user_id(), $content, $image);
        flash('success', 'Your post was published.');
        redirect('feed/index');
    }

    public function edit(): void
    {
        $this->requireLogin();
        $post = $this->ownedPost((int)($_GET['id'] ?? 0));

        $this->view('feed/post_edit', ['title' => 'Edit post', 'post' => $post]);
    }

    public function update(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $post = $this->ownedPost((int)($_POST['id'] ?? 0));

        $content = clean($_POST['content'] ?? '');
        if ($content === '' || mb_strlen($content) > 2000) {
            flash('danger', 'Post content is required (max 2000 characters).');
            redirect('post/edit', ['id' => $post['id']]);
        }

        $image = $post['image'];

        if (!empty($_POST['remove_image'])) {
            delete_upload($image);
            $image = null;
        }

        [$newImage, $error] = handle_image_upload('image');
        if ($error) {
            flash('danger', $error);
            redirect('post/edit', ['id' => $post['id']]);
        }
        if ($newImage) {
            delete_upload($image);
            $image = $newImage;
        }

        $this->posts->update((int)$post['id'], current_user_id(), $content, $image);
        flash('success', 'Post updated.');
        redirect('feed/index');
    }

    public function delete(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $post = $this->ownedPost((int)($_POST['id'] ?? 0));

        delete_upload($post['image']);
        $this->posts->delete((int)$post['id'], current_user_id());
        flash('success', 'Post deleted.');
        redirect('feed/index');
    }

    /** Load a post and make sure the logged-in user owns it (authorization check). */
    private function ownedPost(int $id): array
    {
        $post = $this->posts->find($id, current_user_id());
        if (!$post) {
            $this->notFound();
        }
        if ((int)$post['user_id'] !== current_user_id()) {
            $this->forbidden();
        }
        return $post;
    }
}
