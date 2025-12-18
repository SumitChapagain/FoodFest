<?php
require_once '../config/session.php';
destroyAdminSession();
header('Location: login.php');
exit();
?>
