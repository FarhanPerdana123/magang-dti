$filepath = "customizer.php"
$lines = Get-Content $filepath -Encoding UTF8
# Keep baris 1-317 (index 0-316), dan baris 1272-1274 (index 1271-end)
$kept = @($lines[0..316]) + @($lines[1271..($lines.Count - 1)])
Set-Content $filepath -Value $kept -Encoding UTF8
Write-Host "Done. Lines remaining: $($kept.Count)"
