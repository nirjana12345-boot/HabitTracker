/**
 * Auth Page JavaScript
 * Handles password toggle, password requirements, and confirm password validation
 */

document.addEventListener('DOMContentLoaded', function() {
  
       // ============================================
// 1. PASSWORD TOGGLE FOR LOGIN PAGE
// ============================================
     
      const toggleLoginPassword = document.getElementById("toggleLoginPassword");
const loginPassword = document.getElementById("loginPassword");

if (toggleLoginPassword && loginPassword) {
    toggleLoginPassword.addEventListener("click", function () {
        loginPassword.type =
            loginPassword.type === "password" ? "text" : "password";

        const icon = this.querySelector("i");
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
    });
}

      // ============================================
// 2. PASSWORD TOGGLE FOR REGISTER PAGE
// ============================================

// Register Password
const toggleRegisterPassword = document.getElementById('toggleRegisterPassword');
const registerPassword = document.getElementById('registerPassword');

if (toggleRegisterPassword && registerPassword) {
    toggleRegisterPassword.addEventListener('click', function () {

        registerPassword.type =
            registerPassword.type === 'password' ? 'text' : 'password';

        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
}


const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
const confirmPassword = document.getElementById('confirmPassword');

if (toggleConfirmPassword && confirmPassword) {
    toggleConfirmPassword.addEventListener('click', function () {

        confirmPassword.type =
            confirmPassword.type === 'password' ? 'text' : 'password';

        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
}



    
    // ============================================
    // 3. PASSWORD STRENGTH REQUIREMENTS
    // ============================================
    const password = document.getElementById('registerPassword');
    if (password) {
        const reqLength = document.getElementById('reqLength');
        const reqUppercase = document.getElementById('reqUppercase');
        const reqLowercase = document.getElementById('reqLowercase');
        const reqNumber = document.getElementById('reqNumber');
        
        if (reqLength && reqUppercase && reqLowercase && reqNumber) {
            password.addEventListener('input', function() {
                const val = this.value;
                
                // Check length
                if (val.length >= 6) {
                    reqLength.className = 'valid';
                    reqLength.innerHTML = '<i class="fas fa-check-circle"></i> At least 6 characters';
                } else {
                    reqLength.className = 'invalid';
                    reqLength.innerHTML = '<i class="fas fa-circle"></i> At least 6 characters';
                }
                
                // Check uppercase
                if (/[A-Z]/.test(val)) {
                    reqUppercase.className = 'valid';
                    reqUppercase.innerHTML = '<i class="fas fa-check-circle"></i> At least one uppercase letter';
                } else {
                    reqUppercase.className = 'invalid';
                    reqUppercase.innerHTML = '<i class="fas fa-circle"></i> At least one uppercase letter';
                }
                
                // Check lowercase
                if (/[a-z]/.test(val)) {
                    reqLowercase.className = 'valid';
                    reqLowercase.innerHTML = '<i class="fas fa-check-circle"></i> At least one lowercase letter';
                } else {
                    reqLowercase.className = 'invalid';
                    reqLowercase.innerHTML = '<i class="fas fa-circle"></i> At least one lowercase letter';
                }
                
                // Check number
                if (/[0-9]/.test(val)) {
                    reqNumber.className = 'valid';
                    reqNumber.innerHTML = '<i class="fas fa-check-circle"></i> At least one number';
                } else {
                    reqNumber.className = 'invalid';
                    reqNumber.innerHTML = '<i class="fas fa-circle"></i> At least one number';
                }
            });
        }
    }

    // ============================================
    // 4. CONFIRM PASSWORD MATCH VALIDATION
    // ============================================
           const passwordField = document.getElementById('registerPassword');
const confirmPasswordField = document.getElementById('confirmPassword');
const passwordMatchMessage = document.getElementById('passwordMatchMessage');

if (passwordField && confirmPasswordField) {

    function checkPasswordMatch() {

        if (confirmPasswordField.value === "") {
            confirmPasswordField.style.borderColor = "";
            confirmPasswordField.style.boxShadow = "";
            if (passwordMatchMessage) passwordMatchMessage.innerHTML = "";
            return;
        }

        if (passwordField.value === confirmPasswordField.value) {

            confirmPasswordField.style.borderColor = "var(--success)";
            confirmPasswordField.style.boxShadow =
                "0 0 0 3px rgba(72,187,120,.15)";

            if (passwordMatchMessage) {
                passwordMatchMessage.style.color = "var(--success)";
                passwordMatchMessage.innerHTML =
                    '<i class="fas fa-check-circle"></i> Passwords match';
            }

        } else {

            confirmPasswordField.style.borderColor = "var(--danger)";
            confirmPasswordField.style.boxShadow =
                "0 0 0 3px rgba(252,129,129,.15)";

            if (passwordMatchMessage) {
                passwordMatchMessage.style.color = "var(--danger)";
                passwordMatchMessage.innerHTML =
                    '<i class="fas fa-times-circle"></i> Passwords do not match';
            }
        }
    }

    passwordField.addEventListener('input', checkPasswordMatch);
    confirmPasswordField.addEventListener('input', checkPasswordMatch);
}

    // ============================================
    // 5. FORGOT PASSWORD HANDLER
    // ============================================
    const forgotPasswordLink = document.querySelector('.forgot-password');
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            // You can customize this to show a modal or redirect
            alert('Please contact support to reset your password.\nEmail: support@habittracker.com');
        });
    }
});