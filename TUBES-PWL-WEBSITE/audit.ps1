$views = Get-ChildItem 'resources/views' -Recurse -File -Filter '*.blade.php' |
  Where-Object {
    $_.FullName -notmatch '[\\/]resources[\\/]views[\\/](layouts|components)[\\/]' -and
    $_.FullName -notmatch '[\\/]layout\.blade\.php$' -and
    $_.FullName -notmatch '[\\/]layout[\\/]' -and
    $_.FullName -notmatch '[\\/]resources[\\/]views[\\/]admin[\\/]components[\\/]'
  }
$cssFiles = Get-ChildItem 'resources/css' -Recurse -File -Filter '*.css'
$refMap = @{}
$issues = foreach ($v in $views) {
  $raw = Get-Content $v.FullName -Raw
  $pushBlocks = [regex]::Matches($raw, "(?s)@push\('styles'\)(.*?)@endpush")
  $refs = New-Object System.Collections.Generic.List[string]
  foreach ($m in $pushBlocks) {
    foreach ($n in [regex]::Matches($m.Groups[1].Value, "@vite\(\s*'(?<p>resources/css/[^']+)'\s*\)")) { $refs.Add($n.Groups['p'].Value) }
    foreach ($n in [regex]::Matches($m.Groups[1].Value, '@vite\(\s*"(?<p>resources/css/[^"]+)"\s*\)')) { $refs.Add($n.Groups['p'].Value) }
  }
  foreach ($m in [regex]::Matches($raw, "@vite\(\s*'(?<p>resources/css/[^']+)'\s*\)")) { $refs.Add($m.Groups['p'].Value) }
  foreach ($m in [regex]::Matches($raw, '@vite\(\s*"(?<p>resources/css/[^"]+)"\s*\)')) { $refs.Add($m.Groups['p'].Value) }
  $uniq = $refs | Sort-Object -Unique
  foreach ($r in $uniq) { if (-not $refMap.ContainsKey($r)) { $refMap[$r] = @() }; $refMap[$r] += $v.FullName }
  $missing = @($uniq | Where-Object { -not (Test-Path $_) })
  $likelyException = $raw -match 'auth-form-style|shared partial|page fragment|page fragments|fragment'
  [pscustomobject]@{
    File = $v.FullName
    PushBlocks = $pushBlocks.Count
    CssRefs = $uniq
    MissingCss = $missing
    MultiPush = ($pushBlocks.Count -gt 1)
    MultiVite = ($uniq.Count -gt 1)
    LikelyException = $likelyException
  }
}
$unused = foreach ($c in $cssFiles) {
  $rel = $c.FullName.Substring((Resolve-Path '.').Path.Length).TrimStart('\\','/')
  $vitePath = ($rel -replace '^resources[\\/]css[\\/]', 'resources/css/')
  if (-not $refMap.ContainsKey($vitePath)) { $vitePath }
}
[pscustomobject]@{
  Issues = @($issues | Where-Object { $_.PushBlocks -gt 1 -or $_.MultiVite -or $_.MissingCss.Count -gt 0 -or $_.LikelyException })
  UnusedCss = @($unused)
} | ConvertTo-Json -Depth 6
