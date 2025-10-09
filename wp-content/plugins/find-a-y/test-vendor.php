<?php
require_once __DIR__ . '/vendor/autoload.php';

if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
    echo "SUCCESS: PhpSpreadsheet is loaded correctly!";
} else {
    echo "ERROR: PhpSpreadsheet not found!";
}