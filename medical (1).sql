-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 03, 2025 at 07:43 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medical`
--

-- --------------------------------------------------------

--
-- Table structure for table `diagnoses`
--

CREATE TABLE `diagnoses` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `diagnosis_date` datetime NOT NULL DEFAULT current_timestamp(),
  `condition_name` varchar(255) NOT NULL,
  `condition_category` varchar(50) NOT NULL,
  `severity_level` varchar(50) NOT NULL,
  `symptoms` text NOT NULL,
  `recommendations` text DEFAULT NULL,
  `confidence` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnoses`
--

INSERT INTO `diagnoses` (`id`, `patient_id`, `diagnosis_date`, `condition_name`, `condition_category`, `severity_level`, `symptoms`, `recommendations`, `confidence`) VALUES
(1, 21, '2025-03-12 09:22:13', 'Unknown', 'Acute', 'Medium', 'I have been experiencing itching, vomiting, fatigue, and weight loss. My fever is high and my skin has turned yellow. My urine is dark and I have been experiencing abdominal pain.', 'An error occurred during diagnosis: \'disease\'. Please try again or consult a doctor.', 0),
(2, 21, '2025-03-12 10:19:23', 'Typhoid', 'Acute', 'Low', 'I have lost my appetite and have noticed a significant weight loss. I have abdominal pain, especially in the area of my stomach and intestines. I am concerned about my health.', '\n1. Your symptoms suggest a possible acute condition.\n2. It\'s recommended to follow up with a healthcare provider for confirmation.\n3. Rest and stay hydrated while awaiting professional medical advice.\n4. Take medications only as prescribed by a healthcare professional.\n5. Monitor your symptoms and seek emergency care if they worsen significantly.\n            ', 100),
(3, 21, '2025-03-12 11:31:40', 'Jaundice', 'Acute', 'Low', 'I have been having severe itching, vomiting, and fatigue. I have also lost weight and have a high fever. My skin has turned yellow and my urine is dark. I am also experiencing abdominal pain', '1. Seek immediate medical attention as jaundice indicates liver dysfunction.\r\n2. Increase fluid intake to help flush toxins from your system.\r\n3. Get plenty of rest to allow your body to heal.\r\n4. Follow a low-fat diet and avoid alcohol completely.\r\n5. Follow-up with a specialist for further testing to determine the underlying cause.\r\n6. Take all prescribed medications as directed.\r\n7. Return to the hospital if you experience confusion, severe abdominal pain, or high fever.', 100),
(5, 13, '2025-03-27 16:05:36', 'Common Cold', 'Acute', 'Low', 'am feeling cold and i also have a headache  and shaking on my body', '1. Get plenty of rest to help your body fight the infection.\r\n2. Stay hydrated by drinking plenty of fluids.\r\n3. Use over-the-counter medications to relieve symptoms as needed.\r\n4. Use a humidifier or take hot showers to ease congestion.\r\n5. Gargle with salt water to soothe a sore throat.\r\n6. Wash hands frequently to prevent spreading the virus.\r\n7. Seek medical attention if symptoms last more than 10 days or are severe.', 45.56);

-- --------------------------------------------------------

--
-- Table structure for table `diagnosis_hospitals`
--

CREATE TABLE `diagnosis_hospitals` (
  `id` int(11) NOT NULL,
  `diagnosis_id` int(11) NOT NULL,
  `hospital_name` varchar(255) NOT NULL,
  `specialties` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `county` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `rating` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnosis_hospitals`
--

INSERT INTO `diagnosis_hospitals` (`id`, `diagnosis_id`, `hospital_name`, `specialties`, `location`, `county`, `contact`, `rating`) VALUES
(1, 2, 'Aga Khan University Hospital', NULL, 'Nairobi', NULL, '+254 20 366 2000', 4.7),
(2, 2, 'Nairobi Hospital', NULL, 'Nairobi', NULL, '+254 20 284 5000', 4.6),
(3, 2, 'Kenyatta National Hospital', NULL, 'Nairobi', NULL, '+254 20 2726300', 4.5),
(4, 3, 'Aga Khan University Hospital', NULL, 'Nairobi', NULL, '+254-20-3662000', 4.8),
(5, 3, 'Nairobi Hospital', NULL, 'Nairobi', NULL, '+254-20-2845000', 4.7),
(6, 3, 'Kenyatta National Hospital', NULL, 'Nairobi', NULL, '+254-20-2726300', 4.5),
(7, 5, 'Aga Khan University Hospital', NULL, 'Nairobi', NULL, '+254-20-3662000', 4.8),
(8, 5, 'Nairobi Hospital', NULL, 'Nairobi', NULL, '+254-20-2845000', 4.7),
(9, 5, 'Kenyatta National Hospital', NULL, 'Nairobi', NULL, '+254-20-2726300', 4.5);

-- --------------------------------------------------------

--
-- Table structure for table `diagnosis_medications`
--

CREATE TABLE `diagnosis_medications` (
  `id` int(11) NOT NULL,
  `diagnosis_id` int(11) NOT NULL,
  `medication_name` varchar(255) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `side_effects` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnosis_medications`
--

INSERT INTO `diagnosis_medications` (`id`, `diagnosis_id`, `medication_name`, `dosage`, `instructions`, `frequency`, `side_effects`) VALUES
(1, 1, 'Error in diagnosis', 'N/A', 'Please try again or contact support', 'N/A', 'N/A'),
(2, 2, 'Consult a doctor', 'As prescribed', 'Seek professional medical advice for proper medication', 'As directed', 'Medications should only be taken under medical supervision'),
(3, 3, 'Ursodeoxycholic acid', '10-15 mg/kg daily', 'Take with food to reduce stomach upset', 'Divided into 2-3 doses per day', 'Diarrhea, stomach pain, dizziness'),
(4, 3, 'Cholestyramine', '4g', 'Mix with water or juice and drink before meals', '2-4 times daily', 'Constipation, nausea, bloating'),
(5, 5, 'Paracetamol (Acetaminophen)', '500-1000mg', 'Take with or without food', 'Every 4-6 hours as needed', 'Rarely causes side effects at recommended doses'),
(6, 5, 'Pseudoephedrine', '60mg', 'Do not take close to bedtime', 'Every 4-6 hours as needed', 'Insomnia, nervousness, dizziness, increased blood pressure'),
(7, 5, 'Dextromethorphan', '15-30mg', 'Take with food or milk if stomach upset occurs', 'Every 6-8 hours as needed', 'Dizziness, drowsiness, nausea');

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `diagnosis_id` int(11) DEFAULT NULL,
  `medication_name` varchar(255) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `schedule` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `healing_advice` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`id`, `patient_id`, `diagnosis_id`, `medication_name`, `dosage`, `schedule`, `start_date`, `end_date`, `healing_advice`, `notes`, `status`, `created_at`) VALUES
(1, 13, 5, 'Paracetamo', '7 tablet', '3 times', '2025-04-03', '2025-04-03', 'take daily medicine', '', 'completed', '2025-04-03 05:25:49');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `birth_place` varchar(100) NOT NULL,
  `currentcity` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `fname`, `lname`, `gender`, `email`, `contact`, `password`, `date_of_birth`, `birth_place`, `currentcity`, `age`, `religion`, `registration_date`, `reset_token`, `reset_expires`) VALUES
(1, 'john', 'kiarie', 'male', 'john782@gmail.com', '0717100999', '$2y$10$gd05VZrH/yAHj531d.SRX.rF4M3MjZ6rNxgoYnX8eUO4HiOSbW8vW', '2000-03-20', 'nakuru', 'Murang\\\'a', 24, 'Muslim', '2024-12-02 07:37:26', NULL, NULL),
(2, 'Mercy', 'Wambui', 'male', 'johnmercy7877@gmail.com', '0717100999', '$2y$10$kwlcTLFs4.7aiYvfDi.PvuMPwodCUJI40cRnYX8qmBmQ57Z0OOqqW', '1988-12-12', 'Nairobi', 'Mombasa', 35, 'Christian', '2024-12-02 07:40:53', NULL, NULL),
(3, 'james', 'kamau', 'male', 'saimonmercy7877@gmail.com', '0717100999', '$2y$10$bY5Wzj1tLLtz.9x2IVzg8eJMONosiLUWAOV.Hzgz0Bqc0d7GqUE76', '1988-12-12', 'Kiambu', 'Murang\\\'a', 35, 'Christian', '2024-12-02 07:43:18', NULL, NULL),
(4, 'john', 'k', 'male', 'johnjgh782@gmail.com', '0717100996', '$2y$10$St7TilcYv.4CdnWgGkQCPuzZJUna0vKDbmWOeJrfEBf0HXac/q/De', '2000-03-20', 'Nakuru', 'Murang\\\'a', 24, 'Other', '2024-12-02 07:45:37', NULL, NULL),
(6, 'hjhj', 'mwangi', 'female', 'john253@gmail.com', '0758085920', '$2y$10$PjxFGCyNDedsLbNv9iYfxeM.vggbuYDvgG3f6CNTMCUsz3e6kK8aG', '2002-06-12', 'Kajiado', 'Kisumu', 22, 'Muslim', '2024-12-03 01:18:35', NULL, NULL),
(7, 'Grace', 'kiarie', 'male', 'ghf@gmail.com', '0758085920', '$2y$10$iICNwDvAQ7q.KgRGQiumFe5hWwcXcrOOg2j9EmTx0bFsyFYiUIjnm', '1978-09-12', 'Nairobi', 'Nairobi', 46, 'Christian', '2024-12-03 05:03:39', NULL, NULL),
(8, 'Beth', 'Wanjiku', 'female', 'shiks@gmail.com', '0180567434', '$2y$10$ik5FzvWya3abKUBtm2toVOSykfo5HV9RBsuf0M64OjWzqVXmmMQVi', '2005-08-20', 'Kilifi', 'Kiambu', 19, 'Other', '2024-12-03 05:40:59', NULL, NULL),
(9, 'Alice', 'Wanjiru', 'male', 'alice#@gmail.com', '0758085920', '$2y$10$pw.4zjL5HteZ2RL9LNGpiuBG16X0bTy4TPzOEiDOt2SPAStvXuWSK', '2001-06-12', 'Kisumu', 'Kitui', 23, 'Christian', '2024-12-03 08:00:24', NULL, NULL),
(11, 'Stanley', 'Haven', 'male', 'stanley@gmail.com', '0745637288', '$2y$10$Ej61JjzDhSK6E0qkElnWBueD2siy0KiEEL3qb.1KrSO51WuL6Gwpi', '2003-01-25', 'Nyeri', '', 22, '', '2025-01-26 06:09:31', NULL, NULL),
(12, 'Stanley', 'Haven', 'male', 'sjkhljh@gmail.com', '0745637288', '$2y$10$6ZjdTe98mcINp2/X4OFk.uIq2EW.uNYPp8h1J/SoIfuRvTGGX7ZDy', '2003-02-20', 'Kakamega', '', 21, '', '2025-02-05 01:03:53', NULL, NULL),
(13, 'john', 'kiarie', 'male', 'alicemurugi@gmail.com', '0717100999', '$2y$10$scLfS9ckJDr2zWEeVGBH4ehhEm/DJ8h7jvXBsYntLVDs4mVeMqtiC', '2025-02-06', 'Bungoma', '', 0, '', '2025-02-13 11:54:42', 'af2ce926230a42210533ad3a203cfe8d61e1858bbec24d9c2d2a9538c32f5491', '2025-02-20 05:25:38'),
(14, 'john', 'kiarie', 'male', 'kyle@gmail.com', '0717100999', '$2y$10$LCRTqsSfYa7pNsGZLe1oIe5pF0/x/Lna/YiXCWoorgucWFyS9EoMK', '2012-05-20', 'Homabay', '', 12, '', '2025-02-15 02:36:57', NULL, NULL),
(15, 'John', 'Kiarie', 'male', 'kylkkke@gmail.com', '0717100999', '$2y$10$szz.OtORGb/ZEF8wcSS4cebF6HJJUjF27ht2RywGPKXZRZRL0VWNO', '2003-06-12', 'Bungoma', '', 21, '', '2025-02-20 02:50:02', NULL, NULL),
(16, 'John', 'kamau4556', 'male', 'johnkarish021@gmail.com', '0717100999wedf', '$2y$10$j9XBPt1V.3.IKQNZXqU4e.lAvmeHL2ggLldXiNwYwTMvGLSUidKpG', '2002-12-12', 'Kisii', 'Bungoma', 22, '', '2025-02-20 03:56:11', NULL, NULL),
(17, 'john', 'jhjnb', 'male', 'racewambui@gmail.com', '0876532124', '$2y$10$TfqbMehZDTknP.oDCPBJyekF4op5d2.mTBkCWJUHp8shnuycDQbJO', '2002-09-21', 'Kitui', '', 22, '', '2025-02-20 05:08:26', NULL, NULL),
(18, 'john', 'karish', 'female', 'maina@gmail.com', '0723456789', '$2y$10$GDZyu9vAhKXePXedeZoNme2qzHi//2cTkmRma2Z5bSYHRNQ6eryMy', '2002-09-12', 'Meru', '', 22, '', '2025-02-20 05:17:42', NULL, NULL),
(19, 'saimon', 'kamau', 'male', 'hj@gmail.com', '0754321123', '$2y$10$fT0pdIBZnBxtM9WFGg/H2uA.z81ZCCxPbiLUThQ9s9NOBGfjbCWym', '2004-06-12', 'Meru', '', 20, '', '2025-02-20 06:14:41', NULL, NULL),
(20, 'Grace', 'Wambui', 'male', 'jemo@gmail.com', '0758085920', '$2y$10$a8vXJHUCDQEKRt5LsHo4D.NKeVMlAgZdZDc87Tl/z2PK0QoHLvLFC', '2020-08-27', 'Kericho', '', 4, '', '2025-02-22 03:57:21', NULL, NULL),
(21, 'Grace', 'Wambui', 'male', 'jemokama@gmail.com', '0758085920', '$2y$10$kT8.HDVB1zrthKhC/2Izt.tWAeI7Antjmags1hx9fT2zL6WQEqSiS', '2025-03-04', 'Kericho', '', 0, '', '2025-03-09 06:22:23', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `diagnosis_hospitals`
--
ALTER TABLE `diagnosis_hospitals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diagnosis_id` (`diagnosis_id`);

--
-- Indexes for table `diagnosis_medications`
--
ALTER TABLE `diagnosis_medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diagnosis_id` (`diagnosis_id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `diagnosis_id` (`diagnosis_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `diagnoses`
--
ALTER TABLE `diagnoses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `diagnosis_hospitals`
--
ALTER TABLE `diagnosis_hospitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `diagnosis_medications`
--
ALTER TABLE `diagnosis_medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD CONSTRAINT `diagnoses_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `diagnosis_hospitals`
--
ALTER TABLE `diagnosis_hospitals`
  ADD CONSTRAINT `diagnosis_hospitals_ibfk_1` FOREIGN KEY (`diagnosis_id`) REFERENCES `diagnoses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diagnosis_medications`
--
ALTER TABLE `diagnosis_medications`
  ADD CONSTRAINT `diagnosis_medications_ibfk_1` FOREIGN KEY (`diagnosis_id`) REFERENCES `diagnoses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `medications_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medications_ibfk_2` FOREIGN KEY (`diagnosis_id`) REFERENCES `diagnoses` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
