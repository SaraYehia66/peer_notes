<?php
session_start(); //If a session is already running, it resumes that session, If no session exists, it creates a new session.
session_destroy(); // Removes all data associated with $_SESSION
header('Location: signIn.php');
exit;
?>
