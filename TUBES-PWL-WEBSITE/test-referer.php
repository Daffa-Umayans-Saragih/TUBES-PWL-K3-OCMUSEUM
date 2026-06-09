<?php
$url = "https://collectionapi.metmuseum.org/api/collection/v1/iiif/503480/2185382/main-image";
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Referer: http://127.0.0.1:8000/art/collection/search\r\n"
    ]
];
$context = stream_context_create($opts);
$headers = @get_headers($url, 1, $context);
print_r($headers);
