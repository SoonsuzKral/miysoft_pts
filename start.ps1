# MİYSOFT PTS — Tüm Servisleri Başlat (PowerShell)
# Kullanım: .\start.ps1

Write-Host "==============================" -ForegroundColor Cyan
Write-Host " MİYSOFT PTS — Servis Başlat" -ForegroundColor Cyan
Write-Host "==============================" -ForegroundColor Cyan

$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ProjectRoot

# .env kontrolü
if (-not (Test-Path ".env")) {
    Write-Host "[!] .env dosyası bulunamadı. Önce http://localhost/install adresini ziyaret edin." -ForegroundColor Yellow
    exit 1
}

Write-Host "[1/4] PHP Artisan Serve başlatılıyor... (http://127.0.0.1:8000)" -ForegroundColor Green
$serveJob = Start-Job -ScriptBlock { Set-Location $using:ProjectRoot; php artisan serve --host=127.0.0.1 --port=8000 }

Write-Host "[2/4] Vite dev sunucusu başlatılıyor..." -ForegroundColor Green
$viteJob = Start-Job -ScriptBlock { Set-Location $using:ProjectRoot; npm run dev }

Write-Host "[3/4] Laravel Reverb (WebSocket) başlatılıyor..." -ForegroundColor Green
$reverbJob = Start-Job -ScriptBlock { Set-Location $using:ProjectRoot; php artisan reverb:start }

Write-Host "[4/4] Kuyruk işçisi (Queue Worker) başlatılıyor..." -ForegroundColor Green
$queueJob = Start-Job -ScriptBlock { Set-Location $using:ProjectRoot; php artisan queue:work }

Write-Host ""
Write-Host "==============================" -ForegroundColor Cyan
Write-Host " Tüm servisler çalışıyor!" -ForegroundColor Cyan
Write-Host " PHP Serve:    http://127.0.0.1:8000" -ForegroundColor White
Write-Host " Vite:         http://localhost:5173" -ForegroundColor White
Write-Host " Reverb:       ws://localhost:8080" -ForegroundColor White
Write-Host " Queue Worker: aktif" -ForegroundColor White
Write-Host "==============================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Durdurmak için: Stop-Job -Id $($serveJob.Id), $($viteJob.Id), $($reverbJob.Id), $($queueJob.Id)" -ForegroundColor Yellow
Write-Host "Çıkmak için Ctrl+C" -ForegroundColor Yellow

# Bekle
try {
    while ($true) {
        Start-Sleep -Seconds 1
        # Jobs hala çalışıyor mu kontrol et
        $jobs = @($serveJob, $viteJob, $reverbJob, $queueJob) | Where-Object { $_.State -eq 'Running' }
        if ($jobs.Count -lt 4) {
            Write-Host "[!] Bazı servisler durdu. Log'ları kontrol edin." -ForegroundColor Red
            Get-Job | Where-Object { $_.State -eq 'Failed' } | Receive-Job
            break
        }
    }
} finally {
    Write-Host "Servisler durduruluyor..." -ForegroundColor Yellow
    $serveJob, $viteJob, $reverbJob, $queueJob | Stop-Job -PassThru | Remove-Job
}
