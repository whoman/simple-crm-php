<?php
require_once '../includes/header.php';

// دریافت ID کار
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        // بررسی وجود کار
        $stmt = $db->prepare("SELECT title FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch();
        
        if ($task) {
            // حذف کار
            $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            // پیام موفقیت
            $_SESSION['message'] = 'کار "' . $task['title'] . '" با موفقیت حذف شد.';
            $_SESSION['message_type'] = 'success';
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'خطا در حذف کار: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
}

// بازگشت به لیست
header('Location: index.php');
exit;
?>
