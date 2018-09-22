/*
Navicat MySQL Data Transfer

Source Server         : yii
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : api

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-09-22 17:40:46
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for member
-- ----------------------------
DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '手機號碼',
  `zone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '區號',
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8 DEFAULT '' COMMENT '姓名',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 DEFAULT '' COMMENT '信箱',
  `gender` tinyint(1) DEFAULT '1' COMMENT '性别,1男2女',
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL COMMENT '生日',
  `id_card` varchar(255) CHARACTER SET utf8 DEFAULT '' COMMENT 'ID',
  `status` tinyint(1) DEFAULT '1' COMMENT '用户状态',
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(300) CHARACTER SET utf8 DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='會員';
