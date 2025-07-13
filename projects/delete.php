<?php
require_once '../includes/header.php';

// دریافت ID پروژه
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        // بررسی وجود پروژه
        $stmt = $db->prepare("SELECT name FROM projects WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch();
        
        if ($project) {
            // حذف پروژه (کارها و تراکنش‌ها به صورت CASCADE حذف می‌شوند)
            $stmt = $db->prepare("DELETE FROM projects WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            // پیام موفقیت
            $_SESSION['message'] = 'پروژه "' . $project['name'] . '" با موفقیت حذف شد.';
            $_SESSION['message_type'] = 'success';
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'خطا در حذف پروژه: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
}

// بازگشت به لیست
header('Location: index.php');
exit;
?>
