<?php
$html = file_get_contents('test-html-output.txt');
if (preg_match('/<div class="art-grid">(.*?)<!-- Pagination -->/is', $html, $gridMatch)) {
    if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $gridMatch[1], $matches)) {
        foreach($matches[1] as $src) {
            echo $src . "\n";
        }
    } else {
        echo "No images found in grid.\n";
    }
} else {
    echo "Could not find grid.\n";
}
