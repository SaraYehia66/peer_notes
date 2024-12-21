<?php
session_start();
include 'config.php';

// Check if the user is already signed in
if (isset($_SESSION['email'])) {
    echo "<script>
            alert('You are already signed in. Please log out first before signing up.');
            window.location.href = 'home.php'; // Redirect to the dashboard or home page
          </script>";
    exit;
}
// Initialize message variable
$message = '';

if (isset($_POST['send'])) {
    // Collect and sanitize POST data
    $Fname = trim($_POST['Fname']);
    $Lname = trim($_POST['Lname']);
    $email = trim($_POST['email']);
    $pass = $_POST['pass'];
    $confirm_pass = $_POST['confirm_pass'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];

    // Hash the password
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
    $hashed_confirm_pass = password_hash($confirm_pass, PASSWORD_DEFAULT);

    // Check if email already exists
    $emailCheckStmt = $connection->prepare("SELECT email FROM signup WHERE email = ?");
    $emailCheckStmt->bind_param("s", $email);
    $emailCheckStmt->execute();
    $result = $emailCheckStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Email already exists
        $message = '<p style="color: red;">This email is already registered. Please try with another email.</p>';
    } else {
        // Email does not exist, proceed with insertion
        $stmt = $connection->prepare("INSERT INTO signup (Fname, Lname, email, pass, confirm_pass, date_of_birth, Gender) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("sssssss", $Fname, $Lname, $email, $hashed_pass, $hashed_confirm_pass, $date_of_birth, $gender);

            if ($stmt->execute()) {
                header('Location: signIn.php');
                exit;
            } else {
                $message = '<p style="color: red;">Error: Unable to execute the query.</p>';
            }
            $stmt->close();
        } else {
            $message = '<p style="color: red;">Error: Could not prepare the query.</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeerNotes</title>
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>


<body>
    <!-- Header -->

    <?php include 'header.php'; ?>

    <!-- // Header // -->

    <div class="signUp" id="signUp">

        <div class="content">

            <div class="inputs">
                <form id="signupForm" action="signUp.php" method="post">

                    <h2 class="title">Sign Up</h2>
                    <?php if ($message) {
                        echo $message;
                    } ?>

                    <div>
                        <input type="text" required placeholder="First Name" name="Fname" />
                    </div>

                    <div>
                        <input type="text" required placeholder="Last Name" name="Lname" />
                    </div>

                    <div>
                        <input type="email" required placeholder="email" name="email" />
                    </div>

                    <div>
                        <input type="password" id="password" required placeholder="password" name="pass" />
                    </div>
                    <div>
                        <input type="password" id="confirm_password" required placeholder="Confirm password"
                            name="confirm_pass" />
                    </div>

                    <div>
                        <h5>Date of birth</h5><input type="date" name="date_of_birth" />
                    </div>

                    <div>
                        <h5>Gender</h5>
                        <input id="m" type="radio" name="gender" value="male" checked />
                        <label for="m">male</label>

                        <input id="f" type="radio" name="gender" value="female" />
                        <label for="f">female</label>
                    </div>

                    <div>
                        <input class="btn" type="submit" value="Sign Up" name="send" />

                    </div>
                    <p id="error-message" style="color: red; display: none;">Passwords do not match. Please try again.
                    </p>
                </form>

                <div>
                    <h5>
                        You already have an account ? <a href="signIn.php">Sign In</a>
                    </h5>
                </div>
            </div>

            <div class="logo">
                <i class="fa-solid fa-book-open-reader"></i>
            </div>

        </div>

    </div>

    <script>
        // Get the form, password fields, and error message element
        const signupForm = document.getElementById("signupForm");
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirm_password");
        const errorMessage = document.getElementById("error-message");

        // Add an event listener to the form submission
        signupForm.addEventListener("submit", function (e) {
            // Check if passwords match
            if (password.value !== confirmPassword.value) {
                e.preventDefault(); // Prevent the form from being submitted
                errorMessage.style.display = "block"; // Show the error message
                password.value = ""; // Clear the password fields
                confirmPassword.value = "";
            } else {
                errorMessage.style.display = "none"; // Hide the error message
            }
        });

    </script>

</body>

</html>