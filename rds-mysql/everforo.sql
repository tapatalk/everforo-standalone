/*
 Navicat Premium Data Transfer

 Source Server         : 测试sa
 Source Server Type    : MySQL
 Source Server Version : 50722
 Source Host           : efalone-rds-instance-1.cz7ssnoe2lvt.us-east-1.rds.amazonaws.com:3306
 Source Schema         : everforo

 Target Server Type    : MySQL
 Target Server Version : 50722
 File Encoding         : 65001

 Date: 15/03/2021 10:42:30
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admins
-- ----------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of admins
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for airdrop_exec_log
-- ----------------------------
DROP TABLE IF EXISTS `airdrop_exec_log`;
CREATE TABLE `airdrop_exec_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `user_id` int(10) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(32) NOT NULL,
  `count` decimal(65,0) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=740 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of airdrop_exec_log
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for airdrop_job
-- ----------------------------
DROP TABLE IF EXISTS `airdrop_job`;
CREATE TABLE `airdrop_job` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) DEFAULT NULL,
  `rule_name` varchar(255) NOT NULL,
  `group_id` int(10) NOT NULL,
  `token_id` int(10) NOT NULL,
  `award_count` varchar(110) NOT NULL,
  `repeat` tinyint(1) NOT NULL DEFAULT '0',
  `condition` varchar(255) DEFAULT NULL,
  `days` int(4) NOT NULL DEFAULT '0',
  `exec_status` tinyint(1) NOT NULL DEFAULT '0',
  `exec_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of airdrop_job
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for airdrop_reach_count
-- ----------------------------
DROP TABLE IF EXISTS `airdrop_reach_count`;
CREATE TABLE `airdrop_reach_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `reach_count` int(11) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of airdrop_reach_count
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for attached_files
-- ----------------------------
DROP TABLE IF EXISTS `attached_files`;
CREATE TABLE `attached_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(300) NOT NULL,
  `name` varchar(45) NOT NULL,
  `size` varchar(45) NOT NULL,
  `mime_type` varchar(45) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `thread_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ipfs` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=221 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of attached_files
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for attached_files_setting
-- ----------------------------
DROP TABLE IF EXISTS `attached_files_setting`;
CREATE TABLE `attached_files_setting` (
  `group_id` int(11) NOT NULL,
  `allow_everyone` tinyint(4) NOT NULL DEFAULT '1',
  `allow_post` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of attached_files_setting
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for attachments
-- ----------------------------
DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(300) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `thread_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ipfs` varchar(128) DEFAULT NULL,
  `nsfw` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=652 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of attachments
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for block_users
-- ----------------------------
DROP TABLE IF EXISTS `block_users`;
CREATE TABLE `block_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `block_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique` (`block_user_id`,`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of block_users
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for categorys
-- ----------------------------
DROP TABLE IF EXISTS `categorys`;
CREATE TABLE `categorys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `last_active_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`group_id`,`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=793 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of categorys
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for erc20_token
-- ----------------------------
DROP TABLE IF EXISTS `erc20_token`;
CREATE TABLE `erc20_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) DEFAULT NULL,
  `decimal` smallint(2) DEFAULT '0',
  `contract_address` varchar(100) DEFAULT NULL,
  `transaction` varchar(100) DEFAULT NULL,
  `symbol` varchar(10) DEFAULT NULL,
  `logo` varchar(255) DEFAULT '',
  `abi` text,
  `allow_import` tinyint(2) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `block_number` decimal(65,0) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10065 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of erc20_token
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for erc20token_import_log
-- ----------------------------
DROP TABLE IF EXISTS `erc20token_import_log`;
CREATE TABLE `erc20token_import_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transactionHash` varchar(255) NOT NULL,
  `from` varchar(50) DEFAULT NULL,
  `to` varchar(50) DEFAULT NULL,
  `token_id` int(11) DEFAULT NULL,
  `blockNumber` decimal(65,0) DEFAULT NULL,
  `value` decimal(65,0) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = 验证error, 1 = log交易, 2= 验证success, 3= 正在兑入平台, 4=兑入成功， 5=兑入失败',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of erc20token_import_log
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for erc20token_pool
-- ----------------------------
DROP TABLE IF EXISTS `erc20token_pool`;
CREATE TABLE `erc20token_pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token_id` int(11) DEFAULT NULL,
  `private_key` varchar(100) DEFAULT NULL,
  `public_key` varchar(50) DEFAULT NULL,
  `abi` text,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of erc20token_pool
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `connection` text,
  `queue` text,
  `payload` longtext,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for features
-- ----------------------------
DROP TABLE IF EXISTS `features`;
CREATE TABLE `features` (
  `id` int(11) NOT NULL,
  `feature_name` varchar(50) NOT NULL,
  `desc` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of features
-- ----------------------------
BEGIN;
INSERT INTO `features` VALUES (2, 'subscription', NULL, NULL);
INSERT INTO `features` VALUES (3, 'share_externally', NULL, NULL);
INSERT INTO `features` VALUES (4, 'adminsAndModerators', NULL, NULL);
INSERT INTO `features` VALUES (5, 'GroupLevelPermission', NULL, NULL);
INSERT INTO `features` VALUES (6, 'attached_files', NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for features_config
-- ----------------------------
DROP TABLE IF EXISTS `features_config`;
CREATE TABLE `features_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `group_feature_unique_index` (`group_id`,`feature_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1375 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of features_config
-- ----------------------------
BEGIN;
INSERT INTO `features_config` VALUES (1370, 2, 1, 1, '2021-03-08 06:58:03', NULL);
INSERT INTO `features_config` VALUES (1371, 3, 1, 1, '2021-03-08 06:58:10', NULL);
INSERT INTO `features_config` VALUES (1372, 4, 1, 1, '2021-03-08 06:58:25', '2021-03-08 06:58:47');
INSERT INTO `features_config` VALUES (1373, 5, 1, 1, '2021-03-08 06:58:28', '2021-03-08 06:58:46');
INSERT INTO `features_config` VALUES (1374, 6, 1, 1, '2021-03-08 06:58:33', '2021-03-08 06:58:44');
COMMIT;

-- ----------------------------
-- Table structure for group_admin
-- ----------------------------
DROP TABLE IF EXISTS `group_admin`;
CREATE TABLE `group_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=655 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of group_admin
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for group_ban_users
-- ----------------------------
DROP TABLE IF EXISTS `group_ban_users`;
CREATE TABLE `group_ban_users` (
  `group_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of group_ban_users
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for group_erc20token
-- ----------------------------
DROP TABLE IF EXISTS `group_erc20token`;
CREATE TABLE `group_erc20token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `token_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(2) DEFAULT NULL,
  `blockchain_balance` decimal(65,0) NOT NULL DEFAULT '0',
  `is_import` tinyint(2) NOT NULL DEFAULT '0',
  `private_key` varchar(100) DEFAULT NULL,
  `public_key` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of group_erc20token
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for group_follow
-- ----------------------------
DROP TABLE IF EXISTS `group_follow`;
CREATE TABLE `group_follow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` varchar(45) NOT NULL,
  `likes_count` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_group` (`user_id`,`group_id`,`deleted_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=980 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of group_follow
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for group_level_setting
-- ----------------------------
DROP TABLE IF EXISTS `group_level_setting`;
CREATE TABLE `group_level_setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `visibility` int(11) NOT NULL DEFAULT '0',
  `joining` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of group_level_setting
-- ----------------------------
BEGIN;
INSERT INTO `group_level_setting` VALUES (71, 1, 1, 1, NULL, '2021-03-11 07:43:25', '2021-03-12 08:22:32');
COMMIT;

-- ----------------------------
-- Table structure for group_member_active
-- ----------------------------
DROP TABLE IF EXISTS `group_member_active`;
CREATE TABLE `group_member_active` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of group_member_active
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for group_pin
-- ----------------------------
DROP TABLE IF EXISTS `group_pin`;
CREATE TABLE `group_pin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `thread_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `pin_type` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of group_pin
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for groups
-- ----------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `owner` int(11) DEFAULT NULL,
  `cover` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `followers` int(11) NOT NULL DEFAULT '0',
  `posts` int(11) NOT NULL DEFAULT '0',
  `no_recommend` int(11) NOT NULL DEFAULT '0',
  `super_no_recommend` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ipfs` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ipns` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name_unique` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=505 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of groups
-- ----------------------------
BEGIN;
INSERT INTO `groups` VALUES (1, 'groupname', 'Pororo', '2021-03-05 15:25:56', 1, 'https://cdn.everforo.com/20210311/f314fc4c7f2813668d8402f08a81f70c.jpg', 'https://cdn.everforo.com/20210311/0f08636e54cbd7f8e57753d191954fc1.jpg', '2021-03-11 07:36:37', 'Pororo, the little penguin!', 0, 0, 0, 0, NULL, NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for images
-- ----------------------------
DROP TABLE IF EXISTS `images`;
CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=339 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of images
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for invite_member
-- ----------------------------
DROP TABLE IF EXISTS `invite_member`;
CREATE TABLE `invite_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '',
  `message` varchar(140) NOT NULL DEFAULT '',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of invite_member
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for join_group_record
-- ----------------------------
DROP TABLE IF EXISTS `join_group_record`;
CREATE TABLE `join_group_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `join_msg` varchar(500) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of join_group_record
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for likes
-- ----------------------------
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) NOT NULL DEFAULT '0',
  `reciver_id` int(10) NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1297 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of likes
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of migrations
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_id` int(11) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `thread_id` int(10) unsigned DEFAULT NULL,
  `post_id` int(10) unsigned DEFAULT NULL,
  `msg` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('reply','like','flag','airdrop','join','join_request') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4760 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of notifications
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for oauth_providers
-- ----------------------------
DROP TABLE IF EXISTS `oauth_providers`;
CREATE TABLE `oauth_providers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refresh_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `oauth_providers_user_id_foreign` (`user_id`) USING BTREE,
  KEY `oauth_providers_provider_user_id_index` (`provider_user_id`(191)) USING BTREE,
  CONSTRAINT `oauth_providers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of oauth_providers
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(40) DEFAULT NULL,
  `token` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) DEFAULT NULL,
  `order_total` decimal(14,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '1= 创建 2=支付成功 3=发生错误 5=订单发放完成',
  `user_agent` varchar(255) DEFAULT NULL,
  `related_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联不同产品表的主键',
  `extra_info` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of orders
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`(191)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of password_resets
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for payment
-- ----------------------------
DROP TABLE IF EXISTS `payment`;
CREATE TABLE `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(40) DEFAULT NULL,
  `payment_id` varchar(45) DEFAULT NULL COMMENT 'paypal 返回订单id',
  `money` decimal(14,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `platform` enum('paypal') DEFAULT NULL,
  `is_paid` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `extra_info` text,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of payment
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for posts
-- ----------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `ipfs` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group_post_id` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group_thread_id` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int(11) DEFAULT '-1',
  `group_id` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT '0',
  `deleted_by` int(11) DEFAULT '0',
  `nsfw` int(11) NOT NULL DEFAULT '-1',
  `nsfw_score` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `id_UNIQUE` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3970 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of posts
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for product
-- ----------------------------
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(30) DEFAULT NULL,
  `price` decimal(14,2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `related_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联不同产品表的主键',
  `product_type` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of product
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for push_token
-- ----------------------------
DROP TABLE IF EXISTS `push_token`;
CREATE TABLE `push_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(256) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `app_id` int(11) DEFAULT NULL,
  `arn` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `user_id` (`user_id`) USING BTREE,
  KEY `token` (`token`(255)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of push_token
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for report
-- ----------------------------
DROP TABLE IF EXISTS `report`;
CREATE TABLE `report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `reason` set('1','2','3','4','5') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of report
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for subscriptions
-- ----------------------------
DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `thread_id` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique` (`user_id`,`group_id`,`thread_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1349 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of subscriptions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for thread_subscribe
-- ----------------------------
DROP TABLE IF EXISTS `thread_subscribe`;
CREATE TABLE `thread_subscribe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `thread_id` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=407 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of thread_subscribe
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for threads
-- ----------------------------
DROP TABLE IF EXISTS `threads`;
CREATE TABLE `threads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `posts_count` int(11) DEFAULT NULL,
  `likes_count` int(11) DEFAULT NULL,
  `category_index_id` int(11) DEFAULT NULL,
  `group_thread_id` int(11) DEFAULT NULL,
  `first_post_id` int(11) DEFAULT NULL,
  `nsfw` int(11) NOT NULL DEFAULT '-1',
  `ipfs` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `no_recommend` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=999 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of threads
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for threads_track
-- ----------------------------
DROP TABLE IF EXISTS `threads_track`;
CREATE TABLE `threads_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `group_thread_user_unique` (`group_id`,`thread_id`,`user_id`) USING BTREE,
  KEY `group_id_index` (`group_id`) USING BTREE,
  KEY `user_id_index` (`user_id`) USING BTREE,
  KEY `group_user_id` (`group_id`,`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of threads_track
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for token_transaction
-- ----------------------------
DROP TABLE IF EXISTS `token_transaction`;
CREATE TABLE `token_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(32) NOT NULL,
  `from_wallet_id` int(10) DEFAULT NULL,
  `to_wallet_id` int(10) NOT NULL,
  `count` varchar(150) DEFAULT '0',
  `token_id` int(11) DEFAULT NULL,
  `channel` smallint(3) DEFAULT NULL COMMENT ' 1= airdrop',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1= success 2 = rollback',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=778 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of token_transaction
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for token_wallet
-- ----------------------------
DROP TABLE IF EXISTS `token_wallet`;
CREATE TABLE `token_wallet` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL,
  `token_id` int(11) DEFAULT NULL,
  `balance` decimal(65,0) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of token_wallet
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for token_wallet_log
-- ----------------------------
DROP TABLE IF EXISTS `token_wallet_log`;
CREATE TABLE `token_wallet_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `wallet_id` int(10) unsigned NOT NULL DEFAULT '0',
  `transaction_id` varchar(32) NOT NULL,
  `origin_balance` decimal(65,0) NOT NULL,
  `new_balance` decimal(65,0) NOT NULL,
  `count` decimal(65,0) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1403 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of token_wallet_log
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for user_behavior_record
-- ----------------------------
DROP TABLE IF EXISTS `user_behavior_record`;
CREATE TABLE `user_behavior_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of user_behavior_record
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for user_category_active
-- ----------------------------
DROP TABLE IF EXISTS `user_category_active`;
CREATE TABLE `user_category_active` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of user_category_active
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `photo_url` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `likes` int(10) unsigned NOT NULL DEFAULT '0',
  `posts` int(10) unsigned NOT NULL DEFAULT '0',
  `activate` int(11) DEFAULT '0',
  `last_post_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `email_UNIQUE` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=495 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of users
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for users_settings
-- ----------------------------
DROP TABLE IF EXISTS `users_settings`;
CREATE TABLE `users_settings` (
  `user_id` int(11) NOT NULL,
  `dark_mode` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `language` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of users_settings
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for withdraw_request
-- ----------------------------
DROP TABLE IF EXISTS `withdraw_request`;
CREATE TABLE `withdraw_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(65,0) NOT NULL,
  `token_id` int(11) NOT NULL,
  `to` varchar(50) NOT NULL,
  `order_id` varchar(40) DEFAULT NULL,
  `transactionHash` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1= 未购买创建,2 = 确认购买后创建未认证  3= 发放完成',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of withdraw_request
-- ----------------------------
BEGIN;
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
