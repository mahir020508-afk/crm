<?php
include 'config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$toplamUye = (int)$db->query("SELECT COUNT(*) FROM uyeler")->fetchColumn();
$toplamTahsilat = (float)$db->query("SELECT SUM(miktar) FROM odemeler")->fetchColumn();
$aktifSehir = (int)$db->query("SELECT COUNT(DISTINCT sehir_plaka) FROM uyeler")->fetchColumn();
$ortalamaCiro = $toplamUye > 0 ? ($toplamTahsilat / $toplamUye) : 0;

$haritaSorgu = $db->query("SELECT sehir_plaka, COUNT(*) as sayi FROM uyeler GROUP BY sehir_plaka")->fetchAll();
$sehirVerileri = [];
foreach ($haritaSorgu as $row) {
    if (!empty($row['sehir_plaka'])) {
        $p = sprintf("%02d", (int)$row['sehir_plaka']);
        $sehirVerileri[$p] = (int)$row['sayi'];
    }
}

$liderSehirler = $db->query("
    SELECT s.sehir_adi, s.plaka,
           COUNT(u.id) as uye_sayisi,
           COALESCE(SUM(o.miktar), 0) as toplam_ciro
    FROM sehirler s
    INNER JOIN uyeler u ON s.plaka = u.sehir_plaka
    LEFT JOIN odemeler o ON u.id = o.uye_id
    GROUP BY s.sehir_adi, s.plaka
    ORDER BY toplam_ciro DESC, uye_sayisi DESC
    LIMIT 5
")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-1 w-full min-w-0 p-6 bg-slate-50 min-h-screen overflow-y-auto">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Saha & Analitik Portalı</h1>
            <p class="text-slate-500 text-xs font-medium">İl bazlı üye yoğunluğu ve finansal performans akışı</p>
        </div>
        <div>
            <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-white border border-slate-200 text-slate-600 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-brand-turquoise mr-2 animate-pulse"></span>
                Canlı Sistem: <?= date('d.m.Y') ?>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-brand-electric text-white p-6 rounded-3xl shadow-lg shadow-blue-500/10 relative overflow-hidden flex flex-col justify-between h-36">
            <span class="text-[11px] font-bold uppercase tracking-wider text-white/80">Toplam Kayıtlı Üye</span>
            <div class="text-4xl font-black"><?= number_format($toplamUye) ?></div>
            <i class="fa-solid fa-users absolute right-4 -bottom-2 text-7xl text-white/10 pointer-events-none"></i>
        </div>

        <div class="bg-brand-black text-white p-6 rounded-3xl shadow-xl relative overflow-hidden flex flex-col justify-between h-36 border border-white/5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Toplam Tahsilat</span>
            <div class="text-3xl font-black text-white tracking-tight"><?= number_format($toplamTahsilat, 2) ?> ₺</div>
            <i class="fa-solid fa-sack-dollar absolute right-4 -bottom-2 text-7xl text-white/5 pointer-events-none"></i>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between h-36">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Faal İl Kapsamı</span>
                <div class="text-3xl font-black text-slate-800">
                    <?= $aktifSehir ?> <span class="text-xs text-slate-400 font-semibold">/ 81 İl</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-500 flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-location-dot"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between h-36">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Ortalama Ciro / Üye</span>
                <div class="text-2xl font-black text-slate-800 tracking-tight">
                    <?= number_format($ortalamaCiro, 1) ?> ₺
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-brand-electric flex items-center justify-center text-xl shadow-sm">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-electric mr-2.5"></span>
                    Türkiye Yoğunluk Isı Haritası
                </h3>
                <span class="text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
                    Dinamik Isı Dağılımı
                </span>
            </div>
            <div id="turkiyeHaritasi" class="w-full h-[480px]"></div>
        </div>

        <div class="lg:col-span-4 bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-2 mb-6">
                    <i class="fa-solid fa-trophy text-amber-400 text-lg"></i>
                    <h3 class="text-sm font-bold text-slate-800">Lider Şehirler</h3>
                </div>

                <div class="space-y-4">
                    <?php if(empty($liderSehirler)): ?>
                        <div class="text-center py-10 text-slate-400 text-xs">Henüz üye kaydı bulunmamaktadır.</div>
                    <?php else: ?>
                        <?php foreach ($liderSehirler as $item): ?>
                        <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                            <div>
                                <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($item['sehir_adi']) ?></div>
                                <div class="text-xs text-slate-400 font-medium"><?= number_format($item['toplam_ciro'], 2) ?> ₺ Tahsilat</div>
                            </div>
                            <span class="bg-brand-black text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                <?= $item['uye_sayisi'] ?> Üye
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-50 text-center">
                <a href="bolgeler.php" class="text-xs font-bold text-brand-electric hover:underline inline-flex items-center">
                    Tüm Şehirleri İncele <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
</main>

<script src="https://code.highcharts.com/maps/highmaps.js"></script>
<script src="https://code.highcharts.com/mapdata/countries/tr/tr-all.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sehirVerileri = <?= json_encode($sehirVerileri) ?>;
    var mapGeo = Highcharts.maps['countries/tr/tr-all'];
    var mapSeriesData = [];

    if (mapGeo && mapGeo.features) {
        mapGeo.features.forEach(function(f) {
            var code = f.properties['postal-code'] || f.properties['code'];
            if (code) {
                code = code.replace('TR-', '');
                if (code.length === 1) code = '0' + code;
            }
            var val = (code && sehirVerileri[code]) ? parseInt(sehirVerileri[code]) : null;
            mapSeriesData.push({
                'hc-key': f.properties['hc-key'],
                'value': val
            });
        });
    }

    Highcharts.mapChart('turkiyeHaritasi', {
        chart: { backgroundColor: 'transparent', style: { fontFamily: 'Plus Jakarta Sans' } },
        title: { text: '' },
        mapNavigation: { enabled: true, buttonOptions: { verticalAlign: 'bottom' } },
        colorAxis: {
            min: 1,
            minColor: '#00f2fe',
            maxColor: '#0052ff'
        },
        legend: {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'bottom'
        },
        tooltip: {
            backgroundColor: '#090d16',
            borderColor: '#0052ff',
            borderRadius: 12,
            style: { color: '#ffffff' },
            headerFormat: '',
            pointFormatter: function() {
                var count = this.value ? this.value : 0;
                return '<b>' + this.name + '</b><br><span style="color:#00f2fe;">Kayıtlı Üye:</span> <b>' + count + ' Üye</b>';
            }
        },
        series: [{
            data: mapSeriesData,
            name: 'Üye Sayısı',
            mapData: mapGeo,
            joinBy: 'hc-key',
            nullColor: '#f8fafc',
            borderColor: '#e2e8f0',
            borderWidth: 0.8,
            states: { hover: { color: '#00f2fe' } },
            dataLabels: {
                enabled: true,
                formatter: function() {
                    return this.point.value > 0 ? this.point.value : '';
                },
                style: { fontSize: '10px', fontWeight: 'bold', textOutline: 'none', color: '#090d16' }
            }
        }]
    });
});
</script>

<?php include 'includes/footer.php'; ?>