<?php

function Redirect($url, $permanent = false)
{
    header('Location: ' . $url, true, $permanent ? 301 : 302);

    exit();
}

Redirect('index.html', false);
# Redirect('test.php', false);
# Redirect('404.html', false);
