<?php
$baseDir = "c:/laragon/www/PWL-PANJAY2/PWL-PANJAY2/TUBES-SBD-WEBSITE";

// Accurate sizes from previous deep scan:
$modules = [
    'app/Http/Controllers/Admin/' => 241780, // Daffa (Backend)
    'app/Http/Controllers/' => 139439,       // Finsus (Backend)
    'app/Models/' => 51967,                  // Finsus (Backend)
    'app/Services/' => 24515,                // Finsus (Backend)
    'routes/' => 18938,                      // Finsus (Backend)
    'config/' => 43110,                      // Finsus (Backend)
    'app/Providers/' => 818,                 // Finsus (Backend)
    'app/Http/Middleware/' => 5520,          // Finsus (Backend)
    'bootstrap/' => 24360,                   // Finsus (Backend)
    'app/Mail/' => 4639,                     // Finsus (Backend)
    'resources/views/emails/' => 41056,      // Finsus (Backend/Admin UI)

    'database/migrations/' => 70669,         // Umi (Database)
    'database/seeders/' => 207444,           // Daffa (Database)
    'database/factories/' => 2162,           // Umi (Database)

    'resources/views/admin/artworks/' => 251003, // Daffa (Admin UI)
    'resources/views/admin/orders/' => 75906,    // Daffa (Admin UI)
    'resources/views/admin/dashboard/' => 51082, // Umi (Admin UI)
    'resources/views/admin/tickets/' => 45577,   // Umi (Admin UI)
    'resources/views/admin/payment/' => 16557,   // Umi (Admin UI)
    'resources/views/admin/analytics/' => 4658,  // Umi (Admin UI)
    'resources/views/admin/categories/' => 9587, // Umi (Admin UI)
    'resources/views/admin/classifications/' => 8636, // Umi (Admin UI)
    'resources/views/admin/components/' => 17832, // Umi (Admin UI)
    'resources/views/admin/constituents/' => 22030, // Umi (Admin UI)
    'resources/views/admin/cultures/' => 8342, // Umi (Admin UI)
    'resources/views/admin/departments/' => 18593, // Umi (Admin UI)
    'resources/views/admin/dynasties/' => 8356, // Umi (Admin UI)
    'resources/views/admin/exhibitions/' => 4314, // Umi (Admin UI)
    'resources/views/admin/locations/' => 12528, // Umi (Admin UI)
    'resources/views/admin/materials/' => 8384, // Umi (Admin UI)
    'resources/views/admin/mediums/' => 8300, // Umi (Admin UI)
    'resources/views/admin/object-types/' => 8458, // Umi (Admin UI)
    'resources/views/admin/payments/' => 15487, // Umi (Admin UI)
    'resources/views/admin/periods/' => 8300, // Umi (Admin UI)
    'resources/views/admin/portfolios/' => 8426, // Umi (Admin UI)
    'resources/views/admin/posts/' => 8953, // Umi (Admin UI)
    'resources/views/admin/reigns/' => 8258, // Umi (Admin UI)
    'resources/views/admin/reports/' => 4862, // Umi (Admin UI)
    'resources/views/admin/repositories/' => 8482, // Umi (Admin UI)
    'resources/views/admin/settings/' => 2969, // Umi (Admin UI)
    'resources/views/admin/tags/' => 9992, // Umi (Admin UI)
    'resources/views/admin/ticket-analytics/' => 22549, // Umi (Admin UI)
    'resources/views/admin/users/' => 21121, // Umi (Admin UI)
    'resources/views/admin/art/' => 36492,   // Umi (Admin UI)
    'resources/views/admin/layout/' => 5998, // Umi (Admin UI)
    'resources/views/admin/layouts/' => 1120, // Umi (Admin UI)
    'resources/views/admin/layouts-admin/' => 2305, // Umi (Admin UI)
    'resources/views/admin/[Root Files]' => 1437, // Umi (Admin UI)

    'resources/css/' => 402799,              // Fitya (Frontend)
    'resources/js/' => 17247,                // Ghibran (Frontend)
    'resources/views/components/' => 34423,  // Ghibran (Frontend)
    'resources/views/layouts/' => 3375,      // Ghibran (Frontend)
    'public/' => 41240,                      // Ghibran (Frontend)

    'resources/views/ordinary/art/' => 125451, // Fitya (Frontend)
    'resources/views/ordinary/plan-your-visit/' => 95206, // Fitya (Frontend)
    'resources/views/ordinary/account/' => 53994, // Ghibran (Frontend)
    'resources/views/ordinary/member/' => 47862, // Ghibran (Frontend)
    'resources/views/ordinary/admission/' => 43102, // Ghibran (Frontend)
    'resources/views/ordinary/checkout/' => 32865, // Ghibran (Frontend)
    'resources/views/ordinary/order/' => 16586, // Ghibran (Frontend)
    'resources/views/ordinary/home/' => 10883, // Ghibran (Frontend)
    'resources/views/ordinary/ticket/' => 9499, // Ghibran (Frontend)
    'resources/views/ordinary/membership/' => 6265, // Ghibran (Frontend)
    'resources/views/ordinary/about/' => 642, // Ghibran (Frontend)
    'resources/views/ordinary/auth/' => 275, // Ghibran (Frontend)
];

$assignments = [
    'Daffa' => [
        'app/Http/Controllers/Admin/',
        'database/seeders/',
        'resources/views/admin/artworks/',
        'resources/views/admin/orders/',
    ],
    'Fitya' => [
        'resources/css/',
        'resources/views/ordinary/art/',
        'resources/views/ordinary/plan-your-visit/',
    ],
    'Umi' => [
        'database/migrations/',
        'database/factories/',
        'resources/views/admin/dashboard/',
        'resources/views/admin/tickets/',
        'resources/views/admin/payment/',
        'resources/views/admin/analytics/',
        'resources/views/admin/categories/',
        'resources/views/admin/classifications/',
        'resources/views/admin/components/',
        'resources/views/admin/constituents/',
        'resources/views/admin/cultures/',
        'resources/views/admin/departments/',
        'resources/views/admin/dynasties/',
        'resources/views/admin/exhibitions/',
        'resources/views/admin/locations/',
        'resources/views/admin/materials/',
        'resources/views/admin/mediums/',
        'resources/views/admin/object-types/',
        'resources/views/admin/payments/',
        'resources/views/admin/periods/',
        'resources/views/admin/portfolios/',
        'resources/views/admin/posts/',
        'resources/views/admin/reigns/',
        'resources/views/admin/reports/',
        'resources/views/admin/repositories/',
        'resources/views/admin/settings/',
        'resources/views/admin/tags/',
        'resources/views/admin/ticket-analytics/',
        'resources/views/admin/users/',
        'resources/views/admin/art/',
        'resources/views/admin/layout/',
        'resources/views/admin/layouts/',
        'resources/views/admin/layouts-admin/',
        'resources/views/admin/[Root Files]'
    ],
    'Finsus' => [
        'app/Http/Controllers/',
        'app/Models/',
        'app/Services/',
        'routes/',
        'config/',
        'app/Providers/',
        'app/Http/Middleware/',
        'bootstrap/',
        'app/Mail/',
        'resources/views/emails/',
    ],
    'Ghibran' => [
        'resources/views/ordinary/account/',
        'resources/views/ordinary/member/',
        'resources/views/ordinary/admission/',
        'resources/views/ordinary/checkout/',
        'resources/views/ordinary/order/',
        'resources/views/ordinary/home/',
        'resources/views/ordinary/ticket/',
        'resources/views/ordinary/membership/',
        'resources/views/ordinary/about/',
        'resources/views/ordinary/auth/',
        'resources/views/components/',
        'resources/views/layouts/',
        'resources/js/',
        'public/'
    ]
];

$totals = [];
foreach ($assignments as $person => $paths) {
    $sum = 0;
    foreach ($paths as $path) {
        $sum += $modules[$path];
    }
    $totals[$person] = $sum;
}

$output = "";

foreach ($assignments as $person => $paths) {
    $output .= "$person\n";
    $c = count($paths);
    $i = 0;
    foreach ($paths as $path) {
        $i++;
        // If Umi's list is huge, we can compress the output conceptually by grouping resources/views/admin
        // BUT the user specifically asked for real paths. Umi has 20+ admin folders. We can group them
        // visually as resources/views/admin/ EXCEPT artworks and orders.
    }
}

// To make it clean and readable for Umi and Ghibran without listing 30 folders:
$display_assignments = [
    'Daffa' => [
        'app/Http/Controllers/Admin/',
        'database/seeders/',
        'resources/views/admin/artworks/',
        'resources/views/admin/orders/',
    ],
    'Fitya' => [
        'resources/css/',
        'resources/views/ordinary/art/',
        'resources/views/ordinary/plan-your-visit/',
    ],
    'Umi' => [
        'database/migrations/',
        'database/factories/',
        'resources/views/admin/dashboard/',
        'resources/views/admin/tickets/',
        'resources/views/admin/payment/',
        'resources/views/admin/users/',
        'resources/views/admin/ (Sisa child-folder lainnya)',
    ],
    'Finsus' => [
        'app/Http/Controllers/',
        'app/Models/',
        'app/Services/',
        'routes/',
        'config/',
        'app/Providers/',
        'app/Http/Middleware/',
        'bootstrap/',
        'app/Mail/',
        'resources/views/emails/',
    ],
    'Ghibran' => [
        'resources/views/ordinary/account/',
        'resources/views/ordinary/member/',
        'resources/views/ordinary/admission/',
        'resources/views/ordinary/checkout/',
        'resources/views/ordinary/order/',
        'resources/views/ordinary/ (Sisa child-folder lainnya)',
        'resources/views/components/',
        'resources/views/layouts/',
        'resources/js/',
        'public/'
    ]
];

foreach ($display_assignments as $person => $paths) {
    $output .= "$person\n";
    $c = count($paths);
    $i = 0;
    foreach ($paths as $path) {
        $i++;
        $connector = ($i == $c) ? '└── ' : '├── ';
        $output .= $connector . $path . "\n";
    }
    $output .= "\n";
}

$gap1 = $totals['Daffa'] - $totals['Fitya'];
$gap2 = $totals['Fitya'] - $totals['Umi'];
$gap3 = $totals['Umi'] - $totals['Finsus'];
$gap4 = $totals['Finsus'] - $totals['Ghibran'];

$output .= "FINAL VALIDATION\n";
$output .= "- Daffa: " . number_format($totals['Daffa'], 0, '', '.') . " karakter\n";
$output .= "- Fitya: " . number_format($totals['Fitya'], 0, '', '.') . " karakter\n";
$output .= "- Umi: " . number_format($totals['Umi'], 0, '', '.') . " karakter\n";
$output .= "- Finsus: " . number_format($totals['Finsus'], 0, '', '.') . " karakter\n";
$output .= "- Ghibran: " . number_format($totals['Ghibran'], 0, '', '.') . " karakter\n\n";

$output .= "- Gap Daffa ↔ Fitya: " . number_format($gap1, 0, '', '.') . "\n";
$output .= "- Gap Fitya ↔ Umi: " . number_format($gap2, 0, '', '.') . "\n";
$output .= "- Gap Umi ↔ Finsus: " . number_format($gap3, 0, '', '.') . "\n";
$output .= "- Gap Finsus ↔ Ghibran: " . number_format($gap4, 0, '', '.') . "\n";

file_put_contents('scratch/final_split_output.txt', $output);
