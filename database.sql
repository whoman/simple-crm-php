-- Simple CRM Database Schema
-- Created for Persian CRM System

CREATE DATABASE IF NOT EXISTS simple_crm;
USE simple_crm;

-- جدول مشتریان
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    how_met TEXT,
    status ENUM('جدید', 'در حال پیگیری', 'پروژه فعال', 'پروژه پایان‌یافته', 'بی‌پاسخ') DEFAULT 'جدید',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول پروژه‌ها
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    client_id INT,
    project_type ENUM('وردپرس', 'فروشگاه', 'سئو', 'پشتیبانی') NOT NULL,
    status ENUM('در حال انجام', 'معلق', 'تمام‌شده') DEFAULT 'در حال انجام',
    start_date DATE,
    end_date DATE,
    total_amount DECIMAL(15,0) DEFAULT 0,
    paid_amount DECIMAL(15,0) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول کارها و وظایف
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    project_id INT,
    client_id INT,
    status ENUM('انجام نشده', 'در حال انجام', 'انجام‌شده') DEFAULT 'انجام نشده',
    deadline DATE,
    reminder DATETIME,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول حساب‌های بانکی
CREATE TABLE IF NOT EXISTS bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    initial_balance DECIMAL(15,0) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول تراکنش‌های مالی
CREATE TABLE IF NOT EXISTS finance_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_date DATE NOT NULL,
    transaction_type ENUM('دریافت', 'هزینه') NOT NULL,
    client_id INT,
    project_id INT,
    bank_account_id INT,
    amount DECIMAL(15,0) NOT NULL,
    payment_method ENUM('کارت', 'نقدی', 'رمز ارز', 'زرین‌پال') DEFAULT 'کارت',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول پیگیری‌ها
CREATE TABLE IF NOT EXISTS followups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    contact_status ENUM('پاسخ داده', 'منتظرم', 'جواب نداد') NOT NULL,
    contact_note TEXT,
    next_followup_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ایندکس‌ها برای بهبود کارایی
CREATE INDEX idx_projects_client ON projects(client_id);
CREATE INDEX idx_tasks_project ON tasks(project_id);
CREATE INDEX idx_tasks_client ON tasks(client_id);
CREATE INDEX idx_finance_client ON finance_transactions(client_id);
CREATE INDEX idx_finance_project ON finance_transactions(project_id);
CREATE INDEX idx_finance_account ON finance_transactions(bank_account_id);
CREATE INDEX idx_followups_client ON followups(client_id);

-- نمونه داده‌های اولیه برای تست
INSERT INTO bank_accounts (name, initial_balance) VALUES 
('حساب ملی', 5000000),
('حساب ملت', 3000000);

INSERT INTO clients (name, phone, how_met, status, description) VALUES 
('احمد رضایی', '09121234567', 'از طریق اینستاگرام', 'پروژه فعال', 'مشتری خوب و منظم'),
('شرکت نوین تجارت', '02144556677', 'معرفی دوستان', 'جدید', 'نیاز به سایت فروشگاهی دارند');
