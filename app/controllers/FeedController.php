<?php
/**
 * FeedController - newsfeed / timeline with pagination.
 */
class FeedController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $posts    = new PostModel();
        $comments = new CommentModel();
        $users    = new UserModel();

        $perPage = (int)config('app.per_page', 10);
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $total   = $posts->countAll();
        $pages   = max(1, (int)ceil($total / $perPage));
        $page    = min($page, $pages);

        $feed = $posts->feed(current_user_id(), $perPage, ($page - 1) * $perPage);

        $this->view('feed/index', [
            'title'    => 'Newsfeed',
            'me'       => $users->findById(current_user_id()),
            'people'   => $users->others(current_user_id(), 8),
            'posts'    => $feed,
            'comments' => $comments->forPosts(array_column($feed, 'id')),
            'page'     => $page,
            'pages'    => $pages,
        ]);
    }
}
