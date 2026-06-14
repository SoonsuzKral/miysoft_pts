{{-- MİYSOFT PTS — Global Scripts --}}

{{-- Alpine.js (reaktivite motoru: x-data, x-show, @click) --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- jQuery + DataTables --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

{{-- ── Sidebar & Dropdown JS ── --}}
<script>
    // Sidebar
    window.openSidebar = function () {
        document.getElementById('admin-sidebar').classList.add('sidebar-open');
        document.getElementById('sidebar-overlay').classList.add('active');
    };
    window.closeSidebar = function () {
        document.getElementById('admin-sidebar').classList.remove('sidebar-open');
        document.getElementById('sidebar-overlay').classList.remove('active');
    };
    document.addEventListener('DOMContentLoaded', function () {
        closeSidebar();
    });
    window.addEventListener('pageshow', function () {
        closeSidebar();
    });
    // Sidebar link tıklamaları: overlay varsa kapat, sorunsuz yönlendir
    document.addEventListener('click', function (e) {
        var link = e.target.closest('.pts-nav-link');
        if (link) {
            e.preventDefault();
            closeSidebar();
            window.location.href = link.href;
        }
    });

    // Notif dropdown
    window.toggleNotif = function () {
        const dd = document.getElementById('notif-dropdown');
        const opening = !dd.classList.contains('open');
        dd.classList.toggle('open');
        document.getElementById('user-dropdown').classList.remove('open');
        if (opening) loadNotifications();
    };

    // User menu dropdown
    window.toggleUserMenu = function () {
        document.getElementById('user-dropdown').classList.toggle('open');
        document.getElementById('notif-dropdown').classList.remove('open');
    };

    // Dışarı tıklayınca kapat
    document.addEventListener('click', function (e) {
        const userWrap  = document.getElementById('user-menu-wrap');
        const notifBtn  = e.target.closest('[onclick="toggleNotif()"]');
        const userBtn   = e.target.closest('[onclick="toggleUserMenu()"]');
        if (!notifBtn && !e.target.closest('#notif-dropdown')) {
            document.getElementById('notif-dropdown')?.classList.remove('open');
        }
        if (!userBtn && userWrap && !userWrap.contains(e.target)) {
            document.getElementById('user-dropdown')?.classList.remove('open');
        }
    });
</script>

{{-- Axios CSRF Setup --}}
<script>
    // Axios global CSRF header
    if (typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

        // Global hata yakalayıcı
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 401) {
                    window.location.href = '/login';
                } else if (error.response?.status === 403) {
                    Swal.fire('Yetkisiz', 'Bu işlem için yetkiniz bulunmuyor.', 'error');
                } else if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    let msg = Object.values(errors).flat().join('<br>');
                    Swal.fire({ icon: 'warning', title: 'Doğrulama Hatası', html: msg });
                } else if (error.response?.status >= 500) {
                    Swal.fire('Sunucu Hatası', 'Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.', 'error');
                }
                return Promise.reject(error);
            }
        );
    }

    // jQuery CSRF setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Flash mesajları otomatik kapat
    setTimeout(() => {
        ['alert-success', 'alert-info', 'alert-warning'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.transition = 'opacity 0.5s', el.style.opacity = '0', setTimeout(() => el.remove(), 500);
        });
    }, 5000);

    // Global delete confirm helper
    window.confirmDelete = function(url, callback) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu işlem geri alınamaz!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FA6001',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        }).then(result => {
            if (result.isConfirmed) {
                if (url) {
                    axios.delete(url).then(res => {
                        Swal.fire('Silindi!', res.data.message || 'Kayıt silindi.', 'success');
                        if (callback) callback(res);
                    });
                } else if (callback) {
                    callback();
                }
            }
        });
    };

    // Toast notification helper
    window.toast = function(type, message) {
        const icons = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icons[type] || 'info',
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    };

    // En son görülen bildirim ID'si (polling ile yeni bildirim tespiti için)
    let _lastNotifId = null;

    const colorMap = {
        yellow: 'bg-yellow-100 text-yellow-600',
        orange: 'bg-orange-100 text-orange-600',
        red:    'bg-red-100 text-red-600',
        green:  'bg-green-100 text-green-600',
        blue:   'bg-blue-100 text-blue-600',
    };

    function loadNotifications() {
        if (typeof axios === 'undefined') return;
        axios.get('{{ route("admin.notifications.recent") }}', { params: { limit: 8 } })
            .then(res => {
                const list  = document.getElementById('notif-list');
                const badge = document.getElementById('notif-badge');
                const items = res.data.data;
                const count = res.data.unread_count;

                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }

                if (!items.length) {
                    list.innerHTML = `<div style="padding:1.5rem;text-align:center;font-size:.8125rem;color:#94a3b8">Yeni bildirim yok</div>`;
                    return;
                }

                // Yeni bildirim tespiti (polling yedeği — Echo olmasa da çalışır)
                const latest = items[0];
                if (_lastNotifId === null) {
                    _lastNotifId = latest.id;
                } else if (latest.id !== _lastNotifId && !latest.is_read) {
                    _lastNotifId = latest.id;
                    playNotificationSound();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: latest.title || 'Bildirim',
                            text: latest.message || '',
                            showConfirmButton: true,
                            confirmButtonText: 'İncele',
                            confirmButtonColor: '#02E0FB',
                            timer: 5000,
                            timerProgressBar: true,
                        });
                    }
                }

                list.innerHTML = items.map(n => {
                    const cc = colorMap[n.color] || 'bg-gray-100 text-gray-500';
                    return `
                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer ${!n.is_read ? 'bg-[#02E0FB]/5 border-l-2 border-l-[#02E0FB]' : ''}"
                         onclick="openNotification('${n.id}', '${n.action_url}')">
                        <div class="w-9 h-9 rounded-xl ${cc} flex items-center justify-center text-lg shrink-0 mt-0.5">${n.icon}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-800 leading-snug">${n.title}</p>
                                ${!n.is_read ? '<span class="w-2 h-2 rounded-full bg-[#02E0FB] shrink-0 mt-1.5"></span>' : ''}
                            </div>
                            <p class="text-xs text-gray-600 mt-0.5">${n.message}</p>
                            ${n.subtitle ? `<p class="text-xs text-gray-400 mt-0.5">${n.subtitle}</p>` : ''}
                            <p class="text-[10px] text-gray-300 mt-1">${n.time}</p>
                        </div>
                    </div>`;
                }).join('');
            }).catch(() => {});
    }

    window.openNotification = function(id, url) {
        // Okundu işaretle
        axios.post('{{ route("admin.notifications.markRead") }}', { id }).catch(() => {});
        if (url && url !== 'null') {
            // JSON show route'larını listing sayfalarına yönlendir (eski DB kayıtları için)
            const map = [
                { prefix: '/admin/leave/requests/',  target: '/admin/leave' },
                { prefix: '/admin/expenses/requests/', target: '/admin/expenses' },
                { prefix: '/admin/advances/requests/', target: '/admin/advances' },
                { prefix: '/admin/travel/',           target: '/admin/travel' },
            ];
            for (const m of map) {
                if (url.startsWith(m.prefix)) {
                    window.location.href = m.target;
                    return;
                }
            }
            window.location.href = url;
        }
        // Badge güncelle
        setTimeout(updateBadge, 300);
    };

    window.markAllRead = function() {
        axios.post('{{ route("admin.notifications.markRead") }}').then(res => {
            updateBadge(res.data.unread_count);
            loadNotifications();
        });
    };

    function updateBadge(count) {
        if (typeof axios === 'undefined') return;
        const badge = document.getElementById('notif-badge');
        if (!badge) return;
        const cnt   = typeof count === 'number' ? count : null;
        if (cnt !== null) {
            if (cnt > 0) { badge.textContent = cnt > 99 ? '99+' : cnt; badge.style.display = 'flex'; }
            else badge.style.display = 'none';
        } else {
            axios.get('{{ route("admin.notifications.unread") }}').then(r => {
                const c = r.data.count;
                if (c > 0) { badge.textContent = c > 99 ? '99+' : c; badge.style.display = 'flex'; }
                else badge.style.display = 'none';
            }).catch(() => {});
        }
    }

    // Sayfa yüklenince badge güncelle (30 sn periyot)
    if (document.getElementById('notif-badge')) {
        setTimeout(() => {
            if (typeof axios !== 'undefined') {
                updateBadge();
                setInterval(updateBadge, 30000);
                setInterval(loadNotifications, 60000);
            }
        }, 500);
    }

    // ─── Bildirim Sesi (Web Audio API — autoplay politikası için resume) ─────
    window._audioCtx = null;

    function playNotificationSound() {
        try {
            if (!window._audioCtx) {
                window._audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                // İlk kullanıcı tıklamasında AudioContext'i resume et
                document.addEventListener('click', function resume() {
                    if (window._audioCtx?.state === 'suspended') window._audioCtx.resume();
                    document.removeEventListener('click', resume);
                }, { once: true });
            }
            if (window._audioCtx.state === 'suspended') {
                window._audioCtx.resume();
            }
            const ctx = window._audioCtx;
            const now = ctx.currentTime;

            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'triangle';
            osc1.frequency.value = 880;
            gain1.gain.setValueAtTime(0.25, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.12);
            osc1.connect(gain1).connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.12);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'triangle';
            osc2.frequency.value = 1320;
            gain2.gain.setValueAtTime(0.001, now);
            gain2.gain.linearRampToValueAtTime(0.28, now + 0.08);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
            osc2.connect(gain2).connect(ctx.destination);
            osc2.start(now + 0.1);
            osc2.stop(now + 0.45);
        } catch (e) {}
    }

    // ─── Reverb / Echo Gerçek Zamanlı Bildirimler ──────────────────────────────
    // Echo, Vite modülüyle asenkron yüklenir; bu inline script daha önce çalışır.
    // Bu yüzden Echo hazır olana kadar 200ms aralıklarla deneriz.
    (function initEcho() {
        if (typeof Echo === 'undefined') {
            return setTimeout(initEcho, 200);
        }
        const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        if (!userId) return;

        // Kullanıcıya özel kanal
        Echo.private('App.Models.User.' + userId)
            .notification((notification) => {
                updateBadge();
                playNotificationSound();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: notification.title || 'Bildirim',
                        text: notification.message || '',
                        showConfirmButton: true,
                        confirmButtonText: 'İncele',
                        confirmButtonColor: '#02E0FB',
                        timer: 5000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('click', () => {
                                if (notification.action_url) {
                                    window.location.href = notification.action_url;
                                }
                            });
                        }
                    });
                }
            });

        // Admin genel kanalı
        Echo.channel('admin-notifications')
            .notification((notification) => {
                updateBadge();
                playNotificationSound();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: notification.title || 'Bildirim',
                        text: notification.message || '',
                        showConfirmButton: true,
                        confirmButtonText: 'İncele',
                        confirmButtonColor: '#02E0FB',
                        timer: 5000,
                        timerProgressBar: true,
                    });
                }
            });
    })();

</script>

{{-- Admin global JS --}}
<script src="{{ asset('js/admin/global.js') }}"></script>
