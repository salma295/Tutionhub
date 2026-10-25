
const searchForm = document.getElementById("searchForm");
const subjectInput = document.getElementById("subject");
const locationInput = document.getElementById("location");
const subjectChips = document.querySelectorAll(".subject-chip");
const tutorCards = document.querySelectorAll(".tutor-card");
const resultCount = document.getElementById("resultCount");
const emptyMessage = document.getElementById("emptyMessage");

let selectedSubject = "";

function filterTutors() {
    const subject = selectedSubject.trim().toLowerCase();
    const location = locationInput.value.trim().toLowerCase();

    let visibleCount = 0;

    tutorCards.forEach(function (card) {
        const subjects = card.dataset.subjects.toLowerCase();
        const tutorLocation = card.dataset.location.toLowerCase();

        const matchesSubject =
            subject === "" || subjects.includes(subject);

        const matchesLocation =
            location === "" || tutorLocation.includes(location);

        const matches = matchesSubject && matchesLocation;

        card.hidden = !matches;

        if (matches) {
            visibleCount++;
        }
    });

    resultCount.textContent =
        visibleCount + (visibleCount === 1
            ? " sample tutor"
            : " sample tutors");

    emptyMessage.hidden = visibleCount !== 0;
}

searchForm.addEventListener("submit", function (event) {
    event.preventDefault();

    // A manually typed subject takes priority over a selected chip.
    selectedSubject = subjectInput.value.trim();

    subjectChips.forEach(function (chip) {
        chip.classList.toggle(
            "active",
            chip.dataset.subject.toLowerCase() ===
            selectedSubject.toLowerCase()
        );
    });

    filterTutors();

    document.getElementById("tutors").scrollIntoView({
        behavior: "smooth"
    });
});

subjectChips.forEach(function (chip) {
    chip.addEventListener("click", function () {
        selectedSubject = chip.dataset.subject;
        subjectInput.value = selectedSubject;

        subjectChips.forEach(function (item) {
            item.classList.toggle("active", item === chip);
        });

        filterTutors();
    });
});

document.querySelectorAll(".contact-btn").forEach(function (button) {
    button.addEventListener("click", function () {
        alert(
            "This is a sample tutor profile. " +
            "A full profile page will be added next."
        );
    });
});

filterTutors();
