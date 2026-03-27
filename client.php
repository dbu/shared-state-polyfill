<?php
require ('vendor/autoload.php');

echo 'My time is          '.time()."\n";
echo 'Last worker time is '.frankenphp_worker_get_vars('test')['time'];
