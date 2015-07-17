<?php

/**
 * Return the location of a file from the assets folder
 * @param string $filename
 * @return string
 */
function test_file_location($filename)
{
    return __DIR__ . '/assets/' . $filename;
}
