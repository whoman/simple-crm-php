<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

// دریافت ID مشتری
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// دریافت اطلاعات مشتری
$stmt = $db->prepare("SELECT * FROM clients WHERE id = :id");
$stmt->execute(['id' => $id]);
$client = $stmt->fetch();

if (!$client) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $phone = cleanInput($_POST['phone']);
    $how_met = cleanInput($_POST['how_met']);
    $status = cleanInput($_POST['status']);
    $description = cleanInput($_POST['description']);
    
    if (empty($name)) {
        $message = 'نام مشتری الزامی است.';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $db->prepare("UPDATE clients SET 
                                  name = :name, 
                                  phone = :phone, 
                                  how_met = :how_met, 
                                  status = :status, 
                                  description = :description 
                                  WHERE id = :id");
            $stmt->execute([
                'name' => $name,
                'phone' => $phone,
                'how_met' => $how_met,
                'status' => $status,
                'description' => $description,
                'id' => $id
            ]);
            
            $message = 'اطلاعات مشتری با موفقیت بروزرسانی شد.';
            $messageType = 'success';
            
            // بروزرسانی اطلاعات نمایشی
            $client['name'] = $name;
            $client['phone'] = $phone;
            $client['how_met'] = $how_met;
            $client['status'] = $status;
            $client['description'] = $description;
        } catch (PDOException $e) {
            $message = 'خطا در بروزرسانی اطلاعات: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> ویرایش مشتری</h1>
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
                    <label class="form-label">نام مشتری <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required 
                           value="<?php echo htmlspecialchars($client['name']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">شماره تماس</label>
                    <input type="text" name="phone" class="form-control" dir="ltr"
                           value="<?php echo htmlspecialchars($client['phone']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">نحوه آشنایی</label>
                    <input type="text" name="how_met" class="form-control"
                           value="<?php echo htmlspecialchars($client['how_met']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select" required>
                        <?php foreach (getClientStatuses() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo $client['status'] == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($client['description']); ?></textarea>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> بروزرسانی
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<!-- اطلاعات تکمیلی -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-folder"></i> پروژه‌های مرتبط
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT * FROM projects WHERE client_id = :client_id ORDER BY created_at DESC");
                $stmt->execute(['client_id' => $id]);
                $projects = $stmt->fetchAll();
                
                if (count($projects) > 0):
                ?>
                <ul class="list-group">
                    <?php foreach ($projects as $project): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($project['name']); ?></strong>
                            <br>
                            <small class="text-muted">
                                <?php echo $project['project_type']; ?> - 
                                <?php echo $project['status']; ?>
                            </small>
                        </div>
                        <a href="../projects/edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted mb-0">هیچ پروژه‌ای ثبت نشده است.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-telephone"></i> آخرین پیگیری‌ها
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT * FROM followups WHERE client_id = :client_id ORDER BY created_at DESC LIMIT 5");
                $stmt->execute(['client_id' => $id]);
                $followups = $stmt->fetchAll();
                
                if (count($followups) > 0):
                ?>
                <ul class="list-group">
                    <?php foreach ($followups as $followup): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-info"><?php echo $followup['contact_status']; ?></span>
                            <small><?php echo convertToJalali($followup['created_at']); ?></small>
                        </div>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($followup['contact_note']); ?>
                        </small>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted mb-0">هیچ پیگیری ثبت نشده است.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
