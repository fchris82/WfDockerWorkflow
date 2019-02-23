<?php
$projectPath = $_ENV['PWD'];
$filePath = realpath($projectPath . $_GET['file']);

// The last is a security check!
echo file_exists($filePath) && is_file($filePath) && strpos($filePath, $projectPath) === 0
    ? file_get_contents($filePath)
    : 'Missing file: ' . $filePath;
