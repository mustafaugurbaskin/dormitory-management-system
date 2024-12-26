-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 26 Ara 2024, 21:51:41
-- Sunucu sürümü: 5.7.17
-- PHP Sürümü: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `rekoryurdu`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `odalar`
--

CREATE TABLE `odalar` (
  `id` int(11) NOT NULL,
  `tekil_yurt_kodu` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numarasi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kati` int(11) NOT NULL,
  `kapasitesi` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `odalar`
--

INSERT INTO `odalar` (`id`, `tekil_yurt_kodu`, `numarasi`, `kati`, `kapasitesi`) VALUES
(1, 'AB123456', '101', 1, 6),
(3, 'AB123456', '301', 3, 6),
(24, 'XY987654', '205', 2, 5),
(33, 'XY987654', '301', 3, 7),
(34, 'XY987654', '302', 3, 5),
(41, 'CD456789', '102', 1, 5),
(42, 'MN543210', '201', 2, 3),
(44, 'QT477517', '101', 1, 4),
(45, 'KL678901', '101', 1, 3);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ogrenciler`
--

CREATE TABLE `ogrenciler` (
  `id` int(11) NOT NULL,
  `adi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soyadi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `oda_numarasi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fotograf` json NOT NULL,
  `tc_kimlik_no` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adres` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cinsiyet` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tekil_yurt_kodu` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yoneticiler`
--

CREATE TABLE `yoneticiler` (
  `id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sifre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `yoneticiler`
--

INSERT INTO `yoneticiler` (`id`, `username`, `sifre`) VALUES
(1, 'admin', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yurtlar`
--

CREATE TABLE `yurtlar` (
  `id` int(11) NOT NULL,
  `yurtadi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adres` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resim` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasite` int(11) NOT NULL,
  `kat` int(11) NOT NULL DEFAULT '1',
  `yurt_tipi` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tekil_yurt_kodu` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

--
-- Tablo döküm verisi `yurtlar`
--

INSERT INTO `yurtlar` (`id`, `yurtadi`, `adres`, `telefon`, `resim`, `kapasite`, `kat`, `yurt_tipi`, `tekil_yurt_kodu`) VALUES
(25, 'Hendek Kız Yurdu', '163. Sokak No:150 Bağpınar Mahallesi, Hendek, Sakarya', '0 264 6149775', '{\"path\":\"upload/6765b0b20f83a.jpg\"}', 200, 4, 'Kız', 'AB123456'),
(26, 'Serdivan Erkek Kyk Yurdu', 'Akademiyolu Sokak No:5-46 Esentepe Mahallesi, Serdivan, Sakarya', '0 264 6661070', '{\"path\":\"upload/6765b0b20f83a.jpg\"}', 300, 5, 'Erkek', 'XY987654'),
(27, 'Geyve Kız Yurdu', 'Tepecikler Mahallesi Mehmet Akif Sokak No:41, Geyve, Sakarya', '0 264 5170015', '{\"path\":\"upload/6765b0b20f83a.jpg\"}', 150, 2, 'Kız', 'MN543210'),
(28, 'Adapazarı Erkek Yurdu', 'Çark Caddesi No:17 Adapazarı, Sakarya', '0 264 3412598', '{\"path\":\"upload/6765b0b20f83a.jpg\"}', 250, 4, 'Erkek', 'KL678901'),
(29, 'Arifiye Kız Yurdu', 'Arifiye Mahallesi No:9 Arifiye, Sakarya', '0 264 2714567', '{\"path\":\"upload/6765b0b20f83a.jpg\"}', 100, 1, 'Kız', 'CD456789'),
(42, 'Arif Nihat Asya Erkek Yurdu', 'Kemalpaşa Mahallesi 4. Cadde No:1 Serdivan / Sakarya', '02643460662', '{\"path\":\"upload\\/6766d63619354.jpg\"}', 1000, 7, 'Erkek', 'QT477517');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `odalar`
--
ALTER TABLE `odalar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `ogrenciler`
--
ALTER TABLE `ogrenciler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `yoneticiler`
--
ALTER TABLE `yoneticiler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Tablo için indeksler `yurtlar`
--
ALTER TABLE `yurtlar`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `odalar`
--
ALTER TABLE `odalar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
--
-- Tablo için AUTO_INCREMENT değeri `ogrenciler`
--
ALTER TABLE `ogrenciler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
--
-- Tablo için AUTO_INCREMENT değeri `yoneticiler`
--
ALTER TABLE `yoneticiler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Tablo için AUTO_INCREMENT değeri `yurtlar`
--
ALTER TABLE `yurtlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
