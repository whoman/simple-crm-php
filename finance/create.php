<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

// دریافت لیست‌های مورد نیاز
$clients = $db->query("SELECT id, name FROM clients ORDER BY name")->fetchAll();
$projects = $db->query("SELECT p.id, p.name, c.name as client_name 
                        FROM projects p 
                        LEFT JOIN clients c ON p.client_id = c.id 
                        ORDER BY p.name")->fetchAll();
$accounts = $db->query("SELECT id, name FROM bank_accounts ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_date = cleanInput($_POST['transaction_date']);
    $transaction_type = cleanInput($_POST['transaction_type']);
    $client_id = !empty($_POST['client_id']) ? intval($_POST['client_id']) : null;
    $project_id = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
    $bank_account_id = !empty($_POST['bank_account_id']) ? intval($_POST['bank_account_id']) : null;
    $amount = !empty($_POST['amount']) ? str_replace(',', '', $_POST['amount']) : 0;
    $payment_method = cleanInput($_POST['payment_method']);
    $description = cleanInput($_POST['description']);
    
    if (empty($transaction_date) || empty($transaction_type) || $amount <= 0) {
        $message = 'لطفاً تمام فیلدهای الزامی را پر کنید.';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO finance_transactions 
                (transaction_date, transaction_type, client_id, project_id, bank_account_id, 
                 amount, payment_method, description) 
                VALUES (:transaction_date, :transaction_type, :client_id, :project_id, 
                        :bank_account_id, :amount, :payment_method, :description)");
            
            $stmt->execute([
                'transaction_date' => $transaction_date,
                'transaction_type' => $transaction_type,
                'client_id' => $client_id,
                'project_id' => $project_id,
                'bank_account_id' => $bank_account_id,
                'amount' => $amount,
                'payment_method' => $payment_method,
                'description' => $description
            ]);
            
            // اگر دریافت از پروژه است، مبلغ پرداختی پروژه را بروز کن
            if ($transaction_type == 'دریافت' && $project_id) {
                $stmt = $db->prepare("UPDATE projects 
                                      SET paid_amount = paid_amount + :amount 
                                      WHERE id = :project_id");
                $stmt->execute([
                    'amount' => $amount,
                    'project_id' => $project_id
                ]);
            }
            
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $message = 'خطا در ثبت تراکنش: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-square"></i> ثبت تراکنش جدید</h1>
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
                <div class="col-md-4 mb-3">
                    <label class="form-label">تاریخ <span class="text-danger">*</span></label>
                    <input type="date" name="transaction_date" class="form-control" required 
                           value="<?php echo isset($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d'); ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">نوع تراکنش <span class="text-danger">*</span></label>
                    <select name="transaction_type" id="transaction_type" class="form-select" required>
                        <option value="">-- انتخاب کنید --</option>
                        <?php foreach (getTransactionTypes() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($_POST['transaction_type']) && $_POST['transaction_type'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">مبلغ <span class="text-danger">*</span></label>
                    <input type="text" name="amount" class="form-control currency-input" required
                           placeholder="0"
                           value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>">
                    <small class="text-muted">به تومان</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">مشتری</label>
                    <select name="client_id" id="client_id" class="form-select">
                        <option value="">-- انتخاب مشتری (اختیاری) --</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>" 
                                    <?php echo (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">پروژه</label>
                    <select name="project_id" id="project_id" class="form-select">
                        <option value="">-- انتخاب پروژه (اختیاری) --</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" 
                                    data-client="<?php echo $project['client_id'] ?? ''; ?>"
                                    <?php echo (isset($_POST['project_id']) && $_POST['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['name']); ?>
                                <?php if ($project['client_name']): ?>
                                    (<?php echo htmlspecialchars($project['client_name']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted" id="project_hint" style="display:none;">
                        <i class="bi bi-info-circle"></i> انتخاب پروژه برای دریافت‌ها، موجب بروزرسانی مبلغ دریافتی پروژه می‌شود
                    </small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">حساب بانکی</label>
                    <select name="bank_account_id" class="form-select">
                        <option value="">-- انتخاب حساب (اختیاری) --</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['id']; ?>" 
                                    <?php echo (isset($_POST['bank_account_id']) && $_POST['bank_account_id'] == $account['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($account['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">روش پرداخت</label>
                    <select name="payment_method" class="form-select" required>
                        <?php foreach (getPaymentMethods() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == $key) || (!isset($_POST['payment_method']) && $key == 'کارت') ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="توضیحات تکمیلی..."><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> ذخیره تراکنش
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // نمایش راهنما برای دریافت از پروژه
    const typeSelect = document.getElementById('transaction_type');
    const projectSelect = document.getElementById('project_id');
    const projectHint = document.getElementById('project_hint');
    
    function updateHint() {
        if (typeSelect.value === 'دریافت' && projectSelect.value) {
            projectHint.style.display = 'block';
        } else {
            projectHint.style.display = 'none';
        }
    }
    
    typeSelect.addEventListener('change', updateHint);
    projectSelect.addEventListener('change', updateHint);
    
    // انتخاب خودکار مشتری بر اساس پروژه
    projectSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const clientId = selectedOption.getAttribute('data-client');
        if (clientId) {
            document.getElementById('client_id').value = clientId;
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
