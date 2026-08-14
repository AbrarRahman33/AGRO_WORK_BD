<?php
require_once __DIR__ . '/config/app.php';
$user = require_login();
$stmt = $pdo->prepare('SELECT * FROM PERSON WHERE person_id=?');
$stmt->execute([$user['person_id']]);
$person = $stmt->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare('UPDATE PERSON SET name=?,phone=?,email=?,address=?,date_of_birth=? WHERE person_id=?');
        $stmt->execute([trim($_POST['name']), trim($_POST['phone']), trim($_POST['email']) ?: null, trim($_POST['address']) ?: null, $_POST['date_of_birth'] ?: null, $user['person_id']]);
        $_SESSION['user']['name'] = trim($_POST['name']);
        flash('success', 'Profile updated.');
        redirect('profile.php');
    } catch (Throwable $e) {
        flash('error', db_error_message($e));
        redirect('profile.php');
    }
}
$pageTitle = 'Profile';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Profile</h1>
        <p>Update your personal information.</p>
    </div>
</div>
<div class="card">
    <form method="post">
        <div class="form-grid">
            <div class="field"><label>Name</label><input name="name" value="<?= e($person['name']) ?>" required></div>
            <div class="field"><label>Phone</label><input name="phone" value="<?= e($person['phone']) ?>" required></div>
            <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($person['email']) ?>"></div>
            <div class="field"><label>NID</label><input value="<?= e($person['nid']) ?>" disabled></div>
            <div class="field"><label>Date of birth</label><input type="date" name="date_of_birth" value="<?= e($person['date_of_birth']) ?>"></div>
            <div class="field wide"><label>Address</label><textarea name="address" rows="3"><?= e($person['address']) ?></textarea></div>
        </div>
        <div class="form-actions"><button class="btn btn-primary">Save Profile</button></div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>