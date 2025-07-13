<?php
require_once '../includes/header.php';

// دریافت لیست پروژه‌ها
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$type = isset($_GET['type']) ? cleanInput($_GET['type']) : '';

$sql = "SELECT p.*, c.name as client_name,
        (p.total_amount - p.paid_amount) as remaining_amount
        FROM projects p
        LEFT JOIN clients c ON p.client_id = c.id
        WHERE 1=1";

$params = [];

if ($search) {
    $sql .= " AND (p.name LIKE :search OR c.name LIKE :search)";
    $params['search'] = "%$search%";
}

if ($status) {
    $sql .= " AND p.status = :status";
    $params['status'] = $status;
}

if ($type) {
    $sql .= " AND p.project_type = :type";
    $params['type'] = $type;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-folder-fill"></i> مدیریت پروژه‌ها</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن پروژه جدید
    </a>
</div>

<!-- فیلترها -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">جستجو</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="نام پروژه یا مشتری..." value="<?php echo $search; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">نوع پروژه</label>
                <select name="type" class="form-select">
                    <option value="">همه</option>
                    <?php foreach (getProjectTypes() as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $type == $key ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">وضعیت</label>
                <select name="status" class="form-select">
                    <option value="">همه</option>
                    <?php foreach (getProjectStatuses() as $key => $value): ?>
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
            <?php if ($search || $status || $type): ?>
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

<!-- جدول پروژه‌ها -->
<div class="card">
    <div class="card-body">
        <?php if (count($projects) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام پروژه</th>
                        <th>مشتری</th>
                        <th>نوع</th>
                        <th>وضعیت</th>
                        <th>تاریخ شروع</th>
                        <th>تاریخ پایان</th>
                        <th>مبلغ کل</th>
                        <th>دریافتی</th>
                        <th>مانده</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $index => $project): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($project['name']); ?></strong>
                        </td>
                        <td>
                            <?php if ($project['client_name']): ?>
                                <a href="../clients/edit.php?id=<?php echo $project['client_id']; ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($project['client_name']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo $project['project_type']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo getStatusBadgeClass($project['status'], 'project'); ?>">
                                <?php echo $project['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $project['start_date'] ? convertToJalali($project['start_date']) : '-'; ?>
                        </td>
                        <td>
                            <?php echo $project['end_date'] ? convertToJalali($project['end_date']) : '-'; ?>
                        </td>
                        <td class="text-nowrap">
                            <?php echo formatMoney($project['total_amount']); ?>
                        </td>
                        <td class="text-success text-nowrap">
                            <?php echo formatMoney($project['paid_amount']); ?>
                        </td>
                        <td class="<?php echo $project['remaining_amount'] > 0 ? 'text-danger' : 'text-success'; ?> text-nowrap">
                            <?php echo formatMoney($project['remaining_amount']); ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit.php?id=<?php echo $project['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="ویرایش">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $project['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirmDelete('آیا از حذف این پروژه اطمینان دارید؟')"
                                   title="حذف">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="7">مجموع:</th>
                        <th class="text-nowrap">
                            <?php 
                            $totalAmount = array_sum(array_column($projects, 'total_amount'));
                            echo formatMoney($totalAmount); 
                            ?>
                        </th>
                        <th class="text-success text-nowrap">
                            <?php 
                            $totalPaid = array_sum(array_column($projects, 'paid_amount'));
                            echo formatMoney($totalPaid); 
                            ?>
                        </th>
                        <th class="text-danger text-nowrap">
                            <?php 
                            $totalRemaining = array_sum(array_column($projects, 'remaining_amount'));
                            echo formatMoney($totalRemaining); 
                            ?>
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-folder"></i>
            <h4>هیچ پروژه‌ای یافت نشد</h4>
            <p>برای شروع، اولین پروژه خود را اضافه کنید.</p>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> افزودن پروژه
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
