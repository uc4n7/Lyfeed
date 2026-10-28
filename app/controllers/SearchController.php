<?php
/**
 * SearchController - search users (name / username) and posts (keywords).
 */
class SearchController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $q    = clean($_GET['q'] ?? '');
        $type = ($_GET['type'] ?? 'users') === 'posts' ? 'posts' : 'users';

        $results  = [];
        $comments = [];

        if ($q !== '') {
            if (mb_strlen($q) > 100) {
                $q = mb_substr($q, 0, 100);
            }
            if ($type === 'users') {
                $results = (new UserModel())->search($q);
            } else {
                $results  = (new PostModel())->search($q, current_user_id());
                $comments = (new CommentModel())->forPosts(array_column($results, 'id'));
            }
        }

        $this->view('search/index', [
            'title'    => 'Search',
            'q'        => $q,
            'type'     => $type,
            'results'  => $results,
            'comments' => $comments,
        ]);
    }
}
