/**
 * ============================================
 * Calendar Page JavaScript
 * ============================================
 */

document.addEventListener("DOMContentLoaded", function () {

    // ============================================
    // Month Navigation Loading Effect
    // ============================================

    document.querySelectorAll(".nav-btn").forEach(btn => {

        btn.addEventListener("click", function () {

            const grid = document.querySelector(".calendar-grid");

            if (grid) {

                grid.style.opacity = "0.4";
                grid.style.pointerEvents = "none";

            }

        });

    });

    // ============================================
    // Habit Dropdown Auto Submit
    // ============================================

    const habitSelect = document.getElementById("habitSelect");

    if (habitSelect) {

        habitSelect.addEventListener("change", function () {

            window.location.href = this.value;

        });

    }

    // ============================================
    // Click Calendar Day
    // ============================================

    document.querySelectorAll(".day").forEach(day => {

        if (day.classList.contains("empty")) return;

        day.addEventListener("click", function () {

            const date = this.dataset.date;

            let status = "Future";

            if (this.classList.contains("completed")) {

                status = "✅ Completed";

            }

            else if (this.classList.contains("missed")) {

                status = "❌ Missed";

            }

            else if (this.classList.contains("today")) {

                status = "📅 Today";

            }

            alert(
                "Date : " + date +
                "\nStatus : " + status
            );

        });

    });

    // ============================================
    // Keyboard Navigation
    // ============================================

    document.addEventListener("keydown", function (e) {

        if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") {
            return;
        }

        if (e.key === "ArrowLeft") {

            const prev = document.querySelector(".prev-month");

            if (prev) {

                window.location.href = prev.href;

            }

        }

        if (e.key === "ArrowRight") {

            const next = document.querySelector(".next-month");

            if (next) {

                window.location.href = next.href;

            }

        }

    });

    // ============================================
    // Hover Animation
    // ============================================

    document.querySelectorAll(".day").forEach(day => {

        if (day.classList.contains("empty")) return;

        day.addEventListener("mouseenter", function () {

            this.style.transform = "scale(1.08)";

        });

        day.addEventListener("mouseleave", function () {

            this.style.transform = "scale(1)";

        });

    });

    // ============================================
    // Smooth Fade In
    // ============================================

    const container = document.querySelector(".calendar-container");

    if (container) {

        container.style.opacity = "0";

        setTimeout(() => {

            container.style.transition = "0.5s";
            container.style.opacity = "1";

        }, 100);

    }

});