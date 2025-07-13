<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

// دریافت ID پروژه
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// دریافت اطلاعات پروژه
$stmt = $db->prepare("SELECT * FROM projects WHERE id = :id");
$stmt->execute(['id' => $id]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: index.php');
    exit;
}

// دریافت لیست مشتریان
$stmt = $db->query("SELECT id, name FROM clients ORDER BY name");
$clients = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $client_id = !empty($_POST['client_id']) ? intval($_POST['client_id']) : null;
    $project_type = cleanInput($_POST['project_type']);
    $status = cleanInput($_POST['status']);
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $total_amount = !empty($_POST['total_amount']) ? str_replace(',', '', $_POST['total_amount']) : 0;
    $paid_amount = !empty($_POST['paid_amount']) ? str_replace(',', '', $_POST['paid_amount']) : 0;
    $notes = cleanInput($_POST['notes']);
    
    if (empty($name)) {
        $message = 'نام پروژه الزامی است.';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $db->prepare("UPDATE projects SET 
                name = :name,
                client_id = :client_id,
                project_type = :project_type,
                status = :status,
                start_date = :start_date,
                end_date = :end_date,
                total_amount = :total_amount,
                paid_amount = :paid_amount,
                notes = :notes
                WHERE id = :id");
            
            $stmt->execute([
                'name' => $name,
                'client_id' => $client_id,
                'project_type' => $project_type,
                'status' => $status,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_amount' => $total_amount,
                'paid_amount' => $paid_amount,
                'notes' => $notes,
                'id' => $id
            ]);
            
            $message = 'اطلاعات پروژه با موفقیت بروزرسانی شد.';
            $messageType = 'success';
            
            // بروزرسانی اطلاعات نمایشی
            $project = array_merge($project, $_POST);
            $project['total_amount'] = $total_amount;
            $project['paid_amount'] = $paid_amount;
        } catch (PDOException $e) {
            $message = 'خطا در بروزرسانی پروژه: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> ویرایش پروژه</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i> بازگشت
    </a>
</div>

<?php if ($message): ?>
    <?php echo showMessage($messageType, $message); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">نام پروژه <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required 
                           value="<?php echo htmlspecialchars($project['name']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">مشتری</label>
                    <select name="client_id" class="form-select">
                        <option value="">-- انتخاب مشتری --</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>" 
                                    <?php echo $project['client_id'] == $client['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">نوع پروژه <span class="text-danger">*</span></label>
                    <select name="project_type" class="form-select" required>
                        <?php foreach (getProjectTypes() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo $project['project_type'] == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select" required>
                        <?php foreach (getProjectStatuses() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo $project['status'] == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">تاریخ شروع</label>
                    <input type="date" name="start_date" class="form-control"
                           value="<?php echo $project['start_date']; ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">تاریخ پایان</label>
                    <input type="date" name="end_date" class="form-control"
                           value="<?php echo $project['end_date']; ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">مبلغ کل قرارداد</label>
                    <input type="text" name="total_amount" class="form-control currency-input" 
                           value="<?php echo number_format($project['total_amount'], 0, '.', ','); ?>">
                    <small class="text-muted">به تومان</small>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">مبلغ دریافت شده</label>
                    <input type="text" name="paid_amount" class="form-control currency-input" 
                           value="<?php echo number_format($project['paid_amount'], 0, '.', ','); ?>">
                    <small class="text-muted">به تومان</small>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">یادداشت‌ها</label>
                    <textarea name="notes" class="form-control" rows="4"><?php echo htmlspecialchars($project['notes']); ?></textarea>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>مانده دریافتی:</strong> 
                <?php echo formatMoney($project['total_amount'] - $project['paid_amount']); ?>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> بروزرسانی
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<!-- اطلاعات مرتبط -->
<div class="row mt-4">
    <!-- کارهای پروژه -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-task"></i> کارهای پروژه
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT * FROM tasks WHERE project_id = :project_id ORDER BY deadline");
                $stmt->execute(['project_id' => $id]);
                $tasks = $stmt->fetchAll();
                
                if (count($tasks) > 0):
                ?>
                <ul class="list-group">
                    <?php foreach ($tasks as $task): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                            <br>
                            <small class="text-muted">
                                مهلت: <?php echo $task['deadline'] ? convertToJalali($task['deadline']) : 'ندارد'; ?>
                            </small>
                        </div>
                        <span class="badge <?php echo getStatusBadgeClass($task['status'], 'task'); ?>">
                            <?php echo $task['status']; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted mb-0">هیچ کاری برای این پروژه ثبت نشده است.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- تراکنش‌های مالی -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cash-stack"></i> تراکنش‌های مالی
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT ft.*, ba.name as account_name 
                                      FROM finance_transactions ft
                                      LEFT JOIN bank_accounts ba ON ft.bank_account_id = ba.id
                                      WHERE ft.project_id = :project_id 
                                      ORDER BY ft.transaction_date DESC");
                $stmt->execute(['project_id' => $id]);
                $transactions = $stmt->fetchAll();
                
                if (count($transactions) > 0):
                ?>
                <ul class="list-group">
                    <?php foreach ($transactions as $trans): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span class="<?php echo $trans['transaction_type'] == 'دریافت' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $trans['transaction_type'] == 'دریافت' ? '+' : '-'; ?>
                                <?php echo formatMoney($trans['amount']); ?>
                            </span>
                            <small><?php echo convertToJalali($trans['transaction_date']); ?></small>
                        </div>
                        <small class="text-muted">
                            <?php echo $trans['payment_method']; ?>
                            <?php if ($trans['account_name']): ?>
                                - <?php echo $trans['account_name']; ?>
                            <?php endif; ?>
                        </small>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted mb-0">هیچ تراکنشی برای این پروژه ثبت نشده است.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
