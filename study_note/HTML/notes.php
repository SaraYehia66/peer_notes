<?php
// Avoid redundant session_start
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start a new session if no session is already running
}

// Include the database connection file
include 'config.php';

// Enable error reporting for debugging purposes (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize message variable
$message = ''; 

// Handle file upload logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Check if user is logged in
    if (!isset($_SESSION['email'])) {
        // If user is not logged in, show message prompting them to log in
        $message = "<p style='color: red;'>You must log in to upload files. Please <a href='signIn.php'>log in here</a>.</p>";
    } else {
        // User is logged in, proceed with file processing
        $targetDir = "uploads/";

        // Retrieve uploaded file and department selection from the form
        $fileName = basename($_FILES['file']['name']); // Get the name of the uploaded file
        $targetFilePath = $targetDir . $fileName; // Path to store uploaded file
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION)); // Extract and lowercase file extension
        $department = isset($_POST['department']) ? $_POST['department'] : ''; // Check the department selected

        // Validate if department is empty
        if (empty($department)) {
            $message = "<p style='color: red;'>Please select a department.</p>";
        } else {
            // Allowed file types
            $allowedTypes = ['txt', 'doc', 'docx', 'pdf'];

            if (in_array($fileType, $allowedTypes)) {
                // Ensure upload directory exists or create it
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                // Handle file upload
                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
                    $user_email = $_SESSION['email']; // Get logged-in user's email

                    // Save the uploaded file details to database
                    $stmt = $connection->prepare("INSERT INTO notes (name, path, department, user_email) VALUES (?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("ssss", $fileName, $targetFilePath, $department, $user_email);
                        if ($stmt->execute()) {
                            $message = "<p style='color: green;'>File uploaded and saved successfully. <a href='notes.php'>Go to Notes</a></p>";
                        } else {
                            $message = "<p style='color: red;'>Failed to save file info in the database: " . $stmt->error . "</p>";
                        }
                        $stmt->close();
                    } else {
                        $message = "<p style='color: red;'>Failed to prepare database statement: " . $connection->error . "</p>";
                    }
                } else {
                    $message = "<p style='color: red;'>Error uploading file. Check folder permissions.</p>";
                }
            } else {
                $message = "<p style='color: red;'>Invalid file type. Allowed types are: " . implode(", ", $allowedTypes) . "</p>";
            }
        }
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']); // Get delete request's ID safely as an integer

    if (isset($_SESSION['email'])) { // Ensure the user is logged in
        $user_email = $_SESSION['email'];

        // Verify that the user owns the file they are attempting to delete
        $stmt = $connection->prepare("SELECT path FROM notes WHERE id = ? AND user_email = ?");
        $stmt->bind_param("is", $delete_id, $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $file_path = $row['path'];

            // Delete the file from server
            if (file_exists($file_path)) {
                unlink($file_path); // Remove file
            }

            // Delete the record from database
            $stmt = $connection->prepare("DELETE FROM notes WHERE id = ? AND user_email = ?");
            $stmt->bind_param("is", $delete_id, $user_email);
            if ($stmt->execute()) {
                $message = "<p style='color: green;'>File deleted successfully.</p>";
            } else {
                $message = "<p style='color: red;'>Failed to delete file from the database.</p>";
            }
        } else {
            $message = "<p style='color: red;'>You are not authorized to delete this file.</p>";
        }

        $stmt->close();
    } else {
        $message = "<p style='color: red;'>You must log in to delete files.</p>";
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

    <!-- Notes Section -->
    <section id="notes" class="cards">
        <h2 class="title">Notes</h2>
        <div class="subtitle">
            <label for="department-notes">Department</label>
            <select id="department-filter" name="department" onchange="filterNotes()">
                <option value="">All</option>
                <option value="science">Science</option>
                <option value="arts">Arts</option>
                <option value="business">Business</option>
            </select>
        </div>

        <div class='content'>
            <?php
            $department = isset($_GET['department']) ? $_GET['department'] : '';

            if (!empty($department)) {
                $sql = "SELECT * FROM notes WHERE department = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("s", $department);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $sql = "SELECT * FROM notes";
                $result = $connection->query($sql);
            }

            // Render results
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "
                    <div class='fram'>
                        <h2>" . htmlspecialchars($row['name']) . "</h2>
                        <hr>
                        <div class='info'>
                            <h3>Department: " . htmlspecialchars($row['department']) . "</h3>
                            <p>Uploaded by: " . htmlspecialchars($row['user_email']) . "</p>
                            <p><a href='" . htmlspecialchars($row['path']) . "' target='_blank'>Download Note</a></p>";

                    // Show delete icon if the logged-in user uploaded the file
                    if (isset($_SESSION['email']) && $_SESSION['email'] === $row['user_email']) {
                        echo "
                            <form method='POST' action='notes.php' style='display: inline-block;'>
                                <input type='hidden' name='delete_id' value='" . $row['id'] . "'>
                                <button type='submit' class='btn-delete' title='Delete'>
                                    <i class='fas fa-trash' style='color: red;'></i>
                                </button>
                            </form>";
                    }

                    echo "</div></div>";
                }
            } else {
                echo "<p>No notes found for the selected department.</p>";
            }
            ?>
        </div>
    </section>

    <!-- Upload Section -->
    <section class="upload-notes">
        <h2>Upload Your Notes</h2>

        <!-- Display any feedback message -->
        <?php if (!empty($message)) echo $message; ?>

        <form id="note-form" action="notes.php" method="POST" enctype="multipart/form-data">
            <label for="file-upload">Text, Doc, or PDF File</label>
            <input type="file" id="file-upload" name="file" accept=".txt,.doc,.docx,.pdf" required />
            <label for="department-upload">Department</label>
            <select id="department-upload" name="department" required>
                <option value="">Choose a department</option>
                <option value="science">Science</option>
                <option value="arts">Arts</option>
                <option value="business">Business</option>
            </select>
            <button type="submit" class="btn-upload">Upload</button>
        </form>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
    <!-- // Footer // -->

    <script>
        function filterNotes() {
            const department = document.getElementById('department-filter').value; 
            const url = new URL(window.location.href);

            if (department) {
                url.searchParams.set('department', department); // Set department filter
            } else {
                url.searchParams.delete('department'); // Remove department filter if "All" is selected
            }

            window.location.href = url.toString(); // Redirect to the updated URL
        }

        // Maintain selected value on page reload
        document.addEventListener("DOMContentLoaded", function () {
            const urlParams = new URLSearchParams(window.location.search);
            const department = urlParams.get('department');
            const selectElement = document.getElementById('department-filter');

            if (department) {
                selectElement.value = department;
            }
        });
    </script>
</body>

</html>
