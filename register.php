<?php
require_once __DIR__ . '/config/app.php';
if (current_user()) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $nid = trim($_POST['nid'] ?? '');
    $dob = $_POST['date_of_birth'] ?: null;
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    if (!in_array($role, ['farmer', 'driver'], true)) $error = 'Choose Farmer or Driver.';
    elseif (strlen($password) < 6) $error = 'Password must be at least 6 characters.';
    else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO PERSON(name,phone,email,address,nid,date_of_birth) VALUES(?,?,?,?,?,?)');
            $stmt->execute([$name, $phone, $email ?: null, $address ?: null, $nid, $dob]);
            $personId = (int)$pdo->lastInsertId();
            if ($role === 'farmer') $pdo->prepare('INSERT INTO FARMER(person_id) VALUES(?)')->execute([$personId]);
            else $pdo->prepare("INSERT INTO DRIVER(person_id,availability_status) VALUES(?,'Available')")->execute([$personId]);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO AUTH_USER(person_id,password_hash,role) VALUES(?,?,?)')->execute([$personId, $hash, $role]);
            $pdo->commit();
            login_user(['person_id' => $personId, 'name' => $name, 'nid' => $nid, 'role' => $role]);
            redirect('dashboard.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = db_error_message($e);
        }
    }
}
$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-shell">
    <h1>Create Agro Work account</h1>
    <p class="auth-note">Register as a farmer looking for workers or a driver/worker looking for jobs.</p>
    <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div class="field"><label>Full name</label><input name="name" required></div>
            <div class="field"><label>Phone</label><input name="phone" required></div>
            <div class="field"><label>Email</label><input type="email" name="email"></div>
            <div class="field"><label>NID</label><input name="nid" required></div>
            <div class="field"><label>Date of birth</label><input type="date" name="date_of_birth"></div>
            <div class="field"><label>Account type</label><select name="role" required>
                    <option value="farmer">Farmer</option>
                    <option value="driver">Driver / Worker</option>
                </select></div>
            <div class="field"><label>Password</label><input type="password" name="password" minlength="6" required></div>
            <div class="field wide"><label>Address</label><textarea name="address" rows="3"></textarea></div>
        </div>
        <div class="form-actions"><a class="btn btn-secondary" href="<?= e(base_url()) ?>">Back</a><button class="btn btn-primary">Create Account</button></div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>