<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ?????? ??????? ????
$stmt = $db->prepare("SELECT * FROM bank_accounts WHERE id = :id");
$stmt->execute(['id' => $id]);
$account = $stmt->fetch();

if (!$account) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $initial_balance = !empty($_POST['initial_balance']) ? str_replace(',', '', $_POST['initial_balance']) : 0;
    
    if (empty($name)) {
        $message = '??? ???? ?????? ???.';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $db->prepare("UPDATE bank_accounts SET name = :name, initial_balance = :initial_balance WHERE id = :id");
            $stmt->execute([
                'name' => $name,
                'initial_balance' => $initial_balance,
                'id' => $id
            ]);
            
            $message = '???? ?? ?????? ????????? ??.';
            $messageType = 'success';
            
            // ????????? ??????? ??????
            $account['name'] = $name;
            $account['initial_balance'] = $initial_balance;
        } catch (PDOException $e) {
            $message = '??? ?? ????????? ????: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> ?????? ???? ?????</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i> ??????
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
                    <label class="form-label">??? ???? <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required 
                           value="<?php echo htmlspecialchars($account['name']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">?????? ?????</label>
                    <input type="text" name="initial_balance" class="form-control currency-input"
                           value="<?php echo number_format($account['initial_balance'], 0, '.', ','); ?>">
                    <small class="text-muted">?????? ???? ????? ???? ??????? ?? ????? (?? ?????)</small>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> ?????????
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> ??????
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ????? ?????????? ????? -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-list-ul"></i> ?????????? ????
    </div>
    <div class="card-body">
        <?php
        $stmt = $db->prepare("SELECT ft.*, c.name as client_name, p.name as project_name 
                              FROM finance_transactions ft
                              LEFT JOIN clients c ON ft.client_id = c.id
                              LEFT JOIN projects p ON ft.project_id = p.id
                              WHERE ft.bank_account_id = :account_id 
                              ORDER BY ft.transaction_date DESC 
                              LIMIT 10");
        $stmt->execute(['account_id' => $id]);
        $transactions = $stmt->fetchAll();
        
        if (count($transactions) > 0):
        ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>?????</th>
                        <th>???</th>
                        <th>????</th>
                        <th>?????/?????</th>
                        <th>???????</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trans): ?>
                    <tr>
                        <td><?php echo convertToJalali($trans['transaction_date']); ?></td>
                        <td>
                            <span class="badge <?php echo $trans['transaction_type'] == '??????' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $trans['transaction_type']; ?>
                            </span>
                        </td>
                        <td class="<?php echo $trans['transaction_type'] == '??????' ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $trans['transaction_type'] == '??????' ? '+' : '-'; ?>
                            <?php echo formatMoney($trans['amount']); ?>
                        </td>
                        <td>
                            <?php if ($trans['project_name']): ?>
                                <small>?????: <?php echo htmlspecialchars($trans['project_name']); ?></small>
                            <?php elseif ($trans['client_name']): ?>
                                <small>?????: <?php echo htmlspecialchars($trans['client_name']); ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($trans['description']): ?>
                                <small><?php echo htmlspecialchars(substr($trans['description'], 0, 30)); ?>...</small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted mb-0">??? ??????? ???? ??? ???? ??? ???? ???.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>