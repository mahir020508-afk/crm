<?php
include 'config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM odemeler WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Ödeme kaydı başarıyla silindi.'];
    header("Location: odemeler.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['uye_id'], $_POST['miktar'])) {
    $uye_id = (int)$_POST['uye_id'];
    $miktar = (float)$_POST['miktar'];
    $tarih = !empty($_POST['odeme_tarihi']) ? $_POST['odeme_tarihi'] : date('Y-m-d');

    if ($uye_id > 0 && $miktar > 0) {
        $stmt = $db->prepare("INSERT INTO odemeler (uye_id, miktar, odeme_tarihi) VALUES (?, ?, ?)");
        $stmt->execute([$uye_id, $miktar, $tarih]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Tahsilat kaydı başarıyla oluşturuldu.'];
    }
    header("Location: odemeler.php");
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';

$uyelerList = $db->query("SELECT u.id, u.ad_soyad, u.telefon, s.sehir_adi FROM uyeler u LEFT JOIN sehirler s ON u.sehir_plaka = s.plaka ORDER BY u.ad_soyad ASC")->fetchAll();
?>

<main class="flex-1 w-full min-w-0 p-6 bg-slate-50 min-h-screen overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tahsilat & Kasa</h1>
            <p class="text-slate-500 text-sm">Üye ödemeleri ve cari hareketler.</p>
        </div>
        <button onclick="openModal('modalOdemeEkle')" class="bg-brand-electric hover:bg-brand-electricHover text-white px-5 py-2.5 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center font-semibold">
            <i class="fa-solid fa-plus mr-2"></i> Yeni Tahsilat Gir
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6">
            <table id="tabloOdemeler" class="w-full text-left">
                <thead>
                    <tr class="text-slate-400 text-xs uppercase tracking-widest border-b border-slate-100">
                        <th class="pb-4 font-semibold text-center">MAKBUZ #</th>
                        <th class="pb-4 font-semibold">ÜYE BİLGİSİ</th>
                        <th class="pb-4 font-semibold text-center">ŞEHİR</th>
                        <th class="pb-4 font-semibold text-center">TAHSİLAT TARİHİ</th>
                        <th class="pb-4 font-semibold text-right">MİKTAR</th>
                        <th class="pb-4 font-semibold text-right">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="text-slate-600 text-sm">
                    <?php
                    $sorgu = $db->query("
                        SELECT o.*, u.ad_soyad, u.telefon, s.sehir_adi 
                        FROM odemeler o 
                        JOIN uyeler u ON o.uye_id = u.id 
                        LEFT JOIN sehirler s ON u.sehir_plaka = s.plaka 
                        ORDER BY o.id DESC
                    ");
                    while($row = $sorgu->fetch()):
                    ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50/80 transition">
                        <td class="py-4 text-center font-mono font-bold text-brand-electric">
                            #TR-<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td class="py-4 font-bold text-slate-800">
                            <div><?= htmlspecialchars($row['ad_soyad']) ?></div>
                            <div class="text-xs text-slate-400 font-normal"><?= htmlspecialchars($row['telefon'] ?? '') ?></div>
                        </td>
                        <td class="py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                <?= htmlspecialchars($row['sehir_adi'] ?? 'Belirtilmemiş') ?>
                            </span>
                        </td>
                        <td class="py-4 text-center font-medium text-slate-500">
                            <?= date('d.m.Y', strtotime($row['odeme_tarihi'])) ?>
                        </td>
                        <td class="py-4 text-right font-bold text-emerald-600 text-base">
                            +<?= number_format($row['miktar'], 2) ?> ₺
                        </td>
                        <td class="py-4 text-right">
                            <button onclick="confirmDelete(<?= $row['id'] ?>)" class="p-2 hover:bg-red-50 rounded-lg text-red-500 transition">
                                <i class="fa-solid fa-trash-can text-lg"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="modalOdemeEkle" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-white/20">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800">Yeni Tahsilat Kaydı</h2>
            <button onclick="closeModal('modalOdemeEkle')" class="text-slate-400 hover:text-slate-600 transition text-3xl leading-none">&times;</button>
        </div>
        <form method="POST" id="formTahsilat" onsubmit="return validateTahsilatForm()" class="p-8 space-y-4">
            <input type="hidden" name="uye_id" id="secilenUyeId" required>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Üye Arama Motoru</label>
                
                <div id="secilenUyeKutusu" class="hidden mb-2 p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-brand-electric text-white flex items-center justify-center font-bold text-xs">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <div id="secilenUyeAd" class="font-bold text-slate-800 text-sm"></div>
                            <div id="secilenUyeDetay" class="text-xs text-slate-500"></div>
                        </div>
                    </div>
                    <button type="button" onclick="uyeSecimiTemizle()" class="text-slate-400 hover:text-red-500 text-sm p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div id="aramaKutusuAlani">
                    <div class="relative mb-2">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="uyeAramaInput" onkeyup="filtreleUyeler()" placeholder="İsim, telefon veya şehir yazın..." class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-electric outline-none" autocomplete="off">
                    </div>

                    <div id="uyeListesi" class="max-h-44 overflow-y-auto space-y-1 border border-slate-100 rounded-xl p-2 bg-slate-50/50">
                        <?php foreach($uyelerList as $u): ?>
                        <div onclick="uyeSec(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['ad_soyad'])) ?>', '<?= htmlspecialchars(addslashes($u['telefon'] ?? '')) ?> - <?= htmlspecialchars(addslashes($u['sehir_adi'] ?? '')) ?>')" 
                             class="uye-item p-2.5 rounded-lg hover:bg-white hover:shadow-sm cursor-pointer transition flex items-center justify-between border border-transparent hover:border-slate-200"
                             data-search="<?= mb_strtolower(htmlspecialchars($u['ad_soyad'] . ' ' . ($u['telefon'] ?? '') . ' ' . ($u['sehir_adi'] ?? '')), 'UTF-8') ?>">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                    <?= mb_strtoupper(mb_substr($u['ad_soyad'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-800"><?= htmlspecialchars($u['ad_soyad']) ?></div>
                                    <div class="text-[11px] text-slate-400"><?= htmlspecialchars($u['telefon'] ?? '') ?></div>
                                </div>
                            </div>
                            <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">
                                <?= htmlspecialchars($u['sehir_adi'] ?? '') ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahsilat Tutarı (₺)</label>
                <input type="number" step="0.01" name="miktar" placeholder="0.00" required class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">İşlem Tarihi</label>
                <input type="date" name="odeme_tarihi" value="<?= date('Y-m-d') ?>" required class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-brand-electric hover:bg-black text-white font-bold py-3.5 rounded-xl transition shadow-xl shadow-blue-500/20 text-sm">
                    Tahsilatı Onayla
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function filtreleUyeler() {
    var filter = document.getElementById('uyeAramaInput').value.toLowerCase();
    var items = document.querySelectorAll('.uye-item');
    items.forEach(function(item) {
        var text = item.getAttribute('data-search');
        if (text.indexOf(filter) > -1) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function uyeSec(id, ad, detay) {
    document.getElementById('secilenUyeId').value = id;
    document.getElementById('secilenUyeAd').innerText = ad;
    document.getElementById('secilenUyeDetay').innerText = detay;
    document.getElementById('secilenUyeKutusu').classList.remove('hidden');
    document.getElementById('aramaKutusuAlani').classList.add('hidden');
}

function uyeSecimiTemizle() {
    document.getElementById('secilenUyeId').value = '';
    document.getElementById('secilenUyeKutusu').classList.add('hidden');
    document.getElementById('aramaKutusuAlani').classList.remove('hidden');
    document.getElementById('uyeAramaInput').value = '';
    filtreleUyeler();
}

function validateTahsilatForm() {
    var uyeId = document.getElementById('secilenUyeId').value;
    if (!uyeId || uyeId === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Üye Seçimi Gerekli',
            text: 'Lütfen arama motorunu kullanarak bir üye seçin.',
            confirmButtonColor: '#0052ff'
        });
        return false;
    }
    return true;
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu tahsilat kaydı silinecektir!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Evet, Sil!',
        cancelButtonText: 'Vazgeç'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'odemeler.php?sil=' + id;
        }
    });
}

$(document).ready(function() {
    $('#tabloOdemeler').DataTable({
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
        pageLength: 15,
        order: [[0, 'desc']]
    });
});
</script>

<?php include 'includes/footer.php'; ?>