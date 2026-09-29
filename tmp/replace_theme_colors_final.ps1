
$files = Get-ChildItem -Recurse -Include *.vue, *.blade.php, *.css
$targets = @(
    '#20a8d8', '#2a7fff', '#1d97c2', '#3490dc',  # Blues
    '#4dbd74', '#50a306', '#41af67', '#5cb85c'   # Greens
)

foreach ($file in $files) {
    if ($file.FullName -match "node_modules") { continue }
    if ($file.FullName -match "vendor") { continue }
    
    $content = Get-Content $file.FullName -Raw
    $newContent = $content
    
    # Blues to Purple
    $newContent = $newContent -replace '#20a8d8', '#ad1aac'
    $newContent = $newContent -replace '#2a7fff', '#ad1aac'
    $newContent = $newContent -replace '#1d97c2', '#ad1aac'
    $newContent = $newContent -replace '#3490dc', '#ad1aac'
    
    # Greens to New Green
    $newContent = $newContent -replace '#4dbd74', '#33e034'
    $newContent = $newContent -replace '#50a306', '#33e034'
    $newContent = $newContent -replace '#41af67', '#33e034'
    $newContent = $newContent -replace '#5cb85c', '#33e034'
    
    # RGBA Primary -> Purple
    $newContent = $newContent -replace '32,\s*168,\s*216', '173, 26, 172'
    
    # RGBA Success -> New Green
    $newContent = $newContent -replace '77,\s*189,\s*116', '51, 224, 52'
    
    if ($content -ne $newContent) {
        Set-Content -Path $file.FullName -Value $newContent
        Write-Host "Updated $($file.FullName)"
    }
}
