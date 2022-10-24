/*
 Navicat Premium Data Transfer

 Source Server         : Mysql 5.7 (3310)
 Source Server Type    : MySQL
 Source Server Version : 50734
 Source Host           : 82.202.205.90:3310
 Source Schema         : php-example

 Target Server Type    : MySQL
 Target Server Version : 50734
 File Encoding         : 65001

 Date: 25/01/2022 11:57:53
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for customer_payments
-- ----------------------------
DROP TABLE IF EXISTS `customer_payments`;
CREATE TABLE `customer_payments`  (
                                      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                      `customer_id` bigint(20) UNSIGNED NOT NULL,
                                      `price` decimal(10, 2) NOT NULL,
                                      `created_at` timestamp NULL DEFAULT NULL,
                                      `updated_at` timestamp NULL DEFAULT NULL,
                                      PRIMARY KEY (`id`) USING BTREE,
                                      INDEX `customer_payments_customer_id_index`(`customer_id`) USING BTREE,
                                      CONSTRAINT `customer_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of customer_payments
-- ----------------------------
INSERT INTO `customer_payments` VALUES (10, 13, 1.00, '2020-11-11 12:20:52', '2020-11-11 12:20:52');
INSERT INTO `customer_payments` VALUES (11, 13, 1.00, '2020-11-11 12:20:56', '2020-11-11 12:20:56');
INSERT INTO `customer_payments` VALUES (12, 16, 100.00, '2020-11-13 13:54:21', '2020-11-13 13:54:21');
INSERT INTO `customer_payments` VALUES (13, 16, 200.00, '2020-11-13 13:54:32', '2020-11-13 13:54:32');
INSERT INTO `customer_payments` VALUES (14, 16, 300.00, '2020-11-13 13:54:37', '2020-11-13 13:54:37');

-- ----------------------------
-- Table structure for customers
-- ----------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers`  (
                              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                              `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                              `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                              `outer_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                              `created_at` timestamp NULL DEFAULT NULL,
                              `updated_at` timestamp NULL DEFAULT NULL,
                              PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of customers
-- ----------------------------
INSERT INTO `customers` VALUES (13, 'Ильяс', '79274361277', '64a620a7-4f8f-45cf-a69f-021b230cb2e5', '2020-11-11 12:19:41', '2020-11-11 12:19:41');
INSERT INTO `customers` VALUES (14, 'Дмитрий', '79061100524', 'fc8e99ab-3434-4f4a-94a4-4ddd5fd57340', '2020-11-11 12:20:40', '2020-11-11 12:20:40');
INSERT INTO `customers` VALUES (15, 'Дмитрий', '+79061100524', 'f35fabb8-9b15-4f6e-a69a-2d1b501c1293', '2020-11-11 15:43:00', '2020-11-11 15:43:00');
INSERT INTO `customers` VALUES (16, 'test', '89046784532', 'f6c5a270-b243-49f9-a352-a9ecacc3693e', '2020-11-13 13:53:53', '2020-11-13 13:53:53');

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
                                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                PRIMARY KEY (`id`) USING BTREE,
                                UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
                               `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                               `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                               `batch` int(11) NOT NULL,
                               PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '2014_10_12_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (2, '2014_10_12_100000_create_password_resets_table', 1);
INSERT INTO `migrations` VALUES (3, '2014_10_12_200000_add_two_factor_columns_to_users_table', 1);
INSERT INTO `migrations` VALUES (4, '2019_08_19_000000_create_failed_jobs_table', 1);
INSERT INTO `migrations` VALUES (5, '2019_12_14_000001_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` VALUES (6, '2020_05_21_100000_create_teams_table', 1);
INSERT INTO `migrations` VALUES (7, '2020_05_21_200000_create_team_user_table', 1);
INSERT INTO `migrations` VALUES (8, '2020_11_09_134354_create_sessions_table', 1);
INSERT INTO `migrations` VALUES (9, '2020_11_10_104923_create_customers_table', 1);
INSERT INTO `migrations` VALUES (10, '2020_11_10_104949_create_customer_payments_table', 1);

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets`  (
                                    `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `created_at` timestamp NULL DEFAULT NULL,
                                    INDEX `password_resets_email_index`(`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_resets
-- ----------------------------

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens`  (
                                           `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                           `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `tokenable_id` bigint(20) UNSIGNED NOT NULL,
                                           `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
                                           `last_used_at` timestamp NULL DEFAULT NULL,
                                           `created_at` timestamp NULL DEFAULT NULL,
                                           `updated_at` timestamp NULL DEFAULT NULL,
                                           PRIMARY KEY (`id`) USING BTREE,
                                           UNIQUE INDEX `personal_access_tokens_token_unique`(`token`) USING BTREE,
                                           INDEX `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type`, `tokenable_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of personal_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions`  (
                             `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
                             `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
                             `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
                             `payload` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `last_activity` int(11) NOT NULL,
                             PRIMARY KEY (`id`) USING BTREE,
                             INDEX `sessions_user_id_index`(`user_id`) USING BTREE,
                             INDEX `sessions_last_activity_index`(`last_activity`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sessions
-- ----------------------------
INSERT INTO `sessions` VALUES ('1MrcLPqd6VjAhQV7cUsmIJi0dhFXQ7XV27CcerKc', NULL, '92.118.160.17', 'NetSystemsResearch studies the availability of various services across the internet. Our website is netsystemsresearch.com', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY3FybmVYeXVFMzI0eWdoVTdCOUJiZlphNkllZjVxdEFycnZpSWlRWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642327000);
INSERT INTO `sessions` VALUES ('5Rk9w2yaAuNhxFnofn6R5vDXyhrHKxWtmHJKqFUE', NULL, '185.158.249.55', '', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMHkxOGtjWXB0dEFla2ZEZWdheTJOUnZqU2d6WVh4ekg5YkFjZVh1ZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642983199);
INSERT INTO `sessions` VALUES ('9lPcTdXfxHc6SVYpw4dNZD7WHlNO5G45zP5BNV63', NULL, '35.212.163.212', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko; compatible; BW/1.1; bit.ly/2W6Px8S; 4ba2cd9212) Chrome/84.0.4147.105 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMjUzdlJMMWp1WDZNazU4NVBvOUtYT283QjJVQW1jQ25DckIxdzFjRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642743789);
INSERT INTO `sessions` VALUES ('a6FAAt09XtExeQ2VtOQK5szeNuMw8br79JnuLOSB', NULL, '34.86.35.29', 'Expanse indexes the network perimeters of our customers. If you have any questions or concerns, please reach out to: scaninfo@expanseinc.com', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRUl4QlZQZFl3ZUtrR1M2TGNqbkZQSHZCNkNzRnV4MUltUE0zYkw0WiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vd3d3LnBocC1leGFtcGxlLnJkYngyNC5ydSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1641901032);
INSERT INTO `sessions` VALUES ('aCsE6OCusxU20KJxY1ZqrHs8a18C3nllju4mkyo9', NULL, '45.129.18.240', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.47 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiVkpIRUwzekhvUHAyWHJVYmFmcVo0Tm41bGM5NWM4QXd6a3dWakl1ZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1641866660);
INSERT INTO `sessions` VALUES ('AjHyo5VkbBqjoJkiWCM0xRCKxFlgDaC8CsEnKsWW', NULL, '45.129.18.188', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.47 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoieG1tZmM0bnNBT3FETlFLRklrclZ0U3JmM1hRT0hOZlRGd3FWUk1FSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642125085);
INSERT INTO `sessions` VALUES ('CViISRvPQ1drkZMcxYdpXC7YzxaziR7SNMf16Hdl', NULL, '34.86.35.10', 'Expanse indexes the network perimeters of our customers. If you have any questions or concerns, please reach out to: scaninfo@expanseinc.com', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV2xQMTlrQkd2bDVEWTEzQzUya0JCbjZWdlpjQnN5TVI1THJHRzl0YSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642188136);
INSERT INTO `sessions` VALUES ('e6NoOdEbCAE309VmlPjck9Edb6sIILEvQE2GvdG8', NULL, '34.86.35.26', 'Expanse indexes the network perimeters of our customers. If you have any questions or concerns, please reach out to: scaninfo@expanseinc.com', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWk9RYmdIQ3hUbDJqQ2Y4bjZyQ25PQjlyTkh6ZDFCUGVFSmVjOW9mSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vd3d3LnBocC1leGFtcGxlLnJkYngyNC5ydSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1642516460);
INSERT INTO `sessions` VALUES ('eYHTMr1qQGqkmfPbjR5UucedJFeJDXATNMlWi6vm', NULL, '157.90.206.35', 'python-requests/2.24.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQXFaWDVraWRTU3BLRUhURHUyOFdJNjhhZTlHQmlPdXB6dlNXMkJkTiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642407836);
INSERT INTO `sessions` VALUES ('FeBTdXV0HS58QQ8of9j4SP18hxcoEAJNYo8l6DIl', NULL, '34.222.205.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko; compatible; BW/1.1; bit.ly/2W6Px8S; cd32af34fd) Chrome/84.0.4147.105 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMVYzNjFKTjd2aDFFS09KUkU5a3hKUXVMMm1NRWw2Skd5RnJTdkpZNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642495132);
INSERT INTO `sessions` VALUES ('gh1OyE8S2gXjE4i7j05q5SZa6OyEFd8hzlJT0zzZ', NULL, '159.223.46.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib1B1cER5NUhVaXBLa1pwVVhod3VGOExPWWpFNkxaNTd0b0pheDlPayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642127846);
INSERT INTO `sessions` VALUES ('H23StcsFGlqxqORR5jj8B8sX5yppZj8VimJtDU9r', NULL, '34.96.130.10', 'Expanse indexes the network perimeters of our customers. If you have any questions or concerns, please reach out to: scaninfo@expanseinc.com', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicGRnQjFQWTA2aGNPcGZsNmU3aDhIOGxIQ3NwNTVBN1RhSzRFcDludiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vd3d3LnBocC1leGFtcGxlLnJkYngyNC5ydSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1642219635);
INSERT INTO `sessions` VALUES ('hHghYGLLJO8uDhj69lXNOZ465aUYc6mknOzhRTbu', NULL, '92.118.160.5', 'Go http package', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQVlDWGpDaXpIeWdSdm5TcGtDazVLZkNOMGE5NXAxbEY3eGg4NHh5cCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642241924);
INSERT INTO `sessions` VALUES ('jtg3kZcyIn6TkZmaSsHtNO2TMYjEdkB5tEN6xWFL', NULL, '51.158.109.3', '', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRzl3TFFmcnJ5UkpLcXd3R2xxV0pVT1Job1Rpbm1aTkF1WXZuSHE5MiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vd3d3LnBocC1leGFtcGxlLnJkYngyNC5ydSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1642781318);
INSERT INTO `sessions` VALUES ('mTy4zSCpG0CjnVIFEA3OaircnheVjQtSSLfmuVzV', NULL, '92.118.160.41', 'NetSystemsResearch studies the availability of various services across the internet. Our website is netsystemsresearch.com', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid1JEUnB5S1FKSG5USk1vaDNMbzdua0dTbU9rUEFNS2pjT1JvbjlMVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vd3d3LnBocC1leGFtcGxlLnJkYngyNC5ydSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1642427750);
INSERT INTO `sessions` VALUES ('NzHAaZVFQRS6H02UD6rhfO6FAmplOE6WPGb9nVjo', NULL, '163.172.180.25', '', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia3FKaG5ZM0tnbHNTeDE5ZE9BVUFIbGp2OHBNOTJBUmx3UEFaN1F1aCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642518693);
INSERT INTO `sessions` VALUES ('QFskEwsuYsz9QbUGlTt9LnKTb8KLlngn9hMYcJKa', NULL, '34.86.35.7', 'Expanse indexes the network perimeters of our customers. If you have any questions or concerns, please reach out to: scaninfo@expanseinc.com', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRUtrSGtEdGVjN21rRWVWR0tMZVlyZGpadXg3WnVDWXFKbFdzYVBRSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642464380);
INSERT INTO `sessions` VALUES ('sDPbQGZfUv3hHahP8xEybOMK2bbtH2cfpio84xzp', NULL, '92.118.160.13', 'NetSystemsResearch studies the availability of various services across the internet. Our website is netsystemsresearch.com', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMnRJZWJqSW9yanJ5V3Z4Rm9wQllYQk03U1BkRFlwZldGeklyZGVkeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1643052050);
INSERT INTO `sessions` VALUES ('shhrUD38jWk0p3sIStZZ43riL9nsJtzyWUSxDIlL', NULL, '34.96.130.29', 'Expanse indexes the network perimeters of our customers. If you have any questions or concerns, please reach out to: scaninfo@expanseinc.com', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ0MwR2lud2d3WXh5b2hxTG5LQnRFZHdYY2RyMWpWNDA3MTB5a1V4bCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vcGhwLWV4YW1wbGUucmRieDI0LnJ1Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1642745551);
INSERT INTO `sessions` VALUES ('zWeLFpe6lOyKZueiqr2QEeCGnhcervnm3zfflFF6', NULL, '157.90.206.35', 'python-requests/2.24.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZmZiaURwUWozWTBvYjBzTFpyY0hUd1l3QlRkS1hYQ0sweVIyYkk3ZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vd3d3LnBocC1leGFtcGxlLnJkYngyNC5ydSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1642408393);

-- ----------------------------
-- Table structure for team_user
-- ----------------------------
DROP TABLE IF EXISTS `team_user`;
CREATE TABLE `team_user`  (
                              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                              `team_id` bigint(20) UNSIGNED NOT NULL,
                              `user_id` bigint(20) UNSIGNED NOT NULL,
                              `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
                              `created_at` timestamp NULL DEFAULT NULL,
                              `updated_at` timestamp NULL DEFAULT NULL,
                              PRIMARY KEY (`id`) USING BTREE,
                              UNIQUE INDEX `team_user_team_id_user_id_unique`(`team_id`, `user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of team_user
-- ----------------------------

-- ----------------------------
-- Table structure for teams
-- ----------------------------
DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams`  (
                          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                          `user_id` bigint(20) UNSIGNED NOT NULL,
                          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                          `personal_team` tinyint(1) NOT NULL,
                          `created_at` timestamp NULL DEFAULT NULL,
                          `updated_at` timestamp NULL DEFAULT NULL,
                          PRIMARY KEY (`id`) USING BTREE,
                          INDEX `teams_user_id_index`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of teams
-- ----------------------------
INSERT INTO `teams` VALUES (1, 1, "admin's Team", 1, "2020-11-11 08:02:08", '2020-11-11 08:02:08');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `two_factor_recovery_codes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `current_team_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `profile_photo_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', 'test@rdbx24.ru', NULL, '$2y$10$dtjWDe1oqWpHYvUynxaKIu9OQ6Q/P66eTcDozTalscqv/J6g7Pype', NULL, NULL, 'k3z33fridep61R2q2xXTb9XzAWtyPb3ZjAim1S3oPRGvLYBjM7egZIGxL9BP', 1, NULL, '2020-11-11 08:02:08', '2021-08-23 11:16:55');

SET FOREIGN_KEY_CHECKS = 1;
