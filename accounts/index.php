<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

// پردازش پیغام‌ها
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $messageType = $_GET['type'] ?? 'success';
}

// دریافت فیلترها
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// آمار کلی
$stats = [
    'total_accounts' => $db->query("SELECT COUNT(*) FROM bank_accounts")->fetchColumn(),
    'total_balance' => $db->query("SELECT SUM(initial_balance) FROM bank_accounts")->fetchColumn(),
    'total_income' => $db->query("SELECT SUM(amount) FROM finance_transactions WHERE transaction_type = 'دریافت'")->fetchColumn(),
    'total_expense' => $db->query("SELECT SUM(amount) FROM finance_transactions WHERE transaction_type = 'هزینه'")->fetchColumn()
];

// ساخت کوئری با فیلتر
$where = '';
$params = [];

if ($search) {
    $where = "WHERE name LIKE :search";
    $params['search'] = "%$search%";
}

// دریافت حساب‌ها
$sql = "SELECT ba.*, 
               COALESCE((SELECT SUM(ft.amount) FROM finance_transactions ft 
                        WHERE ft.bank_account_id = ba.id AND ft.transaction_type = 'دریافت'), 0) as total_income,
               COALESCE((SELECT SUM(ft.amount) FROM finance_transactions ft 
                        WHERE ft.bank_account_id = ba.id AND ft.transaction_type = 'هزینه'), 0) as total_expense
        FROM bank_accounts ba 
        $where 
        ORDER BY ba.name";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$accounts = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-bank"></i> حساب‌های بانکی</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> حساب جدید
    </a>
</div>

<?php if ($message): ?>
    <?php echo showMessage($messageType, $message); ?>
<?php endif; ?>

<!-- آمار کلی -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">تعداد حساب‌ها</h6>
                        <h3><?php echo number_format($stats['total_accounts']); ?></h3>
                    </div>
                    <i class="bi bi-bank fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">موجودی اولیه</h6>
                        <h3><?php echo number_format($stats['total_balance']); ?></h3>
                    </div>
                    <i class="bi bi-piggy-bank fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">کل دریافتی</h6>
                        <h3><?php echo number_format($stats['total_income']); ?></h3>
                    </div>
                    <i class="bi bi-arrow-down-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">کل هزینه</h6>
                        <h3><?php echo number_format($stats['total_expense']); ?></h3>
                    </div>
                    <i class="bi bi-arrow-up-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- فیلترها -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" 
                           placeholder="جستجو در نام حساب..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> جستجو
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> پاک کردن
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- جدول حساب‌ها -->
<div class="card">
    <div class="card-body">
        <?php if (count($accounts) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>نام حساب</th>
                            <th>موجودی اولیه</th>
                            <th>دریافتی</th>
                            <th>هزینه</th>
                            <th>مانده فعلی</th>
                            <th>تاریخ ایجاد</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                            <?php 
                            $current_balance = $account['initial_balance'] + $account['total_income'] - $account['total_expense'];
                            ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($account['name']); ?></td>
                                <td><?php echo number_format($account['initial_balance']); ?> تومان</td>
                                <td class="text-success">
                                    <?php echo number_format($account['total_income']); ?> تومان
                                </td>
                                <td class="text-danger">
                                    <?php echo number_format($account['total_expense']); ?> تومان
                                </td>
                                <td class="fw-bold <?php echo $current_balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($current_balance); ?> تومان
                                </td>
                                <td><?php echo formatDate($account['created_at']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit.php?id=<?php echo $account['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="ویرایش">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $account['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-bank display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">هیچ حسابی یافت نشد</h3>
                <p class="text-muted">
                    <?php if ($search): ?>
                        برای جستجوی "<?php echo htmlspecialchars($search); ?>" نتیجه‌ای یافت نشد.
                    <?php else: ?>
                        هنوز حساب بانکی ثبت نشده است.
                    <?php endif; ?>
                </p>
                <a href="create.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> اولین حساب را بسازید
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>