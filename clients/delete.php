<?php
require_once '../includes/header.php';

// دریافت ID مشتری
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        // بررسی وجود مشتری
        $stmt = $db->prepare("SELECT name FROM clients WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $client = $stmt->fetch();
        
        if ($client) {
            // حذف مشتری
            $stmt = $db->prepare("DELETE FROM clients WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            // پیام موفقیت
            $_SESSION['message'] = 'مشتری "' . $client['name'] . '" با موفقیت حذف شد.';
            $_SESSION['message_type'] = 'success';
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'خطا در حذف مشتری: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
}

// بازگشت به لیست
header('Location: index.php');
exit;
?>
