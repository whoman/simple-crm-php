<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

// دریافت لیست مشتریان برای dropdown
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
            $stmt = $db->prepare("INSERT INTO projects 
                (name, client_id, project_type, status, start_date, end_date, total_amount, paid_amount, notes) 
                VALUES (:name, :client_id, :project_type, :status, :start_date, :end_date, :total_amount, :paid_amount, :notes)");
            
            $stmt->execute([
                'name' => $name,
                'client_id' => $client_id,
                'project_type' => $project_type,
                'status' => $status,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_amount' => $total_amount,
                'paid_amount' => $paid_amount,
                'notes' => $notes
            ]);
            
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $message = 'خطا در ثبت پروژه: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-folder-plus"></i> افزودن پروژه جدید</h1>
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
                           value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>"
                           placeholder="عنوان پروژه را وارد کنید">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">مشتری</label>
                    <select name="client_id" class="form-select">
                        <option value="">-- انتخاب مشتری --</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>" 
                                    <?php echo (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : ''; ?>>
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
                                    <?php echo (isset($_POST['project_type']) && $_POST['project_type'] == $key) ? 'selected' : ''; ?>>
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
                                    <?php echo (isset($_POST['status']) && $_POST['status'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">تاریخ شروع</label>
                    <input type="date" name="start_date" class="form-control"
                           value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d'); ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">تاریخ پایان</label>
                    <input type="date" name="end_date" class="form-control"
                           value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">مبلغ کل قرارداد</label>
                    <input type="text" name="total_amount" class="form-control currency-input" 
                           placeholder="0"
                           value="<?php echo isset($_POST['total_amount']) ? $_POST['total_amount'] : ''; ?>">
                    <small class="text-muted">به تومان</small>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">مبلغ دریافت شده</label>
                    <input type="text" name="paid_amount" class="form-control currency-input" 
                           placeholder="0"
                           value="<?php echo isset($_POST['paid_amount']) ? $_POST['paid_amount'] : ''; ?>">
                    <small class="text-muted">به تومان</small>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">یادداشت‌ها</label>
                    <textarea name="notes" class="form-control" rows="4"
                              placeholder="توضیحات تکمیلی در مورد پروژه..."><?php echo isset($_POST['notes']) ? $_POST['notes'] : ''; ?></textarea>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> ذخیره
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// اضافه کردن ویژوال feedback برای مبلغ باقی‌مانده
document.addEventListener('DOMContentLoaded', function() {
    const totalInput = document.querySelector('input[name="total_amount"]');
    const paidInput = document.querySelector('input[name="paid_amount"]');
    
    function updateRemaining() {
        const total = parseInt(totalInput.value.replace(/,/g, '') || 0);
        const paid = parseInt(paidInput.value.replace(/,/g, '') || 0);
        const remaining = total - paid;
        
        // می‌توانید اینجا یک المان برای نمایش مانده اضافه کنید
    }
    
    totalInput.addEventListener('input', updateRemaining);
    paidInput.addEventListener('input', updateRemaining);
});
</script>

<?php require_once '../includes/footer.php'; ?>
