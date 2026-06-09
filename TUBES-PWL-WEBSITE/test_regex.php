<?php
$html = file_get_contents(__DIR__ . '/storage/app/debug_met.html');

// 1. Check for preload image
preg_match_all('/<link[^>]*rel="preload"[^>]*as="image"[^>]*href="([^"]+)"[^>]*>/i', $html, $matches);
echo "PRELOAD MATCHES:\n";
print_r($matches[1]);

// 2. Check for __NEXT_DATA__
preg_match('/<script id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/is', $html, $matches);
if (!empty($matches[1])) {
    $data = json_decode($matches[1], true);
    // Find image URL in JSON
    echo "NEXT DATA HAS IMAGE: ";
    $jsonString = json_encode($data);
    preg_match_all('/https:\/\/collectionapi\.metmuseum\.org\/api\/collection\/v1\/iiif\/[^\/]+\/[^\/]+\/restricted/', $jsonString, $imageMatches);
    print_r($imageMatches[0]);
}

// 3. Fallback to any meta image
preg_match_all('/<meta[^>]*property="og:image"[^>]*content="([^"]+)"[^>]*>/i', $html, $matches);
echo "OG IMAGE MATCHES:\n";
print_r($matches[1]);
