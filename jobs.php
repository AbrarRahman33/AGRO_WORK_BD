<?php
require_once __DIR__ . '/../config/app.php';
$user = require_role('farmer');
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$edit = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = $_POST['op'] ?? '';
    try {
        if ($op === 'create') {
            $pdo->prepare('INSERT INTO JOB(farm_id,job_type_id,description,start_date,end_date,required_drivers,wage_per_day,status) SELECT ?,?,?,?,?,?,?,? FROM FARM WHERE farm_id=? AND farmer_id=?')->execute([(int)$_POST['farm_id'], (int)$_POST['job_type_id'], trim($_POST['description']) ?: null, $_POST['start_date'], $_POST['end_date'] ?: null, (int)$_POST['required_drivers'], (float)$_POST['wage_per_day'], $_POST['status'], (int)$_POST['farm_id'], $user['person_id']]);
            flash('success', 'Job posted.');
        } elseif ($op === 'update') {
            $pdo->prepare('UPDATE JOB j JOIN FARM f ON f.farm_id=j.farm_id SET j.farm_id=?,j.job_type_id=?,j.description=?,j.start_date=?,j.end_date=?,j.required_drivers=?,j.wage_per_day=?,j.status=? WHERE j.job_id=? AND f.farmer_id=?')->execute([(int)$_POST['farm_id'], (int)$_POST['job_type_id'], trim($_POST['description']) ?: null, $_POST['start_date'], $_POST['end_date'] ?: null, (int)$_POST['required_drivers'], (float)$_POST['wage_per_day'], $_POST['status'], (int)$_POST['job_id'], $user['person_id']]);
            flash('success', 'Job updated.');
        } elseif ($op === 'delete') {
            $pdo->prepare('DELETE j FROM JOB j JOIN FARM f ON f.farm_id=j.farm_id WHERE j.job_id=? AND f.farmer_id=?')->execute([(int)$_POST['job_id'], $user['person_id']]);
            flash('success', 'Job deleted.');
        }
    } catch (Throwable $e) {
        flash('error', db_error_message($e));
    }
    redirect('farmer/jobs.php');
}
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare('SELECT j.* FROM JOB j JOIN FARM f ON f.farm_id=j.farm_id WHERE j.job_id=? AND f.farmer_id=?');
    $stmt->execute([$id, $user['person_id']]);
    $edit = $stmt->fetch();
}
$stmt = $pdo->prepare('SELECT farm_id,farm_name FROM FARM WHERE farmer_id=? ORDER BY farm_name');
$stmt->execute([$user['person_id']]);
$farms = $stmt->fetchAll();
$types = $pdo->query('SELECT * FROM JOB_TYPE ORDER BY job_type_name')->fetchAll();
$stmt = $pdo->prepare("SELECT j.*,f.farm_name,jt.job_type_name,(SELECT COUNT(*) FROM JOB_DRIVER jd WHERE jd.job_id=j.job_id AND jd.assignment_status IN ('Assigned','Working','Completed')) assigned FROM JOB j JOIN FARM f ON f.farm_id=j.farm_id JOIN JOB_TYPE jt ON jt.job_type_id=j.job_type_id WHERE f.farmer_id=? ORDER BY j.job_id DESC");
$stmt->execute([$user['person_id']]);
$jobs = $stmt->fetchAll();
$pageTitle = 'My Jobs';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>My Jobs</h1>
        <p>Post jobs that consumers/workers can browse and accept.</p>
    </div><a class="btn btn-primary" href="?action=add">+ Post Job</a>
</div>
<?php if ($action === 'add' || $edit): ?><div class="card" style="margin-bottom:16px">
        <h3><?= $edit ? 'Edit Job' : 'Post New Job' ?></h3>
        <form method="post"><input type="hidden" name="op" value="<?= $edit ? 'update' : 'create' ?>"><?php if ($edit): ?><input type="hidden" name="job_id" value="<?= $edit['job_id'] ?>"><?php endif; ?><div class="form-grid">
                <div class="field"><label>Farm</label><select name="farm_id" required>
                        <option value="">Select farm</option><?php foreach ($farms as $f): ?><option value="<?= $f['farm_id'] ?>" <?= ($edit['farm_id'] ?? '') == $f['farm_id'] ? 'selected' : '' ?>><?= e($f['farm_name'] ?: 'Farm #' . $f['farm_id']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="field"><label>Job type</label><select name="job_type_id" required>
                        <option value="">Select job type</option><?php foreach ($types as $t): ?><option value="<?= $t['job_type_id'] ?>" <?= ($edit['job_type_id'] ?? '') == $t['job_type_id'] ? 'selected' : '' ?>><?= e($t['job_type_name']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="field wide"><label>Description</label><textarea name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea></div>
                <div class="field"><label>Start date</label><input type="date" name="start_date" value="<?= e($edit['start_date'] ?? date('Y-m-d')) ?>" required></div>
                <div class="field"><label>End date</label><input type="date" name="end_date" value="<?= e($edit['end_date'] ?? '') ?>"></div>
                <div class="field"><label>Workers needed</label><input type="number" min="1" name="required_drivers" value="<?= e($edit['required_drivers'] ?? 1) ?>" required></div>
                <div class="field"><label>Wage per day (৳)</label><input type="number" step="0.01" min="1" name="wage_per_day" value="<?= e($edit['wage_per_day'] ?? '800') ?>" required></div>
                <div class="field"><label>Status</label><select name="status"><?php foreach (['Pending', 'In Progress', 'Completed', 'Cancelled'] as $s): ?><option <?= ($edit['status'] ?? 'Pending') === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="form-actions"><a class="btn btn-secondary" href="jobs.php">Cancel</a><button class="btn btn-primary">Save Job</button></div>
        </form>
    </div><?php endif; ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Farm</th>
                    <th>Wage</th>
                    <th>Workers</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody><?php foreach ($jobs as $j): ?><tr>
                        <td><strong><?= e($j['job_type_name']) ?></strong><small>JOB #<?= $j['job_id'] ?></small></td>
                        <td><?= e($j['farm_name']) ?></td>
                        <td>৳<?= number_format($j['wage_per_day'], 2) ?> / day</td>
                        <td><?= $j['assigned'] ?> / <?= $j['required_drivers'] ?></td>
                        <td><?= e($j['start_date']) ?><small><?= e($j['end_date'] ?: 'Open-ended') ?></small></td>
                        <td><span class="badge"><?= e($j['status']) ?></span></td>
                        <td>
                            <div class="actions"><a class="btn btn-secondary" href="?action=edit&id=<?= $j['job_id'] ?>">Edit</a>
                                <form method="post"><input type="hidden" name="op" value="delete"><input type="hidden" name="job_id" value="<?= $j['job_id'] ?>"><button class="btn btn-danger" data-confirm="Delete this job? Assignments/payments may block deletion.">Delete</button></form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$jobs): ?><tr>
                        <td colspan="7" class="empty">No jobs posted yet.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>