<?php
require_once '../includes/header.php';

// ?????? ???????
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$date_filter = isset($_GET['date_filter']) ? cleanInput($_GET['date_filter']) : '';

// Query ????
$sql = "SELECT f.*, c.name as client_name, c.phone as client_phone
        FROM followups f
        JOIN clients c ON f.client_id = c.id
        WHERE 1=1";

$params = [];

// ????? ?????
if ($search) {
    $sql .= " AND c.name LIKE :search";
    $params['search'] = "%$search%";
}

// ????? ?????
if ($status) {
    $sql .= " AND f.contact_status = :status";
    $params['status'] = $status;
}

// ????? ?????
switch ($date_filter) {
    case 'today':
        $sql .= " AND f.next_followup_date = CURDATE()";
        break;
    case 'week':
        $sql .= " AND f.next_followup_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'overdue':
        $sql .= " AND f.next_followup_date < CURDATE()";
        break;
}

$sql .= " ORDER BY f.next_followup_date ASC, f.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$followups = $stmt->fetchAll();

// ???? ?????????
$stats = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN contact_status = '???? ????' THEN 1 ELSE 0 END) as answered,
    SUM(CASE WHEN contact_status = '??????' THEN 1 ELSE 0 END) as waiting,
    SUM(CASE WHEN contact_status = '???? ????' THEN 1 ELSE 0 END) as no_answer,
    SUM(CASE WHEN next_followup_date = CURDATE() THEN 1 ELSE 0 END) as today,
    SUM(CASE WHEN next_followup_date < CURDATE() THEN 1 ELSE 0 END) as overdue
FROM followups")->fetch();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-telephone-fill"></i> ?????? ?????????</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> ?????? ????
    </a>
</div>

<!-- ???? ????????? -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h3 class="text-primary"><?php echo $stats['total']; ?></h3>
                <p class="mb-0">?? ?????????</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-success">
            <div class="card-body text-center">
                <h3 class="text-success"><?php echo $stats['answered']; ?></h3>
                <p class="mb-0">???? ????</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h3 class="text-warning"><?php echo $stats['waiting']; ?></h3>
                <p class="mb-0">??????</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h3 class="text-danger"><?php echo $stats['no_answer']; ?></h3>
                <p class="mb-0">???? ????</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-info">
            <div class="card-body text-center">
                <h3 class="text-info"><?php echo $stats['today']; ?></h3>
                <p class="mb-0">?????</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-dark">
            <div class="card-body text-center">
                <h3 class="text-dark"><?php echo $stats['overdue']; ?></h3>
                <p class="mb-0">??? ??????</p>
            </div>
        </div>
    </div>
</div>

<!-- ??????? -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">?????</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="??? ?????..." value="<?php echo $search; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">????? ????</label>
                <select name="status" class="form-select">
                    <option value="">???</option>
                    <?php foreach (getContactStatuses() as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $status == $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">????? ?????</label>
                <select name="date_filter" class="form-select">
                    <option value="" <?php echo $date_filter == '' ? 'selected' : ''; ?>>???</option>
                    <option value="today" <?php echo $date_filter == 'today' ? 'selected' : ''; ?>>?????</option>
                    <option value="week" <?php echo $date_filter == 'week' ? 'selected' : ''; ?>>???? ?????</option>
                    <option value="overdue" <?php echo $date_filter == 'overdue' ? 'selected' : ''; ?>>??? ??????</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i> ?????
                </button>
            </div>
            <?php if ($search || $status || $date_filter): ?>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <a href="index.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> ??? ????
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ???? ????????? -->
<div class="card">
    <div class="card-body">
        <?php if (count($followups) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="30">#</th>
                        <th>?????</th>
                        <th>????? ????</th>
                        <th>????? ????</th>
                        <th>???????</th>
                        <th>?????? ????</th>
                        <th>????? ???</th>
                        <th>??????</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($followups as $index => $followup): 
                        $isOverdue = $followup['next_followup_date'] && strtotime($followup['next_followup_date']) < strtotime('today');
                        $isToday = $followup['next_followup_date'] && $followup['next_followup_date'] == date('Y-m-d');
                    ?>
                    <tr class="<?php echo $isOverdue ? 'table-danger' : ($isToday ? 'table-warning' : ''); ?>">
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <a href="../clients/edit.php?id=<?php echo $followup['client_id']; ?>" 
                               class="text-decoration-none fw-bold">
                                <?php echo htmlspecialchars($followup['client_name']); ?>
                            </a>
                        </td>
                        <td dir="ltr">
                            <?php if ($followup['client_phone']): ?>
                                <a href="tel:<?php echo $followup['client_phone']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($followup['client_phone']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusClass = match($followup['contact_status']) {
                                '???? ????' => 'bg-success',
                                '??????' => 'bg-warning',
                                '???? ????' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo $followup['contact_status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($followup['contact_note']): ?>
                                <small><?php echo htmlspecialchars(substr($followup['contact_note'], 0, 50)); ?>
                                <?php if (strlen($followup['contact_note']) > 50): ?>...<?php endif; ?>
                                </small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($followup['next_followup_date']): ?>
                                <?php echo convertToJalali($followup['next_followup_date']); ?>
                                <?php if ($isOverdue): ?>
                                    <br><small class="text-danger">
                                        <i class="bi bi-exclamation-triangle"></i> ??? ??????
                                    </small>
                                <?php elseif ($isToday): ?>
                                    <br><small class="text-warning">
                                        <i class="bi bi-calendar-check"></i> ?????
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo convertToJalali($followup['created_at']); ?></small>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit.php?id=<?php echo $followup['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="??????">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $followup['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirmDelete('??? ?? ??? ??? ?????? ??????? ??????')"
                                   title="???">
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
        <div class="empty-state">
            <i class="bi bi-telephone"></i>
            <h4>??? ?????? ???? ???</h4>
            <p>???? ????? ????? ?????? ??? ?? ????? ????.</p>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> ?????? ????
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>