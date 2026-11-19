<?php
$content = file_get_contents('php://stdin');
file_put_contents('mvp/resources/views/dashboard.blade.php', $content);
echo "Dashboard view written successfully\n";