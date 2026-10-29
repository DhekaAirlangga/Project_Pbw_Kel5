
<?php
session_start();

unset(
    $_SESSION['kendaraan'],
    $_SESSION['error'],
    $_SESSION['sukses']
);

header('Location: ../index.php');
exit;
