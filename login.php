<?php
require_once __DIR__ . '/config/app.php';
if (current_user()) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nid = trim($_POST['nid'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT p.person_id,p.name,p.nid,a.role,a.password_hash FROM PERSON p JOIN AUTH_USER a ON a.person_id=p.person_id WHERE p.nid=? LIMIT 1');
    $stmt->execute([$nid]);
    $row = $stmt->fetch();
    if ($row && password_verify($password, $row['password_hash'])) {
        login_user($row);
        redirect('dashboard.php');
    }
    $error = 'Invalid NID or password.';
}
$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-shell" style="max-width:500px">
    <h1>Welcome back</h1>
    <p class="auth-note">Login as Farmer, Driver or Admin.</p>
    <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="field"><label>NID</label><input name="nid" required></div>
        <div class="field section-gap"><label>Password</label><input type="password" name="password" required></div>
        <div class="form-actions"><a class="btn btn-secondary" href="<?= e(base_url()) ?>">Back</a><button class="btn btn-primary">Login</button></div>
    </form>
    <p class="auth-note">New user? <a href="<?= e(base_url('register.php')) ?>"><strong>Create an account</strong></a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>