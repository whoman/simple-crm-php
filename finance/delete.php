<?php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// دریافت اطلاعات تراکنش
$stmt = $db->prepare("SELECT ft.*, c.name as client_name, p.name as project_name 
                      FROM finance_transactions ft
                      LEFT JOIN clients c ON ft.client_id = c.id
                      LEFT JOIN projects p ON ft.project_id = p.id
                      WHERE ft.id = :id");
$stmt->execute(['id' => $id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $db->beginTransaction();
        
        // اگر تراکنش از نوع دریافت باشد، مبلغ را از پروژه کم کنیم
        if ($transaction['transaction_type'] == 'دریافت' && $transaction['project_id']) {
            $stmt = $db->prepare("UPDATE projects 
                                  SET paid_amount = paid_amount - :amount 
                                  WHERE id = :project_id");
            $stmt->execute([
                'amount' => $transaction['amount'], 
                'project_id' => $transaction['project_id']
            ]);
        }
        
        // حذف تراکنش
        $stmt = $db->prepare("DELETE FROM finance_transactions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $db->commit();
        
        header('Location: index.php?message=' . urlencode('تراکنش با موفقیت حذف شد.') . '&type=success');
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        $error = 'خطا در حذف تراکنش: ' . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-trash"></i> حذف تراکنش</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i> بازگشت
    </a>
</div>

<?php if (isset($error)): ?>
    <?php echo showMessage('danger', $error); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            آیا از حذف این تراکنش اطمینان دارید؟ این عمل قابل بازگشت نیست.
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="120">تاریخ:</th>
                        <td><?php echo formatDate($transaction['transaction_date']); ?></td>
                    </tr>
                    <tr>
                        <th>نوع:</th>
                        <td>
                            <span class="badge bg-<?php echo $transaction['transaction_type'] == 'دریافت' ? 'success' : 'danger'; ?>">
                                <?php echo $transaction['transaction_type']; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>مبلغ:</th>
                        <td class="fw-bold"><?php echo number_format($transaction['amount']); ?> تومان</td>
                    </tr>
                    <tr>
                        <th>مشتری:</th>
                        <td><?php echo $transaction['client_name'] ?: 'نامشخص'; ?></td>
                    </tr>
                    <tr>
                        <th>پروژه:</th>
                        <td><?php echo $transaction['project_name'] ?: 'نامشخص'; ?></td>
                    </tr>
                    <tr>
                        <th>روش پرداخت:</th>
                        <td><?php echo getPaymentMethods()[$transaction['payment_method']] ?? $transaction['payment_method']; ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <?php if ($transaction['description']): ?>
                    <h6>توضیحات:</h6>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($transaction['description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <form method="POST" action="">
            <div class="d-flex gap-2 mt-4">
                <button type="submit" name="confirm_delete" class="btn btn-danger">
                    <i class="bi bi-trash"></i> بله، حذف کن
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>