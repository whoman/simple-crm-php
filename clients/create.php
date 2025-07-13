<?php
require_once '../includes/header.php';

$message = '';
$messageType = '';

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
            $stmt = $db->prepare("INSERT INTO clients (name, phone, how_met, status, description) 
                                  VALUES (:name, :phone, :how_met, :status, :description)");
            $stmt->execute([
                'name' => $name,
                'phone' => $phone,
                'how_met' => $how_met,
                'status' => $status,
                'description' => $description
            ]);
            
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $message = 'خطا در ثبت اطلاعات: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-plus"></i> افزودن مشتری جدید</h1>
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
                           value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>"
                           placeholder="نام کامل مشتری را وارد کنید">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">شماره تماس</label>
                    <input type="text" name="phone" class="form-control" dir="ltr"
                           value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>"
                           placeholder="09121234567">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">نحوه آشنایی</label>
                    <input type="text" name="how_met" class="form-control"
                           value="<?php echo isset($_POST['how_met']) ? $_POST['how_met'] : ''; ?>"
                           placeholder="مثال: از طریق اینستاگرام، معرفی دوستان و...">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select" required>
                        <?php foreach (getClientStatuses() as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($_POST['status']) && $_POST['status'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="توضیحات تکمیلی در مورد مشتری..."><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> ذخیره
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
