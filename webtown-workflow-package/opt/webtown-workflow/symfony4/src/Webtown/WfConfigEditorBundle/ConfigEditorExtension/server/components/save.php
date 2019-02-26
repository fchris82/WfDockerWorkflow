<?php
$projectPath = $_ENV['PWD'];
$filePath = realpath($projectPath . $_POST['file']);
$content = $_POST['content'];

// The last is a security check!
if (file_exists($filePath) && is_file($filePath) && strpos($filePath, $projectPath) === 0) {
    file_put_contents($filePath, $content);
    echo $content;
} else {
    echo "ERROR";
}
