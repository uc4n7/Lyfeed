<?php
/**
 * Minimal front-controller router.
 * URL format:  index.php?route=controller/action   (e.g. ?route=post/create)
 * Only whitelisted controllers/actions can be reached.
 */
class Router
{
    /** controller => allowed actions */
    private const ROUTES = [
        'auth'    => ['login', 'register', 'logout'],
        'feed'    => ['index'],
        'post'    => ['store', 'edit', 'update', 'delete'],
        'comment' => ['store', 'edit', 'update', 'delete'],
        'like'    => ['toggle'],
        'profile' => ['show', 'edit', 'update'],
        'search'  => ['index'],
    ];

    public function dispatch(): void
    {
        $route = $_GET['route'] ?? '';
        if ($route === '') {
            $route = is_logged_in() ? 'feed/index' : 'auth/login';
        }

        $parts      = explode('/', $route, 2);
        $controller = $parts[0];
        $action     = $parts[1] ?? 'index';

        if (!isset(self::ROUTES[$controller]) || !in_array($action, self::ROUTES[$controller], true)) {
            (new class extends Controller {
                public function show(): void
                {
                    $this->notFound();
                }
            })->show();
            return;
        }

        $class = ucfirst($controller) . 'Controller';
        $controllerFile = BASE_PATH . '/app/controllers/' . $class . '.php';

        if (!preg_match('/^[A-Za-z]+Controller$/', $class) || !is_file($controllerFile)) {
            (new class extends Controller {
                public function show(): void
                {
                    $this->notFound();
                }
            })->show();
            return;
        }

        require_once $controllerFile;

        $instance = new $class();
        if (!method_exists($instance, $action)) {
            (new class extends Controller {
                public function show(): void
                {
                    $this->notFound();
                }
            })->show();
            return;
        }

        $instance->$action();
    }
}
