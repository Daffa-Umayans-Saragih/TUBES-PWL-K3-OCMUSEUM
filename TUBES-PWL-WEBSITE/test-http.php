<?php
$url = "https://collectionapi.metmuseum.org/api/collection/v1/iiif/503480/2185382/main-image";
$headers = get_headers($url, 1);
print_r($headers);
