/**
 * Settings Page JavaScript
 * Handles settings toggles, notifications, and danger zone
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // 1. TOGGLE SWITCHES
    // ============================================
    document.querySelectorAll('.toggle-switch input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const settingItem = this.closest('.setting-item');
            const settingLabel = settingItem?.querySelector('.setting-label')?.textContent || 'Setting';
            const status = this.checked ? 'Enabled' : 'Disabled';
            
            // Show a toast notification
            if (window.showToast) {
                window.showToast(`${settingLabel}: ${status}`, 'success');
            }
            
            // Log for debugging
            console.log(`${settingLabel}: ${status}`);
        });
    });

    // ============================================
    // 2. NOTIFICATION PREFERENCES
    // ============================================
    document.querySelectorAll('.pref-item input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('.pref-item')?.querySelector('label')?.textContent || 'Preference';
            const status = this.checked ? 'Enabled' : 'Disabled';
            
            // You can save this to localStorage or send to server
            localStorage.setItem(`notification_${label}`, this.checked);
            
            if (window.showToast) {
                window.showToast(`${label}: ${status}`, 'info');
            }
        });
        
        // Load saved preferences from localStorage
        const label = checkbox.closest('.pref-item')?.querySelector('label')?.textContent;
        if (label) {
            const saved = localStorage.getItem(`notification_${label}`);
            if (saved !== null) {
                checkbox.checked = saved === 'true';
            }
        }
    });

    // ============================================
    // 3. DANGER ZONE - ACCOUNT DELETION
    // ============================================
    const confirmDeleteInput = document.querySelector('input[name="confirm_delete"]');
    const deleteAccountBtn = document.querySelector('button[name="delete_account"]');
    
    if (confirmDeleteInput && deleteAccountBtn) {
        // Initially disable the delete button
        deleteAccountBtn.disabled = true;
        deleteAccountBtn.style.opacity = '0.5';
        deleteAccountBtn.style.cursor = 'not-allowed';
        
        confirmDeleteInput.addEventListener('input', function() {
            if (this.value === 'DELETE') {
                deleteAccountBtn.disabled = false;
                deleteAccountBtn.style.opacity = '1';
                deleteAccountBtn.style.cursor = 'pointer';
            } else {
                deleteAccountBtn.disabled = true;
                deleteAccountBtn.style.opacity = '0.5';
                deleteAccountBtn.style.cursor = 'not-allowed';
            }
        });
        
        // Confirm before deletion
        deleteAccountBtn.addEventListener('click', function(e) {
            if (!confirm('⚠️ WARNING: This will permanently delete your account and all associated data. This action cannot be undone. Are you sure?')) {
                e.preventDefault();
            }
        });
    }

    // ============================================
    // 4. THEME TOGGLE IN SETTINGS
    // ============================================
    const settingsThemeToggle = document.getElementById('settingsThemeToggle');
    if (settingsThemeToggle) {
        settingsThemeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const icon = this.querySelector('i');
            if (document.body.classList.contains('dark-mode')) {
                icon.className = 'fas fa-sun';
                this.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
                localStorage.setItem('habitTrackerTheme', 'dark');
            } else {
                icon.className = 'fas fa-moon';
                this.innerHTML = '<i class="fas fa-moon"></i> Toggle Theme';
                localStorage.setItem('habitTrackerTheme', 'light');
            }
        });
    }

    // ============================================
    // 5. REMINDER TIME VALIDATION
    // ============================================
    document.querySelectorAll('input[type="time"]').forEach(timeInput => {
        timeInput.addEventListener('change', function() {
            const value = this.value;
            if (value && window.showToast) {
                window.showToast(`Reminder time set to ${value}`, 'success');
            }
        });
    });

    // ============================================
    // 6. SAVE SETTINGS BUTTON
    // ============================================
    document.querySelectorAll('button[type="submit"]').forEach(button => {
        if (button.textContent.includes('Save') || button.textContent.includes('Update')) {
            button.addEventListener('click', function() {
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                this.disabled = true;
                
                // Form will submit normally
                // The loading state shows until page reloads
            });
        }
    });
});