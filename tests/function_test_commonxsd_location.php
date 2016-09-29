<?php

function test_commonxsd_location($filename)
{
    $location = __DIR__ . '/../commonxsd';
    $dirname = realpath($location);
    if (! $dirname or ! is_dir($dirname)) {
        trigger_error("Missing folder commonxsd: $location", E_USER_ERROR);
    }
    return $dirname . '/' . $filename;
}
