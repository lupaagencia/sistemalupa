
$files = Get-ChildItem -Recurse -Include *.vue, *.blade.php, *.css
$targets = @('#20a8d8', '#2a7fff', '#4dbd74', '#50a306')
# Map them: blues to purple, greens to green
# We can use a simple switch or two regex passes

foreach ($file in $files) {
    if ($file.FullName -match "node_modules") { continue }
    if ($file.FullName -match "vendor") { continue }
    
    $content = Get-Content $file.FullName -Raw
    $newContent = $content
    
    # Blues to Purple
    $newContent = $newContent -replace '#20a8d8', '#ad1aac'
    $newContent = $newContent -replace '#2a7fff', '#ad1aac'
    
    # Greens to new Green
    $newContent = $newContent -replace '#4dbd74', '#33e034'
    $newContent = $newContent -replace '#50a306', '#33e034'
    
    if ($content -ne $newContent) {
        Set-Content -Path $file.FullName -Value $newContent
        Write-Host "Updated $($file.FullName)"
    }
}
