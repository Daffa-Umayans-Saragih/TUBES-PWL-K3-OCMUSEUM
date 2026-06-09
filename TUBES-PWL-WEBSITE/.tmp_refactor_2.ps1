$ErrorActionPreference = "Stop"
$root = Get-Location
$viewsRoot = Join-Path $root "resources/views"
$cssRoot = Join-Path $root "resources/css"

$renamedBlade = 0
$renamedCss = 0
$updatedRefFiles = 0
$updatedBladeFiles = 0
$createdCss = 0

# 1) Blade renames
$bladeRenames = @(
    @{ Src="resources/views/ordinary/art/index/index.blade.php"; Dst="resources/views/ordinary/art/art.blade.php" },
    @{ Src="resources/views/ordinary/membership/index/index.blade.php"; Dst="resources/views/ordinary/membership/membership.blade.php" },
    @{ Src="resources/views/ordinary/ticket/index/index.blade.php"; Dst="resources/views/ordinary/ticket/ticket.blade.php" },
    @{ Src="resources/views/admin/art/index/index.blade.php"; Dst="resources/views/admin/art/art.blade.php" }
)
foreach ($r in $bladeRenames) {
    if ((Test-Path -LiteralPath $r.Src) -and -not (Test-Path -LiteralPath $r.Dst)) {
        Move-Item -LiteralPath $r.Src -Destination $r.Dst
        $renamedBlade++
    }
}

# 2) CSS renames
$cssRenames = @(
    @{ Src="resources/css/ordinary/art/index/index.css"; Dst="resources/css/ordinary/art/art.css" },
    @{ Src="resources/css/ordinary/membership/index/index.css"; Dst="resources/css/ordinary/membership/membership.css" },
    @{ Src="resources/css/ordinary/ticket/index/index.css"; Dst="resources/css/ordinary/ticket/ticket.css" },
    @{ Src="resources/css/admin/art/index/index.css"; Dst="resources/css/admin/art/art.css" }
)
foreach ($r in $cssRenames) {
    if ((Test-Path -LiteralPath $r.Src) -and -not (Test-Path -LiteralPath $r.Dst)) {
        Move-Item -LiteralPath $r.Src -Destination $r.Dst
        $renamedCss++
    }
}

# 3) Update route/controller references
$targets = @()
if (Test-Path -LiteralPath "routes/web.php") {
    $targets += (Resolve-Path "routes/web.php").Path
}
if (Test-Path -LiteralPath "app/Http/Controllers") {
    $targets += Get-ChildItem -Path "app/Http/Controllers" -Recurse -File -Filter "*.php" | ForEach-Object { $_.FullName }
}

$refMap = [ordered]@{
    "ordinary.art.index.index" = "ordinary.art.art"
    "ordinary.membership.index.index" = "ordinary.membership.membership"
    "admin.art.index.index" = "admin.art.art"
}

foreach ($f in ($targets | Select-Object -Unique)) {
    $raw = Get-Content -LiteralPath $f -Raw
    $new = $raw
    foreach ($k in $refMap.Keys) {
        $new = $new.Replace($k, $refMap[$k])
    }
    if ($new -ne $raw) {
        Set-Content -LiteralPath $f -Value $new -NoNewline
        $updatedRefFiles++
    }
}

# 4) Normalize vite lines in all blades
$blades = Get-ChildItem -Path $viewsRoot -Recurse -File -Filter "*.blade.php"
foreach ($b in $blades) {
    $raw = Get-Content -LiteralPath $b.FullName -Raw
    $lines = [System.Collections.Generic.List[string]]::new()
    ($raw -split "`r?`n") | ForEach-Object { [void]$lines.Add($_) }

    $relBlade = $b.FullName.Substring($viewsRoot.Length).TrimStart('\\').Replace('\\','/')
    $mirrorCss = "resources/css/" + ($relBlade -replace "\.blade\.php$", ".css")

    $hasJs = $raw.Contains("resources/js/app.js")

    $keep = [System.Collections.Generic.List[string]]::new()
    $firstViteIndex = $null

    for ($i = 0; $i -lt $lines.Count; $i++) {
        $ln = $lines[$i]
        $isTargetVite = $ln.Contains("@vite") -and ($ln.Contains("resources/css/") -or $ln.Contains("resources\\css\\") -or $ln.Contains("resources/js/app.js"))
        if ($isTargetVite) {
            if ($null -eq $firstViteIndex) { $firstViteIndex = $keep.Count }
            continue
        }
        [void]$keep.Add($ln)
    }

    if ($null -eq $firstViteIndex) {
        $headIdx = -1
        for ($i = 0; $i -lt $keep.Count; $i++) {
            if ($keep[$i] -match "<head[^>]*>") { $headIdx = $i; break }
        }
        if ($headIdx -ge 0) { $firstViteIndex = $headIdx + 1 } else { $firstViteIndex = 0 }
    }

    $insert = @("@vite('resources/css/app.css')", "@vite('$mirrorCss')")
    if ($hasJs) { $insert += "@vite('resources/js/app.js')" }

    $newLines = [System.Collections.Generic.List[string]]::new()
    for ($i = 0; $i -lt $keep.Count; $i++) {
        if ($i -eq $firstViteIndex) {
            $insert | ForEach-Object { [void]$newLines.Add($_) }
        }
        [void]$newLines.Add($keep[$i])
    }
    if ($firstViteIndex -ge $keep.Count) {
        $insert | ForEach-Object { [void]$newLines.Add($_) }
    }

    $newRaw = $newLines -join "`r`n"
    if ($newRaw -ne $raw) {
        Set-Content -LiteralPath $b.FullName -Value $newRaw -NoNewline
        $updatedBladeFiles++
    }
}

# 5) Create missing mirror css files for every blade
$bladesAfter = Get-ChildItem -Path $viewsRoot -Recurse -File -Filter "*.blade.php"
foreach ($b in $bladesAfter) {
    $relBlade = $b.FullName.Substring($viewsRoot.Length).TrimStart('\\').Replace('\\','/')
    $cssRel = ($relBlade -replace "\.blade\.php$", ".css")
    $cssFull = Join-Path $cssRoot ($cssRel -replace "/", "\\")

    if (-not (Test-Path -LiteralPath $cssFull)) {
        $dir = Split-Path -Path $cssFull -Parent
        if (-not (Test-Path -LiteralPath $dir)) {
            New-Item -ItemType Directory -Path $dir -Force | Out-Null
        }
        New-Item -ItemType File -Path $cssFull -Force | Out-Null
        $createdCss++
    }
}

# 6) Validation counts
$refFiles = @()
if (Test-Path -LiteralPath "routes/web.php") { $refFiles += (Resolve-Path "routes/web.php").Path }
if (Test-Path -LiteralPath "app/Http/Controllers") {
    $refFiles += Get-ChildItem -Path "app/Http/Controllers" -Recurse -File -Filter "*.php" | ForEach-Object { $_.FullName }
}

$remainingIndexViews = 0
foreach ($f in ($refFiles | Select-Object -Unique)) {
    $matches = Select-String -Path $f -Pattern "view\([^\)]*index" -AllMatches
    if ($matches) { $remainingIndexViews += $matches.Count }
}

$cssBackslashVite = 0
$bladesCheck = Get-ChildItem -Path $viewsRoot -Recurse -File -Filter "*.blade.php"
foreach ($b in $bladesCheck) {
    $matches = Select-String -Path $b.FullName -Pattern "@vite\('resources\\\\css\\\\" -AllMatches
    if ($matches) { $cssBackslashVite += $matches.Count }
}

$missingMirrorCss = 0
foreach ($b in $bladesCheck) {
    $relBlade = $b.FullName.Substring($viewsRoot.Length).TrimStart('\\').Replace('\\','/')
    $cssRel = ($relBlade -replace "\.blade\.php$", ".css")
    $cssFull = Join-Path $cssRoot ($cssRel -replace "/", "\\")
    if (-not (Test-Path -LiteralPath $cssFull)) { $missingMirrorCss++ }
}

"CHECK: remaining view('...index...') in routes/controllers = $remainingIndexViews"
"CHECK: vite css path containing backslash = $cssBackslashVite"
"CHECK: missing mirror css files = $missingMirrorCss"
"COUNTS: blade_renamed=$renamedBlade css_renamed=$renamedCss refs_files_updated=$updatedRefFiles blades_updated=$updatedBladeFiles css_created=$createdCss"