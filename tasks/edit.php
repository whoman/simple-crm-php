<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

// دریافت ID کار
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// بررسی درخواست تکمیل سریع
if (isset($_GET['complete']) && $_GET['complete'] == 1) {
    $stmt = $db->prepare("UPDATE tasks SET status = 'انجام‌شده' WHERE id = :id");
    $stmt->execute(['id' => $id]);
    header('Location: index.php?success=1');
    exit;
}

// دریافت اطلاعات کار
$stmt = $db->prepare("SELECT * FROM tasks WHERE id = :id");
$stmt->execute(['id' => $id]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: index.php');
    exit;
}

// دریافت لیست مشتریان و پروژه‌ها
$stmt = $db->query("SELECT id, name FROM clients ORDER BY name");
$clients = $stmt->fetchAll();

$stmt = $db->query("SELECT p.id, p.name, c.name as client_name 
                    FROM projects p 
                    LEFT JOIN clients c ON p.client_id = c.id 
                    ORDER BY p.name");
$projects = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = cleanInput($_POST['title']);
    $project_id = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
    $client_id = !empty($_POST['client_id']) ? intval($_POST['client_id']) : null;
    $status = cleanInput($_POST['status']);
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
    $reminder = !empty($_POST['reminder']) ? $_POST['reminder'] : null;
    $description = cleanInput($_POST['description']);
    
    // اگر پروژه انتخاب شده، client_id را null کن
    if ($project_id) {
        $client_id = null;
    }
    
    if (empty($title)) {
        $message = 'عنوان کار الزامی است.';
        $messageType = 'danger';
    } elseif (!$project_id && !$client_id) {
        $message = 'باید یک پروژه یا مشتری انتخاب کنید.';
        $messageType = 'danger';
    } else {
        try {
            $stmt = $db->prepare("UPDATE tasks SET
                title = :title,
                project_id = :project_id,
                client_id = :client_id,
                status = :status,
                deadline = :deadline,
                reminder = :reminder,
                description = :description
                WHERE id = :id");
            
            $stmt->execute([
                'title' => $title,
                'project_id' => $project_id,
                'client_id' => $client_id,
                'status' => $status,
                'deadline' => $deadline,
                'reminder' => $reminder,
                'description' => $description,
                'id' => $id
            ]);
            
            $message = 'اطلاعات کار با موفقیت بروزرسانی شد.';
            $messageType = 'success';
            
            // بروزرسانی اطلاعات نمایشی
            $task = array_merge($task, $_POST);
        } catch (PDOException $e) {
            $message = 'خطا در بروزرسانی کار: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil"></i> ویرایش کار</h1>
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
                <div class="col-md-8 mb-3">
                    <label class="form-label">عنوان کار <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required 
                           value="<?php echo htmlspecialchars($task['title']); ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select" required>
                        <?php foreach (getTaskStatuses() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo $task['status'] == $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">مربوط به پروژه</label>
                    <select name="project_id" id="project_id" class="form-select" 
                            <?php echo $task['client_id'] ? 'disabled' : ''; ?>>
                        <option value="">-- انتخاب پروژه --</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" 
                                    <?php echo $task['project_id'] == $project['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['name']); ?>
                                <?php if ($project['client_name']): ?>
                                    (<?php echo htmlspecialchars($project['client_name']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">یا مربوط به مشتری</label>
                    <select name="client_id" id="client_id" class="form-select"
                            <?php echo $task['project_id'] ? 'disabled' : ''; ?>>
                        <option value="">-- انتخاب مشتری --</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>" 
                                    <?php echo $task['client_id'] == $client['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">مهلت انجام</label>
                    <input type="date" name="deadline" class="form-control"
                           value="<?php echo $task['deadline']; ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">یادآوری</label>
                    <input type="datetime-local" name="reminder" class="form-control"
                           value="<?php echo $task['reminder'] ? date('Y-m-d\TH:i', strtotime($task['reminder'])) : ''; ?>">
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($task['description']); ?></textarea>
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

<script>
// غیرفعال کردن انتخاب همزمان پروژه و مشتری
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_id');
    const clientSelect = document.getElementById('client_id');
    
    projectSelect.addEventListener('change', function() {
        if (this.value) {
            clientSelect.value = '';
            clientSelect.disabled = true;
        } else {
            clientSelect.disabled = false;
        }
    });
    
    clientSelect.addEventListener('change', function() {
        if (this.value) {
            projectSelect.value = '';
            projectSelect.disabled = true;
        } else {
            projectSelect.disabled = false;
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
