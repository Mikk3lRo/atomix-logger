<?php
chdir(__DIR__);
`chmod +x vendor/bin/phpunit vendor/phpunit/phpunit/phpunit`;
passthru('vendor/bin/phpunit --bootstrap vendor/autoload.php tests');