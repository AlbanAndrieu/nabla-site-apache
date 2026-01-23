<?php declare(strict_types = 1);

if (isset($_GET['all'])) {
    phpinfo();
} else {
    phpinfo(INFO_ALL & ~INFO_ENVIRONMENT & ~INFO_CONFIGURATION & ~INFO_VARIABLES);
}

var_dump(opcache_get_status());
