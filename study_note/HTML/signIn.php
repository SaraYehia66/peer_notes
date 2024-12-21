<?php
session_start(); // Start the session

include 'config.php';
// Check if the user is already signed in
if (isset($_SESSION['email'])) {
    echo "<script>
            alert('You are already signed in. Please log out first before signing in again.');
            window.location.href = 'home.php'; // Redirect to the dashboard
          </script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['pass']) ? trim($_POST['pass']) : '';

    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        $stmt = $connection->prepare("SELECT Fname, Lname, pass FROM signup WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashedPassword = $row['pass'];

                if (password_verify($password, $hashedPassword)) {
                    // Store user's email and name in session
                    $_SESSION['email'] = $email;
                    $_SESSION['Fname'] = $row['Fname'];
                    $_SESSION['Lname'] = $row['Lname'];
                    header("Location: home.php"); // Redirect to the dashboard
                    exit;
                } else {
                    $error_message = "Invalid password. Please try again.";
                }
            } else {
                $error_message = "No account found with that email.";
            }
            $stmt->close();
        } else {
            $error_message = "Database error: " . $connection->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeerNotes - Sign In</title>
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>

<body>
    <!-- Header -->

    <?php include 'header.php'; ?>

    <!-- // Header // -->
    <div class="login" id="login">
        <div class="content">
            <div class="inputs">
                <form action="signIn.php" method="POST">
                    <h2 class="title">Sign In</h2>

                    <?php if (isset($error_message)): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
                    <?php endif; ?>

                    <div>
                        <input type="email" name="email" required placeholder="Email" />
                    </div>

                    <div>
                        <input type="password" name="pass" required placeholder="Password" minlength="6"
                            maxlength="16" />
                    </div>

                    <div>
                        <input class="btn" type="submit" value="Login" />
                    </div>
                </form>

                <div>
                    <h5>
                        You do not have an account? <a href="signUp.php">Make an account</a>
                    </h5>
                </div>
            </div>

            <div class="logo">
                <i class="fa-solid fa-book-open-reader"></i>
            </div>
        </div>
    </div>

</body>

</html>