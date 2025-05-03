<?php

@include 'config.php'; // Includes database config, optional for logout

session_start();        // Starts the session
session_unset();        // Unsets all session variables
session_destroy();      // Destroys the session completely

header('location:login.php'); // Redirects user to login page
exit();                        // Best practice to stop script execution

?>
