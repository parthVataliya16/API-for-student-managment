<?php
// if (!isset($_SESSION['user'])) {
//     if (basename($_SERVER['PHP_SELF']) != 'login.php') {
//         header('location: http://localhost/practice/userManagement/views/auth/login.php');
//         exit;
//     }
// } else {
//     if ($_SESSION['user'] == 'admin') {
//         header('location: http://localhost/practice/userManagement/views/admin/dashboard.php');
//         exit;
//     } else {
//         header('location: http://localhost/practice/userManagement/views/students/dashboard.php');
//         exit;
//     }
// }

function checkRole($userId) {
    if ($userId == 1) {
        return "admin";
    } else {
        return "student";
    }
}

?>