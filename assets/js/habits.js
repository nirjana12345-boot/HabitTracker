document.addEventListener("DOMContentLoaded", () => {

    const search = document.getElementById("searchHabit");

    if (search) {

        search.addEventListener("keyup", function () {

            let value = this.value.toLowerCase();

            const cards = document.querySelectorAll(".habit-card");

            cards.forEach(card => {

                if (card.innerText.toLowerCase().includes(value)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }

            });

        });

    }

});