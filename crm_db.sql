-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 17 Kas 2026, 01:29:44
-- Sunucu sürümü: 10.11.16-MariaDB
-- PHP Sürümü: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `crm_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bolgeler`
--

CREATE TABLE `bolgeler` (
  `id` int(11) NOT NULL,
  `bolge_adi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `bolgeler`
--

INSERT INTO `bolgeler` (`id`, `bolge_adi`) VALUES
(1, 'Marmara / İstanbul'),
(2, 'İç Anadolu / Ankara'),
(3, 'Ege / İzmir');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `ad_soyad` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`id`, `kullanici_adi`, `sifre`, `ad_soyad`) VALUES
(1, 'admin', '$2y$10$eA0Iq2IeWvZZvC5x3z32.OFxY7Uu9O/E1bU3H3p3q4P7i8iQ6c2Vy', 'Yönetici Admin');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `odemeler`
--

CREATE TABLE `odemeler` (
  `id` int(11) NOT NULL,
  `uye_id` int(11) DEFAULT NULL,
  `miktar` decimal(10,2) NOT NULL,
  `odeme_tarihi` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `odemeler`
--

INSERT INTO `odemeler` (`id`, `uye_id`, `miktar`, `odeme_tarihi`) VALUES
(1, 1, 1500.00, '2026-03-01'),
(6, 8, 78500.00, '2026-11-17'),
(7, 7, 954201.00, '2026-11-17');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sehirler`
--

CREATE TABLE `sehirler` (
  `id` int(11) NOT NULL,
  `plaka` varchar(2) NOT NULL,
  `sehir_adi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `sehirler`
--

INSERT INTO `sehirler` (`id`, `plaka`, `sehir_adi`) VALUES
(4, '04', 'Ağrı'),
(5, '05', 'Amasya'),
(6, '06', 'Ankara'),
(7, '07', 'Antalya'),
(8, '08', 'Artvin'),
(9, '09', 'Aydın'),
(10, '10', 'Balıkesir'),
(11, '11', 'Bilecik'),
(12, '12', 'Bingöl'),
(13, '13', 'Bitlis'),
(14, '14', 'Bolu'),
(15, '15', 'Burdur'),
(16, '16', 'Bursa'),
(17, '17', 'Çanakkale'),
(18, '18', 'Çankırı'),
(19, '19', 'Çorum'),
(20, '20', 'Denizli'),
(21, '21', 'Diyarbakır'),
(22, '22', 'Edirne'),
(23, '23', 'Elazığ'),
(24, '24', 'Erzincan'),
(25, '25', 'Erzurum'),
(26, '26', 'Eskişehir'),
(27, '27', 'Gaziantep'),
(28, '28', 'Giresun'),
(29, '29', 'Gümüşhane'),
(30, '30', 'Hakkari'),
(31, '31', 'Hatay'),
(32, '32', 'Isparta'),
(33, '33', 'Mersin'),
(34, '34', 'İstanbul'),
(35, '35', 'İzmir'),
(36, '36', 'Kars'),
(37, '37', 'Kastamonu'),
(38, '38', 'Kayseri'),
(39, '39', 'Kırklareli'),
(40, '40', 'Kırşehir'),
(41, '41', 'Kocaeli'),
(42, '42', 'Konya'),
(43, '43', 'Kütahya'),
(44, '44', 'Malatya'),
(45, '45', 'Manisa'),
(46, '46', 'Kahramanmaraş'),
(47, '47', 'Mardin'),
(48, '48', 'Muğla'),
(49, '49', 'Muş'),
(50, '50', 'Nevşehir'),
(51, '51', 'Niğde'),
(52, '52', 'Ordu'),
(53, '53', 'Rize'),
(54, '54', 'Sakarya'),
(55, '55', 'Samsun'),
(56, '56', 'Siirt'),
(57, '57', 'Sinop'),
(58, '58', 'Sivas'),
(59, '59', 'Tekirdağ'),
(60, '60', 'Tokat'),
(61, '61', 'Trabzon'),
(62, '62', 'Tunceli'),
(63, '63', 'Şanlıurfa'),
(64, '64', 'Uşak'),
(65, '65', 'Van'),
(66, '66', 'Yozgat'),
(67, '67', 'Zonguldak'),
(69, '69', 'Bayburt'),
(70, '70', 'Karaman'),
(71, '71', 'Kırıkkale'),
(72, '72', 'Batman'),
(73, '73', 'Şırnak'),
(74, '74', 'Bartın'),
(75, '75', 'Ardahan'),
(76, '76', 'Iğdır'),
(77, '77', 'Yalova'),
(78, '78', 'Karabük'),
(79, '79', 'Kilis'),
(80, '80', 'Osmaniye'),
(81, '81', 'Düzce');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `uyeler`
--

CREATE TABLE `uyeler` (
  `id` int(11) NOT NULL,
  `bolge_id` int(11) DEFAULT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sehir_plaka` varchar(2) DEFAULT '34'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `uyeler`
--

INSERT INTO `uyeler` (`id`, `bolge_id`, `ad_soyad`, `telefon`, `email`, `sehir_plaka`) VALUES
(1, 1, 'Ahmet Yılmaz', '05551112233', 'ahmet@gmail.com', '34'),
(7, NULL, 'serkan aslan', '05123456789', 'uye@gmail.com', '69'),
(8, NULL, 'mustafa yılmaz', '05123456785', 'mustafa@gmail.com', '60');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `bolgeler`
--
ALTER TABLE `bolgeler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`);

--
-- Tablo için indeksler `odemeler`
--
ALTER TABLE `odemeler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uye_id` (`uye_id`);

--
-- Tablo için indeksler `sehirler`
--
ALTER TABLE `sehirler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plaka` (`plaka`);

--
-- Tablo için indeksler `uyeler`
--
ALTER TABLE `uyeler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bolge_id` (`bolge_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `bolgeler`
--
ALTER TABLE `bolgeler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `odemeler`
--
ALTER TABLE `odemeler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `sehirler`
--
ALTER TABLE `sehirler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Tablo için AUTO_INCREMENT değeri `uyeler`
--
ALTER TABLE `uyeler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `odemeler`
--
ALTER TABLE `odemeler`
  ADD CONSTRAINT `odemeler_ibfk_1` FOREIGN KEY (`uye_id`) REFERENCES `uyeler` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `uyeler`
--
ALTER TABLE `uyeler`
  ADD CONSTRAINT `uyeler_ibfk_1` FOREIGN KEY (`bolge_id`) REFERENCES `bolgeler` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
