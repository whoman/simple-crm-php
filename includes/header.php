<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم مدیریت ارتباط با مشتری</title>
    
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/simple-crm/assets/style.css">
    
    <!-- Vazir Font -->
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,.05);
            border-radius: 10px;
        }
        .table {
            font-size: 14px;
        }
        .btn {
            font-size: 14px;
            padding: 8px 16px;
        }
        .sidebar {
            background-color: #2c3e50;
            min-height: calc(100vh - 56px);
            padding-top: 20px;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #34495e;
            border-right: 3px solid #3498db;
        }
        .sidebar i {
            margin-left: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .stat-card {
            border-right: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/simple-crm/">
                <i class="bi bi-briefcase-fill"></i> سیستم CRM
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/simple-crm/">
                            <i class="bi bi-house-fill"></i> داشبورد
                        </a>
                    </li>
                </ul>
                <span class="navbar-text">
                    <i class="bi bi-calendar"></i> <?php echo jdate('Y/m/d'); ?>
                </span>
            </div>
        </div>
    </nav>
    
    <!-- Main Container -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <a href="/simple-crm/" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && dirname($_SERVER['PHP_SELF']) == '/simple-crm' ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2"></i> داشبورد
                    </a>
                    <a href="/simple-crm/clients/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/clients/') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-people-fill"></i> مشتریان
                    </a>
                    <a href="/simple-crm/projects/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/projects/') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-folder-fill"></i> پروژه‌ها
                    </a>
                    <a href="/simple-crm/tasks/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/tasks/') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-list-task"></i> کارها
                    </a>
                    <a href="/simple-crm/finance/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/finance/') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-cash-stack"></i> امور مالی
                    </a>
                    <a href="/simple-crm/accounts/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/accounts/') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-bank"></i> حساب‌های بانکی
                    </a>
                    <a href="/simple-crm/followups/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/followups/') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-telephone-fill"></i> پیگیری‌ها
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10">
                <div class="main-content">
