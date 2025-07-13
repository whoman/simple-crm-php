<?php
require_once 'includes/header.php';

// آمار مشتریان
$stmt = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'جدید' THEN 1 ELSE 0 END) as new_clients,
    SUM(CASE WHEN status = 'پروژه فعال' THEN 1 ELSE 0 END) as active_clients
FROM clients");
$clientStats = $stmt->fetch();

// آمار پروژه‌ها
$stmt = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'در حال انجام' THEN 1 ELSE 0 END) as ongoing,
    SUM(CASE WHEN status = 'تمام‌شده' THEN 1 ELSE 0 END) as completed,
    COALESCE(SUM(total_amount), 0) as total_amount,
    COALESCE(SUM(paid_amount), 0) as total_paid
FROM projects");
$projectStats = $stmt->fetch();

// آمار کارها
$stmt = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'انجام نشده' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'انجام‌شده' THEN 1 ELSE 0 END) as completed
FROM tasks");
$taskStats = $stmt->fetch();

// محاسبه موجودی حساب‌ها
$stmt = $db->query("SELECT 
    ba.id,
    ba.name,
    ba.initial_balance,
    COALESCE(SUM(CASE WHEN ft.transaction_type = 'دریافت' THEN ft.amount ELSE 0 END), 0) as total_income,
    COALESCE(SUM(CASE WHEN ft.transaction_type = 'هزینه' THEN ft.amount ELSE 0 END), 0) as total_expense
FROM bank_accounts ba
LEFT JOIN finance_transactions ft ON ba.id = ft.bank_account_id
GROUP BY ba.id");
$accounts = $stmt->fetchAll();

// کارهای امروز
$stmt = $db->query("SELECT t.*, c.name as client_name, p.name as project_name 
FROM tasks t
LEFT JOIN clients c ON t.client_id = c.id
LEFT JOIN projects p ON t.project_id = p.id
WHERE t.deadline = CURDATE() AND t.status != 'انجام‌شده'
ORDER BY t.deadline");
$todayTasks = $stmt->fetchAll();

// پیگیری‌های امروز
$stmt = $db->query("SELECT f.*, c.name as client_name 
FROM followups f
JOIN clients c ON f.client_id = c.id
WHERE f.next_followup_date = CURDATE()
ORDER BY f.next_followup_date");
$todayFollowups = $stmt->fetchAll();
?>

<h1 class="mb-4">
    <i class="bi bi-speedometer2"></i> داشبورد
</h1>

<!-- آمار کلی -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card primary">
            <h3><?php echo $clientStats['total']; ?></h3>
            <p>کل مشتریان</p>
            <small>
                <i class="bi bi-person-plus"></i> <?php echo $clientStats['new_clients']; ?> جدید | 
                <i class="bi bi-person-check"></i> <?php echo $clientStats['active_clients']; ?> فعال
            </small>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card success">
            <h3><?php echo $projectStats['total']; ?></h3>
            <p>کل پروژه‌ها</p>
            <small>
                <i class="bi bi-play-circle"></i> <?php echo $projectStats['ongoing']; ?> در حال انجام | 
                <i class="bi bi-check-circle"></i> <?php echo $projectStats['completed']; ?> تمام‌شده
            </small>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card warning">
            <h3><?php echo $taskStats['total']; ?></h3>
            <p>کل کارها</p>
            <small>
                <i class="bi bi-clock"></i> <?php echo $taskStats['pending']; ?> در انتظار | 
                <i class="bi bi-check2-all"></i> <?php echo $taskStats['completed']; ?> انجام‌شده
            </small>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card danger">
            <h3><?php echo formatMoney($projectStats['total_amount'] - $projectStats['total_paid']); ?></h3>
            <p>مانده دریافتی</p>
            <small>
                از مجموع <?php echo formatMoney($projectStats['total_amount']); ?>
            </small>
        </div>
    </div>
</div>

<div class="row">
    <!-- کارهای امروز -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-task"></i> کارهای امروز
            </div>
            <div class="card-body">
                <?php if (count($todayTasks) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>عنوان</th>
                                    <th>مرتبط با</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayTasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td>
                                        <?php if ($task['project_name']): ?>
                                            <small class="text-muted">پروژه: <?php echo htmlspecialchars($task['project_name']); ?></small>
                                        <?php elseif ($task['client_name']): ?>
                                            <small class="text-muted">مشتری: <?php echo htmlspecialchars($task['client_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($task['status'], 'task'); ?>">
                                            <?php echo $task['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-check-circle"></i>
                        <p>هیچ کاری برای امروز ثبت نشده است.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- پیگیری‌های امروز -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-telephone"></i> پیگیری‌های امروز
            </div>
            <div class="card-body">
                <?php if (count($todayFollowups) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>مشتری</th>
                                    <th>وضعیت آخرین تماس</th>
                                    <th>یادداشت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayFollowups as $followup): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($followup['client_name']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $followup['contact_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(substr($followup['contact_note'], 0, 50)); ?>...</small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-calendar-check"></i>
                        <p>هیچ پیگیری برای امروز ثبت نشده است.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- وضعیت حساب‌ها -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bank"></i> وضعیت حساب‌های بانکی
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>نام حساب</th>
                                <th>موجودی اولیه</th>
                                <th>کل دریافتی‌ها</th>
                                <th>کل هزینه‌ها</th>
                                <th>موجودی فعلی</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalBalance = 0;
                            foreach ($accounts as $account): 
                                $currentBalance = $account['initial_balance'] + $account['total_income'] - $account['total_expense'];
                                $totalBalance += $currentBalance;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($account['name']); ?></td>
                                <td><?php echo formatMoney($account['initial_balance']); ?></td>
                                <td class="text-success">+<?php echo formatMoney($account['total_income']); ?></td>
                                <td class="text-danger">-<?php echo formatMoney($account['total_expense']); ?></td>
                                <td>
                                    <strong class="<?php echo $currentBalance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo formatMoney($currentBalance); ?>
                                    </strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th colspan="4">مجموع کل:</th>
                                <th class="<?php echo $totalBalance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo formatMoney($totalBalance); ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
