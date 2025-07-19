<?php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ?????? ??????? ????
$stmt = $db->prepare("SELECT ba.*, 
                      COUNT(ft.id) as transaction_count,
                      COALESCE(SUM(CASE WHEN ft.transaction_type = '??????' THEN ft.amount ELSE 0 END), 0) as total_income,
                      COALESCE(SUM(CASE WHEN ft.transaction_type = '?????' THEN ft.amount ELSE 0 END), 0) as total_expense
                      FROM bank_accounts ba
                      LEFT JOIN finance_transactions ft ON ba.id = ft.bank_account_id
                      WHERE ba.id = :id
                      GROUP BY ba.id");
$stmt->execute(['id' => $id]);
$account = $stmt->fetch();

if (!$account) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $db->beginTransaction();
        
        // ????? ?????????? ????? ?? ???? ???? (bank_account_id ?? NULL ????)
        $stmt = $db->prepare("UPDATE finance_transactions SET bank_account_id = NULL WHERE bank_account_id = :id");
        $stmt->execute(['id' => $id]);
        
        // ??? ???? ?? ??? ????
        $stmt = $db->prepare("DELETE FROM bank_accounts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $db->commit();
        
        header('Location: index.php?message=' . urlencode('???? ?? ?????? ??? ??.') . '&type=success');
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        $error = '??? ?? ??? ????: ' . $e->getMessage();
    }
}

$current_balance = $account['initial_balance'] + $account['total_income'] - $account['total_expense'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-trash"></i> ??? ???? ?????</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i> ??????
    </a>
</div>

<?php if (isset($error)): ?>
    <?php echo showMessage('danger', $error); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            ??? ?? ??? ??? ???? ????? ??????? ?????? ??? ??? ???? ?????? ????.
            <?php if ($account['transaction_count'] > 0): ?>
                <br><strong>????:</strong> ??? ???? ????? <?php echo $account['transaction_count']; ?> ?????? ??? ?? ?? ?? ???? ?????? ????? ?? ???? ??? ??????.
            <?php endif; ?>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="120">??? ????:</th>
                        <td><?php echo htmlspecialchars($account['name']); ?></td>
                    </tr>
                    <tr>
                        <th>?????? ?????:</th>
                        <td><?php echo formatMoney($account['initial_balance']); ?></td>
                    </tr>
                    <tr>
                        <th>?? ??????????:</th>
                        <td class="text-success"><?php echo formatMoney($account['total_income']); ?></td>
                    </tr>
                    <tr>
                        <th>?? ????????:</th>
                        <td class="text-danger"><?php echo formatMoney($account['total_expense']); ?></td>
                    </tr>
                    <tr>
                        <th>?????? ????:</th>
                        <td class="fw-bold <?php echo $current_balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo formatMoney($current_balance); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>????? ??????:</th>
                        <td><?php echo $account['transaction_count']; ?> ????</td>
                    </tr>
                    <tr>
                        <th>????? ?????:</th>
                        <td><?php echo convertToJalali($account['created_at']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <form method="POST" action="">
            <div class="d-flex gap-2 mt-4">
                <button type="submit" name="confirm_delete" class="btn btn-danger">
                    <i class="bi bi-trash"></i> ???? ??? ??
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> ??????
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>