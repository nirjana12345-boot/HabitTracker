document.addEventListener("DOMContentLoaded", () => {

    if (!("Notification" in window)) {
        console.log("Browser does not support notifications.");
        return;
    }

    if (Notification.permission !== "granted") {
        Notification.requestPermission();
    }

    // Stores reminders already shown today
    let shownToday = {};

    function checkReminders() {

        fetch("../habits/reminders.php")
            .then(response => response.json())
            .then(habits => {

                const now = new Date();

                const currentHour = String(now.getHours()).padStart(2, "0");
                const currentMinute = String(now.getMinutes()).padStart(2, "0");

                const currentTime = `${currentHour}:${currentMinute}`;

                console.log("Current Time:", currentTime);
                console.log(habits);

                habits.forEach(habit => {

                    if (!habit.reminder_time) return;

                    const reminderTime = habit.reminder_time.substring(0, 5);

                    const todayKey =
                        habit.habit_name + "_" +
                        now.toISOString().split("T")[0];

                    if (
                        reminderTime === currentTime &&
                        !shownToday[todayKey]
                    ) {

                        if (Notification.permission === "granted") {

                            new Notification("⏰ Habit Reminder", {
                                body: `Time for: ${habit.habit_name}`,
                                icon: "../assets/images/logo.png"
                            });

                        }

                        shownToday[todayKey] = true;

                        console.log("Reminder sent for:", habit.habit_name);
                    }

                });

            })
            .catch(error => {
                console.error("Reminder Error:", error);
            });

    }

    // Check immediately
    checkReminders();

    // Check every 30 seconds
    setInterval(checkReminders, 30000);

});