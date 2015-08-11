<?php
/**
 * Script to validate a cfdi and show all the errors found
 */

require_once __DIR__ . '/../vendor/autoload.php';

use CFDIReader\CFDIFactory;

call_user_func(function() use($argv, $argc) {

    $script = array_shift($argv);
    if ($argc == 1) {
        echo "Set the file of the file to validate\n";
        echo "Usage: $script [file1.xml] [file2.xml]\n";
        exit;
    }

    // create objects
    $factory = new CFDIFactory();
    while(count($argv)) {
        $argument = array_shift($argv);
        $filename = realpath($argument);
        if ("" === $filename or !is_readable($filename)) {
            echo "File $argument was not found or is not readable\n";
            continue;
        }
        try {
            $errors = [];
            $warnings = [];
            $reader = $factory->newCFDIReader(file_get_contents($filename), $errors, $warnings);
            foreach($errors as $message) {
                echo "File $argument ERROR: $message\n";
            }
            foreach($warnings as $message) {
                echo "File $argument ERROR: $message\n";
            }
            echo "File $argument with UUID " . $reader->getUUID(), " OK\n";
        } catch(Exception $ex) {
            echo "File $argument give exception: ", $ex->getMessage(), "\n";
            echo $ex->getTraceAsString(), "\n";
        }

    }

});


