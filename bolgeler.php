<?php
include 'config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil']; 
    
    $check = $db->prepare("SELECT COUNT(*) FROM uyeler WHERE sehir_plaka = (SELECT plaka FROM sehirler WHERE id = ?)");
    $check->execute([$id]);
    $uyeSayisi = $check->fetchColumn();

    if ($uyeSayisi > 0) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Hata: Bu bölgede aktif üyeleriniz bulunmaktadır!'];
    } else {
        $delete = $db->prepare("DELETE FROM sehirler WHERE id = ?");
        $delete->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Bölge/Şehir başarıyla silindi.'];
    }
    header("Location: bolgeler.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sehir_adi'])) {
    $ad = htmlspecialchars(trim($_POST['sehir_adi']));
    $plaka = htmlspecialchars(trim($_POST['plaka']));

    if(!empty($ad) && !empty($plaka)) {
        $insert = $db->prepare("INSERT INTO sehirler (sehir_adi, plaka) VALUES (?, ?)");
        $insert->execute([$ad, $plaka]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Yeni bölge sisteme eklendi.'];
    }
    header("Location: bolgeler.php");
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="flex-1 w-full min-w-0 p-6 bg-slate-50 min-h-screen overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Bölge ve Şehir Yönetimi</h1>
            <p class="text-slate-500 text-sm">Faaliyet gösterilen bölgelerin listesi ve ciro analizi.</p>
        </div>
        <button onclick="openModal('modalSehirEkle')" class="bg-brand-electric hover:bg-brand-electricHover text-white px-5 py-2.5 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center font-semibold">
            <i class="fa-solid fa-plus mr-2"></i> Yeni Bölge Ekle
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6">
            <table id="tabloBolgeler" class="w-full text-left">
                <thead>
                    <tr class="text-slate-400 text-xs uppercase tracking-widest border-b border-slate-100">
                        <th class="pb-4 font-semibold text-center">#PLAKA</th>
                        <th class="pb-4 font-semibold">BÖLGE / ŞEHİR ADI</th>
                        <th class="pb-4 font-semibold text-center">ÜYE SAYISI</th>
                        <th class="pb-4 font-semibold text-center">TOPLAM HASILAT</th>
                        <th class="pb-4 font-semibold text-right">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="text-slate-600 text-sm">
                    <?php
                    $sorgu = $db->query("
                        SELECT s.*, 
                        (SELECT COUNT(*) FROM uyeler u WHERE u.sehir_plaka = s.plaka) as uye_sayisi,
                        (SELECT SUM(o.miktar) FROM odemeler o JOIN uyeler u ON o.uye_id = u.id WHERE u.sehir_plaka = s.plaka) as toplam_gelir
                        FROM sehirler s ORDER BY s.sehir_adi ASC
                    ");
                    while($row = $sorgu->fetch()):
                    ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50/80 transition">
                        <td class="py-4 text-center font-mono font-bold text-brand-electric">
                            #<?= $row['plaka'] ?>
                        </td>
                        <td class="py-4 font-bold text-slate-800">
                            <?= $row['sehir_adi'] ?>
                        </td>
                        <td class="py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                <?= $row['uye_sayisi'] ?> Üye
                            </span>
                        </td>
                        <td class="py-4 text-center font-bold text-emerald-600">
                            <?= number_format($row['toplam_gelir'] ?? 0, 2) ?> ₺
                        </td>
                        <td class="py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="uyeler.php?sehir=<?= $row['plaka'] ?>" title="Üyeleri Filtrele" class="p-2 hover:bg-blue-50 rounded-lg text-blue-500 transition">
                                    <i class="fa-solid fa-users-rectangle text-lg"></i>
                                </a>
                                <button onclick="confirmDelete(<?= $row['id'] ?>, <?= $row['uye_sayisi'] ?>)" title="Bölgeyi Sil" class="p-2 hover:bg-red-50 rounded-lg text-red-500 transition">
                                    <i class="fa-solid fa-trash-can text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="modalSehirEkle" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-white/20">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800">Yeni Bölge Tanımla</h2>
            <button onclick="closeModal('modalSehirEkle')" class="text-slate-400 hover:text-slate-600 transition text-3xl leading-none">&times;</button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Plaka / Kod</label>
                <input type="text" name="plaka" placeholder="Örn: 34" required class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Şehir Adı</label>
                <input type="text" name="sehir_adi" placeholder="Örn: İstanbul" required class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full bg-brand-electric hover:bg-black text-white font-bold py-3.5 rounded-xl transition shadow-xl shadow-blue-500/20 text-sm">
                    Sisteme Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id, count) {
    if (count > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Silme Engellendi!',
            text: 'Bu şehirde ' + count + ' adet üye kayıtlı. Önce üyeleri silmeli veya taşımalısınız.',
            confirmButtonColor: '#0052ff'
        });
    } else {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu bölge sistemden silinecektir!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Evet, Sil!',
            cancelButtonText: 'Vazgeç'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'bolgeler.php?sil=' + id;
            }
        });
    }
}

$(document).ready(function() {
    $('#tabloBolgeler').DataTable({
        dom: '<"flex flex-col md:flex-row justify-between items-center mb-6 gap-4"Bf>rtip',
        buttons: [
            { extend: 'excel', text: '<i class="fa-solid fa-file-excel text-emerald-600 mr-2"></i>Excel' },
            { extend: 'pdf', text: '<i class="fa-solid fa-file-pdf text-rose-500 mr-2"></i>PDF' },
            { extend: 'print', text: '<i class="fa-solid fa-print text-blue-600 mr-2"></i>Yazdır' }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json',
            search: "",
            searchPlaceholder: "Tabloda canlı ara..."
        },
        pageLength: 15
    });
});
</script>

<?php include 'includes/footer.php'; ?>