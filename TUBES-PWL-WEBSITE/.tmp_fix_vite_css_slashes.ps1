$ErrorActionPreference = "Stop"
$viewsRoot = Join-Path (Get-Location) "resources/views"
$changed = 0

$blades = Get-ChildItem -Path $viewsRoot -Recurse -File -Filter "*.blade.php"
foreach ($b in $blades) {
    $raw = Get-Content -LiteralPath $b.FullName -Raw
    $new = [regex]::Replace(
        $raw,
        "@vite\('resources/css/[^']*'\)",
        { param($m) ($m.Value -replace "\\", "/") }
    )

    if ($new -ne $raw) {
        Set-Content -LiteralPath $b.FullName -Value $new -NoNewline
        $changed++
    }
}

$remaining = 0
$check = Get-ChildItem -Path $viewsRoot -Recurse -File -Filter "*.blade.php"
foreach ($b in $check) {
    $matches = Select-String -Path $b.FullName -Pattern "@vite\('resources\\\\css\\\\" -AllMatches
    if ($matches) { $remaining += $matches.Count }
}

"CHANGED_FILES=$changed"
"REMAINING_MISMATCHES=$remaining"