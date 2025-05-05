/**
 * Main JavaScript file for the application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize any components that need JavaScript
    initializeDropdowns();
    initializeModals();
    initializeAlerts();
    initializeFormValidation();
    
    // Add any page-specific initialization here
    if (document.getElementById('file-upload')) {
        initializeFileUpload();
    }
    
    if (document.querySelector('.data-table')) {
        initializeDataTables();
    }
});

/**
 * Initialize dropdown menus
 */
function initializeDropdowns() {
    const dropdownButtons = document.querySelectorAll('[data-dropdown]');
    
    dropdownButtons.forEach(button => {
        const target = document.getElementById(button.dataset.dropdown);
        
        if (target) {
            // Toggle dropdown on button click
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                target.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!target.contains(e.target) && !button.contains(e.target)) {
                    target.classList.add('hidden');
                }
            });
        }
    });
}

/**
 * Initialize modal dialogs
 */
function initializeModals() {
    const modalTriggers = document.querySelectorAll('[data-modal]');
    
    modalTriggers.forEach(trigger => {
        const modalId = trigger.dataset.modal;
        const modal = document.getElementById(modalId);
        
        if (modal) {
            // Open modal on trigger click
            trigger.addEventListener('click', function() {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            });
            
            // Close modal on close button click
            const closeButtons = modal.querySelectorAll('[data-close-modal]');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                });
            });
            
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });
            
            // Close modal on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        }
    });
}

/**
 * Initialize alert messages
 */
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        const dismissButton = alert.querySelector('[data-dismiss="alert"]');
        
        if (dismissButton) {
            dismissButton.addEventListener('click', function() {
                alert.remove();
            });
        }
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            if (alert.parentNode) {
                alert.classList.add('opacity-0');
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }
        }, 5000);
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Get all required fields
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                const errorElement = document.getElementById(`${field.id}-error`);
                
                // Clear previous error
                if (errorElement) {
                    errorElement.textContent = '';
                }
                
                // Check if field is empty
                if (!field.value.trim()) {
                    isValid = false;
                    
                    // Show error message
                    if (errorElement) {
                        errorElement.textContent = 'Bidang ini wajib diisi';
                    }
                    
                    // Add error class
                    field.classList.add('border-red-500');
                    field.classList.add('focus:border-red-500');
                    field.classList.add('focus:ring-red-200');
                } else {
                    // Remove error class
                    field.classList.remove('border-red-500');
                    field.classList.remove('focus:border-red-500');
                    field.classList.remove('focus:ring-red-200');
                }
                
                // Email validation
                if (field.type === 'email' && field.value.trim()) {
                    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                    
                    if (!emailRegex.test(field.value)) {
                        isValid = false;
                        
                        // Show error message
                        if (errorElement) {
                            errorElement.textContent = 'Email tidak valid';
                        }
                        
                        // Add error class
                        field.classList.add('border-red-500');
                        field.classList.add('focus:border-red-500');
                        field.classList.add('focus:ring-red-200');
                    }
                }
                
                // Password validation
                if (field.id === 'password' && field.value.trim()) {
                    if (field.value.length < 6) {
                        isValid = false;
                        
                        // Show error message
                        if (errorElement) {
                            errorElement.textContent = 'Password minimal 6 karakter';
                        }
                        
                        // Add error class
                        field.classList.add('border-red-500');
                        field.classList.add('focus:border-red-500');
                        field.classList.add('focus:ring-red-200');
                    }
                }
                
                // Password confirmation validation
                if (field.id === 'password_confirm' && field.value.trim()) {
                    const password = document.getElementById('password');
                    
                    if (password && field.value !== password.value) {
                        isValid = false;
                        
                        // Show error message
                        if (errorElement) {
                            errorElement.textContent = 'Password tidak cocok';
                        }
                        
                        // Add error class
                        field.classList.add('border-red-500');
                        field.classList.add('focus:border-red-500');
                        field.classList.add('focus:ring-red-200');
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Initialize file upload
 */
function initializeFileUpload() {
    const fileInput = document.getElementById('file-upload');
    const fileLabel = document.getElementById('file-upload-label');
    const fileInfo = document.getElementById('file-info');
    
    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                const fileSize = formatFileSize(fileInput.files[0].size);
                
                fileLabel.textContent = 'File dipilih';
                
                if (fileInfo) {
                    fileInfo.textContent = `${fileName} (${fileSize})`;
                    fileInfo.classList.remove('hidden');
                }
            } else {
                fileLabel.textContent = 'Pilih file';
                
                if (fileInfo) {
                    fileInfo.textContent = '';
                    fileInfo.classList.add('hidden');
                }
            }
        });
    }
}

/**
 * Initialize data tables
 */
function initializeDataTables() {
    const tables = document.querySelectorAll('.data-table');
    
    tables.forEach(table => {
        const searchInput = document.querySelector(`[data-search="${table.id}"]`);
        
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    
                    if (text.indexOf(searchValue) === -1) {
                        row.classList.add('hidden');
                    } else {
                        row.classList.remove('hidden');
                    }
                });
            });
        }
    });
}

/**
 * Format file size
 * @param {number} bytes File size in bytes
 * @param {number} precision Precision
 * @return {string} Formatted file size
 */
function formatFileSize(bytes, precision = 2) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    if (bytes === 0) return '0 B';
    
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    
    return (bytes / Math.pow(1024, i)).toFixed(precision) + ' ' + units[i];
}

/**
 * Show/hide password
 * @param {string} inputId Password input ID
 * @param {string} toggleId Toggle button ID
 */
function togglePasswordVisibility(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(toggleId);
    
    if (input && toggle) {
        if (input.type === 'password') {
            input.type = 'text';
            toggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            input.type = 'password';
            toggle.innerHTML = '<i class="fas fa-eye"></i>';
        }
    }
}

/**
 * Confirm action
 * @param {string} message Confirmation message
 * @return {boolean} True if confirmed, false otherwise
 */
function confirmAction(message = 'Apakah Anda yakin?') {
    return confirm(message);
}
