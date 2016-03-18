-- phpMyAdmin SQL Dump
-- version 4.4.13.1deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Час створення: Бер 18 2016 р., 19:49
-- Версія сервера: 5.6.28-0ubuntu0.15.10.1
-- Версія PHP: 5.6.11-1ubuntu3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База даних: `owncloud`
--

-- --------------------------------------------------------

--
-- Структура таблиці `oc_activity`
--

CREATE TABLE IF NOT EXISTS `oc_activity` (
  `activity_id` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '0',
  `type` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `user` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `affecteduser` varchar(64) COLLATE utf8_bin NOT NULL,
  `app` varchar(255) COLLATE utf8_bin NOT NULL,
  `subject` varchar(255) COLLATE utf8_bin NOT NULL,
  `subjectparams` varchar(4000) COLLATE utf8_bin NOT NULL,
  `message` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `messageparams` varchar(4000) COLLATE utf8_bin DEFAULT NULL,
  `file` varchar(4000) COLLATE utf8_bin DEFAULT NULL,
  `link` varchar(4000) COLLATE utf8_bin DEFAULT NULL,
  `object_type` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `object_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_activity_mq`
--

CREATE TABLE IF NOT EXISTS `oc_activity_mq` (
  `mail_id` int(11) NOT NULL,
  `amq_timestamp` int(11) NOT NULL DEFAULT '0',
  `amq_latest_send` int(11) NOT NULL DEFAULT '0',
  `amq_type` varchar(255) COLLATE utf8_bin NOT NULL,
  `amq_affecteduser` varchar(64) COLLATE utf8_bin NOT NULL,
  `amq_appid` varchar(255) COLLATE utf8_bin NOT NULL,
  `amq_subject` varchar(255) COLLATE utf8_bin NOT NULL,
  `amq_subjectparams` varchar(4000) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_appconfig`
--

CREATE TABLE IF NOT EXISTS `oc_appconfig` (
  `appid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `configkey` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `configvalue` longtext COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_collab_links`
--

CREATE TABLE IF NOT EXISTS `oc_collab_links` (
  `id` int(11) NOT NULL,
  `source` int(11) NOT NULL,
  `target` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `deleted` int(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_collab_messages`
--

CREATE TABLE IF NOT EXISTS `oc_collab_messages` (
  `id` int(11) NOT NULL,
  `rid` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text,
  `attachements` tinytext,
  `author` varchar(64) NOT NULL,
  `subscribers` tinytext NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_collab_project`
--

CREATE TABLE IF NOT EXISTS `oc_collab_project` (
  `name` varchar(255) NOT NULL,
  `create_uid` varchar(255) NOT NULL,
  `show_today_line` int(1) NOT NULL DEFAULT '1',
  `show_task_name` int(1) NOT NULL DEFAULT '1',
  `show_user_color` int(1) NOT NULL DEFAULT '0',
  `critical_path` int(1) NOT NULL DEFAULT '0',
  `scale_type` varchar(16) NOT NULL DEFAULT 'week',
  `scale_fit` int(1) DEFAULT '0',
  `is_share` int(1) DEFAULT '0',
  `share_link` varchar(255) DEFAULT NULL,
  `share_is_protected` int(1) NOT NULL DEFAULT '0',
  `share_password` varchar(255) DEFAULT NULL,
  `share_email_recipient` varchar(255) DEFAULT NULL,
  `share_is_expire` int(1) DEFAULT '0',
  `share_expire_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_collab_resources`
--

CREATE TABLE IF NOT EXISTS `oc_collab_resources` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_collab_tasks`
--

CREATE TABLE IF NOT EXISTS `oc_collab_tasks` (
  `id` int(11) NOT NULL,
  `is_project` int(11) NOT NULL DEFAULT '0',
  `type` varchar(16) NOT NULL DEFAULT 'task',
  `text` varchar(255) NOT NULL,
  `users` varchar(2048) DEFAULT NULL,
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `duration` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `progress` float NOT NULL DEFAULT '0',
  `sortorder` double NOT NULL DEFAULT '0',
  `parent` int(11) NOT NULL DEFAULT '0',
  `deadline` timestamp NULL DEFAULT NULL,
  `planned_start` timestamp NULL DEFAULT NULL,
  `planned_end` timestamp NULL DEFAULT NULL,
  `open` int(1) NOT NULL DEFAULT '1',
  `deleted` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_collab_users`
--

CREATE TABLE IF NOT EXISTS `oc_collab_users` (
  `user_id` varchar(64) NOT NULL,
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `group_id` varchar(64) DEFAULT NULL,
  `email` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_collab_user_message`
--

CREATE TABLE IF NOT EXISTS `oc_collab_user_message` (
  `id` int(4) NOT NULL,
  `uid` varchar(64) DEFAULT NULL,
  `mid` int(11) DEFAULT NULL,
  `status` tinytext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_filecache`
--

CREATE TABLE IF NOT EXISTS `oc_filecache` (
  `fileid` int(11) NOT NULL,
  `storage` int(11) NOT NULL DEFAULT '0',
  `path` varchar(4000) COLLATE utf8_bin DEFAULT NULL,
  `path_hash` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `parent` int(11) NOT NULL DEFAULT '0',
  `name` varchar(250) COLLATE utf8_bin DEFAULT NULL,
  `mimetype` int(11) NOT NULL DEFAULT '0',
  `mimepart` int(11) NOT NULL DEFAULT '0',
  `size` bigint(20) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `storage_mtime` int(11) NOT NULL DEFAULT '0',
  `encrypted` int(11) NOT NULL DEFAULT '0',
  `unencrypted_size` bigint(20) NOT NULL DEFAULT '0',
  `etag` varchar(40) COLLATE utf8_bin DEFAULT NULL,
  `permissions` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_files_trash`
--

CREATE TABLE IF NOT EXISTS `oc_files_trash` (
  `auto_id` int(11) NOT NULL,
  `id` varchar(250) COLLATE utf8_bin NOT NULL DEFAULT '',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `timestamp` varchar(12) COLLATE utf8_bin NOT NULL DEFAULT '',
  `location` varchar(512) COLLATE utf8_bin NOT NULL DEFAULT '',
  `type` varchar(4) COLLATE utf8_bin DEFAULT NULL,
  `mime` varchar(255) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_file_locks`
--

CREATE TABLE IF NOT EXISTS `oc_file_locks` (
  `id` int(10) unsigned NOT NULL,
  `lock` int(11) NOT NULL DEFAULT '0',
  `key` varchar(64) COLLATE utf8_bin NOT NULL,
  `ttl` int(11) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_file_map`
--

CREATE TABLE IF NOT EXISTS `oc_file_map` (
  `logic_path` varchar(512) COLLATE utf8_bin NOT NULL DEFAULT '',
  `logic_path_hash` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `physic_path` varchar(512) COLLATE utf8_bin NOT NULL DEFAULT '',
  `physic_path_hash` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_groups`
--

CREATE TABLE IF NOT EXISTS `oc_groups` (
  `gid` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_group_admin`
--

CREATE TABLE IF NOT EXISTS `oc_group_admin` (
  `gid` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `uid` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_group_user`
--

CREATE TABLE IF NOT EXISTS `oc_group_user` (
  `gid` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `uid` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_jobs`
--

CREATE TABLE IF NOT EXISTS `oc_jobs` (
  `id` int(10) unsigned NOT NULL,
  `class` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `argument` varchar(4000) COLLATE utf8_bin NOT NULL DEFAULT '',
  `last_run` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_mimetypes`
--

CREATE TABLE IF NOT EXISTS `oc_mimetypes` (
  `id` int(11) NOT NULL,
  `mimetype` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_notifications`
--

CREATE TABLE IF NOT EXISTS `oc_notifications` (
  `notification_id` int(11) NOT NULL,
  `app` varchar(32) COLLATE utf8_bin NOT NULL,
  `user` varchar(64) COLLATE utf8_bin NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `object_type` varchar(64) COLLATE utf8_bin NOT NULL,
  `object_id` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(64) COLLATE utf8_bin NOT NULL,
  `subject_parameters` longtext COLLATE utf8_bin,
  `message` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `message_parameters` longtext COLLATE utf8_bin,
  `link` varchar(4000) COLLATE utf8_bin DEFAULT NULL,
  `icon` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `actions` longtext COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_preferences`
--

CREATE TABLE IF NOT EXISTS `oc_preferences` (
  `userid` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `appid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `configkey` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `configvalue` longtext COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_privatedata`
--

CREATE TABLE IF NOT EXISTS `oc_privatedata` (
  `keyid` int(10) unsigned NOT NULL,
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `app` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `key` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_properties`
--

CREATE TABLE IF NOT EXISTS `oc_properties` (
  `id` int(11) NOT NULL,
  `userid` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `propertypath` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `propertyname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `propertyvalue` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_share`
--

CREATE TABLE IF NOT EXISTS `oc_share` (
  `id` int(11) NOT NULL,
  `share_type` smallint(6) NOT NULL DEFAULT '0',
  `share_with` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `uid_owner` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `parent` int(11) DEFAULT NULL,
  `item_type` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `item_source` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `item_target` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `file_source` int(11) DEFAULT NULL,
  `file_target` varchar(512) COLLATE utf8_bin DEFAULT NULL,
  `permissions` smallint(6) NOT NULL DEFAULT '0',
  `stime` bigint(20) NOT NULL DEFAULT '0',
  `accepted` smallint(6) NOT NULL DEFAULT '0',
  `expiration` datetime DEFAULT NULL,
  `token` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `mail_send` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_share_external`
--

CREATE TABLE IF NOT EXISTS `oc_share_external` (
  `id` int(11) NOT NULL,
  `remote` varchar(512) COLLATE utf8_bin NOT NULL COMMENT 'Url of the remove owncloud instance',
  `remote_id` int(11) NOT NULL DEFAULT '-1',
  `share_token` varchar(64) COLLATE utf8_bin NOT NULL COMMENT 'Public share token',
  `password` varchar(64) COLLATE utf8_bin DEFAULT NULL COMMENT 'Optional password for the public share',
  `name` varchar(64) COLLATE utf8_bin NOT NULL COMMENT 'Original name on the remote server',
  `owner` varchar(64) COLLATE utf8_bin NOT NULL COMMENT 'User that owns the public share on the remote server',
  `user` varchar(64) COLLATE utf8_bin NOT NULL COMMENT 'Local user which added the external share',
  `mountpoint` varchar(4000) COLLATE utf8_bin NOT NULL COMMENT 'Full path where the share is mounted',
  `mountpoint_hash` varchar(32) COLLATE utf8_bin NOT NULL COMMENT 'md5 hash of the mountpoint',
  `accepted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_storages`
--

CREATE TABLE IF NOT EXISTS `oc_storages` (
  `id` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `numeric_id` int(11) NOT NULL,
  `available` int(11) NOT NULL DEFAULT '1',
  `last_checked` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_users`
--

CREATE TABLE IF NOT EXISTS `oc_users` (
  `uid` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `displayname` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_vcategory`
--

CREATE TABLE IF NOT EXISTS `oc_vcategory` (
  `id` int(10) unsigned NOT NULL,
  `uid` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `type` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `category` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблиці `oc_vcategory_to_object`
--

CREATE TABLE IF NOT EXISTS `oc_vcategory_to_object` (
  `objid` int(10) unsigned NOT NULL DEFAULT '0',
  `categoryid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Індекси збережених таблиць
--

--
-- Індекси таблиці `oc_activity`
--
ALTER TABLE `oc_activity`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `activity_user_time` (`affecteduser`,`timestamp`),
  ADD KEY `activity_filter_by` (`affecteduser`,`user`,`timestamp`),
  ADD KEY `activity_filter_app` (`affecteduser`,`app`,`timestamp`);

--
-- Індекси таблиці `oc_activity_mq`
--
ALTER TABLE `oc_activity_mq`
  ADD PRIMARY KEY (`mail_id`),
  ADD KEY `amp_user` (`amq_affecteduser`),
  ADD KEY `amp_latest_send_time` (`amq_latest_send`),
  ADD KEY `amp_timestamp_time` (`amq_timestamp`);

--
-- Індекси таблиці `oc_appconfig`
--
ALTER TABLE `oc_appconfig`
  ADD PRIMARY KEY (`appid`,`configkey`),
  ADD KEY `appconfig_config_key_index` (`configkey`),
  ADD KEY `appconfig_appid_key` (`appid`);

--
-- Індекси таблиці `oc_collab_links`
--
ALTER TABLE `oc_collab_links`
  ADD PRIMARY KEY (`id`);

--
-- Індекси таблиці `oc_collab_messages`
--
ALTER TABLE `oc_collab_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `oc_collab_messages_id_uindex` (`id`),
  ADD KEY `oc_collab_messages_id_author_status_index` (`id`,`author`,`status`),
  ADD KEY `oc_collab_messages_rid_index` (`rid`);

--
-- Індекси таблиці `oc_collab_project`
--
ALTER TABLE `oc_collab_project`
  ADD UNIQUE KEY `name` (`name`);

--
-- Індекси таблиці `oc_collab_resources`
--
ALTER TABLE `oc_collab_resources`
  ADD PRIMARY KEY (`id`);

--
-- Індекси таблиці `oc_collab_tasks`
--
ALTER TABLE `oc_collab_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Індекси таблиці `oc_collab_users`
--
ALTER TABLE `oc_collab_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `oc_collab_users_id_uindex` (`id`),
  ADD KEY `oc_collab_users_index` (`user_id`,`project_id`,`group_id`);

--
-- Індекси таблиці `oc_collab_user_message`
--
ALTER TABLE `oc_collab_user_message`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `oc_collab_user_message_id_uindex` (`id`);

--
-- Індекси таблиці `oc_filecache`
--
ALTER TABLE `oc_filecache`
  ADD PRIMARY KEY (`fileid`),
  ADD UNIQUE KEY `fs_storage_path_hash` (`storage`,`path_hash`),
  ADD KEY `fs_parent_name_hash` (`parent`,`name`),
  ADD KEY `fs_storage_mimetype` (`storage`,`mimetype`),
  ADD KEY `fs_storage_mimepart` (`storage`,`mimepart`),
  ADD KEY `fs_storage_size` (`storage`,`size`,`fileid`);

--
-- Індекси таблиці `oc_files_trash`
--
ALTER TABLE `oc_files_trash`
  ADD PRIMARY KEY (`auto_id`),
  ADD KEY `id_index` (`id`),
  ADD KEY `timestamp_index` (`timestamp`),
  ADD KEY `user_index` (`user`);

--
-- Індекси таблиці `oc_file_locks`
--
ALTER TABLE `oc_file_locks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lock_key_index` (`key`),
  ADD KEY `lock_ttl_index` (`ttl`);

--
-- Індекси таблиці `oc_file_map`
--
ALTER TABLE `oc_file_map`
  ADD PRIMARY KEY (`logic_path_hash`),
  ADD UNIQUE KEY `file_map_pp_index` (`physic_path_hash`);

--
-- Індекси таблиці `oc_groups`
--
ALTER TABLE `oc_groups`
  ADD PRIMARY KEY (`gid`);

--
-- Індекси таблиці `oc_group_admin`
--
ALTER TABLE `oc_group_admin`
  ADD PRIMARY KEY (`gid`,`uid`),
  ADD KEY `group_admin_uid` (`uid`);

--
-- Індекси таблиці `oc_group_user`
--
ALTER TABLE `oc_group_user`
  ADD PRIMARY KEY (`gid`,`uid`),
  ADD KEY `gu_uid_index` (`uid`);

--
-- Індекси таблиці `oc_jobs`
--
ALTER TABLE `oc_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_class_index` (`class`);

--
-- Індекси таблиці `oc_mimetypes`
--
ALTER TABLE `oc_mimetypes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mimetype_id_index` (`mimetype`);

--
-- Індекси таблиці `oc_notifications`
--
ALTER TABLE `oc_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `oc_notifications_app` (`app`),
  ADD KEY `oc_notifications_user` (`user`),
  ADD KEY `oc_notifications_timestamp` (`timestamp`),
  ADD KEY `oc_notifications_object` (`object_type`,`object_id`);

--
-- Індекси таблиці `oc_preferences`
--
ALTER TABLE `oc_preferences`
  ADD PRIMARY KEY (`userid`,`appid`,`configkey`);

--
-- Індекси таблиці `oc_privatedata`
--
ALTER TABLE `oc_privatedata`
  ADD PRIMARY KEY (`keyid`);

--
-- Індекси таблиці `oc_properties`
--
ALTER TABLE `oc_properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_index` (`userid`);

--
-- Індекси таблиці `oc_share`
--
ALTER TABLE `oc_share`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_share_type_index` (`item_type`,`share_type`),
  ADD KEY `file_source_index` (`file_source`),
  ADD KEY `token_index` (`token`);

--
-- Індекси таблиці `oc_share_external`
--
ALTER TABLE `oc_share_external`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sh_external_mp` (`user`,`mountpoint_hash`),
  ADD KEY `sh_external_user` (`user`);

--
-- Індекси таблиці `oc_storages`
--
ALTER TABLE `oc_storages`
  ADD PRIMARY KEY (`numeric_id`),
  ADD UNIQUE KEY `storages_id_index` (`id`);

--
-- Індекси таблиці `oc_users`
--
ALTER TABLE `oc_users`
  ADD PRIMARY KEY (`uid`);

--
-- Індекси таблиці `oc_vcategory`
--
ALTER TABLE `oc_vcategory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid_index` (`uid`),
  ADD KEY `type_index` (`type`),
  ADD KEY `category_index` (`category`);

--
-- Індекси таблиці `oc_vcategory_to_object`
--
ALTER TABLE `oc_vcategory_to_object`
  ADD PRIMARY KEY (`categoryid`,`objid`,`type`),
  ADD KEY `vcategory_objectd_index` (`objid`,`type`);

--
-- AUTO_INCREMENT для збережених таблиць
--

--
-- AUTO_INCREMENT для таблиці `oc_activity`
--
ALTER TABLE `oc_activity`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_activity_mq`
--
ALTER TABLE `oc_activity_mq`
  MODIFY `mail_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_collab_links`
--
ALTER TABLE `oc_collab_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_collab_messages`
--
ALTER TABLE `oc_collab_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_collab_resources`
--
ALTER TABLE `oc_collab_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_collab_tasks`
--
ALTER TABLE `oc_collab_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_collab_users`
--
ALTER TABLE `oc_collab_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_collab_user_message`
--
ALTER TABLE `oc_collab_user_message`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_filecache`
--
ALTER TABLE `oc_filecache`
  MODIFY `fileid` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_files_trash`
--
ALTER TABLE `oc_files_trash`
  MODIFY `auto_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_file_locks`
--
ALTER TABLE `oc_file_locks`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_jobs`
--
ALTER TABLE `oc_jobs`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_mimetypes`
--
ALTER TABLE `oc_mimetypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_notifications`
--
ALTER TABLE `oc_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_privatedata`
--
ALTER TABLE `oc_privatedata`
  MODIFY `keyid` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_properties`
--
ALTER TABLE `oc_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_share`
--
ALTER TABLE `oc_share`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_share_external`
--
ALTER TABLE `oc_share_external`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_storages`
--
ALTER TABLE `oc_storages`
  MODIFY `numeric_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблиці `oc_vcategory`
--
ALTER TABLE `oc_vcategory`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
