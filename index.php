
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuitionHub | Find Your Perfect Tutor</title>
    <meta name="description"
          content="Discover tutors by subject and location with TuitionHub.">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
    <a href="#" class="brand">
        <span class="brand-icon">T</span>
        Tuition<span>Hub</span>
    </a>

    <nav class="nav-links">
        <a href="#subjects">Subjects</a>
        <a href="#tutors">Find Tutors</a>
        <a href="#how-it-works">How It Works</a>
    </nav>

    <div class="nav-actions">
        <a href="#" class="login-link">Log in</a>
        <a href="#" class="btn btn-small">Join as Tutor</a>
    </div>
</header>

<main>
    <section class="hero">
        <div class="hero-content">
            <span class="eyebrow">YOUR LEARNING, YOUR FUTURE</span>

            <h1>Find the right tutor.<br>
                <span>Unlock your potential.</span>
            </h1>

            <p class="hero-text">
                Connect with tutors who understand your goals.
                Find the right match for your subject and location.
            </p>

            <form id="searchForm" class="search-box">
                <div class="search-field">
                    <label for="subject">Subject</label>
                    <input
                        type="text"
                        id="subject"
                        placeholder="e.g. Mathematics"
                    >
                </div>

                <div class="search-field">
                    <label for="location">Location</label>
                    <input
                        type="text"
                        id="location"
                        placeholder="e.g. Dhanmondi"
                    >
                </div>

                <button type="submit" class="btn search-btn">
                    Find Tutors
                </button>
            </form>

            <p class="search-hint">
                Popular: Mathematics · English · Physics · ICT
            </p>
        </div>

        <div class="hero-visual">
            <div class="visual-card">
                <div class="book-icon">📚</div>
                <h3>Learning made personal</h3>
                <p>Discover a tutor who fits your needs.</p>
                <div class="visual-tags">
                    <span>School</span>
                    <span>College</span>
                    <span>University</span>
                </div>
            </div>
            <div class="floating-note">Your goals matter.</div>
        </div>
    </section>

    <section class="subjects section" id="subjects">
        <div class="section-heading">
            <div>
                <span class="eyebrow">EXPLORE</span>
                <h2>Find tutors by subject</h2>
            </div>
            <p>Choose a subject to start your search.</p>
        </div>

        <div class="subject-list">
            <button class="subject-chip active"
                    data-subject="">All Subjects</button>
            <button class="subject-chip"
                    data-subject="Mathematics">Mathematics</button>
            <button class="subject-chip"
                    data-subject="English">English</button>
            <button class="subject-chip"
                    data-subject="Physics">Physics</button>
            <button class="subject-chip"
                    data-subject="Chemistry">Chemistry</button>
            <button class="subject-chip"
                    data-subject="Biology">Biology</button>
            <button class="subject-chip"
                    data-subject="ICT">ICT</button>
        </div>
    </section>

    <section class="tutors section" id="tutors">
        <div class="section-heading">
            <div>
                <span class="eyebrow">GET STARTED</span>
                <h2>Explore sample tutors</h2>
            </div>
            <span id="resultCount" class="result-count"></span>
        </div>

        <div class="tutor-grid">
            <article class="tutor-card"
                     data-subjects="Mathematics Physics"
                     data-location="Dhanmondi Dhaka">
                <div class="avatar avatar-teal">AR</div>
                <span class="tutor-badge">Sample profile</span>
                <h3>Ahmed Rahman</h3>
                <p class="tutor-detail">Mathematics · Physics</p>
                <p class="tutor-location">📍 Dhanmondi, Dhaka</p>
                <div class="tutor-footer">
                    <span>৳5,000/month</span>
                    <button class="outline-btn contact-btn"
                            type="button">View Profile</button>
                </div>
            </article>

            <article class="tutor-card"
                     data-subjects="English"
                     data-location="Uttara Dhaka">
                <div class="avatar avatar-blue">NS</div>
                <span class="tutor-badge">Sample profile</span>
                <h3>Nusrat Sultana</h3>
                <p class="tutor-detail">English · Grammar</p>
                <p class="tutor-location">📍 Uttara, Dhaka</p>
                <div class="tutor-footer">
                    <span>৳4,000/month</span>
                    <button class="outline-btn contact-btn"
                            type="button">View Profile</button>
                </div>
            </article>

            <article class="tutor-card"
                     data-subjects="Chemistry Biology"
                     data-location="Mirpur Dhaka">
                <div class="avatar avatar-purple">FH</div>
                <span class="tutor-badge">Sample profile</span>
                <h3>Farhan Hossain</h3>
                <p class="tutor-detail">Chemistry · Biology</p>
                <p class="tutor-location">📍 Mirpur, Dhaka</p>
                <div class="tutor-footer">
                    <span>৳4,500/month</span>
                    <button class="outline-btn contact-btn"
                            type="button">View Profile</button>
                </div>
            </article>
        </div>

        <p id="emptyMessage" class="empty-message" hidden>
            No matching sample tutors found. Try another subject or location.
        </p>
    </section>

    <section class="how section" id="how-it-works">
        <span class="eyebrow">SIMPLE AND EASY</span>
        <h2>How TuitionHub works</h2>

        <div class="steps">
            <div class="step">
                <span>01</span>
                <h3>Search</h3>
                <p>Choose a subject and preferred location.</p>
            </div>
            <div class="step">
                <span>02</span>
                <h3>Compare</h3>
                <p>Explore tutor profiles and experience.</p>
            </div>
            <div class="step">
                <span>03</span>
                <h3>Connect</h3>
                <p>Find a suitable tutor for your learning goals.</p>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <a href="#" class="brand footer-brand">
        Tuition<span>Hub</span>
    </a>
    <p>Helping learners find the right guidance.</p>
    <p class="copyright">© 2026 TuitionHub. All rights reserved.</p>
</footer>

<script src="script.js"></script>
</body>
</html>
