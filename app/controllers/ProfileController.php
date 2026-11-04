<?php
// View a profile and update the logged-in user's own profile.
class ProfileController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function show(): void
    {
        $this->requireLogin();

        $id   = (int)($_GET['id'] ?? current_user_id());
        $user = $this->users->findById($id);
        if (!$user) {
            $this->notFound();
        }

        $posts     = new PostModel();
        $comments  = new CommentModel();
        $follows   = new FollowModel();
        $viewerId  = current_user_id();
        $list      = $posts->byUser($id, $viewerId);

        $activeTab = in_array($_GET['tab'] ?? '', ['posts', 'followers', 'following'], true) ? $_GET['tab'] : 'posts';

        $this->view('profile/show', [
            'title'          => $user['full_name'],
            'profile'        => $user,
            'isOwner'        => $id === $viewerId,
            'postCount'      => $posts->countByUser($id, $viewerId),
            'posts'          => $list,
            'comments'       => $comments->forPosts(array_column($list, 'id')),
            'followerCount'  => $follows->countFollowers($id),
            'followingCount' => $follows->countFollowing($id),
            'followers'      => $follows->getFollowers($id, $viewerId),
            'following'      => $follows->getFollowing($id, $viewerId),
            'isFollowing'    => $follows->isFollowing($viewerId, $id),
            'activeTab'      => $activeTab,
        ]);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $user = $this->users->findById(current_user_id());

        $this->view('profile/edit', ['title' => 'Edit profile', 'profile' => $user]);
    }

    public function update(): void
    {
        $this->requireLogin();
        $this->requirePost();

        $user     = $this->users->findById(current_user_id()); // never take user id from POST
        $fullName = clean($_POST['full_name'] ?? '');
        $bio      = clean($_POST['bio'] ?? '');

        $errors = [];
        if ($fullName === '' || mb_strlen($fullName) > 100) {
            $errors[] = 'Full name is required (max 100 characters).';
        }
        if (mb_strlen($bio) > 500) {
            $errors[] = 'Bio must be 500 characters or fewer.';
        }

        $image = $user['profile_image'];
        [$newImage, $uploadError] = handle_image_upload('profile_image');
        if ($uploadError) {
            $errors[] = $uploadError;
        }

        $coverImage = $user['cover_image'] ?? null;
        if (!empty($_POST['remove_cover_image'])) {
            delete_upload($coverImage);
            $coverImage = null;
        }
        [$newCoverImage, $coverUploadError] = handle_image_upload('cover_image');
        if ($coverUploadError) {
            $errors[] = $coverUploadError;
        }

        if ($errors) {
            delete_upload($newImage);
            delete_upload($newCoverImage);
            foreach ($errors as $err) {
                flash('danger', $err);
            }
            redirect('profile/edit');
        }

        if ($newImage) {
            delete_upload($image);
            $image = $newImage;
        }
        if ($newCoverImage) {
            delete_upload($coverImage);
            $coverImage = $newCoverImage;
        }

        $coverPosition = isset($_POST['cover_position'])
            ? max(0, min(100, (int)$_POST['cover_position']))
            : (int)($user['cover_position'] ?? 50);

        $this->users->updateProfile(
            (int)$user['id'],
            $fullName,
            $bio === '' ? null : $bio,
            $image,
            $coverImage,
            $coverPosition
        );
        flash('success', 'Profile updated.');
        redirect('profile/show', ['id' => $user['id']]);
    }
}
