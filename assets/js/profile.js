document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".toggle-password").forEach(icon => {

        icon.addEventListener("click", function () {

            const input = document.getElementById(this.dataset.target);

            if (input.type === "password") {
                input.type = "text";
                this.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                this.classList.replace("fa-eye-slash", "fa-eye");
            }

        });

    });

});