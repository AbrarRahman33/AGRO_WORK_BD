<?php
require_once __DIR__ . '/../config/app.php';
$user = require_role('farmer');
$action = $_GET['action'] ?? '';
$editId = (int)($_GET['id'] ?? 0);
$edit = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = $_POST['op'] ?? '';
    try {
        if ($op === 'create') {
            $pdo->prepare('INSERT INTO FARM(farmer_id,upazila_id,farm_name,village_name,total_area) VALUES(?,?,?,?,?)')->execute([$user['person_id'], (int)$_POST['upazila_id'], trim($_POST['farm_name']) ?: null, trim($_POST['village_name']), (float)$_POST['total_area']]);
            flash('success', 'Farm created.');
        } elseif ($op === 'update') {
            $pdo->prepare('UPDATE FARM SET upazila_id=?,farm_name=?,village_name=?,total_area=? WHERE farm_id=? AND farmer_id=?')->execute([(int)$_POST['upazila_id'], trim($_POST['farm_name']) ?: null, trim($_POST['village_name']), (float)$_POST['total_area'], (int)$_POST['farm_id'], $user['person_id']]);
            flash('success', 'Farm updated.');
        } elseif ($op === 'delete') {
            $pdo->prepare('DELETE FROM FARM WHERE farm_id=? AND farmer_id=?')->execute([(int)$_POST['farm_id'], $user['person_id']]);
            flash('success', 'Farm deleted.');
        }
    } catch (Throwable $e) {
        flash('error', db_error_message($e));
    }
    redirect('farmer/farms.php');
}
if ($action === 'edit' && $editId) {
    $stmt = $pdo->prepare('SELECT * FROM FARM WHERE farm_id=? AND farmer_id=?');
    $stmt->execute([$editId, $user['person_id']]);
    $edit = $stmt->fetch();
}
$upazilas = $pdo->query('SELECT u.upazila_id,u.upazila_name,d.district_name FROM UPAZILA u JOIN DISTRICT d ON d.district_id=u.district_id ORDER BY d.district_name,u.upazila_name')->fetchAll();
$stmt = $pdo->prepare('SELECT f.*,u.upazila_name,d.district_name FROM FARM f JOIN UPAZILA u ON u.upazila_id=f.upazila_id JOIN DISTRICT d ON d.district_id=u.district_id WHERE f.farmer_id=? ORDER BY f.farm_id DESC');
$stmt->execute([$user['person_id']]);
$farms = $stmt->fetchAll();
$pageTitle = 'My Farms';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>My Farms</h1>
        <p>Create, edit and delete the farms you own.</p>
    </div><a class="btn btn-primary" href="?action=add">+ Add Farm</a>
</div>
<?php if ($action === 'add' || $edit): ?><div class="card" style="margin-bottom:16px">
        <h3><?= $edit ? 'Edit Farm' : 'Add Farm' ?></h3>
        <form method="post"><input type="hidden" name="op" value="<?= $edit ? 'update' : 'create' ?>"><?php if ($edit): ?><input type="hidden" name="farm_id" value="<?= $edit['farm_id'] ?>"><?php endif; ?><div class="form-grid">
                <div class="field"><label>Farm name</label><input name="farm_name" value="<?= e($edit['farm_name'] ?? '') ?>"></div>
                <div class="field"><label>Village</label><input name="village_name" value="<?= e($edit['village_name'] ?? '') ?>" required></div>
                <div class="field"><label>Upazila</label><select name="upazila_id" required>
                        <option value="">Select location</option><?php foreach ($upazilas as $u): ?><option value="<?= $u['upazila_id'] ?>" <?= ($edit['upazila_id'] ?? '') == $u['upazila_id'] ? 'selected' : '' ?>><?= e($u['upazila_name'] . ', ' . $u['district_name']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="field"><label>Total area (acres)</label><input type="number" step="0.01" min="0.01" name="total_area" value="<?= e($edit['total_area'] ?? '') ?>" required></div>
            </div>
            <div class="form-actions"><a class="btn btn-secondary" href="farms.php">Cancel</a><button class="btn btn-primary">Save Farm</button></div>
        </form>
    </div><?php endif; ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Farm</th>
                    <th>Village</th>
                    <th>Location</th>
                    <th>Area</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody><?php foreach ($farms as $f): ?><tr>
                        <td><strong><?= e($f['farm_name'] ?: 'Unnamed Farm') ?></strong><small>FARM #<?= $f['farm_id'] ?></small></td>
                        <td><?= e($f['village_name']) ?></td>
                        <td><?= e($f['upazila_name'] . ', ' . $f['district_name']) ?></td>
                        <td><?= e($f['total_area']) ?> acres</td>
                        <td>
                            <div class="actions"><a class="btn btn-secondary" href="?action=edit&id=<?= $f['farm_id'] ?>">Edit</a>
                                <form method="post"><input type="hidden" name="op" value="delete"><input type="hidden" name="farm_id" value="<?= $f['farm_id'] ?>"><button class="btn btn-danger" data-confirm="Delete this farm? Existing jobs will block deletion.">Delete</button></form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$farms): ?><tr>
                        <td colspan="5" class="empty">No farms yet.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>