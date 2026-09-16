-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2026 at 07:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `waygo`
--

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `vehicle_id`, `image_path`, `title`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 18, 'uploads/banners/banner_6a82f616a9dbd1.97302096.jpg', '20% OFF- Drive the Mazda MX-5', 1, 1, '2026-08-17 11:36:07', '2026-08-19 16:16:28'),
(4, NULL, 'uploads/banners/banner_6a82f633863af2.42967340.jpg', 'Book Now', 3, 1, '2026-08-17 11:47:51', '2026-08-17 19:53:23'),
(5, 21, 'uploads/banners/banner_6a82f62917d883.55748345.jpg', 'Book Now', 2, 1, '2026-08-17 11:52:37', '2026-08-19 16:16:45');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `fulfillment_type` enum('pickup','delivery') NOT NULL DEFAULT 'pickup',
  `delivery_time` time DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `delivery_region` varchar(100) DEFAULT NULL,
  `delivery_province` varchar(100) DEFAULT NULL,
  `delivery_city` varchar(100) DEFAULT NULL,
  `delivery_barangay` varchar(100) DEFAULT NULL,
  `delivery_address_line` varchar(255) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','active','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_addons`
--

CREATE TABLE `booking_addons` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `addon_name` varchar(50) NOT NULL,
  `addon_price` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(10) UNSIGNED NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `category` enum('sedan','suv','van','pickup_truck','motorcycle','coupe') NOT NULL,
  `is_scooter` tinyint(1) DEFAULT NULL,
  `motorcycle_type` enum('scooter','underbone','sport_bike','cruiser','naked') DEFAULT NULL,
  `is_sports` tinyint(1) NOT NULL DEFAULT 0,
  `transmission` enum('manual','automatic') NOT NULL DEFAULT 'automatic',
  `plate_number` varchar(20) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('available','maintenance','retired') NOT NULL DEFAULT 'available',
  `location_name` varchar(150) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `make`, `model`, `category`, `is_scooter`, `motorcycle_type`, `is_sports`, `transmission`, `plate_number`, `daily_rate`, `image_path`, `status`, `location_name`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(3, 'Toyota', 'Vios', 'sedan', NULL, NULL, 0, 'automatic', 'NXV 4821', 1500.00, 'uploads/vehicles/vehicle_6a827f649073a6.76240154.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-13 15:16:06', '2026-08-19 16:02:52'),
(4, 'Honda', 'City', 'sedan', NULL, NULL, 0, 'automatic', 'ABR 2994', 2000.00, 'uploads/vehicles/vehicle_6a827eafe8ac61.26805911.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-13 15:27:06', '2026-08-19 16:05:16'),
(5, 'Mitsubishi', 'Montero Sport', 'suv', NULL, NULL, 0, 'automatic', 'ABC 1234', 3000.00, 'uploads/vehicles/vehicle_6a827e4eebe6c6.39571345.jpg', 'available', 'Antonino, Balaoan, La Union, Ilocos Region, 2517, Philippines', 16.82138680, 120.40051370, '2026-08-13 15:32:52', '2026-08-19 16:06:06'),
(6, 'Nissan', 'NV350 Urvan', 'van', NULL, NULL, 0, 'automatic', 'NDU 5590', 3500.00, 'uploads/vehicles/vehicle_6a827dd7df50b5.31906483.jpg', 'available', 'Antonino, Balaoan, La Union, Ilocos Region, 2517, Philippines', 16.82138680, 120.40051370, '2026-08-13 15:35:22', '2026-08-19 16:05:48'),
(7, 'Nissan', 'Almera', 'sedan', NULL, NULL, 0, 'automatic', 'GHI 3456', 1400.00, 'uploads/vehicles/vehicle_6a827d546f5892.32119234.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-13 15:45:39', '2026-08-19 16:03:01'),
(8, 'Geely', 'Coolray', 'suv', NULL, NULL, 0, 'automatic', 'JKL 7890', 2500.00, 'uploads/vehicles/vehicle_6a827cda202e30.96125861.jpg', 'available', 'Antonino, Balaoan, La Union, Ilocos Region, 2517, Philippines', 16.82138680, 120.40051370, '2026-08-13 15:48:40', '2026-08-19 16:05:58'),
(9, 'Hyundai', 'Staria', 'van', NULL, NULL, 0, 'automatic', 'MNO 1357', 3500.00, 'uploads/vehicles/vehicle_6a827bb725cd21.09227149.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-13 15:50:51', '2026-08-19 16:06:58'),
(10, 'Honda', 'Beat 110cc', 'motorcycle', 1, 'scooter', 0, 'automatic', 'NE-12345', 450.00, 'uploads/vehicles/vehicle_6a827b593c8fd1.31604031.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-14 14:23:11', '2026-08-19 16:12:40'),
(11, 'Suzuki', 'Smash 115', 'motorcycle', 0, 'underbone', 0, 'manual', 'SM-83104', 400.00, 'uploads/vehicles/vehicle_6a827ae388dcb3.72630100.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-14 14:38:24', '2026-08-19 16:11:08'),
(12, 'Toyota', 'Hilux 2.4E', 'pickup_truck', NULL, NULL, 0, 'automatic', 'XYZ-6789', 2500.00, 'uploads/vehicles/vehicle_6a827a826c4406.79321277.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-14 14:42:40', '2026-08-19 16:13:30'),
(13, 'Ford', 'Ranger 2.0 Turbo XLT', 'pickup_truck', NULL, NULL, 0, 'automatic', 'NAN 4752', 3000.00, 'uploads/vehicles/vehicle_6a827a137203f5.16274554.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-14 14:46:02', '2026-08-19 16:12:22'),
(14, 'Yamaha', 'R15 V3', 'motorcycle', 0, 'sport_bike', 0, 'manual', 'NFK 7312', 800.00, 'uploads/vehicles/motorcycle_yamaha-r15-v3_20260817-040712_65fd5e.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-17 10:07:12', '2026-08-19 16:11:59'),
(15, 'Honda', 'CBR150R', 'motorcycle', 0, 'sport_bike', 0, 'manual', 'DGP 2946', 900.00, 'uploads/vehicles/motorcycle_honda-cbr150r_20260817-041008_3edec2.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:10:08', '2026-08-19 16:05:08'),
(16, 'Yamaha', 'MT-15', 'motorcycle', 0, 'naked', 0, 'manual', 'BKC 9057', 800.00, 'uploads/vehicles/motorcycle_yamaha-mt-15_20260817-041311_29bd81.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-17 10:13:11', '2026-08-19 16:03:18'),
(17, 'Rusi', 'Classic 250', 'motorcycle', 0, 'cruiser', 0, 'manual', 'AJP 6738', 800.00, 'uploads/vehicles/motorcycle_rusi-classic-250_20260817-041621_fcd9f4.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:16:21', '2026-08-19 16:05:00'),
(18, 'Mazda', 'MX-5', 'coupe', NULL, NULL, 1, 'manual', 'NEC 7430', 4500.00, 'uploads/vehicles/sedan_mazda-mx-5_20260817-041959_24b418.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:19:59', '2026-08-19 16:04:51'),
(19, 'Subaru', 'BRZ 2.0', 'coupe', NULL, NULL, 1, 'manual', 'GHT 3168', 5000.00, 'uploads/vehicles/sedan_subaru-brz-2-0_20260817-042354_342885.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:23:54', '2026-08-19 16:04:39'),
(20, 'Toyota', '86 2.0', 'coupe', NULL, NULL, 1, 'manual', 'NVC 7406', 5000.00, 'uploads/vehicles/sedan_toyota-86-2-0_20260817-042719_72ca53.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-17 10:27:20', '2026-08-19 16:07:24'),
(21, 'Honda', 'Rebel 300', 'motorcycle', 0, 'cruiser', 0, 'manual', 'KDL 2195', 1300.00, 'uploads/vehicles/motorcycle_honda-rebel-300_20260817-043022_b753eb.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-17 10:30:22', '2026-08-19 16:03:30'),
(22, 'Suzuki', 'Gixxer 155', 'motorcycle', 0, 'naked', 0, 'manual', 'NHA 3472', 700.00, 'uploads/vehicles/motorcycle_suzuki-gixxer-155_20260817-043332_6686e4.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:33:32', '2026-08-19 16:04:17'),
(23, 'Suzuki', 'GSX-R150', 'motorcycle', 0, 'sport_bike', 0, 'manual', 'RMT 6183', 800.00, 'uploads/vehicles/motorcycle_suzuki-gsx-r150_20260817-043649_7f0f0a.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:36:49', '2026-08-19 16:04:10'),
(24, 'Mitsubishi', 'Mirage G4', 'sedan', NULL, NULL, 0, 'automatic', 'JTX 4827', 1400.00, 'uploads/vehicles/sedan_mitsubishi-mirage-g4_20260817-044204_0e1326.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:42:04', '2026-08-19 16:04:03'),
(25, 'Mazda', 'CX-5 2.5', 'suv', NULL, NULL, 0, 'automatic', 'RQP 6194', 2700.00, 'uploads/vehicles/suv_mazda-cx-5-2-5_20260817-044428_e0838e.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:44:28', '2026-08-19 16:03:51'),
(26, 'Isuzu', 'D-Max 3.0 LS', 'pickup_truck', NULL, NULL, 0, 'automatic', 'NGR 2312', 2600.00, 'uploads/vehicles/pickup-truck_isuzu-d-max-3-0-ls_20260817-044732_d6824b.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-17 10:47:32', '2026-08-19 16:03:09'),
(27, 'Toyota', 'HiAce Commuter', 'van', NULL, NULL, 0, 'manual', 'DCR 6815', 3000.00, 'uploads/vehicles/van_toyota-hiace-commuter_20260817-044943_167f36.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-17 10:49:43', '2026-08-19 16:10:58'),
(28, 'Hyundai', 'Accent 1.6', 'sedan', NULL, NULL, 0, 'automatic', 'NPM 2948', 1500.00, 'uploads/vehicles/sedan_hyundai-accent-1-6_20260817-045212_ac4d25.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:52:12', '2026-08-19 16:00:40'),
(29, 'Toyota', 'Corolla Altis 1.8', 'sedan', NULL, NULL, 0, 'automatic', 'RLF 8532', 2000.00, 'uploads/vehicles/sedan_toyota-corolla-altis-1-8_20260817-045415_fabdb5.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:54:15', '2026-08-19 16:00:34'),
(30, 'Chevrolet', 'Colorado 2.8 LTZ', 'pickup_truck', NULL, NULL, 0, 'automatic', 'NFN 5632', 2700.00, 'uploads/vehicles/pickup-truck_chevrolet-colorado-2-8-ltz_20260817-045556_28b360.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 10:55:56', '2026-08-19 16:00:27'),
(31, 'Nissan', 'Navara 2.5 EL', 'pickup_truck', NULL, NULL, 0, 'manual', 'NBJ 3262', 2400.00, 'uploads/vehicles/pickup-truck_nissan-navara-2-5-el_20260817-050027_7d8989.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 11:00:27', '2026-08-19 16:00:21'),
(32, 'Kawasaki', 'Dominar 400', 'motorcycle', 0, 'naked', 0, 'manual', 'NBR 5274', 1200.00, 'uploads/vehicles/motorcycle_kawasaki-dominar-400_20260817-093029_1108be.jpg', 'available', 'San Cristobal Street, Central West No. 3, General Terrero, Bangar, La Union, Ilocos Region, 2519, Philippines', 16.89718730, 120.42398940, '2026-08-17 15:30:29', '2026-08-19 16:07:08'),
(33, 'Yamaha', 'XSR155', 'motorcycle', 0, 'naked', 0, 'manual', 'KTF 8316', 900.00, 'uploads/vehicles/vehicle_6a82b949eac738.04220919.jpg', 'available', 'Antonino, Balaoan, La Union, Ilocos Region, 2517, Philippines', 16.82138680, 120.40051370, '2026-08-17 15:32:17', '2026-08-19 15:59:57'),
(34, 'Honda', 'ADV 160', 'motorcycle', 1, 'scooter', 0, 'automatic', 'D525NF', 800.00, 'uploads/vehicles/motorcycle_honda-adv-160_20260817-093539_b2fcb5.jpg', 'available', 'Antonino, Balaoan, La Union, Ilocos Region, 2517, Philippines', 16.82545102, 120.40066158, '2026-08-17 15:35:39', '2026-08-19 15:58:49'),
(35, 'Kia', 'Soluto 1.4', 'sedan', NULL, NULL, 0, 'automatic', 'NDM 1432', 1300.00, 'uploads/vehicles/sedan_kia-soluto-1-4_20260817-093821_d353ad.jpg', 'available', 'Antonino, Balaoan, La Union, Ilocos Region, 2517, Philippines', 16.82138680, 120.40051370, '2026-08-17 15:38:21', '2026-08-19 15:59:43'),
(36, 'Suzuki', 'APV GLX', 'van', NULL, NULL, 0, 'manual', 'NGD 8391', 2000.00, 'uploads/vehicles/van_suzuki-apv-glx_20260817-094013_cb07a8.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 15:40:13', '2026-08-19 15:58:24'),
(37, 'Hyundai', 'Tucson 2.0', 'suv', NULL, NULL, 0, 'automatic', 'NAD 5671', 2500.00, 'uploads/vehicles/suv_hyundai-tucson-2-0_20260817-094227_b46c3c.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 15:42:27', '2026-08-19 15:58:18'),
(38, 'Mitsubishi', 'Triton GX 2WD AT', 'pickup_truck', NULL, NULL, 0, 'automatic', 'XRM 3584', 2600.00, 'uploads/vehicles/pickup-truck_mitsubishi-triton-gx-2wd-at_20260817-094812_9c2684.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 15:48:12', '2026-08-19 15:58:13'),
(39, 'Hyundai', 'Santa Fe 2.5', 'suv', NULL, NULL, 0, 'automatic', 'TXI 681', 2900.00, 'uploads/vehicles/suv_hyundai-santa-fe-2-5_20260817-095356_25eb28.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 15:53:56', '2026-08-19 15:58:02'),
(40, 'Toyota', 'RAV4 2.5 Hybrid', 'suv', NULL, NULL, 0, 'automatic', 'NCP 7316', 3000.00, 'uploads/vehicles/suv_toyota-rav4-2-5-hybrid_20260817-095611_9218c5.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61695350, 120.31295200, '2026-08-17 15:56:11', '2026-08-19 15:57:55'),
(41, 'Hyundai', 'Elantra N', 'coupe', NULL, NULL, 1, 'automatic', 'WMT 6384', 5500.00, 'uploads/vehicles/coupe_hyundai-elantra-n_20260817-095902_591cf7.jpg', 'available', 'A. Bonifacio Street, Barangay IV, Sagayad, San Fernando, La Union, Ilocos Region, 2500, Philippines', 16.61547501, 120.31445765, '2026-08-17 15:59:02', '2026-08-21 13:41:42');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_images`
--

CREATE TABLE `vehicle_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_images`
--

INSERT INTO `vehicle_images` (`id`, `vehicle_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES
(23, 14, 'uploads/vehicles/motorcycle_yamaha-r15-v3_20260817-040712_65fd5e.jpg', 0, '2026-08-17 10:07:12', '2026-08-21 13:41:05'),
(24, 14, 'uploads/vehicles/motorcycle_yamaha-r15-v3_20260817-040712_a9c53f.jpg', 1, '2026-08-17 10:07:12', '2026-08-21 13:41:05'),
(25, 15, 'uploads/vehicles/motorcycle_honda-cbr150r_20260817-041008_3edec2.jpg', 0, '2026-08-17 10:10:08', '2026-08-21 13:41:05'),
(26, 15, 'uploads/vehicles/motorcycle_honda-cbr150r_20260817-041008_e31406.jpg', 1, '2026-08-17 10:10:08', '2026-08-21 13:41:05'),
(27, 15, 'uploads/vehicles/motorcycle_honda-cbr150r_20260817-041008_fedc36.jpg', 2, '2026-08-17 10:10:08', '2026-08-21 13:41:05'),
(28, 15, 'uploads/vehicles/motorcycle_honda-cbr150r_20260817-041008_657c95.jpg', 3, '2026-08-17 10:10:08', '2026-08-21 13:41:05'),
(29, 15, 'uploads/vehicles/motorcycle_honda-cbr150r_20260817-041008_7832bd.jpg', 4, '2026-08-17 10:10:08', '2026-08-21 13:41:05'),
(30, 16, 'uploads/vehicles/motorcycle_yamaha-mt-15_20260817-041311_29bd81.jpg', 0, '2026-08-17 10:13:11', '2026-08-21 13:41:05'),
(31, 16, 'uploads/vehicles/motorcycle_yamaha-mt-15_20260817-041311_315ec0.jpg', 1, '2026-08-17 10:13:11', '2026-08-21 13:41:05'),
(32, 16, 'uploads/vehicles/motorcycle_yamaha-mt-15_20260817-041311_f8de09.jpg', 2, '2026-08-17 10:13:11', '2026-08-21 13:41:05'),
(33, 17, 'uploads/vehicles/motorcycle_rusi-classic-250_20260817-041621_fcd9f4.jpg', 0, '2026-08-17 10:16:21', '2026-08-21 13:41:05'),
(34, 17, 'uploads/vehicles/motorcycle_rusi-classic-250_20260817-041621_f0a550.jpg', 1, '2026-08-17 10:16:21', '2026-08-21 13:41:05'),
(35, 17, 'uploads/vehicles/motorcycle_rusi-classic-250_20260817-041621_993824.jpg', 2, '2026-08-17 10:16:21', '2026-08-21 13:41:05'),
(36, 18, 'uploads/vehicles/sedan_mazda-mx-5_20260817-041959_24b418.jpg', 0, '2026-08-17 10:19:59', '2026-08-21 13:41:05'),
(37, 18, 'uploads/vehicles/sedan_mazda-mx-5_20260817-041959_6a17d2.jpg', 1, '2026-08-17 10:19:59', '2026-08-21 13:41:05'),
(38, 18, 'uploads/vehicles/sedan_mazda-mx-5_20260817-041959_0e39c5.jpg', 2, '2026-08-17 10:19:59', '2026-08-21 13:41:05'),
(39, 18, 'uploads/vehicles/sedan_mazda-mx-5_20260817-041959_3eb4b5.jpg', 3, '2026-08-17 10:19:59', '2026-08-21 13:41:05'),
(40, 18, 'uploads/vehicles/sedan_mazda-mx-5_20260817-041959_946554.jpg', 4, '2026-08-17 10:19:59', '2026-08-21 13:41:05'),
(41, 19, 'uploads/vehicles/sedan_subaru-brz-2-0_20260817-042354_342885.jpg', 0, '2026-08-17 10:23:54', '2026-08-21 13:41:05'),
(42, 19, 'uploads/vehicles/sedan_subaru-brz-2-0_20260817-042354_00cf03.jpg', 1, '2026-08-17 10:23:54', '2026-08-21 13:41:05'),
(43, 19, 'uploads/vehicles/sedan_subaru-brz-2-0_20260817-042354_0a813d.jpg', 2, '2026-08-17 10:23:54', '2026-08-21 13:41:05'),
(44, 19, 'uploads/vehicles/sedan_subaru-brz-2-0_20260817-042354_54225c.jpg', 3, '2026-08-17 10:23:54', '2026-08-21 13:41:05'),
(45, 19, 'uploads/vehicles/sedan_subaru-brz-2-0_20260817-042354_d091e3.jpg', 4, '2026-08-17 10:23:54', '2026-08-21 13:41:05'),
(46, 20, 'uploads/vehicles/sedan_toyota-86-2-0_20260817-042719_72ca53.jpg', 0, '2026-08-17 10:27:20', '2026-08-21 13:41:05'),
(47, 20, 'uploads/vehicles/sedan_toyota-86-2-0_20260817-042719_0e1fd8.jpg', 1, '2026-08-17 10:27:20', '2026-08-21 13:41:05'),
(48, 20, 'uploads/vehicles/sedan_toyota-86-2-0_20260817-042719_900749.jpg', 2, '2026-08-17 10:27:20', '2026-08-21 13:41:05'),
(49, 20, 'uploads/vehicles/sedan_toyota-86-2-0_20260817-042719_572aa5.jpg', 3, '2026-08-17 10:27:20', '2026-08-21 13:41:05'),
(50, 20, 'uploads/vehicles/sedan_toyota-86-2-0_20260817-042720_d5ff32.jpg', 4, '2026-08-17 10:27:20', '2026-08-21 13:41:05'),
(51, 21, 'uploads/vehicles/motorcycle_honda-rebel-300_20260817-043022_b753eb.jpg', 0, '2026-08-17 10:30:22', '2026-08-21 13:41:05'),
(52, 21, 'uploads/vehicles/motorcycle_honda-rebel-300_20260817-043022_98a0c2.jpg', 1, '2026-08-17 10:30:22', '2026-08-21 13:41:05'),
(53, 21, 'uploads/vehicles/motorcycle_honda-rebel-300_20260817-043022_0988cc.jpg', 2, '2026-08-17 10:30:22', '2026-08-21 13:41:05'),
(54, 22, 'uploads/vehicles/motorcycle_suzuki-gixxer-155_20260817-043332_6686e4.jpg', 0, '2026-08-17 10:33:32', '2026-08-21 13:41:05'),
(55, 22, 'uploads/vehicles/motorcycle_suzuki-gixxer-155_20260817-043332_0ecca4.jpg', 1, '2026-08-17 10:33:32', '2026-08-21 13:41:05'),
(56, 23, 'uploads/vehicles/motorcycle_suzuki-gsx-r150_20260817-043649_7f0f0a.jpg', 0, '2026-08-17 10:36:49', '2026-08-21 13:41:05'),
(57, 23, 'uploads/vehicles/motorcycle_suzuki-gsx-r150_20260817-043649_7a0987.jpg', 1, '2026-08-17 10:36:49', '2026-08-21 13:41:05'),
(58, 23, 'uploads/vehicles/motorcycle_suzuki-gsx-r150_20260817-043649_2becaa.jpg', 2, '2026-08-17 10:36:49', '2026-08-21 13:41:05'),
(59, 24, 'uploads/vehicles/sedan_mitsubishi-mirage-g4_20260817-044204_0e1326.jpg', 0, '2026-08-17 10:42:04', '2026-08-21 13:41:05'),
(60, 24, 'uploads/vehicles/sedan_mitsubishi-mirage-g4_20260817-044204_865362.jpg', 1, '2026-08-17 10:42:04', '2026-08-21 13:41:05'),
(61, 24, 'uploads/vehicles/sedan_mitsubishi-mirage-g4_20260817-044204_50cbd1.jpg', 2, '2026-08-17 10:42:04', '2026-08-21 13:41:05'),
(62, 24, 'uploads/vehicles/sedan_mitsubishi-mirage-g4_20260817-044204_69bc13.jpg', 3, '2026-08-17 10:42:04', '2026-08-21 13:41:05'),
(63, 24, 'uploads/vehicles/sedan_mitsubishi-mirage-g4_20260817-044204_06ec9f.jpg', 4, '2026-08-17 10:42:04', '2026-08-21 13:41:05'),
(64, 25, 'uploads/vehicles/suv_mazda-cx-5-2-5_20260817-044428_e0838e.jpg', 0, '2026-08-17 10:44:28', '2026-08-21 13:41:05'),
(65, 25, 'uploads/vehicles/suv_mazda-cx-5-2-5_20260817-044428_521f65.jpg', 1, '2026-08-17 10:44:28', '2026-08-21 13:41:05'),
(67, 26, 'uploads/vehicles/pickup-truck_isuzu-d-max-3-0-ls_20260817-044732_d6824b.jpg', 0, '2026-08-17 10:47:32', '2026-08-21 13:41:05'),
(68, 26, 'uploads/vehicles/pickup-truck_isuzu-d-max-3-0-ls_20260817-044732_514b12.jpg', 1, '2026-08-17 10:47:32', '2026-08-21 13:41:05'),
(70, 27, 'uploads/vehicles/van_toyota-hiace-commuter_20260817-044943_167f36.jpg', 0, '2026-08-17 10:49:43', '2026-08-21 13:41:05'),
(71, 27, 'uploads/vehicles/van_toyota-hiace-commuter_20260817-044943_b6a7d2.jpg', 1, '2026-08-17 10:49:43', '2026-08-21 13:41:05'),
(72, 28, 'uploads/vehicles/sedan_hyundai-accent-1-6_20260817-045212_ac4d25.jpg', 0, '2026-08-17 10:52:12', '2026-08-21 13:41:05'),
(73, 28, 'uploads/vehicles/sedan_hyundai-accent-1-6_20260817-045212_de3a69.jpg', 1, '2026-08-17 10:52:12', '2026-08-21 13:41:05'),
(74, 28, 'uploads/vehicles/sedan_hyundai-accent-1-6_20260817-045212_920c3b.jpg', 2, '2026-08-17 10:52:12', '2026-08-21 13:41:05'),
(75, 28, 'uploads/vehicles/sedan_hyundai-accent-1-6_20260817-045212_f726dd.jpg', 3, '2026-08-17 10:52:12', '2026-08-21 13:41:05'),
(76, 29, 'uploads/vehicles/sedan_toyota-corolla-altis-1-8_20260817-045415_fabdb5.jpg', 0, '2026-08-17 10:54:15', '2026-08-21 13:41:05'),
(77, 29, 'uploads/vehicles/sedan_toyota-corolla-altis-1-8_20260817-045415_707d98.jpg', 1, '2026-08-17 10:54:15', '2026-08-21 13:41:05'),
(79, 30, 'uploads/vehicles/pickup-truck_chevrolet-colorado-2-8-ltz_20260817-045556_28b360.jpg', 0, '2026-08-17 10:55:56', '2026-08-21 13:41:05'),
(80, 30, 'uploads/vehicles/pickup-truck_chevrolet-colorado-2-8-ltz_20260817-045556_b0ee26.jpg', 1, '2026-08-17 10:55:56', '2026-08-21 13:41:05'),
(81, 30, 'uploads/vehicles/pickup-truck_chevrolet-colorado-2-8-ltz_20260817-045556_0fd6c1.jpg', 2, '2026-08-17 10:55:56', '2026-08-21 13:41:05'),
(82, 31, 'uploads/vehicles/pickup-truck_nissan-navara-2-5-el_20260817-050027_7d8989.jpg', 0, '2026-08-17 11:00:27', '2026-08-21 13:41:05'),
(83, 31, 'uploads/vehicles/pickup-truck_nissan-navara-2-5-el_20260817-050027_67bd4e.jpg', 1, '2026-08-17 11:00:27', '2026-08-21 13:41:05'),
(84, 13, 'uploads/vehicles/vehicle_6a827a137203f5.16274554.jpg', 0, '2026-08-17 11:03:47', '2026-08-21 13:41:05'),
(85, 13, 'uploads/vehicles/vehicle_6a827a13725168.55167083.jpg', 1, '2026-08-17 11:03:47', '2026-08-21 13:41:05'),
(86, 13, 'uploads/vehicles/vehicle_6a827a13728c35.70426077.jpg', 2, '2026-08-17 11:03:47', '2026-08-21 13:41:05'),
(87, 12, 'uploads/vehicles/vehicle_6a827a826ae280.51167031.jpg', 1, '2026-08-17 11:05:38', '2026-08-21 13:41:05'),
(88, 12, 'uploads/vehicles/vehicle_6a827a826c4406.79321277.jpg', 0, '2026-08-17 11:05:38', '2026-08-21 13:41:05'),
(89, 12, 'uploads/vehicles/vehicle_6a827a826c9c54.33428793.jpg', 2, '2026-08-17 11:05:38', '2026-08-21 13:41:05'),
(90, 11, 'uploads/vehicles/vehicle_6a827ae388dcb3.72630100.jpg', 0, '2026-08-17 11:07:15', '2026-08-21 13:41:05'),
(91, 11, 'uploads/vehicles/vehicle_6a827ae38a5e30.58580395.jpg', 1, '2026-08-17 11:07:15', '2026-08-21 13:41:05'),
(92, 11, 'uploads/vehicles/vehicle_6a827ae38ab1c0.31070445.jpg', 2, '2026-08-17 11:07:15', '2026-08-21 13:41:05'),
(93, 10, 'uploads/vehicles/vehicle_6a827b593c41d1.55938604.jpg', 1, '2026-08-17 11:09:13', '2026-08-21 13:41:05'),
(94, 10, 'uploads/vehicles/vehicle_6a827b593c8fd1.31604031.jpg', 0, '2026-08-17 11:09:13', '2026-08-21 13:41:05'),
(95, 10, 'uploads/vehicles/vehicle_6a827b593e0338.89551557.jpg', 2, '2026-08-17 11:09:13', '2026-08-21 13:41:05'),
(97, 9, 'uploads/vehicles/vehicle_6a827bb724d3d4.20451771.jpg', 1, '2026-08-17 11:10:47', '2026-08-21 13:41:05'),
(98, 9, 'uploads/vehicles/vehicle_6a827bb7256cf0.22584707.jpg', 2, '2026-08-17 11:10:47', '2026-08-21 13:41:05'),
(99, 9, 'uploads/vehicles/vehicle_6a827bb725cd21.09227149.jpg', 0, '2026-08-17 11:10:47', '2026-08-21 13:41:05'),
(100, 9, 'uploads/vehicles/vehicle_6a827bb7272814.29595493.jpg', 3, '2026-08-17 11:10:47', '2026-08-21 13:41:05'),
(101, 8, 'uploads/vehicles/vehicle_6a827cda202e30.96125861.jpg', 0, '2026-08-17 11:15:38', '2026-08-21 13:41:05'),
(102, 8, 'uploads/vehicles/vehicle_6a827cda20dcc0.32720962.jpg', 2, '2026-08-17 11:15:38', '2026-08-21 13:41:05'),
(103, 8, 'uploads/vehicles/vehicle_6a827cda213dd8.92933392.jpg', 3, '2026-08-17 11:15:38', '2026-08-21 13:41:05'),
(104, 8, 'uploads/vehicles/vehicle_6a827cda217653.37456287.jpg', 4, '2026-08-17 11:15:38', '2026-08-21 13:41:05'),
(105, 7, 'uploads/vehicles/vehicle_6a827d546f5892.32119234.jpg', 0, '2026-08-17 11:17:40', '2026-08-21 13:41:05'),
(106, 7, 'uploads/vehicles/vehicle_6a827d546fb911.12439272.jpg', 2, '2026-08-17 11:17:40', '2026-08-21 13:41:05'),
(107, 7, 'uploads/vehicles/vehicle_6a827d54700df2.70845122.jpg', 3, '2026-08-17 11:17:40', '2026-08-21 13:41:05'),
(109, 6, 'uploads/vehicles/vehicle_6a827dd7de9741.59588386.jpg', 1, '2026-08-17 11:19:51', '2026-08-21 13:41:05'),
(110, 6, 'uploads/vehicles/vehicle_6a827dd7df50b5.31906483.jpg', 0, '2026-08-17 11:19:51', '2026-08-21 13:41:05'),
(111, 6, 'uploads/vehicles/vehicle_6a827dd7dfaab1.24794752.jpg', 2, '2026-08-17 11:19:51', '2026-08-21 13:41:05'),
(113, 5, 'uploads/vehicles/vehicle_6a827e4eebe6c6.39571345.jpg', 0, '2026-08-17 11:21:50', '2026-08-21 13:41:05'),
(114, 5, 'uploads/vehicles/vehicle_6a827e4eeceb97.60375617.jpg', 2, '2026-08-17 11:21:50', '2026-08-21 13:41:05'),
(115, 5, 'uploads/vehicles/vehicle_6a827e4eedd887.27846465.jpg', 3, '2026-08-17 11:21:50', '2026-08-21 13:41:05'),
(117, 4, 'uploads/vehicles/vehicle_6a827eafe85dc2.53451344.jpg', 2, '2026-08-17 11:23:27', '2026-08-21 13:41:05'),
(118, 4, 'uploads/vehicles/vehicle_6a827eafe8ac61.26805911.jpg', 0, '2026-08-17 11:23:27', '2026-08-21 13:41:05'),
(119, 4, 'uploads/vehicles/vehicle_6a827eafe904c2.42305813.jpg', 3, '2026-08-17 11:23:27', '2026-08-21 13:41:05'),
(121, 3, 'uploads/vehicles/vehicle_6a827f649073a6.76240154.jpg', 0, '2026-08-17 11:26:28', '2026-08-21 13:41:05'),
(122, 3, 'uploads/vehicles/vehicle_6a827f6490c0c1.55012226.jpg', 2, '2026-08-17 11:26:28', '2026-08-21 13:41:05'),
(123, 3, 'uploads/vehicles/vehicle_6a827f64911123.23096670.jpg', 3, '2026-08-17 11:26:28', '2026-08-21 13:41:05'),
(124, 3, 'uploads/vehicles/vehicle_6a827f64916ca4.05316499.jpg', 4, '2026-08-17 11:26:28', '2026-08-21 13:41:05'),
(125, 32, 'uploads/vehicles/motorcycle_kawasaki-dominar-400_20260817-093029_1108be.jpg', 0, '2026-08-17 15:30:29', '2026-08-21 13:41:05'),
(126, 32, 'uploads/vehicles/motorcycle_kawasaki-dominar-400_20260817-093029_e8adda.jpg', 1, '2026-08-17 15:30:29', '2026-08-21 13:41:05'),
(127, 33, 'uploads/vehicles/vehicle_6a82b949eac738.04220919.jpg', 0, '2026-08-17 15:33:29', '2026-08-21 13:41:05'),
(128, 33, 'uploads/vehicles/vehicle_6a82b949eac738.04220919.jpg', 1, '2026-08-17 15:33:29', '2026-08-21 13:41:05'),
(129, 33, 'uploads/vehicles/vehicle_6a82b949eb04f9.21417665.jpg', 2, '2026-08-17 15:33:29', '2026-08-21 13:41:05'),
(130, 33, 'uploads/vehicles/vehicle_6a82b949eb34b0.22497296.jpg', 3, '2026-08-17 15:33:29', '2026-08-21 13:41:05'),
(131, 34, 'uploads/vehicles/motorcycle_honda-adv-160_20260817-093539_768dbb.jpg', 1, '2026-08-17 15:35:39', '2026-08-21 13:41:05'),
(132, 34, 'uploads/vehicles/motorcycle_honda-adv-160_20260817-093539_b2fcb5.jpg', 0, '2026-08-17 15:35:39', '2026-08-21 13:41:05'),
(133, 35, 'uploads/vehicles/sedan_kia-soluto-1-4_20260817-093821_d353ad.jpg', 0, '2026-08-17 15:38:21', '2026-08-21 13:41:05'),
(134, 35, 'uploads/vehicles/sedan_kia-soluto-1-4_20260817-093821_dde380.jpg', 1, '2026-08-17 15:38:21', '2026-08-21 13:41:05'),
(135, 35, 'uploads/vehicles/sedan_kia-soluto-1-4_20260817-093821_e44793.jpg', 2, '2026-08-17 15:38:21', '2026-08-21 13:41:05'),
(136, 36, 'uploads/vehicles/van_suzuki-apv-glx_20260817-094013_cb07a8.jpg', 0, '2026-08-17 15:40:13', '2026-08-21 13:41:05'),
(137, 36, 'uploads/vehicles/van_suzuki-apv-glx_20260817-094013_245d89.jpg', 1, '2026-08-17 15:40:13', '2026-08-21 13:41:05'),
(139, 37, 'uploads/vehicles/suv_hyundai-tucson-2-0_20260817-094227_b46c3c.jpg', 0, '2026-08-17 15:42:27', '2026-08-21 13:41:05'),
(140, 37, 'uploads/vehicles/suv_hyundai-tucson-2-0_20260817-094227_49697c.jpg', 1, '2026-08-17 15:42:27', '2026-08-21 13:41:05'),
(141, 37, 'uploads/vehicles/suv_hyundai-tucson-2-0_20260817-094227_ac395b.jpg', 2, '2026-08-17 15:42:27', '2026-08-21 13:41:05'),
(142, 38, 'uploads/vehicles/pickup-truck_mitsubishi-triton-gx-2wd-at_20260817-094812_9c2684.jpg', 0, '2026-08-17 15:48:12', '2026-08-21 13:41:05'),
(143, 38, 'uploads/vehicles/pickup-truck_mitsubishi-triton-gx-2wd-at_20260817-094812_6cb665.jpg', 1, '2026-08-17 15:48:12', '2026-08-21 13:41:05'),
(144, 38, 'uploads/vehicles/pickup-truck_mitsubishi-triton-gx-2wd-at_20260817-094812_4e3d1b.jpg', 2, '2026-08-17 15:48:12', '2026-08-21 13:41:05'),
(145, 38, 'uploads/vehicles/pickup-truck_mitsubishi-triton-gx-2wd-at_20260817-094812_03c7d9.jpg', 3, '2026-08-17 15:48:12', '2026-08-21 13:41:05'),
(146, 39, 'uploads/vehicles/suv_hyundai-santa-fe-2-5_20260817-095356_25eb28.jpg', 0, '2026-08-17 15:53:56', '2026-08-21 13:41:05'),
(147, 39, 'uploads/vehicles/suv_hyundai-santa-fe-2-5_20260817-095356_cd5d25.jpg', 1, '2026-08-17 15:53:56', '2026-08-21 13:41:05'),
(148, 39, 'uploads/vehicles/suv_hyundai-santa-fe-2-5_20260817-095356_3ec8b2.jpg', 2, '2026-08-17 15:53:56', '2026-08-21 13:41:05'),
(149, 40, 'uploads/vehicles/suv_toyota-rav4-2-5-hybrid_20260817-095611_9218c5.jpg', 0, '2026-08-17 15:56:11', '2026-08-21 13:41:05'),
(150, 40, 'uploads/vehicles/suv_toyota-rav4-2-5-hybrid_20260817-095611_480eb0.jpg', 1, '2026-08-17 15:56:11', '2026-08-21 13:41:05'),
(151, 40, 'uploads/vehicles/suv_toyota-rav4-2-5-hybrid_20260817-095611_5e8629.jpg', 2, '2026-08-17 15:56:11', '2026-08-21 13:41:05'),
(152, 41, 'uploads/vehicles/coupe_hyundai-elantra-n_20260817-095902_591cf7.jpg', 0, '2026-08-17 15:59:02', '2026-08-21 13:41:05'),
(153, 41, 'uploads/vehicles/coupe_hyundai-elantra-n_20260817-095902_8617d2.jpg', 1, '2026-08-17 15:59:02', '2026-08-21 13:41:05'),
(154, 41, 'uploads/vehicles/coupe_hyundai-elantra-n_20260817-095902_be9e14.jpg', 2, '2026-08-17 15:59:02', '2026-08-21 13:41:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `booking_addons`
--
ALTER TABLE `booking_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`customer_id`,`vehicle_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`);

--
-- Indexes for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `booking_addons`
--
ALTER TABLE `booking_addons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `banners`
--
ALTER TABLE `banners`
  ADD CONSTRAINT `banners_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `booking_addons`
--
ALTER TABLE `booking_addons`
  ADD CONSTRAINT `booking_addons_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Constraints for table `vehicle_images`
--
ALTER TABLE `vehicle_images`
  ADD CONSTRAINT `vehicle_images_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
