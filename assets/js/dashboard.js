// ==========================================
// Dashboard JavaScript
// assets/js/dashboard.js
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    animateStatCards();
    animateHabitCards();
    greetUser();
    updateCurrentTime();

});


// ================================
// Animate Statistics Cards
// ================================

function animateStatCards() {

    const cards = document.querySelectorAll(".stat-card");

    cards.forEach((card, index) => {

        card.style.opacity = "0";
        card.style.transform = "translateY(25px)";

        setTimeout(() => {

            card.style.transition = "0.5s ease";
            card.style.opacity = "1";
            card.style.transform = "translateY(0)";

        }, index * 150);

    });

}


// ================================
// Animate Habit Cards
// ================================

function animateHabitCards() {

    const cards = document.querySelectorAll(".habit-card");

    cards.forEach((card, index) => {

        card.style.opacity = "0";
        card.style.transform = "scale(0.95)";

        setTimeout(() => {

            card.style.transition = "0.4s ease";
            card.style.opacity = "1";
            card.style.transform = "scale(1)";

        }, index * 100);

    });

}


// ================================
// Greeting Message
// ================================

function greetUser() {

    const hour = new Date().getHours();

    let message = "Welcome!";
      if (hour < 12) {
    message = '<i class="fas fa-sun morning-icon"></i> Good Morning';
}
else if (hour < 18) {
    message = '<i class="fas fa-cloud-sun afternoon-icon"></i> Good Afternoon';
}
else {
    message = '<i class="fas fa-moon evening-icon"></i> Good Evening';
}

    const heading = document.querySelector(".dashboard-header h1");

    if (heading) {

        heading.insertAdjacentHTML(
            "beforeend",
            `<div class="greeting">${message}</div>`
        );

    }

}


// ================================
// Current Time
// ================================

function updateCurrentTime() {

    const clock = document.getElementById("currentTime");

    if (!clock) return;

    setInterval(() => {

        const now = new Date();

        clock.innerHTML = now.toLocaleTimeString();

    }, 1000);

}


// ================================
// Confirm Habit Completion
// ================================

document.addEventListener("click", function (e) {

    if (e.target.classList.contains("track-btn")) {

        e.target.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Saving...';

    }

});


// ================================
// Card Hover Effect
// ================================

document.querySelectorAll(".habit-card").forEach(card => {

    card.addEventListener("mouseenter", () => {

        card.style.transform = "translateY(-6px)";

    });

    card.addEventListener("mouseleave", () => {

        card.style.transform = "translateY(0)";

    });

});


// ================================
// Success Alert Auto Hide
// ================================

const alertBox = document.querySelector(".alert");

if (alertBox) {

    setTimeout(() => {

        alertBox.style.transition = "0.5s";
        alertBox.style.opacity = "0";

        setTimeout(() => {

            alertBox.remove();

        }, 500);

    }, 3000);

}