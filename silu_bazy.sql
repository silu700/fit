	CREATE TABLE `fit_exercises` (
 `id` int NOT NULL AUTO_INCREMENT,
 `nazwa` varchar(100) NOT NULL,
 `opis` text,
 `youtube_link` varchar(255) DEFAULT NULL,
 `garmin_exercise_link` varchar(255) DEFAULT NULL,
 `image_path` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2

CREATE TABLE `fit_groups` (
 `id` int NOT NULL AUTO_INCREMENT,
 `nazwa` varchar(50) NOT NULL,
 `godzina` time NOT NULL,
 `opis` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin2

CREATE TABLE `fit_payments` (
 `id` int NOT NULL AUTO_INCREMENT,
 `user_id` int NOT NULL,
 `kwota` decimal(10,2) NOT NULL,
 `miesiac` tinyint NOT NULL COMMENT '1-12',
 `rok` int NOT NULL,
 `data_wplaty` date NOT NULL,
 `metoda` enum('Gotówka','Przelew','Karta') DEFAULT 'Gotówka',
 PRIMARY KEY (`id`),
 KEY `user_id` (`user_id`),
 CONSTRAINT `fit_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `fit_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin2

CREATE TABLE `fit_plan_details` (
 `id` int NOT NULL AUTO_INCREMENT,
 `plan_id` int NOT NULL,
 `exercise_id` int NOT NULL,
 `dzien_tygodnia` enum('Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota','Niedziela') NOT NULL,
 `kolejnosc` int DEFAULT '1',
 `serie` int NOT NULL,
 `powtorzenia` varchar(20) NOT NULL,
 `ciezar` decimal(5,2) DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `fk_detail_plan` (`plan_id`),
 KEY `fk_detail_exercise` (`exercise_id`),
 CONSTRAINT `fk_detail_exercise` FOREIGN KEY (`exercise_id`) REFERENCES `fit_exercises` (`id`),
 CONSTRAINT `fk_detail_plan` FOREIGN KEY (`plan_id`) REFERENCES `fit_training_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin2

CREATE TABLE `fit_schedule` (
 `id` int NOT NULL AUTO_INCREMENT,
 `group_id` int NOT NULL,
 `dzien_tygodnia` int NOT NULL COMMENT '1-Pn, 7-Nd',
 `godzina` time NOT NULL,
 PRIMARY KEY (`id`),
 KEY `group_id` (`group_id`),
 CONSTRAINT `fit_schedule_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `fit_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin2

CREATE TABLE `fit_training_plans` (
 `id` int NOT NULL AUTO_INCREMENT,
 `user_id` int NOT NULL,
 `data_start` date NOT NULL,
 `data_koniec` date NOT NULL,
 `czy_aktywny` tinyint(1) DEFAULT '1',
 PRIMARY KEY (`id`),
 KEY `fk_plan_user` (`user_id`),
 CONSTRAINT `fk_plan_user` FOREIGN KEY (`user_id`) REFERENCES `fit_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin2

CREATE TABLE `fit_users` (
 `id` int NOT NULL AUTO_INCREMENT,
 `group_id` int DEFAULT NULL,
 `imie` varchar(50) NOT NULL,
 `nazwisko` varchar(50) NOT NULL,
 `email` varchar(100) NOT NULL,
 `garmin_user_link` varchar(255) DEFAULT NULL,
 `subscription_status` enum('active','inactive') DEFAULT 'inactive',
 `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 UNIQUE KEY `email` (`email`),
 KEY `fk_user_group` (`group_id`),
 CONSTRAINT `fk_user_group` FOREIGN KEY (`group_id`) REFERENCES `fit_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin2

CREATE TABLE `fit_user_groups` (
 `id` int NOT NULL AUTO_INCREMENT,
 `user_id` int NOT NULL,
 `group_id` int NOT NULL,
 PRIMARY KEY (`id`),
 KEY `user_id` (`user_id`),
 KEY `group_id` (`group_id`),
 CONSTRAINT `fit_user_groups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `fit_users` (`id`) ON DELETE CASCADE,
 CONSTRAINT `fit_user_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `fit_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin2