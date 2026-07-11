/**
 * Habit Tracker - Main JavaScript
 * Global functionality for all pages
 */

document.addEventListener('DOMContentLoaded', function() {


    


    

    
    // ============================================
    // 1. THEME TOGGLE (Dark/Light Mode)
    // ============================================
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        // Check saved theme preference
        const savedTheme = localStorage.getItem('habitTrackerTheme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
        }
        
        // Toggle theme on click
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('habitTrackerTheme', 'dark');
                this.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
            } else {
                localStorage.setItem('habitTrackerTheme', 'light');
                this.innerHTML = '<i class="fas fa-moon"></i> Dark Mode';
            }
        });
    }

    // ============================================
    // 2. AUTO-DISMISS ALERTS
    // ============================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Add close button to alerts
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            margin-left: auto;
            color: inherit;
            opacity: 0.6;
            padding: 0 8px;
        `;
        closeBtn.addEventListener('click', function() {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
        alert.appendChild(closeBtn);
        alert.style.display = 'flex';
        alert.style.alignItems = 'center';
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 500);
        }, 5000);
    });

    // ============================================
    // 3. CONFIRM DELETE
    // ============================================
    document.querySelectorAll('.delete-btn, .btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 
                           'Are you sure you want to delete this item? This action cannot be undone.';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ============================================
    // 4. KEYBOARD SHORTCUTS
    // ============================================
    document.addEventListener('keydown', function(e) {
        // Ctrl + N = New Habit
        if (e.ctrlKey && e.key === 'n') {
            const newHabitBtn = document.querySelector('a[href*="add_habit"]');
            if (newHabitBtn) {
                e.preventDefault();
                window.location.href = newHabitBtn.href;
            }
        }
        
        // Escape key = Close modals
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal, .modal-overlay');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });

    

    // ============================================
    // 6. TOAST NOTIFICATIONS
    // ============================================
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            background: var(--white);
            box-shadow: var(--shadow-lg);
            border-left: 4px solid ${type === 'success' ? 'var(--success)' : 'var(--danger)'};
            z-index: 9999;
            max-width: 400px;
            animation: slideIn 0.3s ease;
        `;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);

    // Expose toast function globally
    window.showToast = showToast;

    console.log('✅ Habit Tracker JavaScript loaded successfully!');
});