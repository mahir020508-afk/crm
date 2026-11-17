<?php
include 'config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$currentUserId = (int)$_SESSION['user_id'];

if (isset($_GET['sil'])) {
    $silId = (int)$_GET['sil'];

    if ($silId === $currentUserId) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Güvenlik Uyarısı: Kendi yönetici hesabınızı silemezsiniz!'];
    } else {
        $countAdmins = $db->query("SELECT COUNT(*) FROM kullanicilar")->fetchColumn();
        if ($countAdmins <= 1) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Hata: Sistemde en az bir yönetici hesabı kalmalıdır!'];
        } else {
            $stmt = $db->prepare("DELETE FROM kullanicilar WHERE id = ?");
            $stmt->execute([$silId]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Yönetici hesabı başarıyla kaldırıldı.'];
        }
    }
    header("Location: ayarlar.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'profil_guncelle') {
        $ad_soyad = htmlspecialchars(trim($_POST['ad_soyad'] ?? ''));
        $kullanici_adi = htmlspecialchars(trim($_POST['kullanici_adi'] ?? ''));
        $eski_sifre = trim($_POST['eski_sifre'] ?? '');
        $yeni_sifre = trim($_POST['yeni_sifre'] ?? '');
        $yeni_sifre_tekrar = trim($_POST['yeni_sifre_tekrar'] ?? '');

        $checkUser = $db->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ? AND id != ?");
        $checkUser->execute([$kullanici_adi, $currentUserId]);
        if ($checkUser->fetch()) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Bu kullanıcı adı zaten başka bir yönetici tarafından kullanılıyor!'];
            header("Location: ayarlar.php");
            exit;
        }

        $stmt = $db->prepare("SELECT sifre FROM kullanicilar WHERE id = ?");
        $stmt->execute([$currentUserId]);
        $currentPassHash = $stmt->fetchColumn();

        if (!empty($yeni_sifre)) {
            $isOldPassValid = password_verify($eski_sifre, $currentPassHash) || ($currentPassHash === $eski_sifre);
            if (!$isOldPassValid) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Mevcut şifrenizi hatalı girdiniz!'];
                header("Location: ayarlar.php");
                exit;
            }

            if ($yeni_sifre !== $yeni_sifre_tekrar) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Yeni şifreler birbiriyle eşleşmiyor!'];
                header("Location: ayarlar.php");
                exit;
            }

            if (strlen($yeni_sifre) < 6) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Yeni şifre en az 6 karakter olmalıdır!'];
                header("Location: ayarlar.php");
                exit;
            }

            $newHash = password_hash($yeni_sifre, PASSWORD_BCRYPT);
            $update = $db->prepare("UPDATE kullanicilar SET ad_soyad = ?, kullanici_adi = ?, sifre = ? WHERE id = ?");
            $update->execute([$ad_soyad, $kullanici_adi, $newHash, $currentUserId]);
        } else {
            $update = $db->prepare("UPDATE kullanicilar SET ad_soyad = ?, kullanici_adi = ? WHERE id = ?");
            $update->execute([$ad_soyad, $kullanici_adi, $currentUserId]);
        }

        $_SESSION['ad_soyad'] = $ad_soyad;
        $_SESSION['kullanici_adi'] = $kullanici_adi;
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profil ve güvenlik bilgileriniz güncellendi.'];
        header("Location: ayarlar.php");
        exit;
    }

    if ($_POST['action'] === 'admin_ekle') {
        $yeni_ad = htmlspecialchars(trim($_POST['yeni_ad_soyad'] ?? ''));
        $yeni_kullanici = htmlspecialchars(trim($_POST['yeni_kullanici_adi'] ?? ''));
        $yeni_sifre_admin = trim($_POST['yeni_sifre_admin'] ?? '');

        if (!empty($yeni_ad) && !empty($yeni_kullanici) && !empty($yeni_sifre_admin)) {
            $check = $db->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ?");
            $check->execute([$yeni_kullanici]);
            if ($check->fetch()) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Bu kullanıcı adı zaten sistemde kayıtlı!'];
            } else {
                $hash = password_hash($yeni_sifre_admin, PASSWORD_BCRYPT);
                $insert = $db->prepare("INSERT INTO kullanicilar (ad_soyad, kullanici_adi, sifre) VALUES (?, ?, ?)");
                $insert->execute([$yeni_ad, $yeni_kullanici, $hash]);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Yeni yönetici hesabı oluşturuldu.'];
            }
        }
        header("Location: ayarlar.php");
        exit;
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';

$curUser = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$curUser->execute([$currentUserId]);
$me = $curUser->fetch();

$adminler = $db->query("SELECT * FROM kullanicilar ORDER BY id ASC")->fetchAll();
?>

<main class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Sistem Ayarları & Yöneticiler</h1>
        <p class="text-slate-500 text-sm">Hesap güvenliğinizi sağlayın ve yetkili kullanıcıları yönetin.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-1 flex items-center">
                <i class="fa-solid fa-lock text-brand-electric mr-2"></i> Profil & Şifre Değiştir
            </h2>
            <p class="text-slate-400 text-xs mb-6">Mevcut bilgilerinizi ve giriş şifrenizi güncelleyin.</p>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="profil_guncelle">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ad Soyad</label>
                    <input type="text" name="ad_soyad" value="<?= htmlspecialchars($me['ad_soyad']) ?>" required class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kullanıcı Adı</label>
                    <input type="text" name="kullanici_adi" value="<?= htmlspecialchars($me['kullanici_adi']) ?>" required class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Şifre Değiştir (İsteğe Bağlı)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mevcut Şifreniz</label>
                    <input type="password" name="eski_sifre" placeholder="Şifrenizi değiştirmeyecekseniz boş bırakın" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Yeni Şifre</label>
                        <input type="password" name="yeni_sifre" placeholder="En az 6 karakter" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="yeni_sifre_tekrar" placeholder="Tekrar yazın" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-brand-electric hover:bg-black text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-blue-500/20 text-sm">
                        Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-users-gear text-brand-electric mr-2"></i> Yetkili Yöneticiler
                        </h2>
                        <p class="text-slate-400 text-xs">Panele erişim izni olan yönetici listesi.</p>
                    </div>
                    <button onclick="openModal('modalAdminEkle')" class="bg-slate-900 hover:bg-black text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center">
                        <i class="fa-solid fa-user-plus mr-2"></i> Yeni Yönetici Ekle
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-slate-400 text-xs uppercase tracking-widest border-b border-slate-100">
                                <th class="pb-3 font-semibold">YÖNETİCİ</th>
                                <th class="pb-3 font-semibold">KULLANICI ADI</th>
                                <th class="pb-3 font-semibold text-center">DURUM</th>
                                <th class="pb-3 font-semibold text-right">İŞLEM</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-600">
                            <?php foreach($adminler as $adm): ?>
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                                <td class="py-3.5 font-bold text-slate-800 flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs mr-3">
                                        <?= strtoupper(substr($adm['ad_soyad'], 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($adm['ad_soyad']) ?>
                                </td>
                                <td class="py-3.5 font-mono text-slate-500">
                                    @<?= htmlspecialchars($adm['kullanici_adi']) ?>
                                </td>
                                <td class="py-3.5 text-center">
                                    <?php if ($adm['id'] == $currentUserId): ?>
                                        <span class="bg-blue-50 text-brand-electric px-2.5 py-1 rounded-full text-xs font-bold border border-blue-100">
                                            Siz (Aktif)
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full text-xs font-bold">
                                            Yönetici
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 text-right">
                                    <?php if ($adm['id'] != $currentUserId): ?>
                                        <button onclick="confirmAdminDelete(<?= $adm['id'] ?>)" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition inline-flex items-center justify-center">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-slate-300 text-xs italic">Korumalı</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-6 p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center text-xs text-slate-500">
                <i class="fa-solid fa-shield-halved text-brand-electric text-lg mr-3"></i>
                Tüm yönetici şifreleri veritabanında tek yönlü BCrypt hash algoritması ile şifrelenmektedir.
            </div>
        </div>
    </div>
</main>

<div id="modalAdminEkle" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-white/20">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800">Yeni Yönetici Ata</h2>
            <button onclick="closeModal('modalAdminEkle')" class="text-slate-400 hover:text-slate-600 transition text-3xl leading-none">&times;</button>
        </div>
        <form method="POST" class="p-8 space-y-4">
            <input type="hidden" name="action" value="admin_ekle">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ad Soyad</label>
                <input type="text" name="yeni_ad_soyad" required placeholder="Örn: Mehmet Öz" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kullanıcı Adı</label>
                <input type="text" name="yeni_kullanici_adi" required placeholder="Örn: mehmetoz" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Giriş Şifresi</label>
                <input type="password" name="yeni_sifre_admin" required placeholder="En az 6 karakter" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-electric outline-none">
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full bg-brand-electric hover:bg-black text-white font-bold py-3.5 rounded-xl transition shadow-xl shadow-blue-500/20 text-sm">
                    Yöneticiyi Oluştur
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmAdminDelete(id) {
    Swal.fire({
        title: 'Yöneticiyi Sil?',
        text: "Bu kullanıcının yönetim paneline erişimi sonlandırılacaktır!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Evet, Sil!',
        cancelButtonText: 'Vazgeç'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'ayarlar.php?sil=' + id;
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>