<?php

function test_commonxsd_location($filename)
{
    $dirname = realpath(__DIR__ . '/../commonxsd');
    if (! is_dir($dirname)) {
        trigger_error('Missing folder commonxsd', E_USER_ERROR);
    }
    return $dirname . '/' . $filename;
}
