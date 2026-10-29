<?php
session_start();
unset($_SESSION['auth_role'], $_SESSION['user_data'], $_SESSION['role']);
$_SESSION['sukses'] = 'Anda telah berhasil keluar (logout) dari sistem parkir.';
header('Location: ../index.php');
exit;
