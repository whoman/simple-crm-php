                </div> <!-- end main-content -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div> <!-- end container-fluid -->
    
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">
            <i class="bi bi-code-slash"></i> 
            سیستم مدیریت ارتباط با مشتری - نسخه 1.0
        </p>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
        
        // Confirm delete
        function confirmDelete(message) {
            return confirm(message || 'آیا از حذف این مورد اطمینان دارید؟');
        }
        
        // Format currency input
        function formatCurrency(input) {
            // Remove all non-digits
            let value = input.value.replace(/\D/g, '');
            
            // Format with commas
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            
            input.value = value;
        }
        
        // Add currency formatting to all currency inputs
        document.querySelectorAll('.currency-input').forEach(function(input) {
            input.addEventListener('input', function() {
                formatCurrency(this);
            });
        });
    </script>
</body>
</html>
