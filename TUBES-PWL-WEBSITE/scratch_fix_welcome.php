<?php
$file = 'c:\\xampp\\htdocs\\TUBES-SBD-K3-METMUSEUM\\TUBES-SBD-WEBSITE\\resources\\views\\ordinary\\home\\welcome\\welcome.blade.php';
$content = file_get_contents($file);

// Fix the malformed </a> tags
$content = preg_replace('/<\/div>\s*<\/a>/', "</a>\n                        </div>", $content);

// The last item has script tag
$content = preg_replace('/<\/script>\s*<\/div>\s*<\/div>\s*<\/a>/s', "</script>\n                            </a>\n                        </div>\n                    </div>", $content);

file_put_contents($file, $content);
echo "Fixed welcome.blade.php\n";
