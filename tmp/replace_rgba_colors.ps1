
$files = Get-ChildItem -Recurse -Include *.vue, *.blade.php, *.css
# New Green: 51, 224, 52
# New Purple: 173, 26, 172

foreach ($file in $files) {
    if ($file.FullName -match "node_modules") { continue }
    if ($file.FullName -match "vendor") { continue }
    
    $content = Get-Content $file.FullName -Raw
    $newContent = $content
    
    # RGBA Primary (Blue) -> Purple
    $newContent = $newContent -replace '32,\s*168,\s*216', '173, 26, 172'
    
    # RGBA Success (Green) -> New Green
    $newContent = $newContent -replace '77,\s*189,\s*116', '51, 224, 52'
    
    if ($content -ne $newContent) {
        Set-Content -Path $file.FullName -Value $newContent
        Write-Host "Updated (RGBA) $($file.FullName)"
    }
}
