<?php
include 'config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if (isset($_GET['sil'])) {
    $id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM uyeler WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Üye kaydı başarıyla silindi.'];
    header("Location: uyeler.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ad_soyad'])) {
    $ad_soyad = htmlspecialchars(trim($_POST['ad_soyad']));
    $telefon = preg_replace('/[^0-9]/', '', $_POST['telefon']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null;
    $sehir_plaka = htmlspecialchars($_POST['sehir_plaka']);

    if (isset($_POST['uye_id']) && !empty($_POST['uye_id'])) {
        $id = (int)$_POST['uye_id'];
        $stmt = $db->prepare("UPDATE uyeler SET ad_soyad = ?, telefon = ?, email = ?, sehir_plaka = ? WHERE id = ?");
        $stmt->execute([$ad_soyad, $telefon, $email, $sehir_plaka, $id]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Üye bilgileri güncellendi.'];
    } else {
        $stmt = $db->prepare("INSERT INTO uyeler (ad_soyad, telefon, email, sehir_plaka) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ad_soyad, $telefon, $email, $sehir_plaka]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Yeni üye başarıyla kaydedildi.'];
    }
    header("Location: uyeler.php");
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';

$where = "WHERE 1=1";
$params = [];
if (!empty($_GET['sehir'])) {
    $where .= " AND u.sehir_plaka = ?";
    $params[] = $_GET['sehir'];
}
if (!empty($_GET['ara'])) {
    $where .= " AND (u.ad_soyad LIKE ? OR u.telefon LIKE ? OR u.email LIKE ?)";
    $ara = "%" . $_GET['ara'] . "%";
    $params[] = $ara; $params[] = $ara; $params[] = $ara;
}

$stmt = $db->prepare("SELECT u.*, s.sehir_adi FROM uyeler u LEFT JOIN sehirler s ON u.sehir_plaka = s.plaka $where ORDER BY u.id DESC");
$stmt->execute($params);
$uyeler = $stmt->fetchAll();

$sehirler = $db->query("SELECT * FROM sehirler ORDER BY sehir_adi ASC")->fetchAll();
?>

<main class="flex-1 w-full min-w-0 p-6 bg-slate-50 min-h-screen overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Üye Portföy Yönetimi</h1>
            <p class="text-slate-500 text-sm">Kayıtlı portföy, düzenleme ve iletişim merkezi</p>
        </div>
        <button onclick="openModal('modalUyeEkle')" class="bg-brand-electric hover:bg-brand-electricHover text-white px-5 py-2.5 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center font-semibold">
            <i class="fa-solid fa-plus mr-2"></i> Yeni Üye Ekle
        </button>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="ara" value="<?= htmlspecialchars($_GET['ara'] ?? '') ?>" placeholder="İsim, telefon veya e-posta ile ara..." class="w-full pl-11 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>
            <select name="sehir" class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand-electric outline-none">
                <option value="">-- Tüm Şehirler --</option>
                <?php foreach($sehirler as $s): ?>
                    <option value="<?= $s['plaka'] ?>" <?= (isset($_GET['sehir']) && $_GET['sehir'] == $s['plaka']) ? 'selected' : '' ?>>
                        <?= $s['sehir_adi'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-slate-900 hover:bg-black text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition">
                Filtrele
            </button>
            <?php if(!empty($_GET['ara']) || !empty($_GET['sehir'])): ?>
                <a href="uyeler.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center">
                    Temizle
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6">
            <table id="tabloUyeler" class="w-full text-left">
                <thead>
                    <tr class="text-slate-400 text-xs uppercase tracking-widest border-b border-slate-100">
                        <th class="pb-4 font-semibold">AD SOYAD</th>
                        <th class="pb-4 font-semibold">ŞEHİR</th>
                        <th class="pb-4 font-semibold">TELEFON</th>
                        <th class="pb-4 font-semibold">E-POSTA</th>
                        <th class="pb-4 font-semibold text-center">İLETİŞİM</th>
                        <th class="pb-4 font-semibold text-right">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="text-slate-600 text-sm">
                    <?php foreach($uyeler as $row): ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50/80 transition">
                        <td class="py-4 font-bold text-slate-800">
                            <?= htmlspecialchars($row['ad_soyad']) ?>
                        </td>
                        <td class="py-4">
                            <span class="inline-flex items-center text-xs font-semibold text-slate-600">
                                <i class="fa-solid fa-location-dot text-brand-electric mr-1.5"></i>
                                <?= htmlspecialchars($row['sehir_adi'] ?? 'Belirtilmemiş') ?>
                            </span>
                        </td>
                        <td class="py-4 font-mono text-xs font-semibold text-slate-600">
                            <?= htmlspecialchars($row['telefon'] ?? '-') ?>
                        </td>
                        <td class="py-4 text-slate-500 text-xs">
                            <?= htmlspecialchars($row['email'] ?? '-') ?>
                        </td>
                        <td class="py-4 text-center">
                            <?php if(!empty($row['telefon'])): ?>
                                <a href="https://wa.me/90<?= $row['telefon'] ?>?text=Merhaba%20Sayın%20<?= urlencode($row['ad_soyad']) ?>," target="_blank" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 transition">
                                    <i class="fa-brands fa-whatsapp mr-1.5 text-sm"></i> WhatsApp
                                </a>
                            <?php else: ?>
                                <span class="text-slate-300 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick='duzenle(<?= json_encode($row) ?>)' class="p-2 hover:bg-blue-50 rounded-lg text-blue-600 transition">
                                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                                </button>
                                <button onclick="confirmDelete(<?= $row['id'] ?>)" class="p-2 hover:bg-red-50 rounded-lg text-red-500 transition">
                                    <i class="fa-solid fa-trash-can text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="modalUyeEkle" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-white/20">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h2 id="modalTitle" class="text-xl font-bold text-slate-800">Yeni Üye Kaydı</h2>
            <button onclick="closeModal('modalUyeEkle')" class="text-slate-400 hover:text-slate-600 transition text-3xl leading-none">&times;</button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <input type="hidden" name="uye_id" id="form_uye_id">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ad Soyad</label>
                <input type="text" name="ad_soyad" id="form_ad_soyad" required class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Telefon</label>
                <input type="text" name="telefon" id="form_telefon" placeholder="05XXXXXXXXX" required class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">E-Posta</label>
                <input type="email" name="email" id="form_email" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Şehir / Bölge</label>
                <select name="sehir_plaka" id="form_sehir_plaka" required class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white focus:ring-2 focus:ring-brand-electric outline-none">
                    <?php foreach($sehirler as $s): ?>
                        <option value="<?= $s['plaka'] ?>"><?= $s['sehir_adi'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full bg-brand-electric hover:bg-black text-white font-bold py-3.5 rounded-xl transition shadow-xl shadow-blue-500/20 text-sm">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function duzenle(data) {
    document.getElementById('modalTitle').innerText = 'Üye Bilgilerini Düzenle';
    document.getElementById('form_uye_id').value = data.id;
    document.getElementById('form_ad_soyad').value = data.ad_soyad;
    document.getElementById('form_telefon').value = data.telefon;
    document.getElementById('form_email').value = data.email || '';
    document.getElementById('form_sehir_plaka').value = data.sehir_plaka;
    openModal('modalUyeEkle');
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Üye kaydı silinecektir!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Evet, Sil!',
        cancelButtonText: 'Vazgeç'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'uyeler.php?sil=' + id;
        }
    });
}

$(document).ready(function() {
    $('#tabloUyeler').DataTable({
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