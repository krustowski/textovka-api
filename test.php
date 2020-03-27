<?php

$file = file_get_contents("./test.json");

$a = json_decode($file, true);

header("Content-type: application/json");
echo json_encode($a, JSON_PRETTY_PRINT);
exit();
