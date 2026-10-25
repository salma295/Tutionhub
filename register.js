
const roleSelect = document.getElementById("role");
const tutorFields = document.getElementById("tutor-fields");

const tutorInputs = [
    document.getElementById("qualification"),
    document.getElementById("experience_years"),
    document.getElementById("location")
];

function updateRegistrationForm() {
    const isTutor = roleSelect.value === "tutor";

    tutorFields.hidden = !isTutor;

    tutorInputs.forEach((input) => {
        input.required = isTutor;
    });
}

roleSelect.addEventListener("change", updateRegistrationForm);

updateRegistrationForm();
