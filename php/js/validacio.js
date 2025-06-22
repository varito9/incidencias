document.addEventListener("DOMContentLoaded", function() {
    const formulari = document.getElementById("formulariIncidencia");
    const inputs = formulari.querySelectorAll("input[required], textarea[required], select[required]");
    const errorMissatge = document.getElementById("errorMissatge");

    const nomRegex = /^[A-Za-zÀ-ÿ\s]+$/;
    const emailRegex = /^[\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,}$/;

    formulari.addEventListener("submit", function(e) {
        let valid = true;
        errorMissatge.classList.add("d-none");

        inputs.forEach(input => {
            input.classList.remove("is-invalid");
        });

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add("is-invalid");
                valid = false;
            } else if (input.name === "nom" && !nomRegex.test(input.value)) {
                input.classList.add("is-invalid");
                valid = false;
            } else if (input.name === "cognom" && !nomRegex.test(input.value)) {
                input.classList.add("is-invalid");
                valid = false;
            } else if (input.name === "email" && !emailRegex.test(input.value)) {
                input.classList.add("is-invalid");
                valid = false;
            }
        });

        if (!valid) {
            e.preventDefault();
            errorMissatge.classList.remove("d-none");
            errorMissatge.setAttribute("aria-live", "polite");

            inputs.forEach(input => {
                if (input.classList.contains("is-invalid")) {
                    let errorMessage = "";

                    if (input.name === "nom" || input.name === "cognom") {
                        errorMessage = "El nom i cognom només poden contenir lletres i espais.";
                    } else if (input.name === "email") {
                        errorMessage = "El correu electrònic no és vàlid.";
                    } else if (!input.value.trim()) {
                        errorMessage = "Tots els camps són obligatoris.";
                    }

                    errorMissatge.textContent = errorMessage;
                }
            });
        }
    });

    inputs.forEach(input => {
        input.addEventListener("focus", () => {
            input.classList.add("focus-animat");
        });
        input.addEventListener("blur", () => {
            input.classList.remove("focus-animat");
        });
    });
});