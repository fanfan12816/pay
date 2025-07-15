/**
  创建日期:2024-03-18
  CHARSET=UTF8 默认编码格式
  ENGINE=InnoDB  数据库引擎为InnoDB
  UNSIGNED  不能为负数
  COMMENT  注释
  DEFAULT 0  默认为0
  DEFAULT NULL 默认为空(NULL)
  AUTO_INCREMENT 1 默认从1开始排列
  DROP TABLE IF EXISTS 表名  如果这个表已存在则删除
 */

-- 
-- DROP DATABASE IF EXISTS order;
-- CREATE DATABASE order CHARSET=UTF8;
-- USE order;

/** 前台数据表 Start **/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

/** 轮播图 **/

DROP TABLE IF EXISTS order_swipe;
CREATE TABLE `order_swipe` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `swipe_image`           VARCHAR (500) DEFAULT NULL COMMENT '图片地址',
  `swipe_path`            VARCHAR (500) DEFAULT NULL COMMENT '跳转地址',
  `swipe_isNewOpen`       TINYINT (1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否新窗口打开 1新窗口打开 0本窗口打开 默认为0',
  `swipe_type`            TINYINT (1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '终端类型 1移动端 0PC端 默认为0',
  `swipe_status`          TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '轮播状态 1启用 0禁用 默认为1',
  `swipe_sort`            INT (11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '轮播排序',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss'
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '轮播图';

/** 广告数据表 **/

DROP TABLE IF EXISTS order_advertise;
CREATE TABLE `order_advertise` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `ad_image`              VARCHAR (500) DEFAULT NULL COMMENT '广告图片地址',
  `ad_path`               VARCHAR (500) DEFAULT NULL COMMENT '广告跳转地址',
  `ad_isNewOpen`          TINYINT (1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否新窗口打开 1新窗口打开 0本窗口打开 默认为0',
  `ad_type`               TINYINT (1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '终端类型 1移动端 0PC端 默认为0',
  `ad_status`             TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '广告状态 1启用 0禁用 默认为1',
  `ad_sort`               INT (11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '广告排序',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss'
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '广告';

/** 公告数据表 **/

DROP TABLE IF EXISTS order_placard;
CREATE TABLE `order_placard` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `placard_name`          VARCHAR (100) DEFAULT NULL COMMENT '公告名称',
  `placard_content`       LONGTEXT DEFAULT NULL COMMENT '公告内容',
  `placard_type`          TINYINT (1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '公告类型 1弹窗 0普通 默认为0',
  `placard_status`        TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '公告状态 1启用 0禁用 默认为1',
  `placard_sort`          INT (11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '公告排序',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss'
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '公告';

/** 国家列表 **/

DROP TABLE IF EXISTS order_country;
CREATE TABLE `order_country` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `country_name`          VARCHAR (200) NOT NULL COMMENT '国家名称',
  `country_lang`          VARCHAR (200) NOT NULL COMMENT '语言标识',
  `country_code`          VARCHAR (20) NOT NULL COMMENT '手机区号',
  `country_sort`          INT (11) UNSIGNED NOT NULL COMMENT '排序编号',
  `country_status`        TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 1启用, 0禁用',
  `release_time`          DATETIME DEFAULT NULL COMMENT '修改时间 格式: YYYY-MM-DD HH:ii:ss',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss',
  UNIQUE INDEX `country_lang`(`country_lang`, `country_name`, `country_code`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '国家列表';

/** 语言类型 **/

DROP TABLE IF EXISTS order_lang_type;
CREATE TABLE `order_lang_type` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `lang_name`             VARCHAR (200) NOT NULL COMMENT '语言名称',
  `lang_code`             VARCHAR (200) NOT NULL COMMENT '语言标识',
  `lang_icon`             VARCHAR (500) NOT NULL COMMENT '语言图标',
  `lang_sort`             INT (11) UNSIGNED NOT NULL COMMENT '排序编号',
  `lang_status`           TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 1启用, 0禁用',
  `lang_default`          TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否默认语言: 1启用, 0禁用',
  `release_time`          DATETIME DEFAULT NULL COMMENT '修改时间 格式: YYYY-MM-DD HH:ii:ss',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss',
  UNIQUE INDEX `lang_code`(`lang_code`, `lang_name`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '语言类型';

INSERT INTO `order_lang_type` VALUES (NULL, '简体中文', 'zh_CN', '/UploadFile/locale/cn.svg', 1, 1, 1, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, '繁体中文', 'zh_HK', '/UploadFile/locale/hk.svg', 2, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, 'EngLish', 'en_US', '/UploadFile/locale/en.svg', 3, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, 'Español', 'es_ES', '/UploadFile/locale/es.svg', 4, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, 'شكرا جزيلا', 'ar_SA', '/UploadFile/locale/ar.svg', 5, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, 'Brasileiro', 'pt_BR', '/UploadFile/locale/pt.svg', 6, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, 'Türkçe', 'tr_TR', '/UploadFile/locale/tr.svg', 7, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, 'Français', 'fr_FR', '/UploadFile/locale/fr.svg', 8, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, 'Deutsch', 'de_DE', '/UploadFile/locale/de.svg', 9, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, '한국어', 'ko_KR', '/UploadFile/locale/ko.svg', 10, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, '日本語', 'ja_JP', '/UploadFile/locale/ja.svg', 11, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, 'Русский', 'ru_RU', '/UploadFile/locale/ru.svg', 12, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59'),
(NULL, 'ViệtName', 'vi_VN', '/UploadFile/locale/vi.svg', 13, 1, 0, '2023-12-14 13:55:59', '2023-12-14 13:55:59');

/** 语言配置 **/

DROP TABLE IF EXISTS order_lang_code;
CREATE TABLE `order_lang_code` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `lang_text`             VARCHAR (200) NOT NULL COMMENT '语言内容',
  `lang_code`             VARCHAR (200) NOT NULL COMMENT '语言标识',
  `code_number`           INT (11) UNSIGNED NOT NULL COMMENT '状态码',
  `lang_status`           TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 1启用, 0禁用',
  `is_admin`              TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '语言类型: 1客户端, 2服务端',
  `release_time`          DATETIME DEFAULT NULL COMMENT '修改时间 格式: YYYY-MM-DD HH:ii:ss',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss',
  UNIQUE INDEX `lang_code`(`lang_code`, `lang_text`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 10000 COMMENT = '语言配置';

/** 客服列表 **/

DROP TABLE IF EXISTS order_coustem_server;
CREATE TABLE `order_coustem_server` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `coustem_name`          VARCHAR (200) NOT NULL COMMENT '客服名称',
  `coustem_path`          VARCHAR (500) NOT NULL COMMENT '客服链接',
  `coustem_icon`          VARCHAR (500) NOT NULL COMMENT '客服图标',
  `coustem_status`        TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 1启用, 0禁用',
  `coustem_sort`          INT (11) UNSIGNED NOT NULL COMMENT '排序编号',
  `release_time`          DATETIME DEFAULT NULL COMMENT '修改时间 格式: YYYY-MM-DD HH:ii:ss',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss',
  UNIQUE INDEX `coustem_path`(`coustem_path`, `coustem_name`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '客服列表';

/** 站点信息数据表 **/

DROP TABLE IF EXISTS order_config;
CREATE TABLE `order_config` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `website_name`          VARCHAR (100) DEFAULT NULL COMMENT '站点名称',
  `website_description`   VARCHAR (500) DEFAULT NULL COMMENT '站点描述',
  `website_keywords`      VARCHAR (500) DEFAULT NULL COMMENT '站点关键词',
  `website_favicon`       VARCHAR (500) DEFAULT NULL COMMENT '站点角标',
  `website_login_logo`    VARCHAR (500) DEFAULT NULL COMMENT '登录logo',
  `website_logo`          VARCHAR (500) DEFAULT NULL COMMENT '站点logo',
  `website_copyright`     VARCHAR (100) DEFAULT NULL COMMENT '版权信息',
  `website_beian`         VARCHAR (100) DEFAULT NULL COMMENT '备案信息',
  `website_status`        TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点状态 1启用 0维护 默认为1',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss'
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '站点信息';

INSERT INTO `order_config` VALUES (NULL, 'order', 'order', 'order', 'https://www.baidu.com', 'https://www.baidu.com', 'https://www.baidu.com', 'CopyRight', 'order', 1, '2024-05-05 00:34:50');

/** 前台数据表 End **/

/** 后台数据表 Start **/

/** 后台账号数据表 **/

DROP TABLE IF EXISTS order_admin_user;
CREATE TABLE `order_admin_user` (
  `member_id`             INT (10) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `member_nickname`       VARCHAR (50) NOT NULL COMMENT '用户昵称',
  `member_portrait`       VARCHAR (500) DEFAULT NULL COMMENT '头像链接地址',
  `member_username`       VARCHAR (100) NOT NULL COMMENT '登录账号',
  `member_password`       VARCHAR (1000) NOT NULL COMMENT '登录密码',
  `member_auth_ip`        VARCHAR (500) NOT NULL COMMENT 'IP白名单',
  `member_authkey`        VARCHAR (500) NOT NULL COMMENT '谷歌验证码秘钥',
  `member_ip`             VARCHAR (100) DEFAULT NULL COMMENT '登录IP',
  `member_location`       VARCHAR (100) DEFAULT NULL COMMENT '登录IP属地',
  `member_group`          INT (11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '用户组ID',
  `user_id`               INT (11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '前台会员ID',
  `google_status`         TINYINT (1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '谷歌验证码状态 1开启 0关闭 默认0',
  `member_status`         TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '账户状态 1开启 0禁用 默认为1',
  `account_type`          TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '账号类型 1代理账号 2后台账号 默认为1',
  `member_online`         TINYINT (1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '在线状态 1在线 0离线 默认为0',
  `next_time`             DATETIME DEFAULT NULL COMMENT '上次登录时间 格式: YYYY-MM-DD HH:ii:ss',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss',
  UNIQUE INDEX `member_id`(`member_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '后台用户';

INSERT INTO `order_admin_user` VALUES (1, 'Admin', '/UploadFile/Avater/default.gif', 'admin', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzUxMiJ9.eyJwYXJhbXMiOiIxMjM0NTYifQ.NoGR1QRAh4GTvbmUtWbeZOE7L0T7xCYyedKLFXA4M68_6qZjOsLtRSzbhU1E0oFuoxMsqUemtFW4ij9TlGdnC1G2KznYyhgyO4pIGIjETYhqfoAlmlNdNs1hbyE-T3r88CTBLQKTcEXWswNzTcUE3w_8lr6Wa6R6ZVducD7_Fao', '127.0.0.1,0.0.0.0', 'V6NE6XKLCV4A62QI', NULL, NULL, 1, 0, 1, 1, 2, 0, NULL, '2024-03-18 22:17:59');

/** 用户组 **/

DROP TABLE IF EXISTS order_admin_auth;
CREATE TABLE `order_admin_auth` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `auth_title`            VARCHAR (100) NOT NULL COMMENT '用户组名称',
  `auth_code`             VARCHAR (100) NOT NULL COMMENT '角色标识',
  `auth_system`           TINYINT (1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作权限 1开启 0禁用 默认为0',
  `auth_status`           TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '用户组状态 1开启 0禁用 默认为1',
  `auth_permission`       VARCHAR (900) DEFAULT NULL COMMENT '授权菜单ID',
  `auth_sort`             INT (11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  `release_time`          DATETIME DEFAULT NULL COMMENT '审核时间 格式: YYYY-MM-DD HH:ii:ss',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss'
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '用户组';

INSERT INTO `order_admin_auth` VALUES (NULL, '超级管理员', 'admin', 1, 1, '1,2,37,38,39,40,41,42,65,66,67,70,6,10,43,44,45,57,26,27,28,46,47,48,49,50,51,52,53,54,61,62,63,55,7,8,9,32,56,17,18,19,20,21,22,23,24,25,33,34,35,58,59,60,29,30,31,64,68,69,4,14,15,16,3,5,36', 0, '2024-03-18 11:38:44', '2024-03-18 11:38:44'),
(NULL, '商户', 'mch', '0', '1', '72,74', '2', '2024-11-12 16:22:35', '2024-11-12 16:22:35');

/** 谷歌验证码管理 **/

DROP TABLE IF EXISTS order_google;
CREATE TABLE `order_google` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `key`                   CHAR (200) NOT NULL COMMENT '验证码秘钥',
  `qrcode`                VARCHAR (700) DEFAULT NULL COMMENT '二维码地址',
  `status`                TINYINT (3) UNSIGNED DEFAULT 1 COMMENT '是否启用 1启用 0关闭 默认为1',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss'
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '谷歌验证码';

/** 系统配置 **/

DROP TABLE IF EXISTS order_system_config;
CREATE TABLE `order_system_config` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `config_name`           VARCHAR (100) NOT NULL COMMENT '配置名称',
  `config_key`            VARCHAR (100) NOT NULL COMMENT '配置Key',
  `config_value`          VARCHAR (500) NOT NULL COMMENT '配置内容',
  `config_status`         TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '配置状态 1启用 0停用 默认为1',
  `admin_id`              INT (11) UNSIGNED NOT NULL COMMENT '修改用户ID',
  `release_time`          DATETIME DEFAULT NULL COMMENT '修改时间 格式: YYYY-MM-DD HH:ii:ss',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss',
  UNIQUE INDEX `config_key`(`config_key`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '系统配置';

INSERT INTO `order_system_config` VALUES (NULL, '注册状态', 'register_status', '1', 1, 2, '2024-03-18 03:15:46', '2024-03-18 03:15:46'),
(NULL, '站点名称', 'website_name', 'pay', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '站点描述', 'website_description', 'pay', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '站点关键词', 'website_keywords', 'pay', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '站点角标', 'website_favicon', '', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '登录logo', 'website_login_logo', '', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '站点logo', 'website_logo', '', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '首页Logo', 'website_home', '', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '版权信息', 'website_copyright', '', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '备案信息', 'website_beian', '', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '站点状态', 'website_status', '1', 1, 2, '2024-03-18 03:16:08', '2024-03-18 03:16:08'),
(NULL, '维护内容', 'maintain_content', '系统升级维护中,请耐心等候!', 1, 2, '2024-03-18 03:17:23', '2024-03-18 03:17:23'),
(NULL, '前端长时间未操作退出(秒)', 'logout_request_web', '7200', 1, 1, '2024-05-25 15:15:49', '2023-12-18 14:06:59'),
(NULL, '前端登录超时时间(秒)', 'logout_time_web', '172800', 1, 9, '2024-01-24 17:40:39', '2023-12-18 14:06:59'),
(NULL, '后台登录超时时间(秒)', 'logout_time_admin', '172800', 1, 2, '2024-01-20 08:43:32', '2024-01-20 08:30:47'),
(NULL, '商户后台登录超时时间(秒)', 'logout_time_mch', '172800', 1, 2, '2024-01-20 08:43:32', '2024-01-20 08:30:47'),
(NULL, '商户后台账号安全机制开启', 'login_restrictions', '1', 1, 2, '2024-01-20 08:43:32', '2024-01-20 08:30:47'),
(NULL, '商户后台连续输错次数,锁定次数', 'password_error_times', '5', 1, 2, '2024-01-20 08:43:32', '2024-01-20 08:30:47'),
(NULL, '商户后台连续输错次数,锁定时间(分)', 'limit_login_time', '10', 1, 2, '2024-01-20 08:43:32', '2024-01-20 08:30:47'),
(NULL, '图片域名', 'image_domain', 'https://api.order.cn', 1, 1, '2024-05-25 22:15:44', '2024-05-25 22:15:44'),
(NULL, '打针预设状态', 'inyectar_status', '1', 1, 1, '2024-05-25 22:15:44', '2024-05-25 22:15:44'),
(NULL, '默认任务数量', 'default_order_num', '10', 1, 1, '2024-05-25 22:15:44', '2024-05-25 22:15:44'),
(NULL, '火山翻译AccessKey', 'hs_access_key', 'AKLTMzkzZTEzNjg3OTg2NDViM2IwNmFlYzhmNzE4MmI4YmI', 1, 1, '2024-05-25 22:15:44', '2024-05-25 22:15:44'),
(NULL, '火山翻译SecretKey', 'hs_secret_key', 'TVRneU16STFOVFV4WVRkbE5ERTJaV0pqWm1aaU1UaGlNVFppWldZeE1HUQ==', 1, 1, '2024-05-25 22:15:44', '2024-05-25 22:15:44');

/** 接口访问记录 **/

DROP TABLE IF EXISTS order_interfacelog;
CREATE TABLE `order_interfacelog` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `interface_path`        VARCHAR (200) NOT NULL COMMENT '接口地址',
  `interface_type`        VARCHAR (20) NOT NULL COMMENT '接口应用名',
  `interface_method`      VARCHAR (20) NOT NULL COMMENT '请求类型',
  `interface_headers`     LONGTEXT NOT NULL COMMENT '请求头内容',
  `interface_re_header`   LONGTEXT NOT NULL COMMENT '响应头内容',
  `interface_params`      LONGTEXT NOT NULL COMMENT '请求参数',
  `interface_respones`    LONGTEXT NOT NULL COMMENT '响应内容',
  `interface_ip`          VARCHAR (50) NOT NULL COMMENT '客户端IP地址',
  `interface_city`        VARCHAR (200) NOT NULL COMMENT '客户端IP属地',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss'
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '接口访问记录';

/** 后台菜单 **/

DROP TABLE IF EXISTS order_auth_rule;
CREATE TABLE `order_auth_rule` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `rule_pid`              INT (11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级ID',
  `rule_title`            VARCHAR (50) NOT NULL COMMENT '菜单名称',
  `rule_permission`       VARCHAR (100) NULL DEFAULT NULL COMMENT '权限标识',
  `rule_path`             VARCHAR (255) NULL DEFAULT NULL COMMENT '路由地址',
  `rule_component`        VARCHAR (255) NULL DEFAULT NULL COMMENT '组件/页面路径',
  `rule_icon`             VARCHAR (50) NULL DEFAULT NULL COMMENT '菜单图标',
  `rule_condition`        VARCHAR (100) NULL DEFAULT NULL COMMENT '菜单条件',
  `rule_remark`           VARCHAR (255) NULL DEFAULT NULL COMMENT '菜单备注',
  `rule_ismenu`           VARCHAR (50) NOT NULL COMMENT '菜单类型 catalogue目录 menu菜单 button按钮',
  `rule_keepalive`        TINYINT (4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否缓存',
  `rule_show`             TINYINT (4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否显示',
  `rule_sort`             INT (11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '菜单排序',
  `rule_status`           TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态 1启用 0禁用',
  `admin_id`              INT (11) UNSIGNED NOT NULL COMMENT '修改用户ID',
  `type`                  TINYINT (1) UNSIGNED NOT NULL COMMENT '0=管理后台,1=商户后台',
  `release_time`          DATETIME DEFAULT NULL COMMENT '修改时间 格式: YYYY-MM-DD HH:ii:ss',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss',
  UNIQUE INDEX `rule_title`(`rule_title`, `rule_pid`, `rule_permission`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '后台菜单';

INSERT INTO `order_auth_rule` VALUES (1, 0, '后台首页', '', '/dashboard', 'LAYOUT', 'ant-design:home-outlined', NULL, '后台首页', 'catalogue', 1, 1, 1, 1, 1, 0, '2024-03-18 21:57:10', '2024-03-18 21:57:10'),
(2, 1, '分析台', '', '/analysis', '/dashboard/analysis/index', '', NULL, '分析台', 'menu', 1, 1, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(3, 0, '账户管理', '', '/AccountManger', 'LAYOUT', 'ant-design:user-outlined', '', '账户管理', 'catalogue', 1, 1, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(5, 3, '管理账户', 'AdminMember:getAdminMemberList', '/AdminMember', '/AccountManger/AdminMember/AdminMember', '', '', '管理账户', 'menu', 1, 1, 5, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(6, 0, '站点管理', '', '/SiteManger', 'LAYOUT', 'ant-design:hdd-outlined', '', '站点管理', 'catalogue', 1, 1, 8, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(7, 55, '系统配置', 'SystemConfig:getSystemConfigList', '/SystemConfig', '/SystemManger/SystemConfig/SystemConfig', '', NULL, '系统配置', 'menu', 1, 1, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(8, 55, '角色管理', 'AdminGroup:getAdminGroupList', '/UserGroup', '/SystemManger/UserGroup/UserGroup', '', NULL, '角色管理', 'menu', 1, 1, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(9, 55, '菜单管理', 'AuthRule:getAuthRuleList', '/AuthRule', '/SystemManger/AuthRule/AuthRule', '', NULL, '菜单管理', 'menu', 1, 1, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(10, 6, '站点信息', 'WebConfig:getWebSiteConfig', '/WebSite', '/SiteManger/WebSite/WebSite', '', NULL, '站点信息', 'menu', 1, 1, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(14, 5, '新增管理员', 'AdminMember:AddAdminMember', '', '', '', '', '新增管理员', 'button', 0, 0, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(15, 5, '编辑管理员', 'AdminMember:UpgradeAdminMember', '', '', '', '', '编辑管理员', 'button', 0, 0, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(16, 5, '删除管理员', 'AdminMember:DeleteAdminMember', '', '', '', '', '删除管理员', 'button', 0, 0, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(17, 7, '新增配置', 'SystemConfig:AddSystemConfigList', '', '', '', '', '新增配置', 'button', 0, 0, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(18, 7, '编辑配置', 'SystemConfig:UpgradeSystemConfigList', '', '', '', '', '编辑配置', 'button', 0, 0, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(19, 7, '删除配置', 'SystemConfig:DeleteSystemConfigList', '', '', '', '', '删除配置', 'button', 0, 0, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(20, 8, '新增角色', 'AdminGroup:AddAdminGroup', '', '', '', '', '新增角色', 'button', 0, 0, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(21, 8, '编辑角色', 'AdminGroup:UpgradeAdminGroup', '', '', '', '', '编辑角色', 'button', 0, 0, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(22, 8, '删除角色', 'AdminGroup:DeleteAdminGroup', '', '', '', '', '删除角色', 'button', 0, 0, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(23, 9, '新增菜单', 'AuthRule:AddAuthRule', '', '', '', '', '新增菜单', 'button', 0, 0, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(24, 9, '编辑菜单', 'AuthRule:UpgradeAuthRule', '', '', '', '', '编辑菜单', 'button', 0, 0, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(25, 9, '删除菜单', 'AuthRule:DeleteAuthRule', '', '', '', '', '删除菜单', 'button', 0, 0, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(26, 10, '新增站点', 'WebConfig:AddSiteConfig', '', '', '', '', '新增站点', 'button', 0, 0, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(27, 10, '编辑站点', 'WebConfig:UpgradeSiteConfig', '', '', '', '', '编辑站点', 'button', 0, 0, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(28, 10, '删除站点', 'WebConfig:DeleteSiteConfig', '', '', '', '', '删除站点', 'button', 0, 0, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(29, 0, '日志管理', NULL, '/LogManger', 'LAYOUT', 'ant-design:bug-filled', NULL, '日志管理', 'catalogue', 1, 1, 30, 1, 1, 0,'2024-05-21 21:39:42', '2024-05-21 21:39:42'),
(30, 29, '接口日志', 'InterFace:getInerFaceLogList', '/InterFaceLog', '/LogManger/InterFaceLog/InterFaceLog', '', NULL, '接口日志', 'menu', 1, 1, 1, 1, 1, 0, '2024-05-21 21:42:26', '2024-05-21 21:42:26'),
(31, 29, '余额日志', 'InterFace:getFinancialRecords', '/FinancialRecords', '/LogManger/FinancialRecords/FinancialRecords', NULL, NULL, '余额日志', 'menu', 1, 1, 2, 1, 1,  0, '2024-05-21 21:50:47', '2024-05-21 21:50:47'),
(32, 55, '语言管理', 'LangType:getLangTypeList', '/LangTypeManger', '/SystemManger/LangTypeManger/LangTypeManger', NULL, NULL, '语言管理', 'menu', 1, 1, 5, 1, 1,  0, '2024-05-22 23:47:00', '2024-05-22 23:47:00'),
(33, 32, '新增语言', 'LangType:CreateLangType', NULL, NULL, NULL, NULL, '新增语言', 'button', 0, 0, 1, 1, 1,  0, '2024-05-22 23:48:50', '2024-05-22 23:48:50'),
(34, 32, '编辑语言', 'LangType:UpgradeLangType', NULL, NULL, NULL, NULL, '编辑语言', 'button', 0, 0, 2, 1, 1, 0,  '2024-05-22 23:49:30', '2024-05-22 23:49:30'),
(35, 32, '删除语言', 'LangType:DeleteLangType', NULL, NULL, NULL, NULL, '删除语言', 'button', 0, 0, 3, 1, 1,  0, '2024-05-22 23:50:10', '2024-05-22 23:50:10'),
(43, 6, '轮播管理', 'Swipe:getSwipeList', '/Swipe', '/SiteManger/Swipe/Swipe', NULL, NULL, '轮播管理', 'menu', 1, 1, 6, 1, 1,  0, '2024-05-25 16:03:37', '2024-05-25 16:03:37'),
(44, 6, '广告管理', 'Commercial:getCommercial', '/Commercial', '/SiteManger/Commercial/Commercial', NULL, NULL, '广告管理', 'menu', 1, 1, 7, 1, 1, 0,  '2024-05-25 16:05:03', '2024-05-25 16:05:03'),
(45, 6, '公告管理', 'Placard:getPlacardList', '/Placard', '/SiteManger/Placard/Placard', NULL, NULL, '公告管理', 'menu', 1, 1, 8, 1, 1, 0,  '2024-05-25 16:06:05', '2024-05-25 16:06:05'),
(46, 43, '新增轮播', 'Swipe:AddSwipe', '', '', '', '', '新增轮播', 'button', 0, 0, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(47, 43, '编辑轮播', 'Swipe:UpgradeSwipe', '', '', '', '', '编辑轮播', 'button', 0, 0, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(48, 43, '删除轮播', 'Swipe:DeleteSwipe', '', '', '', '', '删除轮播', 'button', 0, 0, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(49, 44, '新增广告', 'Commercial:AddCommercial', '', '', '', '', '新增广告', 'button', 0, 0, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(50, 44, '编辑广告', 'Commercial:UpgradeCommercial', '', '', '', '', '编辑广告', 'button', 0, 0, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(51, 44, '删除广告', 'Commercial:DeleteCommercial', '', '', '', '', '删除广告', 'button', 0, 0, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(52, 45, '新增公告', 'Placard:AddPlacard', '', '', '', '', '新增公告', 'button', 0, 0, 1, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(53, 45, '编辑公告', 'Placard:UpgradePlacard', '', '', '', '', '编辑公告', 'button', 0, 0, 2, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(54, 45, '删除公告', 'Placard:DeletePlacard', '', '', '', '', '删除公告', 'button', 0, 0, 3, 1, 1, 0, NULL, '2024-03-18 21:57:10'),
(55, 0, '系统管理', NULL, '/SystemManger', 'LAYOUT', 'ant-design:appstore-filled', NULL, '系统管理', 'catalogue', 1, 1, 9, 1, 1,  0, '2024-05-26 20:23:28', '2024-05-26 20:23:28'),
(56, 55, '国家管理', 'Country:getCountryList', '/CountryManger', '/SystemManger/CountryManger/CountryManger', NULL, NULL, '国家管理', 'menu', 1, 1, 5, 1, 1,  0, '2024-05-26 20:51:21', '2024-05-26 20:51:21'),
(57, 6, '客服管理', 'CoustemServer:getCoustemServerList', '/CoustemServerManger', '/SiteManger/CoustemServerManger/CoustemServerManger', NULL, NULL, '客服管理', 'menu', 1, 1, 10, 1, 1,  0, '2024-05-26 20:52:48', '2024-05-26 20:52:48'),
(58, 56, '新增国家', 'Country:AddCountry', NULL, NULL, NULL, NULL, '新增国家', 'button', 0, 0, 1, 1, 1,  0, '2024-05-26 22:26:06', '2024-05-26 22:26:06'),
(59, 56, '编辑国家', 'Country:UpgradeCountry', NULL, NULL, NULL, NULL, '编辑国家', 'button', 0, 0, 2, 1, 1,  0, '2024-05-26 22:27:00', '2024-05-26 22:27:00'),
(60, 56, '删除国家', 'Country:DeleteCountry', NULL, NULL, NULL, NULL, '删除国家', 'button', 0, 0, 3, 1, 1,  0, '2024-05-26 22:27:39', '2024-05-26 22:27:39'),
(61, 57, '新增客服', 'CoustemServer:AddCoustemServer', NULL, NULL, NULL, NULL, '新增客服', 'button', 0, 0, 1, 1, 1,  0, '2024-05-26 22:28:29', '2024-05-26 22:28:29'),
(62, 57, '编辑客服', 'CoustemServer:UpgradeCoustemServer', NULL, NULL, NULL, NULL, '编辑客服', 'button', 0, 0, 2, 1, 1, 0,  '2024-05-26 22:29:13', '2024-05-26 22:29:13'),
(63, 57, '删除客服', 'CoustemServer:DeleteCoustemServer', NULL, NULL, NULL, NULL, '删除客服', 'button', 0, 0, 3, 1, 1, 0, '2024-05-26 22:29:48', '2024-05-26 22:29:48'),
(64, 0, '数据库管理', '', '/DataBaseManger', 'LAYOUT', 'ant-design:console-sql-outlined', NULL, '数据库管理', 'catalogue', 1, 1, 9, 1, 1, 0, '2024-06-29 12:20:31', '2024-06-29 12:20:31'),
(65, 64, '数据库表', 'SystemDatabase:getDataBaseList', '/DataBaseList', '/DataBaseManger/DataBaseList/DataBaseList', NULL, NULL, '数据库表', 'menu', 1, 1, 1, 1, 1,  0, '2024-06-29 12:21:42', '2024-06-29 12:21:42'),
(66, 65, '备份', 'SystemDatabase:BackUpDataBase', NULL, NULL, NULL, NULL, '备份', 'button', 0, 0, 1, 1, 1, 0,  '2024-06-29 19:47:14', '2024-06-29 19:47:14'),
(67, 65, '优化表', 'SystemDatabase:UpdateOptimize', NULL, NULL, NULL, NULL, '优化表', 'button', 0, 0, 1, 1, 1,  0, '2024-06-29 19:47:52', '2024-06-29 19:47:52'),
(68, 65, '修复表', 'SystemDatabase:RepairDataBase', NULL, NULL, NULL, NULL, '修复表', 'button', 0, 0, 1, 1, 1, 0,  '2024-06-29 19:48:15', '2024-06-29 19:48:15'),
(69, 65, '数据表结构', 'SystemDatabase:getDataBaseInfo', NULL, NULL, NULL, NULL, '数据表结构', 'button', 0, 0, 1, 1, 1, 0,  '2024-06-29 19:49:36', '2024-06-29 19:49:36'),
(70, 5, '生成谷歌秘钥', 'AdminMember:CreateGoogleKey', NULL, NULL, NULL, NULL, '生成谷歌秘钥', 'button', 0, 0, 4, 1, 1, 0,  '2024-08-20 00:29:47', '2024-08-20 00:29:47');

/** 国家列表 **/

DROP TABLE IF EXISTS order_country;
CREATE TABLE `order_country` (
  `id`                    INT (11) PRIMARY KEY AUTO_INCREMENT COMMENT '主键ID',
  `country_name`          VARCHAR (100) NOT NULL COMMENT '中文名称',
  `country_en`            VARCHAR (100) NOT NULL COMMENT '英文名称',
  `country_id`            VARCHAR (100) NOT NULL COMMENT '国家代码',
  `country_code`          VARCHAR (100) NOT NULL COMMENT '国家区号代码',
  `country_status`        TINYINT (1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '国家状态: 1启用, 0禁用',
  `country_sort`          INT (11) UNSIGNED NOT NULL COMMENT '排序编号',
  `admin_id`              INT (11) UNSIGNED NOT NULL COMMENT '修改用户ID',
  `release_time`          DATETIME DEFAULT NULL COMMENT '修改时间 格式: YYYY-MM-DD HH:ii:ss',
  `create_time`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间 格式: YYYY-MM-DD HH:ii:ss'
) ENGINE = InnoDB DEFAULT CHARSET = UTF8 COLLATE = utf8_general_ci AUTO_INCREMENT 1 COMMENT = '国家列表';

INSERT INTO `order_country` VALUES (1, '阿富汗', 'Afghanistan', 'AF', '+93', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(2, '阿尔巴尼亚', 'Albania', 'AL', '+355', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(3, '阿尔及利亚', 'Algeria', 'DZ', '+213', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(4, '安道尔', 'Andorra', 'AD', '+376', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(5, '安哥拉', 'Angola', 'AO', '+244', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(6, '安圭拉', 'Anguilla', 'AI', '+1264', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(7, '安提瓜和巴布达', 'Antigua & Barbuda', 'AG', '+268', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(8, '阿根廷', 'Argentina', 'AR', '+54', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(9, '亚美尼亚', 'Armenia', 'AM', '+374', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(10, '阿鲁巴', 'Aruba', 'AW', '+297', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(11, '澳大利亚', 'Australia', 'AU', '+61', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(12, '奥地利', 'Austria', 'AT', '+43', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(13, '阿塞拜疆', 'Azerbaijan', 'AZ', '+994', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(14, '巴林', 'Bahrain', 'BH', '+973', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(15, '孟加拉国国', 'Bangladesh', 'BD', '+880', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(16, '巴巴多斯', 'Barbados', 'BB', '+268', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(17, '白俄罗斯', 'Belarus', 'BY', '+375', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(18, '比利时', 'Belgium', 'BE', '+32', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(19, '伯利兹', 'Belize', 'BZ', '+501', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(20, '贝宁', 'Benin', 'BJ', '+229', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(21, '百慕大', 'Bermuda', 'BM', '+1441', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(22, '不丹', 'Bhutan', 'BT', '+975', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(23, '玻利维亚', 'Bolivia', 'BO', '+591', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(24, '波斯尼亚和黑塞哥维那', 'Bosnia & Herzegovina', 'BA', '+387', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(25, '博茨瓦纳', 'Botswana', 'BW', '+267', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(26, '巴西', 'Brazil', 'BR', '+55', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(27, '英属印度洋领地', 'British Indian Ocean Territory', 'IO', '+246', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(28, '英属维尔京群岛', 'British Virgin Islands', 'VG', '+1284', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(29, '文莱', 'Brunei', 'BN', '+673', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(30, '保加利亚', 'Bulgaria', 'BG', '+359', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(31, '布基纳法索', 'Burkina', 'BF', '+226', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(32, '布隆迪', 'Burundi', 'BI', '+257', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(33, '柬埔寨', 'Cambodia', 'KH', '+855', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(34, '喀麦隆', 'Cameroon', 'CM', '+237', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(35, '加拿大', 'Canada', 'CA', '+1', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(36, '佛得角', 'Cape Verde', 'CV', '+238', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(37, '开曼群岛', 'Cayman Islands', 'KY', '+1-345', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(38, '乍得', 'Chad', 'TD', '+235', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(39, '智利', 'chile', 'CL', '+56', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(40, '中国', 'China', 'CN', '+86', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(41, '香港特别行政区', 'China(Hong Kong)', 'HK', '+852', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(42, '澳门特别行政区', 'China(Macao)', 'MO', '+853', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(43, '台湾省', 'China(Taiwan)', 'TW', '+886', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(44, '哥伦比亚', 'Colombia', 'CO', '+57', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(45, '哥斯达黎加', 'Costa Rica', 'CR', '+506', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(46, '科特迪瓦', "Cote d\'Ivoire", 'CI', '+225', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(47, '克罗地亚', 'Croatia', 'HR', '+385', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(48, '古巴', 'Cuba', 'CU', '+53', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(49, '库拉索', 'Curaçao', 'CW', '+50', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(50, '塞浦路斯', 'Cyprus', 'CY', '+357', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(51, '捷克', 'Czech Republic', 'CZ', '+420', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(52, '刚果（金）', 'Democratic Republic of the Congo', 'CD', '+243', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(53, '丹麦', 'Denmark', 'DK', '+45', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(54, '吉布提', 'Djibouti', 'DJ', '+253', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(55, '多米尼加共和国', 'Dominica', 'DM', '+1767', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(56, '厄瓜多尔', 'Ecuador', 'EC', '+593', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(57, '埃及', 'Egypt', 'EG', '+20', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(58, '萨尔瓦多', 'El Salvador', 'SV', '+503', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(59, '赤道几内亚', 'Equatorial Guinea', 'GQ', '+240', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(60, '厄立特里亚', 'Eritrea', 'ER', '+291', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(61, '爱沙尼亚', 'Estonia', 'EE', '+372', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(62, '埃塞俄比亚', 'Ethiopia', 'ET', '+251', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(63, '马尔维纳斯群岛（福克兰）', 'Falkland Islands', 'FK', '+500', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(64, '法罗群岛', 'Faroe Islands', 'FO', '+298', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(65, '密克罗尼西亚联邦', 'Federated States of Micronesia', 'FM', '+691', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(66, '斐济群岛', 'Fiji', 'FJ', '+679', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(67, '芬兰', 'Finland', 'FI', '+358', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(68, '法国', 'France', 'FR', '+33', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(69, '法属圭亚那', 'French Guiana', 'GF', '+594', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(70, '法属波利尼西亚', 'French polynesia', 'PF', '+689', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(71, '加蓬', 'Gabon', 'GA', '+241', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(72, '冈比亚', 'Gambia', 'GM', '+220', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(73, '格鲁吉亚', 'Georgia', 'GE', '+995', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(74, '德国', 'Germany', 'DE', '+49', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(75, '加纳', 'Ghana', 'GH', '+233', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(76, '直布罗陀', 'Gibraltar', 'GI', '+350', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(77, '英国', 'Great Britain (United Kingdom; England)', 'GB', '+44', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(78, '希腊', 'Greece', 'GR', '+30', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(79, '格陵兰', 'Greenland', 'GL', '+45', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(80, '格林纳达', 'Grenada', 'GD', '+1473', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(81, '瓜德罗普', 'Guadeloupe', 'GP', '+590', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(82, '关岛', 'Guam', 'GU', '+1-671', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(83, '危地马拉', 'Guatemala', 'GT', '+502', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(84, '几内亚', 'Guinea', 'GN', '+224', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(85, '几内亚比绍', 'Guinea-Bissau', 'GW', '+245', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(86, '圭亚那', 'Guyana', 'GY', '+592', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(87, '海地', 'Haiti', 'HT', '+509', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(88, '洪都拉斯', 'Honduras', 'HN', '+504', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(89, '匈牙利', 'Hungary', 'HU', '+36', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(90, '冰岛', 'Iceland', 'IS', '+354', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(91, '印度', 'India', 'IN', '+91', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(92, '印度尼西亚', 'Indonesia', 'ID', '+62', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(93, '伊朗', 'Iran', 'IR', '+98', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(94, '伊拉克', 'Iraq', 'IQ', '+964', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(95, '爱尔兰', 'Ireland', 'IE', '+353', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(96, '马恩岛', 'Isle of Man', 'IM', '+44', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(97, '以色列', 'Israel', 'IL', '+972', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(98, '意大利', 'Italy', 'IT', '+39', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(99, '牙买加', 'Jamaica', 'JM', '+876', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(100, '日本', 'Japan', 'JP', '+81', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(101, '泽西岛', 'Jersey', 'JE', '+44', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(102, '约旦', 'Jordan', 'JO', '+962', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(103, '哈萨克斯坦', 'Kazakhstan', 'KZ', '+7', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(104, '肯尼亚', 'Kenya', 'KE', '+254', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(105, '基里巴斯', 'Kiribati', 'KI', '+686', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(106, '科索沃', 'Kosovo', 'XK', '+383', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(107, '科威特', 'Kuwait', 'KW', '+965', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(108, '吉尔吉斯斯坦', 'Kyrgyzstan', 'KG', '+996', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(109, '老挝', 'Laos', 'LA', '+856', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(110, '拉脱维亚', 'Latvia', 'LV', '+371', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(111, '黎巴嫩', 'Lebanon', 'LB', '+961', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(112, '莱索托', 'Lesotho', 'LS', '+266', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(113, '利比里亚', 'Liberia', 'LR', '+231', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(114, '利比亚', 'Libya', 'LY', '+218', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(115, '列支敦士登', 'Liechtenstein', 'LI', '+423', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(116, '立陶宛', 'Lithuania', 'LT', '+370', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(117, '卢森堡', 'Luxembourg', 'LU', '+352', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(118, '马达加斯加', 'Madagascar', 'MG', '+261', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(119, '马拉维', 'Malawi', 'MW', '+265', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(120, '马来西亚', 'Malaysia', 'MY', '+60', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(121, '马尔代夫', 'Maldives', 'MV', '+960', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(122, '马里', 'Mali', 'ML', '+223', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(123, '马耳他', 'Malta', 'MT', '+356', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(124, '马绍尔群岛', 'Marshall islands', 'MH', '+692', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(125, '马提尼克', 'Martinique', 'MQ', '+596', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(126, '毛里塔尼亚', 'Mauritania', 'MR', '+222', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(127, '毛里求斯', 'Mauritius', 'MU', '+230', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(128, '马约特', 'Mayotte', 'YT', '+262', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(129, '墨西哥', 'Mexico', 'MX', '+52', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(130, '摩尔多瓦', 'Moldova', 'MD', '+373', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(131, '摩纳哥', 'Monaco', 'MC', '+377', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(132, '蒙古国', 'Mongolia', 'MN', '+976', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(133, '黑山', 'Montenegro', 'ME', '+382', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(134, '摩洛哥', 'Morocco', 'MA', '+212', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(135, '莫桑比克', 'Mozambique', 'MZ', '+258', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(136, '缅甸', 'Myanmar (Burma)', 'MM', '+95', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(137, '纳米比亚', 'Namibia', 'NA', '+264', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(138, '瑙鲁', 'Nauru', 'NR', '+674', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(139, '尼泊尔', 'Nepal', 'NP', '+977', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(140, '荷兰', 'Netherlands', 'NL', '+31', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(141, '新喀里多尼亚', 'New Caledonia', 'NC', '+687', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(142, '新西兰', 'New Zealand', 'NZ', '+64', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(143, '尼加拉瓜', 'Nicaragua', 'NI', '+505', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(144, '尼日尔', 'Niger', 'NE', '+227', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(145, '尼日利亚', 'Nigeria', 'NG', '+234', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(146, '纽埃', 'Niue', 'NU', '+683', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(147, '朝鲜', 'North Korea', 'KP', '+850', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(148, '北马里亚纳群岛', 'Northern Mariana Islands', 'MP', '+1-670', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(149, '挪威', 'Norway', 'NO', '+47', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(150, '阿曼', 'Oman', 'OM', '+968', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(151, '巴基斯坦', 'Pakistan', 'PK', '+92', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(152, '帕劳', 'Palau', 'PW', '+680', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(153, '巴勒斯坦', 'Palestine', 'PS', '+970', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(154, '巴拿马', 'Panama', 'PA', '+507', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(155, '巴布亚新几内亚', 'Papua New Guinea', 'PG', '+675', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(156, '巴拉圭', 'Paraguay', 'PY', '+595', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(157, '秘鲁', 'Peru', 'PE', '+51', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(158, '波兰', 'Poland', 'PL', '+48', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(159, '葡萄牙', 'Portugal', 'PT', '+351', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(160, '波多黎各', 'Puerto Rico', 'PR', '+1-787,+1-939', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(161, '卡塔尔', 'Qatar', 'QA', '+974', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(162, '刚果（布）', 'Republic of the Congo', 'CG', '+242', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(163, '留尼汪', 'Réunion', 'RE', '+262', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(164, '罗马尼亚', 'Romania', 'RO', '+40', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(165, '俄罗斯', 'Russian Federation', 'RU', '+7', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(166, '卢旺达', 'Rwanda', 'RW', '+250', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(167, '萨摩亚', 'Samoa', 'WS', '+685', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(168, '圣马力诺', 'San Marino', 'SM', '+378', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(169, '圣多美和普林西比', 'Sao Tome & Principe', 'ST', '+239', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(170, '沙特阿拉伯', 'Saudi Arabia', 'SA', '+966', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(171, '塞内加尔', 'Senegal', 'SN', '+221', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(172, '塞尔维亚', 'Serbia', 'RS', '+381', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(173, '塞舌尔', 'Seychelles', 'SC', '+248', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(174, '塞拉利昂', 'Sierra Leone', 'SL', '+232', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(175, '新加坡', 'Singapore', 'SG', '+65', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(176, '斯洛伐克', 'Slovakia', 'SK', '+421', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(177, '斯洛文尼亚', 'Slovenia', 'SI', '+386', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(178, '所罗门群岛', 'Solomon Islands', 'SB', '+677', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(179, '索马里', 'Somalia', 'SO', '+252', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(180, '南非', 'South Africa', 'ZA', '+27', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(181, '韩国', 'South Korea', 'KR', '+82', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(182, '南苏丹', 'South Sudan', 'SS', '+211', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(183, '西班牙', 'Spain', 'ES', '+34', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(184, '斯里兰卡', 'Sri Lanka', 'LK', '+94', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(185, '圣基茨和尼维斯', 'St. Kitts & Nevis', 'KN', '+1-869', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(186, '圣卢西亚', 'St. Lucia', 'LC', '+1-758', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(187, '圣文森特和格林纳丁斯', 'St. Vincent & the Grenadines', 'VC', '+1784', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(188, '苏丹', 'Sudan', 'SD', '+249', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(189, '苏里南', 'Suriname', 'SR', '+597', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(190, '斯威士兰', 'Swaziland', 'SZ', '+268', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(191, '瑞典', 'Sweden', 'SE', '+46', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(192, '瑞士', 'Switzerland', 'CH', '+41', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(193, '叙利亚', 'Syria', 'SY', '+963', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(194, '塔吉克斯坦', 'Tajikistan', 'TJ', '+992', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(195, '坦桑尼亚', 'Tanzania', 'TZ', '+255', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(196, '泰国', 'Thailand', 'TH', '+66', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(197, '巴哈马', 'The Bahamas', 'BS', '+1242', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(198, '科摩罗', 'The Comoros', 'KM', '+269', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(199, '菲律宾', 'The Philippines', 'PH', '+63', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(200, '北马其顿共和国', 'The Republic of North Macedonia', 'MKD', '+389', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(201, '东帝汶', 'Timor-Leste (East Timor)', 'TL', '+670', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(202, '多哥', 'Togo', 'TG', '+228', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(203, '托克劳', 'Tokelau', 'TK', '+690', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(204, '汤加', 'Tonga', 'TO', '+676', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(205, '特立尼达和多巴哥', 'Trinidad & Tobago', 'TT', '+1-868', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(206, '突尼斯', 'Tunisia', 'TN', '+216', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(207, '土耳其', 'Turkey', 'TR', '+90', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(208, '土库曼斯坦', 'Turkmenistan', 'TM', '+993', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(209, '特克斯和凯科斯群岛', 'Turks & Caicos Islands', 'TC', '+1-649', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(210, '图瓦卢', 'Tuvalu', 'TV', '+688', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(211, '乌干达', 'Uganda', 'UG', '+256', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(212, '乌克兰', 'Ukraine', 'UA', '+380', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(213, '阿拉伯联合酋长国', 'United Arab Emirates', 'AE', '+971', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(214, '美国', 'United States of America (USA)', 'US', '+1', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(215, '美属维尔京群岛', 'united states virgin island', 'VI', '+1-340', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(216, '乌拉圭', 'Uruguay', 'UY', '+598', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(217, '乌兹别克斯坦', 'Uzbekistan', 'UZ', '+998', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(218, '瓦努阿图', 'Vanuatu', 'VU', '+678', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(219, '梵蒂冈', 'Vatican City (The Holy See)', 'VA', '+379', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(220, '委内瑞拉', 'Venezuela', 'VE', '+58', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(221, '越南', 'Vietnam', 'VN', '+84', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(222, '瓦利斯和富图纳', 'Wallis and Futuna', 'WF', '+681', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(223, '也门', 'Yemen', 'YE', '+967', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(224, '赞比亚', 'Zambia', 'ZM', '+260', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(225, '津巴布韦', 'Zimbabwe', 'ZW', '+263', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(226, '库克群岛', 'Cook Islands', 'CK', '+682', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-06 01:27:43'),
(227, '蒙塞拉特岛', 'Montserrat', 'MS', '+1664', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-07 01:36:13'),
(228, '圣赫勒拿岛', 'Saint Helena', 'SH', '+290', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-07 01:38:06'),
(229, '其他', 'ZZ-Other', 'ZZ', '00', 1, 1, 1, '2024-09-06 01:58:58', '2023-10-07 01:38:06');




-- ----------------------------
-- 商户表
-- ----------------------------
DROP TABLE IF EXISTS `order_merchant`;
CREATE TABLE `order_merchant`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sn` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '编号',
  `nick_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户头像',
  `account` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `google_key` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '谷歌验证码',
  `is_google` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否开启谷歌验证码 0-否 1-是',
  `debug` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否开启沙盒测试 0-否 1-是',
  `pay_pwd` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '支付密码',
  `ip_white` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '[]' COMMENT 'ip白名单',
  `money` decimal(14, 4) UNSIGNED NULL DEFAULT 0.00 COMMENT '用户余额',
  `reserve_money` decimal(14, 4) UNSIGNED NULL DEFAULT 0.00 COMMENT '备付金',
  `frozen_capital` decimal(14, 4) UNSIGNED NULL DEFAULT 0.00 COMMENT '冻结资金',
  `timezone` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '时区',
  `secret_key` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密钥',
  `salt` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL COMMENT '密钥盐',
  `online`     tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '在线状态 1在线 0离线 默认为0',
  `login_num` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '登录次数',
  `login_time` int(10) NULL DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '最后登录ip',
  `location`  varchar(128) DEFAULT NULL COMMENT '登录IP属地',
  `disable` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否禁用：0-否；1-是；',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商户表';

-- ----------------------------
-- 商户表资金记录表
-- ----------------------------
DROP TABLE IF EXISTS `order_merchant_account_log`;
CREATE TABLE `order_merchant_account_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '流水号',
  `mch_id` int(10) NOT NULL COMMENT '商户id',
  `change_object` tinyint(1) NOT NULL DEFAULT 0 COMMENT '变动对象;[1=余额,2=备付金]',
  `change_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '变动类型;[1=充值,2=提现,3=代付,4=代收,5=兑换6=后台操作]',
  `action` tinyint(1) NOT NULL DEFAULT 0 COMMENT '动作 1-增加 2-减少',
  `left_amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '变动前数量',
  `change_amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '变动数量',
  `right_amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '变动后数量',
  `source_sn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '关联单号',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '预留扩展字段',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商户表资金记录表';


-- ----------------------------
-- 商户充值表
-- ----------------------------
DROP TABLE IF EXISTS `order_merchant_recharge_order`;
CREATE TABLE `order_merchant_recharge_order`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `order_sn` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单编号',
  `mch_id` int(10) NOT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '充值类型 1-余额充值 2-备付金充值',
  `pay_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '支付类型 1-USDT 2-银行卡 3-余额支付',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态:0-待审核;1-已审核;2-审核失败',
  `pay_time` int(10) NULL DEFAULT NULL COMMENT '审核时间',
  `order_amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '充值金额',
  `rate` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '汇率',
  `service_charge` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '手续费',
  `reality_amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '到账金额',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '预留扩展字段',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商户充值表';

-- ----------------------------
-- 商户提现表
-- ----------------------------
DROP TABLE IF EXISTS `order_merchant_withdraw_order`;
CREATE TABLE `order_merchant_withdraw_order`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `order_sn` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单编号',
  `mch_id` int(10) NOT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '提现类型 1-余额提现 2-备付金提现',
  `pay_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '到账类型 1-TRC20 ',
  `wallet_address` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '钱包地址',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态:0-待审核;1-已审核;2-审核失败 3-取消提现',
  `pay_time` int(10) NULL DEFAULT NULL COMMENT '审核时间',
  `rate` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '汇率',
  `service_charge` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '手续费',
  `order_amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '提现金额',
  `reality_amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '到账金额',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '预留扩展字段',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商户提现表';

-- ----------------------------
-- 通道表
-- ----------------------------
DROP TABLE IF EXISTS `order_channel`;
CREATE TABLE `order_channel`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '通道名称',
  `desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '描述',
  `in_ratio` decimal(14,4) DEFAULT '0.0000' COMMENT '入金费率',
  `out_ratio` decimal(14,4) DEFAULT '0.0000' COMMENT '出金费率',
  `video_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '视频地址',
  `instr_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '介绍地址',
  `min` decimal(14,4) DEFAULT '0.0000' COMMENT '最小值',
  `max` decimal(14,4) DEFAULT '0.0000' COMMENT '最大值',
  `status` tinyint(1) NOT NULL DEFAULT 0  COMMENT '状态:0-关闭,1开启',
  `type` tinyint(1) NOT NULL DEFAULT 0  COMMENT '',
  `in_per` decimal(14,4) DEFAULT '0.0000' COMMENT '入金每笔扣费',
  `out_per` decimal(14,4) DEFAULT '0.0000' COMMENT '出金每笔扣费',
  `source` tinyint(1) NOT NULL DEFAULT 0  COMMENT '通道来源:0-内部通道,1外接通道',
  `in_status` tinyint(1) NOT NULL DEFAULT 0  COMMENT '代收状态:0-关闭,1开启',
  `out_status` tinyint(1) NOT NULL DEFAULT 0  COMMENT '代付状态:0-关闭,1开启',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '预留扩展字段',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 100 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '通道表';

-- ----------------------------
-- 商户通道绑定
-- ----------------------------
DROP TABLE IF EXISTS `order_merchant_channel`;
CREATE TABLE `order_merchant_channel`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `mch_id` int DEFAULT '0' COMMENT '商户编号',
  `channel_id` int DEFAULT '0' COMMENT '通道id',
  `in_ratio` decimal(14,4) DEFAULT '0.0000' COMMENT '入金费率',
  `out_ratio` decimal(14,4) DEFAULT '0.0000' COMMENT '出金费率',
  `min` decimal(14,4) DEFAULT '0.0000' COMMENT '最小值',
  `max` decimal(14,4) DEFAULT '0.0000' COMMENT '最大值',
  `status` tinyint(1) NOT NULL DEFAULT 0  COMMENT '状态:0-关闭,1开启',
  `in_per` decimal(14,4) DEFAULT '0.0000' COMMENT '入金每笔扣费',
  `out_per` decimal(14,4) DEFAULT '0.0000' COMMENT '出金每笔扣费',
  `source` tinyint(1) NOT NULL DEFAULT 0  COMMENT '通道来源:0-内部通道,1外接通道',
  `in_status` tinyint(1) NOT NULL DEFAULT 0  COMMENT '代收状态:0-关闭,1开启',
  `out_status` tinyint(1) NOT NULL DEFAULT 0  COMMENT '代付状态:0-关闭,1开启',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '预留扩展字段',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商户通道绑定表';


-- ----------------------------
-- 通道银行卡表
-- ----------------------------
DROP TABLE IF EXISTS `order_channel_bank`;
CREATE TABLE `order_channel_bank`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `channel_id` int DEFAULT '0' COMMENT '通道id',
  `pay_type` tinyint(1) NOT NULL DEFAULT 0  COMMENT '类型:0=收,1代付',
  `type` tinyint(1) NOT NULL DEFAULT 0  COMMENT '类型:0银行卡,1钱包',
  `desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '描述',
  `bank_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '银行卡名称/钱包名称',
  `user_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '持卡人名称',
  `bank_num` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '银行卡号码/钱包卡号',
  `iban` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'iban',
  `image` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '图片地址',
  `min` decimal(14,4) DEFAULT '0.0000' COMMENT '最小值',
  `max` decimal(14,4) DEFAULT '0.0000' COMMENT '最大值',
  `status` tinyint(1) NOT NULL DEFAULT 0  COMMENT '状态:0-关闭,1开启',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '预留扩展字段',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `sort` int(5) NULL DEFAULT 0 COMMENT '排序',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 100 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '通道银行卡表';



-- ----------------------------
-- 代收订单
-- ----------------------------
DROP TABLE IF EXISTS `order_payin_order`;
CREATE TABLE `order_payin_order`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `mch_id` int(10) NOT NULL COMMENT '商户id',
  `order_sn` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '平台订单编号',
  `mch_sn` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商户订单编号',
  `channel_id` int DEFAULT '0' COMMENT '通道id',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '订单类型 1-商户订单 2-手工补单 3-沙盒订单',
  `notify_url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL COMMENT '回调地址',
  `notice_count` int(10) NOT NULL DEFAULT 0 COMMENT '通知次数',
  `is_notice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '订单类型 1-通知成功 2-返回失败 3-通知超时',
  `notice_back` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '通知返回结果',
  `amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '订单金额',
  `reality_amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '到账金额',
  `service_charge` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '服务费',
  `pay_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '支付类型 1-银行卡 2-钱包',
  `bank_id` int(10) NOT NULL COMMENT '银行卡/钱包id',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态:0-待付款;1-确认中;2-审核成功,3-审核失败,4-订单超时已关闭,5-订单手动关闭',
  `status_time` int(10) NULL DEFAULT NULL COMMENT '状态变换时间',
  `request_time` int(10) NULL DEFAULT NULL COMMENT '请求时间',
  `expire_time` int(10) NULL DEFAULT NULL COMMENT '交易过期时间',
  `image` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '交易凭证',
  `timezone` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '时区',
  `sign` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL COMMENT '请求签名',
  `sign_back` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL COMMENT '返回签名',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '预留扩展字段',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `order_sn`(`order_sn`) USING BTREE,
  UNIQUE INDEX `mch_sn`(`mch_sn`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '代收订单表';


-- ----------------------------
-- 代付订单
-- ----------------------------
DROP TABLE IF EXISTS `order_payout_order`;
CREATE TABLE `order_payout_order`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `mch_id` int(10) NOT NULL COMMENT '商户id',
  `order_sn` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '平台订单编号',
  `mch_sn` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '商户订单编号',
  `channel_id` int DEFAULT '0' COMMENT '通道id',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '订单类型 1-商户订单 2-手工补单 3-沙盒订单',
  `notify_url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '回调地址',
  `notice_count` int(10) NOT NULL DEFAULT 0 COMMENT '通知次数',
  `is_notice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '订单类型 1-通知成功 2-返回失败 3-通知超时',
  `notice_back` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '通知返回结果',
  `amount` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '订单金额',
  `service_charge` decimal(14, 4)  NULL DEFAULT 0.0000  COMMENT '服务费',
  `pay_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '支付类型 1-银行卡 2-钱包',
  `bank_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '银行卡名称/钱包名称',
  `user_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '持卡人名称',
  `bank_num` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '银行卡号码/钱包卡号',
  `iban` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'iban',
  `bank_image` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '钱包图片地址',
  `user_email` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '邮箱',
  `user_phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '电话号码',
  `user_address` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '地址',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态:0-待付款;1-确认中;2-审核成功,3-审核失败,4-订单超时已关闭,5-订单手动关闭',
  `status_time` int(10) NULL DEFAULT NULL COMMENT '状态变换时间',
  `request_time` int(10) NULL DEFAULT NULL COMMENT '请求时间',
  `expire_time` int(10) NULL DEFAULT NULL COMMENT '交易过期时间',
  `image` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL COMMENT '交易凭证',
  `timezone` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL COMMENT '时区',
  `sign` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL COMMENT '请求签名',
  `sign_back` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL COMMENT '返回签名',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '预留扩展字段',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `order_sn`(`order_sn`) USING BTREE,
  UNIQUE INDEX `mch_sn`(`mch_sn`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '代付订单表';



-- ----------------------------
-- 机器人群列表
-- ----------------------------
DROP TABLE IF EXISTS `order_bot_group`;
CREATE TABLE `order_bot_group`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `mch_id` int(10) NOT NULL COMMENT '商户id',
  `chat_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '飞机群id',
  `channel_id` int DEFAULT '0' COMMENT '通道id',
  `scene_id` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '场景,1-代付通知,2-代收通知',
  `recipient` tinyint(1) NULL DEFAULT 0 COMMENT '通知接收对象类型;1-全部;2-商家;',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '预留扩展字段',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '机器人群列表';



-- ----------------------------
-- 多语言
-- ----------------------------
DROP TABLE IF EXISTS `order_language`;
CREATE TABLE `order_language`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `mch_id` int(10) NOT NULL COMMENT '商户id',
  `channel_id` int DEFAULT '0' COMMENT '通道id',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  DEFAULT '收银台' COMMENT '标题',
  `logo` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'logo',
  `desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '描述',
  `next` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '下一步'  NULL COMMENT '下一步',
  `previous` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '上一步'  NULL COMMENT '上一步',
  `accomplish` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '完成'  NULL COMMENT '完成',
  `bank` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '选择银行'  NULL COMMENT '选择银行',
  `bankInfo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '付款信息'  NULL COMMENT '付款信息',
  `credit` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '付款凭证'  NULL COMMENT '付款凭证',
  `await` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '等待确认'  NULL COMMENT '等待确认',
  `bankName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '银行名称'  NULL COMMENT '银行名称',
  `bankNum` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '银行卡号'  NULL COMMENT '银行卡号',
  `bankIban` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'IBAN'  NULL COMMENT 'IBAN',
  `bankUser` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '持卡人姓名'  NULL COMMENT '持卡人姓名',
  `price` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '金额'  NULL COMMENT '金额',
  `sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '订单号'  NULL COMMENT '订单号',
  `create` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '下单时间'  NULL COMMENT '下单时间',
  `end` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '过期时间'  NULL COMMENT '过期时间',
  `scpzts` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '请上传付款凭证'  NULL COMMENT '请上传付款凭证',
  `fzwzts` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '复制成功'  NULL COMMENT '复制成功',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '多语言';

-- ----------------------------
-- 机器人发送记录
-- ----------------------------
DROP TABLE IF EXISTS `order_bot_record`;
CREATE TABLE `order_bot_record`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `mch_id` int(10) NOT NULL COMMENT '商户id',
  `chat_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '发送id',
  `request_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '发送内容',
  `back_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '返回内容',
  `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'ip',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '机器人发送记录';


-- ----------------------------
-- 通知记录表
-- ----------------------------
DROP TABLE IF EXISTS `order_notice_record`;
CREATE TABLE `order_notice_record`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `scene_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '场景',
  `read` tinyint(1) NULL DEFAULT 0 COMMENT '已读状态;0-未读,1-已读',
  `recipient` tinyint(1) NULL DEFAULT 0 COMMENT '通知接收对象类型;1-全部;2-商家;3-管理员;',
  `send_type` tinyint(1) NULL DEFAULT 0 COMMENT '通知发送类型 1-系统通知 2-订单通知 3-钱包通知',
  `notice_type` tinyint(1) NULL DEFAULT NULL COMMENT '通知类型 1-业务通知 2-验证码',
  `extra` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '' COMMENT '其他',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '通知记录表';