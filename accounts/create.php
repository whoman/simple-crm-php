<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $initial_balance = !empty($_POST['initial_balance']) ? str_replace(',', '', $_POST['initial_balance']) : 0;
    
    if (empty($name)) {
        $message = 'نام حساب الزامی است.';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO bank_accounts (name, initial_balance) VALUES (:name, :initial_balance)");
            $stmt->execute([
                'name' => $name,
                'initial_balance' => $initial_balance
            ]);
            
            header('Location: index.php?message=' . urlencode('حساب جدید با موفقیت ایجاد شد.') . '&type=success');
            exit;
        } catch (PDOException $e) {
            $message = 'خطا در ایجاد حساب: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-circle"></i> حساب بانکی جدید</h1>
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
                    <label class="form-label">نام حساب <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required 
                           placeholder="مثال: حساب ملی، حساب پاسارگاد، ..."
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">موجودی اولیه</label>
                    <input type="text" name="initial_balance" class="form-control currency-input"
                           placeholder="0"
                           value="<?php echo isset($_POST['initial_balance']) ? htmlspecialchars($_POST['initial_balance']) : ''; ?>">
                    <small class="text-muted">موجودی حساب هنگام شروع استفاده از سیستم (به تومان)</small>
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

<?php require_once '../includes/footer.php'; ?>