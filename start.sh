#!/bin/bash
# MİYSOFT PTS — Tüm Servisleri Başlat
# Kullanım: chmod +x start.sh && ./start.sh

set -e

echo "=============================="
echo " MİYSOFT PTS — Servis Başlat"
echo "=============================="

# Proje kök dizini
DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$DIR"

# .env kontrolü
if [ ! -f .env ]; then
    echo "[!] .env dosyası bulunamadı. Önce http://localhost/install adresini ziyaret edin."
    exit 1
fi

echo "[1/4] PHP Artisan Serve başlatılıyor... (http://127.0.0.1:8000)"
php artisan serve --host=127.0.0.1 --port=8000 &
SERVE_PID=$!

echo "[2/4] Vite dev sunucusu başlatılıyor..."
npm run dev &
VITE_PID=$!

echo "[3/4] Laravel Reverb (WebSocket) başlatılıyor..."
php artisan reverb:start &
REVERB_PID=$!

echo "[4/4] Kuyruk işçisi (Queue Worker) başlatılıyor..."
php artisan queue:work &
QUEUE_PID=$!

echo ""
echo "=============================="
echo " Tüm servisler çalışıyor!"
echo " PHP Serve:    http://127.0.0.1:8000"
echo " Vite:         http://localhost:5173"
echo " Reverb:       ws://localhost:8080"
echo " Queue Worker: aktif"
echo "=============================="
echo ""
echo "Durdurmak için Ctrl+C basın."

# Trap Ctrl+C and kill all background processes
trap "echo 'Servisler durduruluyor...'; kill $SERVE_PID $VITE_PID $REVERB_PID $QUEUE_PID 2>/dev/null; exit" SIGINT SIGTERM

# Wait for any process to exit
wait
