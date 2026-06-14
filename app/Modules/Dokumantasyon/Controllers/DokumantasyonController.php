<?php

namespace App\Modules\Dokumantasyon\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DokumantasyonController extends Controller
{
    public function index(Request $request, string $category = null, string $page = null)
    {
        $this->authorize('admin');

        $sidebar = $this->getSidebar();

        if (!$category && !$page) {
            $first = $sidebar[0] ?? null;
            if ($first && isset($first['pages'][0])) {
                $category = $first['id'];
                $page = $first['pages'][0]['id'];
            } elseif ($first) {
                $category = $first['id'];
                $page = $first['id'];
            }
        }

        $viewPath = $this->resolveView($category, $page);
        $pageMeta = $this->getPageMeta($sidebar, $category, $page);

        if (!view()->exists($viewPath)) {
            abort(404, 'Döküman sayfası bulunamadı.');
        }

        $html = view($viewPath)->render();

        return view('layouts.dokumantasyon', compact(
            'sidebar', 'category', 'page', 'html', 'pageMeta'
        ));
    }

    private function resolveView(?string $category, ?string $page): string
    {
        if ($page) {
            return "admin.dokumantasyon.pages.{$category}.{$page}";
        }
        return "admin.dokumantasyon.pages.{$category}.index";
    }

    private function getPageMeta(array $sidebar, ?string $currentCat, ?string $currentPage): array
    {
        foreach ($sidebar as $section) {
            if ($section['id'] !== $currentCat) continue;
            if ($section['id'] === $currentPage) {
                return ['title' => $section['label'], 'icon' => $section['icon'] ?? '📄'];
            }
            foreach ($section['pages'] ?? [] as $p) {
                if ($p['id'] === $currentPage) {
                    return ['title' => $p['label'], 'icon' => $p['icon'] ?? '📄'];
                }
            }
        }
        return ['title' => 'Dökümantasyon', 'icon' => '📄'];
    }

    private function getSidebar(): array
    {
        return [
            [
                'id' => 'genel-bakis',
                'label' => 'Genel Bakış',
                'icon' => '🏠',
                'pages' => [
                    ['id' => 'genel-bakis', 'label' => 'Dashboard', 'icon' => '📊'],
                ],
            ],
            [
                'id' => 'ik',
                'label' => 'İnsan Kaynakları',
                'icon' => '👥',
                'pages' => [
                    ['id' => 'personel', 'label' => 'Personel Yönetimi', 'icon' => '👤'],
                    ['id' => 'izin', 'label' => 'İzin Yönetimi', 'icon' => '🌴'],
                    ['id' => 'puantaj', 'label' => 'Puantaj', 'icon' => '⏱'],
                    ['id' => 'vardiya', 'label' => 'Vardiya', 'icon' => '🔄'],
                    ['id' => 'surec', 'label' => 'Süreç / Onboarding', 'icon' => '🚀'],
                ],
            ],
            [
                'id' => 'sirket',
                'label' => 'Şirket Yapısı',
                'icon' => '🏢',
                'pages' => [
                    ['id' => 'sirket-departman', 'label' => 'Şirket & Departman', 'icon' => '🏛'],
                    ['id' => 'tatil', 'label' => 'Tatil Takvimi', 'icon' => '📅'],
                ],
            ],
            [
                'id' => 'lokasyon',
                'label' => 'Lokasyon',
                'icon' => '📍',
                'pages' => [
                    ['id' => 'lokasyonlar', 'label' => 'Lokasyon Yönetimi', 'icon' => '🗺'],
                    ['id' => 'ozel-saat', 'label' => 'Özel Saat', 'icon' => '⏰'],
                ],
            ],
            [
                'id' => 'finans',
                'label' => 'Varlıklar & Finans',
                'icon' => '💰',
                'pages' => [
                    ['id' => 'envanter', 'label' => 'Envanter & Zimmet', 'icon' => '📦'],
                    ['id' => 'avans', 'label' => 'Avans', 'icon' => '💵'],
                    ['id' => 'masraf', 'label' => 'Masraf', 'icon' => '🧾'],
                    ['id' => 'seyahat', 'label' => 'Seyahat', 'icon' => '✈️'],
                    ['id' => 'arac', 'label' => 'Araç Yönetimi', 'icon' => '🚗'],
                    ['id' => 'hizmet', 'label' => 'Hizmetler', 'icon' => '🔧'],
                ],
            ],
            [
                'id' => 'etkilesim',
                'label' => 'Etkileşim',
                'icon' => '💬',
                'pages' => [
                    ['id' => 'duyuru', 'label' => 'Duyurular & Anketler', 'icon' => '📢'],
                    ['id' => 'ziyaretci', 'label' => 'Ziyaretçi', 'icon' => '🚪'],
                ],
            ],
            [
                'id' => 'sistem',
                'label' => 'Sistem',
                'icon' => '⚙️',
                'pages' => [
                    ['id' => 'raporlar', 'label' => 'Raporlar', 'icon' => '📊'],
                    ['id' => 'cms', 'label' => 'İçerik Yönetimi', 'icon' => '📝'],
                    ['id' => 'medya', 'label' => 'Medya Kütüphanesi', 'icon' => '🖼'],
                    ['id' => 'abonelik', 'label' => 'Abonelik', 'icon' => '⭐'],
                    ['id' => 'ayarlar', 'label' => 'Sistem Ayarları', 'icon' => '⚙️'],
                    ['id' => 'rol-yetki', 'label' => 'Rol & Yetki', 'icon' => '🔐'],
                ],
            ],
        ];
    }
}
