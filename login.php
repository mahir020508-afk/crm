<?php
include 'config/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['kullanici_adi'] ?? '');
    $password = trim($_POST['sifre'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && (password_verify($password, $user['sifre']) || $user['sifre'] === $password)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['kullanici_adi'] = $user['kullanici_adi'];
            $_SESSION['ad_soyad'] = $user['ad_soyad'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı!';
        }
    } else {
        $error = 'Lütfen tüm alanları doldurun.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | CRM PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            black: '#090d16',
                            dark: '#0f172a',
                            electric: '#0052ff',
                            electricHover: '#0041cc',
                            turquoise: '#00f2fe',
                            cyan: '#06b6d4'
                        }
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-black min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-brand-dark/90 border border-white/10 rounded-3xl p-8 backdrop-blur-xl shadow-2xl relative">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-electric to-brand-turquoise flex items-center justify-center text-white text-2xl mx-auto mb-4 shadow-lg shadow-brand-electric/30">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Yönetim Paneli</h1>
            <p class="text-slate-400 text-sm mt-1">Devam etmek için oturum açın</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center">
                <i class="fa-solid fa-triangle-exclamation mr-3 text-base"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Kullanıcı Adı</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-regular fa-user"></i>
                    </span>
                    <input type="text" name="kullanici_adi" required class="w-full bg-brand-black/50 border border-white/10 rounded-xl py-3.5 pl-11 pr-4 text-white text-sm focus:outline-none focus:border-brand-electric focus:ring-1 focus:ring-brand-electric transition">
                </div>
            </div>

            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Şifre</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="sifre" required class="w-full bg-brand-black/50 border border-white/10 rounded-xl py-3.5 pl-11 pr-4 text-white text-sm focus:outline-none focus:border-brand-electric focus:ring-1 focus:ring-brand-electric transition">
                </div>
            </div>

            <button type="submit" class="w-full bg-brand-electric hover:bg-brand-electricHover text-white font-bold py-3.5 rounded-xl shadow-lg shadow-brand-electric/30 transition duration-200 mt-2">
                Giriş Yap
            </button>
        </form>
    </div>
</body>
</html>