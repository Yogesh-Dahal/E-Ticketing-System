// logout.php
<?php
session_start();
header('Location: login.html');
session_destroy();
exit;
?>