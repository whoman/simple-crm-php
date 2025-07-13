<?php
// Helper Functions

// تبدیل تاریخ میلادی به شمسی
function convertToJalali($date) {
    if (empty($date)) return '';
    
    $timestamp = strtotime($date);
    $jdate = jdate('Y/m/d', $timestamp);
    return $jdate;
}

// تبدیل تاریخ شمسی به میلادی
function convertToGregorian($jalaliDate) {
    if (empty($jalaliDate)) return '';
    
    $parts = explode('/', $jalaliDate);
    if (count($parts) != 3) return '';
    
    // برای سادگی، از تابع ساده استفاده می‌کنیم
    // در پروژه واقعی باید از کتابخانه مناسب استفاده کنید
    return date('Y-m-d');
}

// فرمت کردن مبلغ به صورت فارسی
function formatMoney($amount) {
    return number_format($amount, 0, '.', ',') . ' تومان';
}

// تمیز کردن ورودی‌ها
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// دریافت وضعیت‌ها به صورت آرایه
function getClientStatuses() {
    return [
        'جدید' => 'جدید',
        'در حال پیگیری' => 'در حال پیگیری',
        'پروژه فعال' => 'پروژه فعال',
        'پروژه پایان‌یافته' => 'پروژه پایان‌یافته',
        'بی‌پاسخ' => 'بی‌پاسخ'
    ];
}

function getProjectTypes() {
    return [
        'وردپرس' => 'وردپرس',
        'فروشگاه' => 'فروشگاه',
        'سئو' => 'سئو',
        'پشتیبانی' => 'پشتیبانی'
    ];
}

function getProjectStatuses() {
    return [
        'در حال انجام' => 'در حال انجام',
        'معلق' => 'معلق',
        'تمام‌شده' => 'تمام‌شده'
    ];
}

function getTaskStatuses() {
    return [
        'انجام نشده' => 'انجام نشده',
        'در حال انجام' => 'در حال انجام',
        'انجام‌شده' => 'انجام‌شده'
    ];
}

function getTransactionTypes() {
    return [
        'دریافت' => 'دریافت',
        'هزینه' => 'هزینه'
    ];
}

function getPaymentMethods() {
    return [
        'کارت' => 'کارت',
        'نقدی' => 'نقدی',
        'رمز ارز' => 'رمز ارز',
        'زرین‌پال' => 'زرین‌پال'
    ];
}

function getContactStatuses() {
    return [
        'پاسخ داده' => 'پاسخ داده',
        'منتظرم' => 'منتظرم',
        'جواب نداد' => 'جواب نداد'
    ];
}

// نمایش پیام موفقیت یا خطا
function showMessage($type, $message) {
    $class = ($type == 'success') ? 'alert-success' : 'alert-danger';
    return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
        ' . $message . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

// محاسبه مانده پروژه
function calculateRemaining($total, $paid) {
    return $total - $paid;
}

// دریافت رنگ بر اساس وضعیت
function getStatusBadgeClass($status, $type = 'client') {
    $classes = [
        'client' => [
            'جدید' => 'bg-primary',
            'در حال پیگیری' => 'bg-warning',
            'پروژه فعال' => 'bg-success',
            'پروژه پایان‌یافته' => 'bg-secondary',
            'بی‌پاسخ' => 'bg-danger'
        ],
        'project' => [
            'در حال انجام' => 'bg-primary',
            'معلق' => 'bg-warning',
            'تمام‌شده' => 'bg-success'
        ],
        'task' => [
            'انجام نشده' => 'bg-danger',
            'در حال انجام' => 'bg-warning',
            'انجام‌شده' => 'bg-success'
        ]
    ];
    
    return isset($classes[$type][$status]) ? $classes[$type][$status] : 'bg-secondary';
}

// تابع ساده برای تاریخ شمسی (برای تست)
function jdate($format, $timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }
    
    // این یک تابع ساده است، در پروژه واقعی از کتابخانه مناسب استفاده کنید
    $date = date('Y/m/d', $timestamp);
    $parts = explode('/', $date);
    
    // تبدیل ساده (نه دقیق)
    $year = intval($parts[0]) + 1379;
    
    return $year . '/' . $parts[1] . '/' . $parts[2];
}
