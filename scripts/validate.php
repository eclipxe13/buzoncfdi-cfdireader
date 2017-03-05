<?php
/**
 * Script to validate cfdi files and show all the errors found
 */
require_once __DIR__ . '/../vendor/autoload.php';

\CFDIReader\Scripts\Validate::make($argv)->run();
