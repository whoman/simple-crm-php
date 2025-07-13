<?php
require_once '../includes/header.php';

// دریافت فیلترها
$type = isset($_GET['type']) ? cleanInput($_GET['type']) : '';
$account = isset($_GET['account']) ? intval($_GET['account']) : 0;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');

// Query اصلی
$sql = "SELECT ft.*, 
        c.name as client_name,
        p.name as project_name,
        ba.name as account_name
        FROM finance_transactions ft
        LEFT JOIN clients c ON ft.client_id = c.id
        LEFT JOIN projects p ON ft.project_id = p.id
        LEFT JOIN bank_accounts ba ON ft.bank_account_id = ba.id
        WHERE 1=1";

$params = [];

// فیلترها
if ($type) {
    $sql .= " AND ft.transaction_type = :type";
    $params['type'] = $type;
}

if ($account) {
    $sql .= " AND ft.bank_account_id = :account";
    $params['account'] = $account;
}

if ($date_from) {
    $sql .= " AND ft.transaction_date >= :date_from";
    $params['date_from'] = $date_from;
}

if ($date_to) {
    $sql .= " AND ft.transaction_date <= :date_to";
    $params['date_to'] = $date_to;
}

$sql .= " ORDER BY ft.transaction_date DESC, ft.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// محاسبه مجموع‌ها
$totals = $db->prepare("SELECT 
    SUM(CASE WHEN transaction_type = 'دریافت' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN transaction_type = 'هزینه' THEN amount ELSE 0 END) as total_expense
    FROM finance_transactions
    WHERE (:type = '' OR transaction_type = :type)
    AND (:account = 0 OR bank_account_id = :account)
    AND transaction_date >= :date_from
    AND transaction_date <= :date_to");
$totals->execute($params);
$summary = $totals->fetch();

// دریافت لیست حساب‌ها
$accounts = $db->query("SELECT id, name FROM bank_accounts ORDER BY name")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-cash-stack"></i> مدیریت امور مالی</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> ثبت تراکنش جدید
    </a>
</div>

<!-- خلاصه مالی -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title text-success">
                    <i class="bi bi-arrow-down-circle"></i> کل دریافتی‌ها
                </h5>
                <h3 class="mb-0"><?php echo formatMoney($summary['total_income']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body">
                <h5 class="card-title text-danger">
                    <i class="bi bi-arrow-up-circle"></i> کل هزینه‌ها
                </h5>
                <h3 class="mb-0"><?php echo formatMoney($summary['total_expense']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body">
                <h5 class="card-title text-primary">
                    <i class="bi bi-calculator"></i> تراز
                </h5>
                <h3 class="mb-0 <?php echo ($summary['total_income'] - $summary['total_expense']) >= 0 ? 'text-success' : 'text-danger'; ?>">
                    <?php echo formatMoney($summary['total_income'] - $summary['total_expense']); ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- فیلترها -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">نوع تراکنش</label>
                <select name="type" class="form-select">
                    <option value="">همه</option>
                    <?php foreach (getTransactionTypes() as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $type == $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">حساب بانکی</label>
                <select name="account" class="form-select">
                    <option value="0">همه حساب‌ها</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>" <?php echo $account == $acc['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($acc['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">از تاریخ</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">تا تاریخ</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <a href="index.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> نمایش ماه جاری
                </a>
            </div>
        </form>
    </div>
</div>

<!-- لیست تراکنش‌ها -->
<div class="card">
    <div class="card-body">
        <?php if (count($transactions) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>تاریخ</th>
                        <th>نوع</th>
                        <th>مبلغ</th>
                        <th>مشتری</th>
                        <th>پروژه</th>
                        <th>حساب</th>
                        <th>روش پرداخت</th>
                        <th>توضیحات</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $index => $trans): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo convertToJalali($trans['transaction_date']); ?></td>
                        <td>
                            <span class="badge <?php echo $trans['transaction_type'] == 'دریافت' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $trans['transaction_type']; ?>
                            </span>
                        </td>
                        <td class="text-nowrap <?php echo $trans['transaction_type'] == 'دریافت' ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $trans['transaction_type'] == 'دریافت' ? '+' : '-'; ?>
                            <?php echo formatMoney($trans['amount']); ?>
                        </td>
                        <td>
                            <?php if ($trans['client_name']): ?>
                                <a href="../clients/edit.php?id=<?php echo $trans['client_id']; ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($trans['client_name']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($trans['project_name']): ?>
                                <a href="../projects/edit.php?id=<?php echo $trans['project_id']; ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($trans['project_name']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $trans['account_name'] ? htmlspecialchars($trans['account_name']) : '<span class="text-muted">-</span>'; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo $trans['payment_method']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($trans['description']): ?>
                                <small><?php echo htmlspecialchars(substr($trans['description'], 0, 30)); ?>...</small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit.php?id=<?php echo $trans['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="ویرایش">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $trans['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirmDelete('آیا از حذف این تراکنش اطمینان دارید؟')"
                                   title="حذف">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="3">جمع صفحه:</th>
                        <th colspan="7">
                            <?php 
                            $pageIncome = array_sum(array_map(function($t) { 
                                return $t['transaction_type'] == 'دریافت' ? $t['amount'] : 0; 
                            }, $transactions));
                            $pageExpense = array_sum(array_map(function($t) { 
                                return $t['transaction_type'] == 'هزینه' ? $t['amount'] : 0; 
                            }, $transactions));
                            ?>
                            <span class="text-success">+<?php echo formatMoney($pageIncome); ?></span> | 
                            <span class="text-danger">-<?php echo formatMoney($pageExpense); ?></span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-wallet2"></i>
            <h4>هیچ تراکنشی یافت نشد</h4>
            <p>برای شروع، اولین تراکنش خود را ثبت کنید.</p>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> ثبت تراکنش
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
