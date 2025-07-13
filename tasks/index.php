<?php
require_once '../includes/header.php';

// دریافت فیلترها
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$filter = isset($_GET['filter']) ? cleanInput($_GET['filter']) : 'all';

// Query اصلی
$sql = "SELECT t.*, 
        c.name as client_name,
        p.name as project_name
        FROM tasks t
        LEFT JOIN clients c ON t.client_id = c.id
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE 1=1";

$params = [];

// فیلتر جستجو
if ($search) {
    $sql .= " AND t.title LIKE :search";
    $params['search'] = "%$search%";
}

// فیلتر وضعیت
if ($status) {
    $sql .= " AND t.status = :status";
    $params['status'] = $status;
}

// فیلترهای زمانی
switch ($filter) {
    case 'today':
        $sql .= " AND t.deadline = CURDATE()";
        break;
    case 'week':
        $sql .= " AND t.deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'overdue':
        $sql .= " AND t.deadline < CURDATE() AND t.status != 'انجام‌شده'";
        break;
}

$sql .= " ORDER BY 
          CASE WHEN t.status = 'انجام‌شده' THEN 1 ELSE 0 END,
          t.deadline ASC, t.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// آمار کارها
$stats = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'انجام نشده' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'در حال انجام' THEN 1 ELSE 0 END) as progress,
    SUM(CASE WHEN status = 'انجام‌شده' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN deadline < CURDATE() AND status != 'انجام‌شده' THEN 1 ELSE 0 END) as overdue
FROM tasks")->fetch();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-list-task"></i> مدیریت کارها</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن کار جدید
    </a>
</div>

<!-- آمار کارها -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h3 class="text-primary"><?php echo $stats['total']; ?></h3>
                <p class="mb-0">کل کارها</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h3 class="text-danger"><?php echo $stats['pending']; ?></h3>
                <p class="mb-0">انجام نشده</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h3 class="text-warning"><?php echo $stats['progress']; ?></h3>
                <p class="mb-0">در حال انجام</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h3 class="text-success"><?php echo $stats['completed']; ?></h3>
                <p class="mb-0">انجام شده</p>
            </div>
        </div>
    </div>
</div>

<!-- فیلترها -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">جستجو</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="عنوان کار..." value="<?php echo $search; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">وضعیت</label>
                <select name="status" class="form-select">
                    <option value="">همه</option>
                    <?php foreach (getTaskStatuses() as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $status == $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">فیلتر زمانی</label>
                <select name="filter" class="form-select">
                    <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>همه</option>
                    <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>امروز</option>
                    <option value="week" <?php echo $filter == 'week' ? 'selected' : ''; ?>>هفته آینده</option>
                    <option value="overdue" <?php echo $filter == 'overdue' ? 'selected' : ''; ?>>عقب افتاده</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i> جستجو
                </button>
            </div>
            <?php if ($search || $status || $filter != 'all'): ?>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <a href="index.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> پاک کردن
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- لیست کارها -->
<div class="card">
    <div class="card-body">
        <?php if (count($tasks) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="30">#</th>
                        <th>عنوان</th>
                        <th>مرتبط با</th>
                        <th>وضعیت</th>
                        <th>مهلت</th>
                        <th>یادآوری</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $index => $task): 
                        $isOverdue = $task['deadline'] && strtotime($task['deadline']) < strtotime('today') && $task['status'] != 'انجام‌شده';
                    ?>
                    <tr class="<?php echo $isOverdue ? 'table-danger' : ''; ?>">
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                            <?php if ($task['description']): ?>
                                <br><small class="text-muted">
                                    <?php echo htmlspecialchars(substr($task['description'], 0, 50)); ?>...
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($task['project_name']): ?>
                                <span class="badge bg-info">
                                    <i class="bi bi-folder"></i> <?php echo htmlspecialchars($task['project_name']); ?>
                                </span>
                            <?php elseif ($task['client_name']): ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($task['client_name']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo getStatusBadgeClass($task['status'], 'task'); ?>">
                                <?php echo $task['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($task['deadline']): ?>
                                <?php echo convertToJalali($task['deadline']); ?>
                                <?php if ($isOverdue): ?>
                                    <br><small class="text-danger">
                                        <i class="bi bi-exclamation-triangle"></i> عقب افتاده
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($task['reminder']): ?>
                                <small><?php echo convertToJalali($task['reminder']); ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($task['status'] != 'انجام‌شده'): ?>
                                    <a href="edit.php?id=<?php echo $task['id']; ?>&complete=1" 
                                       class="btn btn-sm btn-success" title="تکمیل">
                                        <i class="bi bi-check2"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="edit.php?id=<?php echo $task['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="ویرایش">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $task['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirmDelete('آیا از حذف این کار اطمینان دارید؟')"
                                   title="حذف">
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
            <i class="bi bi-clipboard-check"></i>
            <h4>هیچ کاری یافت نشد</h4>
            <p>برای شروع، اولین کار خود را اضافه کنید.</p>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> افزودن کار
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
