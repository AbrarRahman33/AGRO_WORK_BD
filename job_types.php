<?php
require_once __DIR__ . '/../config/app.php';
$user = require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $op = $_POST['op'] ?? '';
        if ($op === 'create') {
            $pdo->prepare('INSERT INTO JOB_TYPE(job_type_name,description) VALUES(?,?)')->execute([trim($_POST['job_type_name']), trim($_POST['description']) ?: null]);
            flash('success', 'Job type created.');
        } elseif ($op === 'update') {
            $pdo->prepare('UPDATE JOB_TYPE SET job_type_name=?,description=? WHERE job_type_id=?')->execute([trim($_POST['job_type_name']), trim($_POST['description']) ?: null, (int)$_POST['job_type_id']]);
            flash('success', 'Job type updated.');
        } elseif ($op === 'delete') {
            $pdo->prepare('DELETE FROM JOB_TYPE WHERE job_type_id=?')->execute([(int)$_POST['job_type_id']]);
            flash('success', 'Job type deleted.');
        }
    } catch (Throwable $e) {
        flash('error', db_error_message($e));
    }
    redirect('admin/job_types.php');
}
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM JOB_TYPE WHERE job_type_id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query('SELECT * FROM JOB_TYPE ORDER BY job_type_name')->fetchAll();
$pageTitle = 'Job Types';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Job Types</h1>
        <p>Maintain the main categories farmers can choose when posting work.</p>
    </div>
</div>
<div class="card" style="margin-bottom:16px">
    <h3><?= $edit ? 'Edit Job Type' : 'Add Job Type' ?></h3>
    <form method="post"><input type="hidden" name="op" value="<?= $edit ? 'update' : 'create' ?>"><?php if ($edit): ?><input type="hidden" name="job_type_id" value="<?= $edit['job_type_id'] ?>"><?php endif; ?><div class="form-grid">
            <div class="field"><label>Name</label><input name="job_type_name" value="<?= e($edit['job_type_name'] ?? '') ?>" required></div>
            <div class="field"><label>Description</label><input name="description" value="<?= e($edit['description'] ?? '') ?>"></div>
        </div>
        <div class="form-actions"><button class="btn btn-primary">Save</button></div>
    </form>
</div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Job Type</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?><tr>
                        <td><?= $r['job_type_id'] ?></td>
                        <td><?= e($r['job_type_name']) ?></td>
                        <td><?= e($r['description']) ?></td>
                        <td>
                            <div class="actions"><a class="btn btn-secondary" href="?edit=<?= $r['job_type_id'] ?>">Edit</a>
                                <form method="post"><input type="hidden" name="op" value="delete"><input type="hidden" name="job_type_id" value="<?= $r['job_type_id'] ?>"><button class="btn btn-danger" data-confirm="Delete this job type? Existing jobs will block deletion.">Delete</button></form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>