--first input
CREATE DATABASE `to-do_list`;

--second input
CREATE TABLE `users` (
  `id` int(11) PRIMARY KEY,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
)

--third input
CREATE TABLE `tasks` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) DEFAULT NULL,
  `task` varchar(255) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

