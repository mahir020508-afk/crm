<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-brand-black border-r border-white/5 flex flex-col justify-between hidden md:flex shrink-0 min-h-screen">
    <div>
        <div class="p-6 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-electric to-brand-turquoise flex items-center justify-center text-white shadow-lg shadow-brand-electric/20">
                <i class="fa-solid fa-bolt text-lg"></i>
            </div>
            <div>
                <span class="text-white font-bold text-lg tracking-wide block leading-none">CRM PRO</span>
                <span class="text-xs text-slate-500 font-mono tracking-wider uppercase"><?= htmlspecialchars($_SESSION['ad_soyad'] ?? 'YÖNETİCİ') ?></span>
            </div>
        </div>

        <nav class="px-4 space-y-1.5 mt-2">
            <a href="index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition font-medium text-sm <?= $currentPage == 'index.php' ? 'bg-brand-electric text-white shadow-lg shadow-brand-electric/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <i class="fa-solid fa-gauge-high text-lg w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="uyeler.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition font-medium text-sm <?= $currentPage == 'uyeler.php' ? 'bg-brand-electric text-white shadow-lg shadow-brand-electric/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <i class="fa-solid fa-users text-lg w-5 text-center"></i>
                <span>Üyeler Portföyü</span>
            </a>
            <a href="bolgeler.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition font-medium text-sm <?= $currentPage == 'bolgeler.php' ? 'bg-brand-electric text-white shadow-lg shadow-brand-electric/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <i class="fa-solid fa-map-location-dot text-lg w-5 text-center"></i>
                <span>Şehirler / Bölgeler</span>
            </a>
            <a href="odemeler.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition font-medium text-sm <?= $currentPage == 'odemeler.php' ? 'bg-brand-electric text-white shadow-lg shadow-brand-electric/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <i class="fa-solid fa-credit-card text-lg w-5 text-center"></i>
                <span>Tahsilat & Kasa</span>
            </a>
            <a href="ayarlar.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition font-medium text-sm <?= $currentPage == 'ayarlar.php' ? 'bg-brand-electric text-white shadow-lg shadow-brand-electric/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <i class="fa-solid fa-user-shield text-lg w-5 text-center"></i>
                <span>Ayarlar & Yöneticiler</span>
            </a>
        </nav>
    </div>

    <div class="p-4 border-t border-white/5">
        <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition font-medium text-sm">
            <i class="fa-solid fa-arrow-right-from-bracket text-lg w-5 text-center"></i>
            <span>Güvenli Çıkış</span>
        </a>
    </div>
</aside>

<div class="md:hidden bg-brand-black border-b border-white/5 p-4 flex justify-between items-center sticky top-0 z-40">
    <div class="flex items-center space-x-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-electric to-brand-turquoise flex items-center justify-center text-white">
            <i class="fa-solid fa-bolt text-sm"></i>
        </div>
        <span class="text-white font-bold text-base">CRM PRO</span>
    </div>
    <button id="mobileMenuBtn" class="text-slate-400 hover:text-white p-2">
        <i class="fa-solid fa-bars text-xl"></i>
    </button>
</div>

<div id="mobileDrawer" class="fixed inset-0 bg-brand-black/95 z-50 flex flex-col justify-between p-6 transform -translate-x-full transition-transform duration-300 md:hidden">
    <div>
        <div class="flex justify-between items-center mb-8">
            <span class="text-white font-bold text-lg">Menü</span>
            <button id="mobileCloseBtn" class="text-slate-400 hover:text-white text-2xl">&times;</button>
        </div>
        <nav class="space-y-2">
            <a href="index.php" class="block px-4 py-3 rounded-xl text-slate-300 hover:bg-white/5 font-medium">Dashboard</a>
            <a href="uyeler.php" class="block px-4 py-3 rounded-xl text-slate-300 hover:bg-white/5 font-medium">Üyeler Portföyü</a>
            <a href="bolgeler.php" class="block px-4 py-3 rounded-xl text-slate-300 hover:bg-white/5 font-medium">Şehirler / Bölgeler</a>
            <a href="odemeler.php" class="block px-4 py-3 rounded-xl text-slate-300 hover:bg-white/5 font-medium">Tahsilat & Kasa</a>
            <a href="ayarlar.php" class="block px-4 py-3 rounded-xl text-slate-300 hover:bg-white/5 font-medium">Ayarlar & Yöneticiler</a>
        </nav>
    </div>
    <a href="logout.php" class="block px-4 py-3 rounded-xl text-rose-400 hover:bg-rose-500/10 font-medium">Güvenli Çıkış</a>
</div>

<script>
document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
    document.getElementById('mobileDrawer').classList.remove('-translate-x-full');
});
document.getElementById('mobileCloseBtn')?.addEventListener('click', () => {
    document.getElementById('mobileDrawer').classList.add('-translate-x-full');
});
</script>