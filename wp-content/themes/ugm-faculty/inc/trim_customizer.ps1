# trim_customizer.ps1
# Script sekali pakai: trim baris tidak diperlukan dari customizer.php
# Jalankan dari direktori inc/:  .\trim_customizer.ps1

$filepath = "customizer.php"

if ( -not (Test-Path $filepath) ) {
    Write-Host "ERROR: $filepath tidak ditemukan. Pastikan dijalankan dari direktori inc/." -ForegroundColor Red
    exit 1
}

$lines = Get-Content $filepath -Encoding UTF8
Write-Host "Lines sebelum: $($lines.Count)"

# Pertahankan baris 1-317 (index 0-316) dan baris 1272-end (index 1271+)
$kept = @($lines[0..316]) + @($lines[1271..($lines.Count - 1)])

Set-Content $filepath -Value $kept -Encoding UTF8
Write-Host "Lines sesudah: $($kept.Count)" -ForegroundColor Green
Write-Host "Done."
