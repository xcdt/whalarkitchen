<?php
    require("vendor/autoload.php");
    $openapi = \OpenApi\scan('/src');
    header('Content-Type: application/x-yaml');
    echo $openapi->toYaml();
