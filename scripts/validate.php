<?php
/**
 * Script to validate cfdi files and show all the errors found
 */

require_once __DIR__ . '/../vendor/autoload.php';

use CFDIReader\CFDIFactory;

$script = array_shift($argv);
if ($argc == 1 || in_array('-h', $argv) || in_array('--help', $argv)) {
    echo "Set the file of the file to validate\n";
    echo "Usage: $script [file1.xml] [file2.xml]\n";
    exit;
}

// create the factory
$factory = new CFDIFactory();
while (count($argv)) {
    // process next argument
    $argument = array_shift($argv);
    $filename = realpath($argument);
    if ('' === $filename || ! is_file($filename) || ! is_readable($filename)) {
        echo "File $argument FATAL: not found or is not readable\n";
        continue;
    }
    // do the object creation
    $errors = [];
    $warnings = [];
    try {
        $reader = $factory->newCFDIReader(file_get_contents($filename), $errors, $warnings);
        foreach ($errors as $message) {
            echo "File $argument ERROR: $message\n";
        }
        foreach ($warnings as $message) {
            echo "File $argument WARNING: $message\n";
        }
        echo "File $argument UUID: " . $reader->getUUID(), "\n";
    } catch (Exception $ex) {
        echo "File $argument FATAL: ", $ex->getMessage(), "\n";
    }
}
