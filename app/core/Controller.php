<?php
/**
 * Base controller: view rendering and common guards.
 */
abstract class Controller
{
    /** Render a view inside the header/footer layout. $data keys become variables. */
    protected function view(string $view, array $data = [], bool $layout = true): void
    {
        // Logged-in user for the navbar, fetched here so views stay free of database logic.
        $data['navUser'] = is_logged_in() ? (new UserModel())->findById(current_user_id()) : null;
        extract($data, EXTR_SKIP);
        $file = BASE_PATH . '/app/views/' . $view . '.php';

        if (!is_file($file)) {
            http_response_code(500);
            exit('View not found.');
        }

        if ($layout) {
            require BASE_PATH . '/app/views/layouts/header.php';
        }
        require $file;
        if ($layout) {
            require BASE_PATH . '/app/views/layouts/footer.php';
        }
        clear_old();
    }

    /** Only logged-in users may continue. */
    protected function requireLogin(): void
    {
        if (!is_logged_in()) {
            flash('warning', 'Please log in to continue.');
            redirect('auth/login');
        }
    }

    /** Only guests (logged-out users) may continue. */
    protected function requireGuest(): void
    {
        if (is_logged_in()) {
            redirect('feed/index');
        }
    }

    /** Only POST requests with a valid CSRF token may continue. */
    protected function requirePost(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
                exit;
            }
            exit('Method not allowed.');
        }
        if (!csrf_valid()) {
            http_response_code(419);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'error' => 'Invalid or expired form token. Please refresh the page and try again.']);
                exit;
            }
            exit('Invalid or expired form token. Please go back, refresh, and try again.');
        }
    }

    protected function notFound(): void
    {
        http_response_code(404);
        $this->view('errors/404');
        exit;
    }

    protected function forbidden(): void
    {
        http_response_code(403);
        flash('danger', 'You are not allowed to do that.');
        redirect('feed/index');
    }
}
