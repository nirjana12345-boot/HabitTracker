document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("searchHistory");
    const statusFilter = document.getElementById("statusFilter");

    function filterTable() {

        const searchValue = searchInput ? searchInput.value.toLowerCase() : "";
        const statusValue = statusFilter ? statusFilter.value : "";

        document.querySelectorAll(".history-row").forEach(function (row) {

            const text = row.innerText.toLowerCase();
            const badge = row.querySelector(".status-badge");

            const status = badge
                ? badge.innerText.trim()
                : "";

            const matchSearch = text.includes(searchValue);
            const matchStatus =
                statusValue === "" || status === statusValue;

            row.style.display =
                (matchSearch && matchStatus)
                ? ""
                : "none";

        });

    }

    if (searchInput) {
        searchInput.addEventListener("keyup", filterTable);
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", filterTable);
    }

});