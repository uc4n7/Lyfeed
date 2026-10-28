<?php
/**
 * AuthController - register, login, logout.
 */
class AuthController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function register(): void
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePost();

            // Rate limit: max 5 registrations per 15 minutes per session
            if (!rate_limit('register', 5, 900)) {
                flash('danger', 'Too many registration attempts. Please try again later.');
                redirect('auth/register');
            }

            $username = clean($_POST['username'] ?? '');
            $fullName = clean($_POST['full_name'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $confirm  = (string)($_POST['confirm_password'] ?? '');

            $errors = [];
            if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
                $errors[] = 'Username must be 3-30 characters: letters, numbers, and underscores only.';
            } elseif ($this->users->usernameExists($username)) {
                $errors[] = 'That username is already taken.';
            }
            if ($fullName === '' || mb_strlen($fullName) > 100) {
                $errors[] = 'Full name is required (max 100 characters).';
            }
            // Validate password strength (min 8, max 128)
            $pwErrors = validate_password($password);
            if ($pwErrors) {
                $errors = array_merge($errors, $pwErrors);
            }
            if ($password !== $confirm) {
                $errors[] = 'Passwords do not match.';
            }

            [$image, $uploadError] = handle_image_upload('profile_image');
            if ($uploadError) {
                $errors[] = $uploadError;
            }

            if ($errors) {
                delete_upload($image);
                foreach ($errors as $err) {
                    flash('danger', $err);
                }
                remember_old(['username' => $username, 'full_name' => $fullName]);
                redirect('auth/register');
            }

            $id = $this->users->create($username, password_hash($password, PASSWORD_DEFAULT), $fullName, $image);

            session_regenerate_id(true);
            csrf_regenerate();
            $_SESSION['user_id'] = $id;
            flash('success', 'Welcome to ' . config('app.name') . ', ' . $fullName . '!');
            redirect('feed/index');
        }

        $this->view('auth/register', ['title' => 'Register']);
    }

    public function login(): void
    {
        $this->requireGuest();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePost();

            // Rate limit: max 5 login attempts per 5 minutes per session
            if (!rate_limit('login', 7, 60)) {
                flash('danger', 'Too many login attempts. Please try again in a few minutes.');
                redirect('auth/login');
            }

            $username = clean($_POST['username'] ?? '');
            $password = (string)($_POST['password'] ?? '');

            if ($username === '' || $password === '') {
                flash('danger', 'Please enter your username and password.');
                remember_old(['username' => $username]);
                redirect('auth/login');
            }

            $user = $this->users->findByUsername($username);

            // Same message for unknown user and wrong password (no user enumeration).
            if (!$user || !password_verify($password, $user['password'])) {
                flash('danger', 'Invalid username or password.');
                remember_old(['username' => $username]);
                redirect('auth/login');
            }

            // Rehash password if algorithm/cost has changed
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $this->users->updatePassword((int)$user['id'], password_hash($password, PASSWORD_DEFAULT));
            }

            session_regenerate_id(true);                    // prevent session fixation
            csrf_regenerate();                              // rotate CSRF token too
            $_SESSION['user_id'] = (int)$user['id'];
            flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect('feed/index');
        }

        $this->view('auth/login', ['title' => 'Login']);
    }

    public function logout(): void
    {
        $this->requirePost();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        session_start();
        flash('info', 'You have been logged out.');
        redirect('auth/login');
    }
}
