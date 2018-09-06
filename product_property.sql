/*
Navicat MySQL Data Transfer

Source Server         : test
Source Server Version : 100109
Source Host           : localhost:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 100109
File Encoding         : 65001

Date: 2018-09-06 17:35:29
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for z_goods_property_rel
-- ----------------------------
DROP TABLE IF EXISTS `z_goods_property_rel`;
CREATE TABLE `z_goods_property_rel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `property_id` int(11) NOT NULL DEFAULT '0' COMMENT '属性值ID',
  `property_name` varchar(255) DEFAULT NULL COMMENT '属性名称',
  `vid` int(11) NOT NULL DEFAULT '0' COMMENT '属性值ID',
  `value_name` varchar(255) DEFAULT NULL COMMENT '属性值名称',
  `goods_type_id` int(11) NOT NULL DEFAULT '0',
  `property_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '属性类型 #0普通属性#1主销售属性#2销售属性',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `goods_id` (`goods_id`),
  KEY `zhcart_goods_id_vid` (`goods_id`,`vid`),
  KEY `property_id` (`property_id`) USING BTREE,
  KEY `zhcart_vid` (`vid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=609809 DEFAULT CHARSET=utf8 COMMENT='产品与属性关联表';
