-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql102.infinityfree.com
-- Generation Time: Apr 19, 2025 at 04:11 AM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_38410847_voting_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `party` varchar(100) NOT NULL,
  `bio` text NOT NULL,
  `photo_url` varchar(255) DEFAULT NULL COMMENT 'Candidate profile photo path',
  `votes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `name`, `party`, `bio`, `photo_url`, `votes`, `created_at`) VALUES
(1, 'Narendra Modi', 'BJP', 'Sabka Saath, Sabka Vikas, Sabka Vishwas, Sabka Prayas', 'uploads/candidates/72c5b7d84df4f7744951c5d1867bf8bc.png', 15, '2025-03-02 00:12:54'),
(2, 'Rahul Gandhi', 'Congress', 'Nyay, Samanta, Bhaichara', 'uploads/candidates/d4817f509590e3c7d54f8f83b69f8b54.png', 2, '2025-03-02 00:13:47'),
(3, 'Arvind Kejriwal', 'AAP', 'Paani, Bijli, Sadak, Shiksha, Swasthya', 'uploads/candidates/e86e485caa7d462aabb0aa95456e72f5.png', 1, '2025-03-02 00:14:33'),
(4, 'Mamta Banarjee', 'AITC', 'Ma, Mati, Manush', 'uploads/candidates/9aa775d8e6b3273fa9808ccdf90bd5cb.png', 0, '2025-03-02 00:15:53'),
(5, 'Nitish Kumar', 'JDU', 'Nyay ke Saath Vikas', 'uploads/candidates/3cd5855392b924ce92c80d3a4be7d32e.png', 0, '2025-03-02 00:23:12');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gmail` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `gmail`, `mobile`, `description`, `submitted_at`) VALUES
(1, 'Jyoti Jayant', 'jyotijayantofficial@gmail.com', '8002168630', 'The website is working perfect on mobile', '2025-03-02 06:25:37'),
(2, 'Ayush Kumar', 'ayush27a2@gmail.com', '9162345342', 'Hello this is Ayush, Contact kijiye malik ..just type tesing..', '2025-03-02 06:48:08'),
(3, 'Prem Prakash Kumar', 'premprakash801110@gmail.com', '9304910224', 'Bjp jindabad', '2025-03-02 07:35:08'),
(4, 'Arjun Kumar', 'kumararjun9852@gmail.com', '6207263304', 'Nice', '2025-03-03 05:36:26'),
(5, 'Chaitanya', 'chaitanya211515@gmail.com', '6203343779', 'I am very happy and satisfied with this interface ????', '2025-03-03 05:44:22'),
(6, 'Atul Anand', 'atulanand015@gmail.com', '8825183377', '????????', '2025-03-03 13:51:01');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `id` int(11) NOT NULL,
  `election_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_votes` int(11) NOT NULL,
  `total_candidates` int(11) NOT NULL,
  `total_voters` int(11) NOT NULL,
  `snapshot_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `election_date`, `total_votes`, `total_candidates`, `total_voters`, `snapshot_data`) VALUES
(1, '2025-04-18 22:06:54', 18, 5, 19, '{\"candidates\":[{\"id\":1,\"name\":\"Narendra Modi\",\"party\":\"BJP\",\"bio\":\"Sabka Saath, Sabka Vikas, Sabka Vishwas, Sabka Prayas\",\"photo_url\":\"uploads\\/candidates\\/72c5b7d84df4f7744951c5d1867bf8bc.png\",\"votes\":15,\"created_at\":\"2025-03-01 16:12:54\"},{\"id\":2,\"name\":\"Rahul Gandhi\",\"party\":\"Congress\",\"bio\":\"Nyay, Samanta, Bhaichara\",\"photo_url\":\"uploads\\/candidates\\/d4817f509590e3c7d54f8f83b69f8b54.png\",\"votes\":2,\"created_at\":\"2025-03-01 16:13:47\"},{\"id\":3,\"name\":\"Arvind Kejriwal\",\"party\":\"AAP\",\"bio\":\"Paani, Bijli, Sadak, Shiksha, Swasthya\",\"photo_url\":\"uploads\\/candidates\\/e86e485caa7d462aabb0aa95456e72f5.png\",\"votes\":1,\"created_at\":\"2025-03-01 16:14:33\"},{\"id\":4,\"name\":\"Mamta Banarjee\",\"party\":\"AITC\",\"bio\":\"Ma, Mati, Manush\",\"photo_url\":\"uploads\\/candidates\\/9aa775d8e6b3273fa9808ccdf90bd5cb.png\",\"votes\":0,\"created_at\":\"2025-03-01 16:15:53\"},{\"id\":5,\"name\":\"Nitish Kumar\",\"party\":\"JDU\",\"bio\":\"Nyay ke Saath Vikas\",\"photo_url\":\"uploads\\/candidates\\/3cd5855392b924ce92c80d3a4be7d32e.png\",\"votes\":0,\"created_at\":\"2025-03-01 16:23:12\"}],\"votes\":[{\"id\":1,\"user_id\":3,\"candidate_id\":1,\"voted_at\":\"2025-03-01 22:12:07\"},{\"id\":2,\"user_id\":4,\"candidate_id\":1,\"voted_at\":\"2025-03-01 22:48:27\"},{\"id\":3,\"user_id\":5,\"candidate_id\":1,\"voted_at\":\"2025-03-01 23:34:31\"},{\"id\":4,\"user_id\":7,\"candidate_id\":1,\"voted_at\":\"2025-03-02 11:29:58\"},{\"id\":5,\"user_id\":6,\"candidate_id\":1,\"voted_at\":\"2025-03-02 11:30:07\"},{\"id\":9,\"user_id\":8,\"candidate_id\":1,\"voted_at\":\"2025-03-02 20:32:26\"},{\"id\":10,\"user_id\":9,\"candidate_id\":1,\"voted_at\":\"2025-03-02 21:28:27\"},{\"id\":11,\"user_id\":10,\"candidate_id\":3,\"voted_at\":\"2025-03-02 21:33:05\"},{\"id\":12,\"user_id\":11,\"candidate_id\":1,\"voted_at\":\"2025-03-02 21:35:05\"},{\"id\":13,\"user_id\":12,\"candidate_id\":1,\"voted_at\":\"2025-03-02 21:43:56\"},{\"id\":14,\"user_id\":13,\"candidate_id\":1,\"voted_at\":\"2025-03-02 22:38:01\"},{\"id\":15,\"user_id\":14,\"candidate_id\":1,\"voted_at\":\"2025-03-03 00:32:57\"},{\"id\":16,\"user_id\":15,\"candidate_id\":1,\"voted_at\":\"2025-03-03 04:20:48\"},{\"id\":17,\"user_id\":16,\"candidate_id\":1,\"voted_at\":\"2025-03-03 05:50:09\"},{\"id\":18,\"user_id\":17,\"candidate_id\":1,\"voted_at\":\"2025-03-03 20:24:07\"},{\"id\":19,\"user_id\":18,\"candidate_id\":1,\"voted_at\":\"2025-03-04 21:29:43\"},{\"id\":20,\"user_id\":19,\"candidate_id\":2,\"voted_at\":\"2025-03-06 05:57:02\"},{\"id\":21,\"user_id\":21,\"candidate_id\":2,\"voted_at\":\"2025-03-10 11:15:51\"}],\"voters\":[{\"id\":2,\"username\":\"admin@123\",\"email\":\"admin123@gmail.com\",\"has_voted\":0},{\"id\":3,\"username\":\"jyoti\",\"email\":\"jyotijayantofficial@gmail.com\",\"has_voted\":0},{\"id\":4,\"username\":\"Ayush\",\"email\":\"ayush@gmail.com\",\"has_voted\":1},{\"id\":5,\"username\":\"Prem Prakash Kumar\",\"email\":\"premprakash801110@gmail.com\",\"has_voted\":1},{\"id\":6,\"username\":\"Shiv12\",\"email\":\"shiv456c@gmail.com\",\"has_voted\":1},{\"id\":7,\"username\":\"Anshu Anand\",\"email\":\"avilvats07@gmail.com\",\"has_voted\":1},{\"id\":8,\"username\":\"Ravi\",\"email\":\"ravi@gmail.com\",\"has_voted\":1},{\"id\":9,\"username\":\"Aman Kumar\",\"email\":\"amanper2002@gmail.com\",\"has_voted\":1},{\"id\":10,\"username\":\"Tejaswi sinha\",\"email\":\"sinhatejashwi19@gmail.com\",\"has_voted\":1},{\"id\":11,\"username\":\"Arjun kumar\",\"email\":\"kumararjun9852@gmail.com\",\"has_voted\":1},{\"id\":12,\"username\":\"Chaitanya@2115\",\"email\":\"chaitanya211515@gmail.com\",\"has_voted\":1},{\"id\":13,\"username\":\"Shashikant kumar\",\"email\":\"sashikantkumarbth000@gmail.com\",\"has_voted\":1},{\"id\":14,\"username\":\"Payal Kumari\",\"email\":\"payalkumari957075@gmail.com\",\"has_voted\":1},{\"id\":15,\"username\":\"Ankit kumar\",\"email\":\"ankit1234@gmail.com\",\"has_voted\":1},{\"id\":16,\"username\":\"Atul Anand\",\"email\":\"atulanand015@gmail.com\",\"has_voted\":1},{\"id\":17,\"username\":\"Sameer\",\"email\":\"sam2@gmail.com\",\"has_voted\":1},{\"id\":18,\"username\":\"Nikhil kumar\",\"email\":\"nk574239@gmail.com\",\"has_voted\":1},{\"id\":19,\"username\":\"Sonu\",\"email\":\"sk660139729038@gmail.com\",\"has_voted\":1},{\"id\":20,\"username\":\"Vivek\",\"email\":\"vivekcimage@gmail.com\",\"has_voted\":0},{\"id\":21,\"username\":\"Amrit5215\",\"email\":\"Amrit5215@gmail.com\",\"has_voted\":1}],\"created_at\":\"2025-04-18 18:06:55\"}');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(10) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `nationality` varchar(22) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `has_voted` tinyint(1) DEFAULT 0,
  `is_admin_dba` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `mobile`, `gender`, `dob`, `nationality`, `profile_photo`, `description`, `has_voted`, `is_admin_dba`, `created_at`) VALUES
(2, 'admin@123', '$2y$10$thAau0EfwPX4B1N/eQVVJOcEw4A.Nc7dvq8XJ3YzGUO4S9.wISnrq', 'admin123@gmail.com', NULL, NULL, NULL, NULL, NULL, '', 0, 1, '2025-03-02 06:01:46'),
(3, 'jyoti', '$2y$10$grTcM.Z2PdxyLU0Zx9w21.KRkIJ/wwYF6SzhK5XbOnkCKGW0ccbaC', 'jyotijayantofficial@gmail.com', '8002168630', 'Male', '2002-09-15', 'Indian', 'uploads/profiles/profile_67c3f4b14a5f6.jpg', '', 0, 0, '2025-03-02 06:03:29'),
(4, 'Ayush', '$2y$10$M6jf8wGBHp.dt32uwntnL.a8fB8UQyBwhxtr1zPcO.U3ceZfC0.py', 'ayush@gmail.com', '9162345342', 'Male', '2003-02-27', 'Indian', 'uploads/profiles/profile_67c3fe45ecb62.jpg', '', 1, 0, '2025-03-02 06:44:22'),
(5, 'Prem Prakash Kumar', '$2y$10$erwu8By6FNgltw3/e4Uz.eKyRr4607eJGbko8coZdr3mVwwqOxXuq', 'premprakash801110@gmail.com', '9304910224', 'Male', '2004-07-16', 'Indian', 'uploads/profiles/profile_67c409ef15a00.png', '', 1, 0, '2025-03-02 07:34:07'),
(6, 'Shiv12', '$2y$10$mbHVAhRjQJv7FH2LHz7qLus3fAMEby8CS76RXc7oShkn/YkgcxWaa', 'shiv456c@gmail.com', '6437549756', 'Male', '2003-03-14', 'Indian', 'uploads/profiles/profile_67c4b162e63f8.jpg', '', 1, 0, '2025-03-02 19:28:35'),
(7, 'Anshu Anand', '$2y$10$wrUG14LEm6AJY.Mtzs6.ue8RJlLZWQ87PBuPq6wlPllzENyYwWhSe', 'avilvats07@gmail.com', '9771653461', 'Male', '2005-03-07', 'Indian', 'uploads/profiles/profile_67c4b19718bbf.jpg', '', 1, 0, '2025-03-02 19:29:27'),
(8, 'Ravi', '$2y$10$E4r/5lJv/qlUmT2WVsmCr.yVhjtjhLbtpqSD2ZarM./22TeML8yu.', 'ravi@gmail.com', '8102966336', 'Male', '2004-04-18', 'Indian', 'uploads/profiles/profile_67c530b855789.jpg', '', 1, 0, '2025-03-03 04:31:52'),
(9, 'Aman Kumar', '$2y$10$0e1bskkrkAIFVIuJIuXnf.wne81zaoTkFlBRMyGT.kaD47341Khre', 'amanper2002@gmail.com', '8210684570', 'Male', '2002-09-09', 'Indian', 'uploads/profiles/profile_67c53de31757a.jpeg', '', 1, 0, '2025-03-03 05:28:03'),
(10, 'Tejaswi sinha', '$2y$10$jQDAsD7fqF6FrTcgacmDmeog37YunCksly66jshU5fuUNKL0FJ/R2', 'sinhatejashwi19@gmail.com', '9102643380', 'Male', '2005-02-19', 'Indian', 'uploads/profiles/profile_67c53ed83b8ea.jpg', '', 1, 0, '2025-03-03 05:32:08'),
(11, 'Arjun kumar', '$2y$10$2OqaBNEhbjp99gE6NtqN/OzqW098ydHAMPOCcg/u6eYYVrZbppoxe', 'kumararjun9852@gmail.com', '6207263304', 'Male', '2003-10-06', 'Indian', 'uploads/profiles/profile_67c53f666bc12.jpg', '', 1, 0, '2025-03-03 05:34:30'),
(12, 'Chaitanya@2115', '$2y$10$CrDG4t9e8tT.Y9OFdQarquoAjiduO6IyGj1vCc1bNXYrmIu9kIbQ6', 'chaitanya211515@gmail.com', '6203343779', 'Male', '2005-02-14', 'Indian', 'uploads/profiles/profile_67c541881cd06.jpg', '', 1, 0, '2025-03-03 05:43:36'),
(13, 'Shashikant kumar', '$2y$10$6POIXTROsvlF4meWepf2L.Jy.rVyOfE2ehRjQdcHIzu1pzTZafSyG', 'sashikantkumarbth000@gmail.com', '7370960729', 'Male', '2006-03-17', 'Indian', 'uploads/profiles/profile_67c54e01e75b4.jpg', '', 1, 0, '2025-03-03 06:36:50'),
(14, 'Payal Kumari', '$2y$10$9ZFBQlPh38gOsEKOnjM8yO9Zls3cvmb0FKiw/etJXnKTX3QxE9UQm', 'payalkumari957075@gmail.com', '9570757082', 'Female', '2005-07-12', 'Indian', 'uploads/profiles/profile_67c568fc9aa77.jpg', '', 1, 0, '2025-03-03 08:31:56'),
(15, 'Ankit kumar', '$2y$10$gQdKaK4pXivBIpo5.UodUufe4R2.tFlTv3uj0WxWZGrpfKz6NRGti', 'ankit1234@gmail.com', '6206015066', 'Male', '2002-04-07', 'Indian', 'uploads/profiles/profile_67c59e78e8a4e.jpg', '', 1, 0, '2025-03-03 12:20:09'),
(16, 'Atul Anand', '$2y$10$B2vm4HjMxF2KZLDGLkBwI.OOEjFsDqiDR7U7HXCA0w8WC6Vd9jjG.', 'atulanand015@gmail.com', '8825183377', 'Male', '2003-03-31', 'Indian', 'uploads/profiles/profile_67c5ace2bd80f.jpg', '', 1, 0, '2025-03-03 13:21:38'),
(17, 'Sameer', '$2y$10$8EeXnsIFmbotwGzniT3hQOtOFYDoFzMwatm/wGtQi.GrCUnAU5tQS', 'sam2@gmail.com', '7646042097', 'Male', '2004-03-04', 'Indian', 'uploads/profiles/profile_67c68041e380b.jpg', '', 1, 0, '2025-03-04 04:23:29'),
(18, 'Nikhil kumar', '$2y$10$7FAVGd7W//mg4urpKUVzeOlgh4LTcIDaj0hiO8dLeYxHrDzGplS9m', 'nk574239@gmail.com', '8603026219', 'Male', '2003-04-15', 'Indian', 'uploads/profiles/profile_67c7e12b29901.jpg', '', 1, 0, '2025-03-05 05:29:15'),
(19, 'Sonu', '$2y$10$hUECegTGMolxuO6HMhZDeO3ZdFXeqpZLuWw4J7oCs7RzifX4nykYS', 'sk660139729038@gmail.com', '7482093883', 'Male', '2004-03-06', 'Indian', 'uploads/profiles/profile_67c9a9568fa38.jpg', '', 1, 0, '2025-03-06 13:55:34'),
(20, 'Vivek', '$2y$10$AgVambK8ERRIxFEgwUBd4e.f7tCVuEOGuHFPOtR7k5UFx6MBRHnWy', 'vivekcimage@gmail.com', '6200746391', 'Male', '1982-03-13', 'Indian', 'uploads/profiles/profile_67c9bca0943c3.jpg', '', 0, 0, '2025-03-06 15:17:52'),
(21, 'Amrit5215', '$2y$10$FgAUKwJjl4Bp.TZThubW.ONpurkCKCp9YGmSuctz2fBIZIDgnj0Ju', 'Amrit5215@gmail.com', '9839949885', 'Male', '2000-01-10', 'Indian', 'uploads/profiles/profile_67cf2c138f039.jpeg', '', 1, 0, '2025-03-10 18:14:43');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `user_id`, `candidate_id`, `voted_at`) VALUES
(1, 3, 1, '2025-03-02 06:12:07'),
(2, 4, 1, '2025-03-02 06:48:27'),
(3, 5, 1, '2025-03-02 07:34:31'),
(4, 7, 1, '2025-03-02 19:29:58'),
(5, 6, 1, '2025-03-02 19:30:07'),
(9, 8, 1, '2025-03-03 04:32:26'),
(10, 9, 1, '2025-03-03 05:28:27'),
(11, 10, 3, '2025-03-03 05:33:05'),
(12, 11, 1, '2025-03-03 05:35:05'),
(13, 12, 1, '2025-03-03 05:43:56'),
(14, 13, 1, '2025-03-03 06:38:01'),
(15, 14, 1, '2025-03-03 08:32:57'),
(16, 15, 1, '2025-03-03 12:20:48'),
(17, 16, 1, '2025-03-03 13:50:09'),
(18, 17, 1, '2025-03-04 04:24:07'),
(19, 18, 1, '2025-03-05 05:29:43'),
(20, 19, 2, '2025-03-06 13:57:02'),
(21, 21, 2, '2025-03-10 18:15:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique` (`mobile`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
