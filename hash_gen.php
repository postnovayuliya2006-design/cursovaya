<?php
// hash_gen.php
$password = 'admin321';
echo password_hash($password, PASSWORD_DEFAULT);
?>