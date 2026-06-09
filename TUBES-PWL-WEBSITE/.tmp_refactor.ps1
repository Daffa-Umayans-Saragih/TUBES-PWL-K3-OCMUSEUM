$ErrorActionPreference = "Stop"
$root = Get-Location
$viewsRoot = Join-Path $root "resources/views"
$cssRoot = Join-Path $root "resources/css"
$renamedBlade = 0; $renamedCss = 0; $createdCss = 0; $updatedRefsFiles = 0; $updatedBladeFiles = 0

$bladeRenames = @(
    @{ Src="resources/views/ordinary/account/login/index.blade.php"; Dst="resources/views/ordinary/account/login/login.blade.php" },
    @{ Src="resources/views/ordinary/admission/index.blade.php"; Dst="resources/views/ordinary/admission/admission.blade.php" },
    @{ Src="resources/views/ordinary/checkout/cart/index.blade.php"; Dst="resources/views/ordinary/checkout/cart/cart.blade.php" }
)
foreach ($r in $bladeRenames) { if ((Test-Path -LiteralPath $r.Src) -and -not (Test-Path -LiteralPath $r.Dst)) { Move-Item -LiteralPath $r.Src -Destination $r.Dst; $renamedBlade++ } }

$cssRenames = @(
    @{ Src="resources/css/ordinary/account/login/index.css"; Dst="resources/css/ordinary/account/login/login.css" },
    @{ Src="resources/css/ordinary/admission/index.css"; Dst="resources/css/ordinary/admission/admission.css" },
    @{ Src="resources/css/ordinary/checkout/cart/index.css"; Dst="resources/css/ordinary/checkout/cart/cart.css" }
)
foreach ($r in $cssRenames) { if ((Test-Path -LiteralPath $r.Src) -and -not (Test-Path -LiteralPath $r.Dst)) { Move-Item -LiteralPath $r.Src -Destination $r.Dst; $renamedCss++ } }

$targets = @()
if (Test-Path -LiteralPath "routes/web.php") { $targets += (Resolve-Path "routes/web.php").Path }
if (Test-Path -LiteralPath "app/Http/Controllers") { $targets += Get-ChildItem -Path "app/Http/Controllers" -Recurse -File -Filter "*.php" | ForEach-Object { $_.FullName } }
$map = [ordered]@{
    "ordinary.account.login.index" = "ordinary.account.login.login"
    "ordinary.admission.index" = "ordinary.admission.admission"
    "ordinary.checkout.cart.index" = "ordinary.checkout.cart.cart"
    "ordinary.plan-your-visit.index.visit" = "ordinary.plan-your-visit.visit.visit"
}
foreach ($f in $targets | Select-Object -Unique) {
    $raw = Get-Content -LiteralPath $f -Raw
    $new = $raw
    foreach ($k in $map.Keys) { $new = $new.Replace($k, $map[$k]) }
    if ($new -ne $raw) { Set-Content -LiteralPath $f -Value $new -NoNewline; $updatedRefsFiles++ }
}

$blades = Get-ChildItem -Path $viewsRoot -Recurse -File -Filter "*.blade.php" | Sort-Object FullName
foreach ($b in $blades) {
    $raw = Get-Content -LiteralPath $b.FullName -Raw
    $lines = [System.Collections.Generic.List[string]]::new()
    ($raw -split "`r?`n") | ForEach-Object { [void]$lines.Add($_) }
    $relBlade = $b.FullName.Substring($viewsRoot.Length).TrimStart('\\').Replace('\\','/')
    $mirrorCss = "resources/css/" + ($relBlade -replace "\.blade\.php$",".css")
    $hasJs = $raw.Contains("resources/js/app.js")

    $keep = [System.Collections.Generic.List[string]]::new()
    $firstViteIndex = $null
    for ($i=0; $i -lt $lines.Count; $i++) {
        $ln = $lines[$i]
        $isTargetVite = $ln.Contains("@vite") -and ($ln.Contains("resources/css/") -or $ln.Contains("resources/js/app.js"))
        if ($isTargetVite) { if ($null -eq $firstViteIndex) { $firstViteIndex = $keep.Count }; continue }
        [void]$keep.Add($ln)
    }

    if ($null -eq $firstViteIndex) {
        $headIdx = -1
        for ($i=0; $i -lt $keep.Count; $i++) { if ($keep[$i] -match "<head[^>]*>") { $headIdx = $i; break } }
        if ($headIdx -ge 0) { $firstViteIndex = $headIdx + 1 } else { $firstViteIndex = 0 }
    }

    $insert = @("@vite('resources/css/app.css')", "@vite('$mirrorCss')")
    if ($hasJs) { $insert += "@vite('resources/js/app.js')" }

    $newLines = [System.Collections.Generic.List[string]]::new()
    for ($i=0; $i -lt $keep.Count; $i++) {
        if ($i -eq $firstViteIndex) { $insert | ForEach-Object { [void]$newLines.Add($_) } }
        [void]$newLines.Add($keep[$i])
    }
    if ($firstViteIndex -ge $keep.Count) { $insert | ForEach-Object { [void]$newLines.Add($_) } }

    $newRaw = ($newLines -join "`r`n")
    if ($newRaw -ne $raw) { Set-Content -LiteralPath $b.FullName -Value $newRaw -NoNewline; $updatedBladeFiles++ }
}

$bladesAfter = Get-ChildItem -Path $viewsRoot -Recurse -File -Filter "*.blade.php"
foreach ($b in $bladesAfter) {
    $relBlade = $b.FullName.Substring($viewsRoot.Length).TrimStart('\\').Replace('\\','/')
    $cssRel = ($relBlade -replace "\.blade\.php$", ".css")
    $cssFull = Join-Path $cssRoot ($cssRel -replace "/","\\")
    if (-not (Test-Path -LiteralPath $cssFull)) {
        $dir = Split-Path -Path $cssFull -Parent
        if (-not (Test-Path -LiteralPath $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
        New-Item -ItemType File -Path $cssFull -Force | Out-Null
        $createdCss++
    }
}

$remainingIndex = Get-ChildItem -Path $viewsRoot -Recurse -File -Filter "index.blade.php" | Where-Object { (Split-Path $_.DirectoryName -Leaf) -ne "index" } | ForEach-Object { $_.FullName.Substring($viewsRoot.Length).TrimStart('\\').Replace('\\','/') }
$refFiles = @()
if (Test-Path -LiteralPath "routes/web.php") { $refFiles += (Resolve-Path "routes/web.php").Path }
if (Test-Path -LiteralPath "app/Http/Controllers") { $refFiles += Get-ChildItem -Path "app/Http/Controllers" -Recurse -File -Filter "*.php" | ForEach-Object { $_.FullName } }
$indexRefs = foreach ($f in ($refFiles | Select-Object -Unique)) {
    $matches = Select-String -Path $f -Pattern 'view\('
    foreach ($m in $matches) { if ($m.Line -match 'index') { [PSCustomObject]@{ File = $m.Path.Substring($root.Path.Length).TrimStart('\\').Replace('\\','/'); Line = $m.LineNumber; Text = $m.Line.Trim() } } }
}

$missingApp = @(); $missingMirror = @()
$bladesCheck = Get-ChildItem -Path $viewsRoot -Recurse -File -Filter "*.blade.php"
foreach ($b in $bladesCheck) {
    $raw = Get-Content -LiteralPath $b.FullName -Raw
    $rel = $b.FullName.Substring($viewsRoot.Length).TrimStart('\\').Replace('\\','/')
    $mirrorLine = "@vite('resources/css/" + ($rel -replace "\.blade\.php$", ".css") + "')"
    if (-not $raw.Contains("@vite('resources/css/app.css')")) { $missingApp += $rel }
    if (-not $raw.Contains($mirrorLine)) { $missingMirror += $rel }
}

"CHECK: remaining index.blade.php with non-index parent = $($remainingIndex.Count)"
if ($remainingIndex.Count -gt 0) { $remainingIndex | Select-Object -First 20 | ForEach-Object { " - $_" } }
"CHECK: view refs containing index in routes/controllers = $($indexRefs.Count)"
if ($indexRefs.Count -gt 0) { $indexRefs | Select-Object -First 20 | ForEach-Object { " - $($_.File):$($_.Line): $($_.Text)" } }
"CHECK: blades missing app.css vite line = $($missingApp.Count)"
if ($missingApp.Count -gt 0) { $missingApp | Select-Object -First 20 | ForEach-Object { " - $_" } }
"CHECK: blades missing mirror css vite line = $($missingMirror.Count)"
if ($missingMirror.Count -gt 0) { $missingMirror | Select-Object -First 20 | ForEach-Object { " - $_" } }
"COUNTS: blade_renamed=$renamedBlade css_renamed=$renamedCss css_created=$createdCss refs_files_updated=$updatedRefsFiles blades_updated=$updatedBladeFiles"