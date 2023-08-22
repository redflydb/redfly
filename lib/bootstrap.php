<?php
require __DIR__ . "/../vendor/autoload.php";
ini_set(
    "memory_limit",
    "512M"
);
ini_set(
    "max_execution_time",
    0
);
ini_set(
    "auto_detect_line_endings",
    true
);
$settings = yaml_parse_file(__DIR__ . "/../config/settings.yml");
$GLOBALS["options"] = json_decode(json_encode($settings));
session_start();
