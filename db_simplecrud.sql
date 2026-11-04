CREATE DATABASE IF NOT EXISTS `db_simplecrud` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_simplecrud`;

DROP TABLE IF EXISTS `tb_loan_items`;
DROP TABLE IF EXISTS `tb_loans`;
DROP TABLE IF EXISTS `tb_goods`;
DROP TABLE IF EXISTS `tb_facilities`;
DROP TABLE IF EXISTS `tb_users`;
DROP TABLE IF EXISTS `tb_borrowers`;

CREATE TABLE `tb_borrowers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `campus_id` varchar(50) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `borrower_type` varchar(20) NOT NULL DEFAULT 'Student',
  `department` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `active_status` varchar(20) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `campus_id_unique` (`campus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `borrower_id` int DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'User',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`),
  KEY `borrower_idx` (`borrower_id`),
  CONSTRAINT `fk_tb_users_borrower` FOREIGN KEY (`borrower_id`) REFERENCES `tb_borrowers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_facilities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `building` varchar(150) NOT NULL,
  `capacity` int NOT NULL,
  `description` text DEFAULT NULL,
  `availability_status` varchar(30) NOT NULL DEFAULT 'Available',
  `maintenance_status` varchar(30) NOT NULL DEFAULT 'Operational',
  `maintenance_notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_goods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `inventory_code` varchar(80) NOT NULL,
  `category` varchar(100) NOT NULL,
  `location` varchar(150) NOT NULL,
  `quantity` int NOT NULL DEFAULT 0,
  `condition_status` varchar(30) NOT NULL DEFAULT 'Good',
  `description` text DEFAULT NULL,
  `maintenance_status` varchar(30) NOT NULL DEFAULT 'Available',
  `maintenance_notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_code_unique` (`inventory_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_loans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `borrower_id` int NOT NULL,
  `request_type` varchar(30) NOT NULL DEFAULT 'Facility',
  `facility_id` int DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `request_date` datetime NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `issue_date` datetime DEFAULT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `condition_on_return` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `borrower_idx` (`borrower_id`),
  KEY `facility_idx` (`facility_id`),
  CONSTRAINT `fk_tb_loans_borrower` FOREIGN KEY (`borrower_id`) REFERENCES `tb_borrowers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tb_loans_facility` FOREIGN KEY (`facility_id`) REFERENCES `tb_facilities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tb_loan_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `loan_id` int NOT NULL,
  `good_id` int DEFAULT NULL,
  `facility_id` int DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `loan_idx` (`loan_id`),
  KEY `good_idx` (`good_id`),
  KEY `facility_item_idx` (`facility_id`),
  CONSTRAINT `fk_tb_loan_items_loan` FOREIGN KEY (`loan_id`) REFERENCES `tb_loans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tb_loan_items_good` FOREIGN KEY (`good_id`) REFERENCES `tb_goods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tb_loan_items_facility` FOREIGN KEY (`facility_id`) REFERENCES `tb_facilities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tb_borrowers` (`campus_id`, `full_name`, `borrower_type`, `department`, `phone`, `email`, `active_status`) VALUES
('IDB-2024-001', 'I Putu Adi Wijaya', 'Student', 'Architecture', '+62 812-3456-1001', 'adi.wijaya@student.idbbali.ac.id', 'Active'),
('IDB-2024-012', 'Ni Kadek Sari Hapsari', 'Student', 'Visual Communication Design', '+62 812-3456-1012', 'sari.hapsari@student.idbbali.ac.id', 'Active'),
('IDB-2024-021', 'Made Dwi Pratama', 'Staff', 'Business Management', '+62 812-3456-1021', 'dwi.pratama@idbbali.ac.id', 'Active'),
('IDB-2024-034', 'Putu Angga Cahyadi', 'Student', 'Digital Business', '+62 812-3456-1034', 'angga.cahyadi@student.idbbali.ac.id', 'Active'),
('IDB-2024-045', 'Ni Made Ayu Lestari', 'Student', 'Interior Design', '+62 812-3456-1045', 'ayu.lestari@student.idbbali.ac.id', 'Active'),
('IDB-2024-052', 'Komang Surya Wirawan', 'Student', 'Master of Design', '+62 812-3456-1052', 'surya.wirawan@student.idbbali.ac.id', 'Active');

INSERT INTO `tb_users` (`borrower_id`, `full_name`, `email`, `password_hash`, `role`, `is_active`) VALUES
(NULL, 'Stockly Operator', 'operator@idbbali.ac.id', '$2y$10$a0E9fIGLhmxzNmydk9c0/eRIKClSSzwebZmr1B54C9aEJPrZL7Cr2', 'Operator', 1),
((SELECT `id` FROM `tb_borrowers` WHERE `campus_id` = 'IDB-2024-001'), 'I Putu Adi Wijaya', 'student@idbbali.ac.id', '$2y$10$RQjvyuKxvBGh7etXjzNIEuITvdqIF5LfxjDVu/9xs9uzyiO6AMRMe', 'User', 1),
((SELECT `id` FROM `tb_borrowers` WHERE `campus_id` = 'IDB-2024-012'), 'Ni Kadek Sari Hapsari', 'design@idbbali.ac.id', '$2y$10$RQjvyuKxvBGh7etXjzNIEuITvdqIF5LfxjDVu/9xs9uzyiO6AMRMe', 'User', 1),
((SELECT `id` FROM `tb_borrowers` WHERE `campus_id` = 'IDB-2024-021'), 'Made Dwi Pratama', 'staff@idbbali.ac.id', '$2y$10$RQjvyuKxvBGh7etXjzNIEuITvdqIF5LfxjDVu/9xs9uzyiO6AMRMe', 'User', 1);

INSERT INTO `tb_facilities` (`name`, `building`, `capacity`, `description`, `availability_status`, `maintenance_status`, `maintenance_notes`) VALUES
('Computer Lab', 'Innovation Center', 20, 'Computer lab for digital design, software practice, and creative projects.', 'Available', 'Operational', NULL),
('iMac Lab', 'Creative Technology Hub', 12, 'Apple-based design and media workstation laboratory for digital product work.', 'Available', 'Operational', NULL),
('Fashion Design Lab', 'Building A', 14, 'Practical sewing and material exploration room for design students.', 'Available', 'Operational', NULL),
('Library Reading Room', 'Library Building', 30, 'Quiet study space for individual learning and team review sessions.', 'Available', 'Operational', NULL),
('Creative Gallery', 'Arts Wing', 25, 'Presentation and display space for student showcase and project exhibitions.', 'Available', 'Operational', NULL),
('Hall', 'Main Campus Hall', 80, 'Multi-purpose hall for events, seminars, and presentation activities.', 'Available', 'Operational', NULL),
('Workshop Studio', 'Building B', 16, 'Studio space for model making, prototyping, and small-group creative workshops.', 'Available', 'Maintenance', 'Floor polishing and equipment check scheduled for this week.');

INSERT INTO `tb_goods` (`name`, `inventory_code`, `category`, `location`, `quantity`, `condition_status`, `description`, `maintenance_status`, `maintenance_notes`) VALUES
('MacBook Pro 14', 'IT-LAP-001', 'Laptop', 'iMac Lab', 8, 'Good', 'Portable workstation for visual design and digital media work.', 'Available', NULL),
('Canon EOS R10', 'DS-CAM-012', 'Camera', 'Creative Media Room', 3, 'Good', 'Digital camera for photography, product documentation, and studio tasks.', 'Available', NULL),
('Sewing Machine', 'FASH-SM-004', 'Textile Equipment', 'Fashion Design Lab', 6, 'Good', 'Portable sewing machine for design and prototyping practice.', 'Available', NULL),
('Projector Epson EB-945', 'AV-PRO-021', 'AV Equipment', 'Main Hall', 2, 'Good', 'Portable projector for lecture, presentation, and event use.', 'Available', NULL),
('3D Printer', 'CAD-3D-007', 'Prototyping', 'Innovation Hub', 2, 'Fair', 'Used for concept modeling and rapid physical prototyping.', 'Maintenance', 'Calibration needed next week.'),
('Measurement Kit', 'ARCH-ME-010', 'Architecture Tools', 'Architecture Studio', 10, 'Good', 'Measuring tools for site analysis and design drawing tasks.', 'Available', NULL),
('Drawing Tablet', 'DES-TAB-005', 'Design Tools', 'Visual Design Studio', 6, 'Good', 'Graphics tablet for illustration and product concept work.', 'Available', NULL),
('Camera Tripod', 'DS-TRIP-008', 'Photography', 'Media Room', 4, 'Good', 'Adjustable tripod for product and campus event photography.', 'Available', NULL);

INSERT INTO `tb_loans` (`id`, `borrower_id`, `request_type`, `facility_id`, `purpose`, `notes`, `request_date`, `start_date`, `end_date`, `due_date`, `issue_date`, `return_date`, `status`, `condition_on_return`) VALUES
(1, 2, 'Facility', 1, 'Design workshop and software practice', 'Students need the computer lab for prototyping and digital design exercises.', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '09:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00'), NULL, NULL, 'Approved', NULL),
(2, 1, 'Goods', NULL, 'Photography shoot for final project documentation', 'Need a camera and tripod for a campus design project shoot.', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '14:30:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:15:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:10:00'), 'Returned', 'Good'),
(3, 3, 'Facility', 2, 'Creative media review session', 'Need the iMac lab for digital editing and discussion before presentation.', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '08:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00'), NULL, NULL, 'Pending', NULL),
(4, 4, 'Goods', NULL, 'Prototype fabrication support', 'Need a small set of measuring tools and design equipment for a prototype review.', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '16:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '09:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '12:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '12:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '09:15:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '11:50:00'), 'Checked Out', 'Minor wear noted'),
(5, 5, 'Facility', 5, 'Student exhibition layout planning', 'Planning an art and design display for the upcoming gallery showcase.', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 6 DAY), '07:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '12:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '12:00:00'), NULL, NULL, 'Rejected', NULL),
(6, 6, 'Facility', 6, 'Campus event briefing', 'Need the hall for a student event briefing and presentation rehearsal.', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '11:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '12:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '12:00:00'), NULL, NULL, 'Approved', NULL),
(7, 1, 'Goods', NULL, 'Final model review equipment', 'Borrowing a drawing tablet and measurement kit for final studio reviews.', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 7 DAY), '10:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '13:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '16:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '16:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '13:05:00'), NULL, 'Checked Out', NULL),
(8, 2, 'Goods', NULL, 'Event setup and documentation', 'Borrowing a tripod and camera for event photos and media coverage.', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '17:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '08:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '12:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 5 DAY), '12:00:00'), NULL, NULL, 'Pending', NULL),
(9, 5, 'Facility', 4, 'Library study and review session', 'Need a quiet space for revision and small group reading before the midterm week.', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '08:30:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '11:30:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '11:30:00'), NULL, NULL, 'Cancelled', NULL);

INSERT INTO `tb_loan_items` (`loan_id`, `good_id`, `facility_id`, `quantity`) VALUES
(1, NULL, 1, 1),
(2, 2, NULL, 1),
(2, 8, NULL, 1),
(3, NULL, 2, 1),
(4, 6, NULL, 2),
(4, 7, NULL, 1),
(5, NULL, 5, 1),
(6, NULL, 6, 1),
(7, 7, NULL, 1),
(7, 6, NULL, 1),
(8, 2, NULL, 1),
(8, 8, NULL, 1),
(9, NULL, 4, 1);

SELECT 'Stockly demo data loaded successfully.' AS `status`;
