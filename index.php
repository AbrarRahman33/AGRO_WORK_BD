<?php
require_once __DIR__ . '/config/app.php';
if (current_user()) redirect('dashboard.php');
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>
<nav class="public-nav">
    <a class="brand" href="<?= e(base_url()) ?>"><span class="brand-icon">A</span><span><strong>Agro Work</strong><small>Smart Workforce</small></span></a>
    <div class="actions"><a class="btn btn-secondary" href="<?= e(base_url('login.php')) ?>">Login</a><a class="btn btn-primary" href="<?= e(base_url('register.php')) ?>">Create account</a></div>
</nav>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">Agricultural workforce marketplace</span>
        <h1>Find farm work. <span>Hire reliable workers.</span></h1>
        <p>Agro Work connects farmers who need seasonal agricultural workers with drivers/workers looking for jobs. Farmers post jobs with wage, dates and required workers; drivers can accept suitable jobs instantly.</p>
        <div class="actions"><a class="btn btn-primary" href="<?= e(base_url('register.php')) ?>">Get Started</a><a class="btn btn-secondary" href="<?= e(base_url('login.php')) ?>">Sign In</a></div>
    </div>
    <div class="hero-panel">
        <span class="badge">How it works</span>
        <div class="job-preview"><strong>1. Farmer posts a job</strong><small>Farm, job type, workers needed, wage/day, start & end date.</small></div>
        <div class="job-preview"><strong>2. Driver accepts</strong><small>An assignment is created automatically. No manual ID entry.</small></div>
        <div class="job-preview"><strong>3. Work & payment</strong><small>Track assignment status and record payment after the work.</small></div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>