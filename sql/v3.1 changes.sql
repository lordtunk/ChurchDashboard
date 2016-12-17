ALTER TABLE `FollowUps` ADD `info_growth` TINYINT(1) NOT NULL DEFAULT '0' AFTER `info_visit`;
ALTER TABLE `People` ADD `info_growth` TINYINT(1) NOT NULL DEFAULT '0' AFTER `info_visit`;