<?php
// hash_gen.php
$password = 'user123';
echo password_hash($password, PASSWORD_DEFAULT);
?>