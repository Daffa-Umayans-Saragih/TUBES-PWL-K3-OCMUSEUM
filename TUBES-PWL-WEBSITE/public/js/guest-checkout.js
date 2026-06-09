(function () {
    const form = document.querySelector("[data-guest-checkout-form]");

    if (!form) {
        return;
    }

    const fields = {
        email: form.querySelector('[data-guest-field="email"]'),
        confirmEmail: form.querySelector('[data-guest-field="confirm_email"]'),
        firstName: form.querySelector('[data-guest-field="first_name"]'),
        lastName: form.querySelector('[data-guest-field="last_name"]'),
    };

    const submitButton = form.querySelector("[data-guest-submit]");
    const summary = document.getElementById("guest-error-summary");

    const errorNodes = {
        email: document.querySelector('[data-error-for="email"]'),
        confirmEmail: document.querySelector(
            '[data-error-for="confirm_email"]',
        ),
        firstName: document.querySelector('[data-error-for="first_name"]'),
        lastName: document.querySelector('[data-error-for="last_name"]'),
    };

    const messages = {
        emailRequired: "Email is required",
        emailInvalid: "Please enter a valid email address",
        confirmRequired: "Confirm email is required",
        confirmMismatch: "Confirm email must match email",
        firstNameRequired: "First name is required",
        firstNameMax: "First name must not exceed 100 characters",
        lastNameRequired: "Last name is required",
        lastNameMax: "Last name must not exceed 100 characters",
    };

    function getTrimmedValue(element) {
        return element.value.trim();
    }

    function validateEmail() {
        const value = getTrimmedValue(fields.email);

        if (!value) {
            return messages.emailRequired;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(value)) {
            return messages.emailInvalid;
        }

        return "";
    }

    function validateConfirmEmail() {
        const value = getTrimmedValue(fields.confirmEmail);

        if (!value) {
            return messages.confirmRequired;
        }

        if (value !== getTrimmedValue(fields.email)) {
            return messages.confirmMismatch;
        }

        return "";
    }

    function validateRequired(field, label, maxLength = null) {
        const value = getTrimmedValue(field);

        if (!value) {
            return `${label} is required`;
        }

        if (maxLength !== null && value.length > maxLength) {
            return `${label} must not exceed ${maxLength} characters`;
        }

        return "";
    }

    function setFieldState(field, errorNode, message) {
        const hasValue = getTrimmedValue(field).length > 0;
        const isTouched = field.classList.contains("touched");

        field.classList.toggle("is-invalid", Boolean(message) && isTouched);
        field.classList.toggle("is-valid", !message && hasValue);

        if (errorNode) {
            errorNode.textContent = isTouched ? message : "";
        }
    }

    function renderSummary(errors) {
        if (!summary) {
            return;
        }

        if (!errors.length) {
            summary.innerHTML = "";
            return;
        }

        summary.innerHTML = `
            <ul>
                ${errors.map((message) => `<li>${message}</li>`).join("")}
            </ul>
        `;
    }

    function validateForm() {
        const errors = [];

        const emailError = validateEmail();
        const confirmEmailError = validateConfirmEmail();
        const firstNameError = validateRequired(
            fields.firstName,
            "First name",
            100,
        );
        const lastNameError = validateRequired(
            fields.lastName,
            "Last name",
            100,
        );

        setFieldState(fields.email, errorNodes.email, emailError);
        setFieldState(
            fields.confirmEmail,
            errorNodes.confirmEmail,
            confirmEmailError,
        );
        setFieldState(fields.firstName, errorNodes.firstName, firstNameError);
        setFieldState(fields.lastName, errorNodes.lastName, lastNameError);

        [emailError, confirmEmailError, firstNameError, lastNameError].forEach(
            (message) => {
                if (message) {
                    errors.push(message);
                }
            },
        );

        renderSummary(errors);
        submitButton.disabled = errors.length > 0;

        return errors.length === 0;
    }

    function handleInput(fieldKey) {
        if (fieldKey === "email") {
            setFieldState(fields.email, errorNodes.email, validateEmail());
            setFieldState(
                fields.confirmEmail,
                errorNodes.confirmEmail,
                validateConfirmEmail(),
            );
        }

        if (fieldKey === "confirmEmail") {
            setFieldState(
                fields.confirmEmail,
                errorNodes.confirmEmail,
                validateConfirmEmail(),
            );
        }

        if (fieldKey === "firstName") {
            setFieldState(
                fields.firstName,
                errorNodes.firstName,
                validateRequired(fields.firstName, "First name", 100),
            );
        }

        if (fieldKey === "lastName") {
            setFieldState(
                fields.lastName,
                errorNodes.lastName,
                validateRequired(fields.lastName, "Last name", 100),
            );
        }

        validateForm();
    }

    fields.email.addEventListener("input", () => handleInput("email"));
    fields.email.addEventListener("blur", () => {
        fields.email.classList.add("touched");
        handleInput("email");
    });

    fields.confirmEmail.addEventListener("input", () =>
        handleInput("confirmEmail"),
    );
    fields.confirmEmail.addEventListener("blur", () => {
        fields.confirmEmail.classList.add("touched");
        handleInput("confirmEmail");
    });

    fields.firstName.addEventListener("input", () => handleInput("firstName"));
    fields.firstName.addEventListener("blur", () => {
        fields.firstName.classList.add("touched");
        handleInput("firstName");
    });

    fields.lastName.addEventListener("input", () => handleInput("lastName"));
    fields.lastName.addEventListener("blur", () => {
        fields.lastName.classList.add("touched");
        handleInput("lastName");
    });

    form.addEventListener("submit", (event) => {
        // Mark all fields as touched on submit to show errors
        Object.values(fields).forEach(field => field.classList.add("touched"));
        
        if (!validateForm()) {
            event.preventDefault();
            const firstInvalid = form.querySelector(".is-invalid");

            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });

})();
