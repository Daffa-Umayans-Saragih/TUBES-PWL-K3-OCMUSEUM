<?php
$baseDir = "c:/laragon/www/PWL-PANJAY2/PWL-PANJAY2/TUBES-SBD-WEBSITE";
$allowed_exts = ['php', 'js', 'css', 'blade.php', 'json', 'ts', 'html'];

$exclude_paths = [
    str_replace('/', DIRECTORY_SEPARATOR, "storage/framework/views"),
    str_replace('/', DIRECTORY_SEPARATOR, "bootstrap/cache"),
    str_replace('/', DIRECTORY_SEPARATOR, "public/build"),
    str_replace('/', DIRECTORY_SEPARATOR, "public/images")
];

function isExcluded($path) {
    global $baseDir, $exclude_paths;
    $relPath = str_replace($baseDir . '/', '', str_replace('\\', '/', $path));
    foreach ($exclude_paths as $ex) {
        if (strpos($relPath, str_replace(DIRECTORY_SEPARATOR, '/', $ex)) === 0) return true;
    }
    return false;
}

function countChars($dir) {
    global $baseDir, $allowed_exts;
    if (!is_dir($baseDir . '/' . $dir)) return 0;
    
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir . '/' . $dir));
    $chars = 0;
    foreach ($iter as $file) {
        if ($file->isFile() && !isExcluded($file->getPathname())) {
            $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if (strpos($file->getFilename(), '.blade.php') !== false) $ext = 'blade.php';
            if (in_array($ext, $allowed_exts)) {
                $c = @file_get_contents($file->getPathname());
                if (strlen($c) <= 1000000) $chars += strlen($c);
            }
        }
    }
    return $chars;
}

// 1. Daffa
$daffa = 0;
$daffa += countChars('app/Http/Controllers/Admin/');
$daffa += countChars('database/seeders/');
$daffa += countChars('resources/views/admin/artworks/');
$daffa += countChars('resources/views/admin/orders/');
$daffa += countChars('resources/views/admin/reports/');

// 2. Fitya
$fitya = 0;
$fitya += countChars('resources/css/');
$fitya += countChars('resources/views/ordinary/art/');
$fitya += countChars('resources/views/ordinary/plan-your-visit/');

// 3. Umi
$umi = 0;
$umi += countChars('database/migrations/');
$umi += countChars('database/factories/');
$umi += countChars('resources/views/admin/dashboard/');
$umi += countChars('resources/views/admin/tickets/');
$umi += countChars('resources/views/admin/payment/');
$umi += countChars('resources/views/admin/users/');
$total_admin_views = countChars('resources/views/admin/');
$umi += $total_admin_views - (
    countChars('resources/views/admin/artworks/') + 
    countChars('resources/views/admin/orders/') + 
    countChars('resources/views/admin/reports/') +
    countChars('resources/views/admin/dashboard/') +
    countChars('resources/views/admin/tickets/') +
    countChars('resources/views/admin/payment/') +
    countChars('resources/views/admin/users/')
);

// 4. Finsus
$finsus = 0;
$total_controllers = countChars('app/Http/Controllers/');
$finsus += ($total_controllers - countChars('app/Http/Controllers/Admin/')); // Controllers Root
$finsus += countChars('app/Models/');
$finsus += countChars('app/Services/');
$finsus += countChars('routes/');
$finsus += countChars('config/');
$finsus += countChars('app/Providers/');
$finsus += countChars('app/Http/Middleware/');
$finsus += countChars('bootstrap/');
$finsus += countChars('app/Mail/');
$finsus += countChars('resources/views/emails/');

// 5. Ghibran
$ghibran = 0;
$ghibran += countChars('resources/views/ordinary/account/');
$ghibran += countChars('resources/views/ordinary/member/');
$ghibran += countChars('resources/views/ordinary/admission/');
$ghibran += countChars('resources/views/ordinary/checkout/');
$ghibran += countChars('resources/views/ordinary/order/');
$ghibran += countChars('resources/views/ordinary/home/');
$total_ordinary = countChars('resources/views/ordinary/');
$ghibran += $total_ordinary - (
    countChars('resources/views/ordinary/art/') +
    countChars('resources/views/ordinary/plan-your-visit/') +
    countChars('resources/views/ordinary/account/') +
    countChars('resources/views/ordinary/member/') +
    countChars('resources/views/ordinary/admission/') +
    countChars('resources/views/ordinary/checkout/') +
    countChars('resources/views/ordinary/order/') +
    countChars('resources/views/ordinary/home/')
);
$ghibran += countChars('resources/views/components/');
$ghibran += countChars('resources/views/layouts/');
$ghibran += countChars('resources/js/');
$ghibran += countChars('public/');

echo "Daffa: $daffa\n";
echo "Fitya: $fitya\n";
echo "Umi: $umi\n";
echo "Finsus: $finsus\n";
echo "Ghibran: $ghibran\n";

$gap1 = $daffa - $fitya;
$gap2 = $fitya - $umi;
$gap3 = $umi - $finsus;
$gap4 = $finsus - $ghibran;

echo "Gap1: $gap1\n";
echo "Gap2: $gap2\n";
echo "Gap3: $gap3\n";
echo "Gap4: $gap4\n";
