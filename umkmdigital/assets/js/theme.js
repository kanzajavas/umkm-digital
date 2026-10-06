document.addEventListener("DOMContentLoaded", function () {

    const themeBtn = document.getElementById("themeBtn");
    const themeIcon = document.getElementById("themeIcon");
    const themeText = document.getElementById("themeText");

    const savedTheme = localStorage.getItem("dashboardTheme");

    if (savedTheme === "dark") {
        document.body.classList.add("dark-mode");
    } else {
        document.body.classList.remove("dark-mode");
    }

    updateThemeButton();

    if (themeBtn) {

        themeBtn.addEventListener("click", function () {

            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {

                localStorage.setItem("dashboardTheme", "dark");

            } else {

                localStorage.setItem("dashboardTheme", "light");

            }

            updateThemeButton();

        });

    }


    function updateThemeButton() {

        if (!themeIcon) {
            return;
        }

        if (document.body.classList.contains("dark-mode")) {

            themeIcon.classList.remove("bi-moon-fill");
            themeIcon.classList.add("bi-sun-fill");

            if (themeText) {
                themeText.textContent = "Mode Terang";
            }

        } else {

            themeIcon.classList.remove("bi-sun-fill");
            themeIcon.classList.add("bi-moon-fill");

            if (themeText) {
                themeText.textContent = "Mode Gelap";
            }

        }

    }

});