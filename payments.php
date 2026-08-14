<?php
require_once __DIR__ . '/../config/app.php';
$user = require_role('farmer');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $op = $_POST['op'] ?? '';
        if ($op === 'create') {
            $jd = (int)$_POST['job_driver_id'];
            $owns = scalar($pdo, 'SELECT COUNT(*) FROM JOB_DRIVER jd JOIN JOB j ON j.job_id=jd.job_id JOIN FARM f ON f.farm_id=j.farm_id WHERE jd.job_driver_id=? AND f.farmer_id=?', [$jd, $user['person_id']]);
            if (!$owns) throw new RuntimeException('Not your assignment');
            $pdo->prepare('INSERT INTO PAYMENT(job_driver_id,amount,payment_date,payment_method,payment_status,transaction_reference) VALUES(?,?,?,?,?,?)')->execute([$jd, (float)$_POST['amount'], $_POST['payment_date'], $_POST['payment_method'], $_POST['payment_status'], trim($_POST['transaction_reference']) ?: null]);
            flash('success', 'Payment recorded.');
        } elseif ($op === 'delete') {
            $pdo->prepare('DELETE p FROM PAYMENT p JOIN JOB_DRIVER jd ON jd.job_driver_id=p.job_driver_id JOIN JOB j ON j.job_id=jd.job_id JOIN FARM f ON f.farm_id=j.farm_id WHERE p.payment_id=? AND f.farmer_id=?')->execute([(int)$_POST['payment_id'], $user['person_id']]);
            flash('success', 'Payment deleted.');
        }
    } catch (Throwable $e) {
        flash('error', db_error_message($e));
    }
    redirect('farmer/payments.php');
}
$stmt = $pdo->prepare("SELECT jd.job_driver_id,p.name driver_name,jt.job_type_name,jd.agreed_wage_per_day,DATEDIFF(COALESCE(jd.assignment_end,CURDATE()),jd.assignment_start)+1 days FROM JOB_DRIVER jd JOIN PERSON p ON p.person_id=jd.driver_id JOIN JOB j ON j.job_id=jd.job_id JOIN JOB_TYPE jt ON jt.job_type_id=j.job_type_id JOIN FARM f ON f.farm_id=j.farm_id WHERE f.farmer_id=? ORDER BY jd.job_driver_id DESC");
$stmt->execute([$user['person_id']]);
$assignments = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT p.*,pe.name driver_name,jt.job_type_name FROM PAYMENT p JOIN JOB_DRIVER jd ON jd.job_driver_id=p.job_driver_id JOIN PERSON pe ON pe.person_id=jd.driver_id JOIN JOB j ON j.job_id=jd.job_id JOIN JOB_TYPE jt ON jt.job_type_id=j.job_type_id JOIN FARM f ON f.farm_id=j.farm_id WHERE f.farmer_id=? ORDER BY p.payment_id DESC');
$stmt->execute([$user['person_id']]);
$payments = $stmt->fetchAll();
$pageTitle = 'Payments';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Payments</h1>
        <p>Record payments for workers assigned to your jobs.</p>
    </div>
</div>
<div class="card" style="margin-bottom:16px">
    <h3>Record payment</h3>
    <form method="post"><input type="hidden" name="op" value="create">
        <div class="form-grid">
            <div class="field wide"><label>Assignment / worker</label><select name="job_driver_id" id="assignmentSelect" required>
                    <option value="">Select worker assignment</option><?php foreach ($assignments as $a): ?><option value="<?= $a['job_driver_id'] ?>" data-wage="<?= $a['agreed_wage_per_day'] ?>" data-days="<?= $a['days'] ?>"><?= e($a['driver_name'] . ' — ' . $a['job_type_name'] . ' — ৳' . $a['agreed_wage_per_day'] . '/day') ?></option><?php endforeach; ?>
                </select></div>
            <div class="field"><label>Amount (৳)</label><input type="number" step="0.01" min="1" name="amount" id="paymentAmount" required></div>
            <div class="field"><label>Date</label><input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required></div>
            <div class="field"><label>Method</label><select name="payment_method">
                    <option>Cash</option>
                    <option>Bank Transfer</option>
                    <option>Mobile Banking</option>
                </select></div>
            <div class="field"><label>Status</label><select name="payment_status">
                    <option>Paid</option>
                    <option>Pending</option>
                    <option>Failed</option>
                </select></div>
            <div class="field wide"><label>Transaction reference</label><input name="transaction_reference"></div>
        </div>
        <div class="form-actions"><button class="btn btn-primary">Save Payment</button></div>
    </form>
</div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Worker</th>
                    <th>Job</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody><?php foreach ($payments as $p): ?><tr>
                        <td><?= e($p['driver_name']) ?></td>
                        <td><?= e($p['job_type_name']) ?></td>
                        <td>৳<?= number_format($p['amount'], 2) ?></td>
                        <td><?= e($p['payment_date']) ?></td>
                        <td><?= e($p['payment_method']) ?></td>
                        <td><span class="badge"><?= e($p['payment_status']) ?></span></td>
                        <td>
                            <form method="post"><input type="hidden" name="op" value="delete"><input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>"><button class="btn btn-danger" data-confirm="Delete this payment record?">Delete</button></form>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$payments): ?><tr>
                        <td colspan="7" class="empty">No payments recorded.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</div>
<script>
    const s = document.getElementById('assignmentSelect'),
        a = document.getElementById('paymentAmount');
    if (s && a) s.addEventListener('change', () => {
        const o = s.options[s.selectedIndex];
        const w = Number(o.dataset.wage || 0),
            d = Math.max(1, Number(o.dataset.days || 1));
        if (w) a.value = (w * d).toFixed(2);
    });
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>