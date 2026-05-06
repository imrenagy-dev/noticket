-- Jira Clone Schema
-- Run against your MySQL database AFTER running Laravel migrations (php artisan migrate)

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `key` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_team_key_unique` (`team_id`,`key`),
  KEY `projects_team_id_index` (`team_id`),
  KEY `projects_created_by_index` (`created_by`),
  CONSTRAINT `projects_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sprints` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `goal` text DEFAULT NULL,
  `status` enum('planned','active','completed') NOT NULL DEFAULT 'planned',
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sprints_project_id_index` (`project_id`),
  CONSTRAINT `sprints_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `issues` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `sprint_id` bigint(20) UNSIGNED DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reporter_id` bigint(20) UNSIGNED NOT NULL,
  `assignee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `type` enum('epic','story','task','bug','subtask') NOT NULL DEFAULT 'task',
  `status` enum('todo','in_progress','in_review','done') NOT NULL DEFAULT 'todo',
  `priority` enum('lowest','low','medium','high','highest') NOT NULL DEFAULT 'medium',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `checklist` json DEFAULT NULL,
  `story_points` tinyint(3) UNSIGNED DEFAULT NULL,
  `board_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `backlog_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `issues_project_number_unique` (`project_id`,`number`),
  KEY `issues_sprint_id_index` (`sprint_id`),
  KEY `issues_parent_id_index` (`parent_id`),
  KEY `issues_reporter_id_index` (`reporter_id`),
  KEY `issues_assignee_id_index` (`assignee_id`),
  CONSTRAINT `issues_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `issues_sprint_id_foreign` FOREIGN KEY (`sprint_id`) REFERENCES `sprints` (`id`) ON DELETE SET NULL,
  CONSTRAINT `issues_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `issues` (`id`) ON DELETE SET NULL,
  CONSTRAINT `issues_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`),
  CONSTRAINT `issues_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `labels` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3B82F6',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `labels_project_id_index` (`project_id`),
  CONSTRAINT `labels_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `issue_label` (
  `issue_id` bigint(20) UNSIGNED NOT NULL,
  `label_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`issue_id`,`label_id`),
  KEY `issue_label_label_id_index` (`label_id`),
  CONSTRAINT `issue_label_issue_id_foreign` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `issue_label_label_id_foreign` FOREIGN KEY (`label_id`) REFERENCES `labels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `issue_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_issue_id_index` (`issue_id`),
  KEY `comments_user_id_index` (`user_id`),
  CONSTRAINT `comments_issue_id_foreign` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
