
$files = Get-ChildItem -Recurse -Include *.vue, *.blade.php
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $newContent = $content -replace 'img/logo.png', 'img/LOGO-LUPA.jpg'
    if ($content -ne $newContent) {
        Set-Content -Path $file.FullName -Value $newContent
        Write-Host "Updated $($file.FullName)"
    }
}
