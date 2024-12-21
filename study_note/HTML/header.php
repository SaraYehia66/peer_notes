<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it's not already active
}
// Get the current file name (e.g., "aboutUs.php")
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <title>PeerNotes</title>
</head>

<body>
    <header class="navbar">
        <a href="#" class="logo"><i class="fa-solid fa-book-open-reader"></i> PeerNotes</a>
        <nav id="navLinks" class="nav-links">
            <a href="home.php" class="<?php echo $currentPage == 'home.php' ? 'active' : ''; ?>">Home</a>
            <a href="aboutUs.php" class="<?php echo $currentPage == 'aboutUs.php' ? 'active' : ''; ?>">About us</a>
            <a href="notes.php" class="<?php echo $currentPage == 'notes.php' ? 'active' : ''; ?>">Notes</a>
            <a href="contact.php" class="<?php echo $currentPage == 'contact.php' ? 'active' : ''; ?>">Contact</a>

            <!-- Check if user is signed in -->
            <?php if (isset($_SESSION['user_email'])): ?>
                <a href="#" style="pointer-events: none; color: green;">Welcome,
                    <?php echo htmlspecialchars($_SESSION['user_email']); ?></a>
                <a href="logout.php" class="<?php echo $currentPage == 'logout.php' ? 'active' : ''; ?>">Logout</a>
            <?php else: ?>
                <a href="signIn.php" class="<?php echo $currentPage == 'signIn.php' ? 'active' : ''; ?>">Sign In</a>
                <a href="signUp.php" class="<?php echo $currentPage == 'signUp.php' ? 'active' : ''; ?>">Register</a>
            <?php endif; ?>
            <a href="#" onclick="toggleDashboard()" class="dashboard-link">
                <i class="fa-solid fa-user"></i>
            </a>
        </nav>

        <div class="menu-icon">
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>
    </header>

    <!-- Dashboard Box Content -->
    <div id="dashboardBox" class="dashboard-box">
        <?php if (isset($_SESSION['email'], $_SESSION['Fname'], $_SESSION['Lname'])): ?>
            <h2>Welcome to Your Dashboard</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['Fname'] . ' ' . $_SESSION['Lname']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            <button onclick="toggleChangePasswordForm()" class="btn">Change Password</button>

            <!-- Logout link -->
            <a href="logout.php" class="btn" style="display: block; margin-top: 10px; text-align: center;">Log Out</a>

            <!-- Hidden Change Password Form -->
            <div id="changePasswordFormContainer" class="change-password-container" style="display: none;">
                <h3>Change Password</h3>
                <form id="changePasswordForm">
                    <label for="oldPassword">Old Password</label>
                    <input type="password" id="oldPassword" name="oldPassword" required />

                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required />

                    <label for="confirmNewPassword">Confirm New Password</label>
                    <input type="password" id="confirmNewPassword" name="confirmNewPassword" required />

                    <button type="submit" class="btn">Change Password</button>
                </form>
                <p id="feedbackMessage" style="color: green;"></p>
            </div>
        <?php else: ?>
            <p>You are not logged in. Please <a href="signIn.php">Sign In</a> to view your dashboard.</p>
        <?php endif; ?>
    </div>


    <script>
        // JavaScript to toggle the dashboard box visibility
        const dashboardBox = document.getElementById('dashboardBox');
        const navLinks = document.getElementById('navLinks');
        // Function to toggle the dashboard box
        function toggleDashboard() {
            const dashboardBox = document.getElementById('dashboardBox');
            dashboardBox.classList.toggle('active');
            navLinks.classList.remove('active');

            // Add a click event listener to the document if the dashboard box is active
            if (dashboardBox.classList.contains('active')) {
                document.addEventListener('click', closeDashboardOnOutsideClick);
            }
        }

        // Function to close the dashboard box when clicking outside
        function closeDashboardOnOutsideClick(event) {
            const dashboardBox = document.getElementById('dashboardBox');
            const dashboardLink = document.querySelector('.dashboard-link');

            // Check if the click was outside the dashboard box and the dashboard link
            if (
                !dashboardBox.contains(event.target) &&
                !dashboardLink.contains(event.target)
            ) {
                dashboardBox.classList.remove('active');
                document.removeEventListener('click', closeDashboardOnOutsideClick); // Remove the event listener
            }
        }

        // JavaScript to toggle the menu (existing functionality)
        const menuBtn = document.getElementById('menu-btn');
        menuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        function toggleChangePasswordForm() {
            const formContainer = document.getElementById('changePasswordFormContainer');
            formContainer.style.display = formContainer.style.display === 'none' ? 'block' : 'none';
        }

        document.getElementById('changePasswordForm').addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent full-page submission
            const oldPassword = document.getElementById('oldPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmNewPassword = document.getElementById('confirmNewPassword').value;

            if (newPassword !== confirmNewPassword) {
                document.getElementById('feedbackMessage').innerText = 'Passwords do not match.';
                return;
            }

            fetch('changePassword.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    oldPassword,
                    newPassword
                })
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('feedbackMessage').innerText = data.message;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('feedbackMessage').innerText = 'An unexpected error occurred.';
                });
        });
    </script>
</body>

</html>