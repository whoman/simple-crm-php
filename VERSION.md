<?php
/**
 * Simple CRM System - Complete PHP Project
 * 
 * A comprehensive Customer Relationship Management system
 * built with pure PHP and MySQL for managing clients,
 * projects, tasks, finances, bank accounts, and followups.
 * 
 * Features:
 * - Complete Persian RTL interface
 * - Responsive design with Bootstrap 5
 * - Secure PDO database connections
 * - CRUD operations for all modules
 * - Advanced search and filtering
 * - Financial management and reporting
 * - Customer followup system
 * - Bank account management
 * 
 * Modules:
 * ? Clients Management (Complete)
 * ? Projects Management (Complete)
 * ? Tasks Management (Complete)
 * ? Finance Management (Complete)
 * ? Bank Accounts Management (Complete)
 * ? Customer Followups (Complete)
 * 
 * @version 1.0.0
 * @author Hooman
 * @license MIT
 * @created 2025-07-20
 */

require_once 'includes/header.php';

// ?????? ???? ???? ?????
$totalClients = $db->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$totalProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$totalTasks = $db->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
$totalTransactions = $db->query("SELECT COUNT(*) FROM finance_transactions")->fetchColumn();
$totalAccounts = $db->query("SELECT COUNT(*) FROM bank_accounts")->fetchColumn();
$totalFollowups = $db->query("SELECT COUNT(*) FROM followups")->fetchColumn();

echo "
<div class='container-fluid py-5'>
    <div class='row justify-content-center'>
        <div class='col-md-8 text-center'>
            <h1 class='display-4 mb-4'>? ????? CRM ???? ??!</h1>
            <p class='lead'>???? ???????? ?? ?????? ?????????? ????</p>
            
            <div class='row mt-5'>
                <div class='col-md-2'>
                    <div class='card bg-primary text-white'>
                        <div class='card-body text-center'>
                            <h3>$totalClients</h3>
                            <p class='mb-0'>?????</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-2'>
                    <div class='card bg-success text-white'>
                        <div class='card-body text-center'>
                            <h3>$totalProjects</h3>
                            <p class='mb-0'>?????</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-2'>
                    <div class='card bg-warning text-white'>
                        <div class='card-body text-center'>
                            <h3>$totalTasks</h3>
                            <p class='mb-0'>???</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-2'>
                    <div class='card bg-info text-white'>
                        <div class='card-body text-center'>
                            <h3>$totalTransactions</h3>
                            <p class='mb-0'>??????</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-2'>
                    <div class='card bg-secondary text-white'>
                        <div class='card-body text-center'>
                            <h3>$totalAccounts</h3>
                            <p class='mb-0'>????</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-2'>
                    <div class='card bg-dark text-white'>
                        <div class='card-body text-center'>
                            <h3>$totalFollowups</h3>
                            <p class='mb-0'>??????</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class='mt-5'>
                <h3>? ????????? ????? ???:</h3>
                <ul class='list-unstyled mt-3'>
                    <li class='py-1'><i class='bi bi-check-circle text-success'></i> ?????? ???????</li>
                    <li class='py-1'><i class='bi bi-check-circle text-success'></i> ?????? ????????</li>
                    <li class='py-1'><i class='bi bi-check-circle text-success'></i> ?????? ?????</li>
                    <li class='py-1'><i class='bi bi-check-circle text-success'></i> ?????? ???? ????</li>
                    <li class='py-1'><i class='bi bi-check-circle text-success'></i> ?????? ???????? ?????</li>
                    <li class='py-1'><i class='bi bi-check-circle text-success'></i> ????? ?????? ???????</li>
                </ul>
            </div>
            
            <div class='mt-4'>
                <a href='https://github.com/whoman/simple-crm-php' class='btn btn-primary btn-lg me-3' target='_blank'>
                    <i class='bi bi-github'></i> ?????? ?? GitHub
                </a>
                <a href='clients/' class='btn btn-success btn-lg'>
                    <i class='bi bi-arrow-left'></i> ???? ???????
                </a>
            </div>
        </div>
    </div>
</div>
";

require_once 'includes/footer.php';
?>