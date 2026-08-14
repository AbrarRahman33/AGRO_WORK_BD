<?php
require_once __DIR__ . '/../config/app.php';
$user = require_role('farmer');
$stmt = $pdo->prepare('SELECT jd.*,p.name driver_name,p.phone,j.job_id,jt.job_type_name,f.farm_name FROM JOB_DRIVER jd JOIN DRIVER d ON d.person_id=jd.driver_id JOIN PERSON p ON p.person_id=d.person_id JOIN JOB j ON j.job_id=jd.job_id JOIN JOB_TYPE jt ON jt.job_type_id=j.job_type_id JOIN FARM f ON f.farm_id=j.farm_id WHERE f.farmer_id=? ORDER BY jd.job_driver_id DESC');
$stmt->execute([$user['person_id']]);
$rows = $stmt->fetchAll();
$pageTitle = 'Assigned Workers';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Assigned Workers</h1>
        <p>Assignments are created automatically when a driver accepts your posted job.</p>
    </div>
</div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Worker</th>
                    <th>Job</th>
                    <th>Farm</th>
                    <th>Wage/day</th>
                    <th>Start</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?><tr>
                        <td><strong><?= e($r['driver_name']) ?></strong><small><?= e($r['phone']) ?></small></td>
                        <td><?= e($r['job_type_name']) ?><small>JOB #<?= $r['job_id'] ?></small></td>
                        <td><?= e($r['farm_name']) ?></td>
                        <td>৳<?= number_format($r['agreed_wage_per_day'], 2) ?></td>
                        <td><?= e($r['assignment_start']) ?></td>
                        <td><span class="badge"><?= e($r['assignment_status']) ?></span></td>
                    </tr><?php endforeach; ?><?php if (!$rows): ?><tr>
                        <td colspan="6" class="empty">No workers have accepted your jobs yet.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>