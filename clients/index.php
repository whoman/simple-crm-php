<?php
require_once '../includes/header.php';

// دریافت لیست مشتریان
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';

$sql = "SELECT c.*, 
        COUNT(DISTINCT p.id) as project_count,
        COUNT(DISTINCT t.id) as task_count
        FROM clients c
        LEFT JOIN projects p ON c.id = p.client_id
        LEFT JOIN tasks t ON c.id = t.client_id
        WHERE 1=1";

$params = [];

if ($search) {
    $sql .= " AND (c.name LIKE :search OR c.phone LIKE :search)";
    $params['search'] = "%$search%";
}

if ($status) {
    $sql .= " AND c.status = :status";
    $params['status'] = $status;
}

$sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people-fill"></i> مدیریت مشتریان</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن مشتری جدید
    </a>
</div>

<!-- فیلترها -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">جستجو</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="نام یا شماره تماس..." value="<?php echo $search; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">وضعیت</label>
                <select name="status" class="form-select">
                    <option value="">همه</option>
                    <?php foreach (getClientStatuses() as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $status == $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i> جستجو
                </button>
            </div>
            <?php if ($search || $status): ?>
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

<!-- جدول مشتریان -->
<div class="card">
    <div class="card-body">
        <?php if (count($clients) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام مشتری</th>
                        <th>شماره تماس</th>
                        <th>نحوه آشنایی</th>
                        <th>وضعیت</th>
                        <th>تعداد پروژه</th>
                        <th>تعداد کار</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $index => $client): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($client['name']); ?></strong>
                            <?php if ($client['description']): ?>
                                <br><small class="text-muted">
                                    <?php echo htmlspecialchars(substr($client['description'], 0, 50)); ?>...
                                </small>
                            <?php endif; ?>
                        </td>
                        <td dir="ltr"><?php echo htmlspecialchars($client['phone']); ?></td>
                        <td><?php echo htmlspecialchars($client['how_met']); ?></td>
                        <td>
                            <span class="badge <?php echo getStatusBadgeClass($client['status'], 'client'); ?>">
                                <?php echo $client['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($client['project_count'] > 0): ?>
                                <span class="badge bg-info"><?php echo $client['project_count']; ?> پروژه</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($client['task_count'] > 0): ?>
                                <span class="badge bg-warning"><?php echo $client['task_count']; ?> کار</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo convertToJalali($client['created_at']); ?></small>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit.php?id=<?php echo $client['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="ویرایش">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $client['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirmDelete('آیا از حذف این مشتری اطمینان دارید؟')"
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
            <i class="bi bi-people"></i>
            <h4>هیچ مشتری یافت نشد</h4>
            <p>برای شروع، اولین مشتری خود را اضافه کنید.</p>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> افزودن مشتری
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
