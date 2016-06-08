ALTER TABLE People
ADD first_visit datetime AFTER description,
ADD visitor tinyint(1) NOT NULL DEFAULT 0 AFTER active,
ADD baptized tinyint(1) NOT NULL DEFAULT 0 AFTER visitor,
ADD saved tinyint(1) NOT NULL DEFAULT 0 AFTER baptized,
ADD member tinyint(1) NOT NULL DEFAULT 0 AFTER saved,
ADD assigned_agent tinyint(1) NOT NULL DEFAULT 0 AFTER member,
ADD street1 varchar(100) AFTER assigned_agent,
ADD street2 varchar(100) AFTER street1,
ADD city varchar(100) AFTER street2,
ADD state varchar(30) AFTER city,
ADD zip varchar(5) AFTER state,
ADD email varchar(100) AFTER zip,
ADD primary_phone varchar(15) AFTER email,
ADD secondary_phone varchar(15) AFTER primary_phone,
ADD info_next tinyint(1) NOT NULL DEFAULT 0 AFTER secondary_phone,
ADD info_gkids tinyint(1) NOT NULL DEFAULT 0 AFTER info_next,
ADD info_ggroups tinyint(1) NOT NULL DEFAULT 0 AFTER info_gkids,
ADD info_gteams tinyint(1) NOT NULL DEFAULT 0 AFTER info_ggroups,
ADD info_member tinyint(1) NOT NULL DEFAULT 0 AFTER info_gteams,
ADD info_visit tinyint(1) NOT NULL DEFAULT 0 AFTER info_member,
ADD commitment_christ tinyint(1) NOT NULL DEFAULT 0 AFTER info_visit,
ADD recommitment_christ tinyint(1) NOT NULL DEFAULT 0 AFTER commitment_christ,
ADD commitment_tithe tinyint(1) NOT NULL DEFAULT 0 AFTER recommitment_christ,
ADD commitment_ministry tinyint(1) NOT NULL DEFAULT 0 AFTER commitment_tithe,
ADD commitment_baptism tinyint(1) NOT NULL DEFAULT 0 AFTER commitment_ministry;
ALTER TABLE People ADD `starting_point_notified` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE People ADD `attender_status` tinyint(1) NOT NULL DEFAULT '3';
ALTER TABLE People ADD `primary_phone_type` tinyint(1) NOT NULL DEFAULT '2';
ALTER TABLE People ADD `secondary_phone_type` tinyint(1) NOT NULL DEFAULT '2';


CREATE TABLE FollowUps (
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  follow_up_to_person_id int(11) NOT NULL,
  type tinyint(1) NOT NULL DEFAULT '2',
  follow_up_date datetime,
  comments varchar(5000),
  last_modified_dt datetime,
  modified_by int(11),
  creation_dt datetime,
  created_by int(11),
  FOREIGN KEY (follow_up_to_person_id) REFERENCES People(id)
);

CREATE TABLE FollowUpVisitors (
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  follow_up_id int(11) NOT NULL,
  person_id int(11) NOT NULL,
  FOREIGN KEY (follow_up_id) REFERENCES FollowUps(id),
  FOREIGN KEY (person_id) REFERENCES People(id)
);


ALTER TABLE FollowUps
ADD last_modified_dt datetime AFTER comments,
ADD modified_by int(11) AFTER last_modified_dt,
ADD creation_dt datetime AFTER modified_by,
ADD created_by int(11) AFTER creation_dt,
ADD attendance_frequency tinyint(1) DEFAULT NULL


CREATE TABLE IF NOT EXISTS `Services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_dt` date NOT NULL,
  `label` int(10) unsigned NOT NULL,
  `campus` int(10) unsigned NOT NULL DEFAULT '1',
  `adult_visitors` int(10) unsigned DEFAULT NULL,
  `kid_visitors` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `U_SERVICE` (`service_dt`,`label`,`campus`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `gcbdash`.`Services` ADD UNIQUE `U_SERVICE` (`service_dt`, `label`, `campus`)COMMENT '';


CREATE TABLE IF NOT EXISTS `Settings` (
  `starting_point_emails` varchar(500) DEFAULT NULL,
  `campuses` varchar(500) NOT NULL,
  `service_labels` varchar(500) NOT NULL,
  `default_campus` int(10) unsigned NOT NULL,
  `default_first_service_label` int(10) unsigned NOT NULL,
  `default_second_service_label` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `Settings` (`starting_point_emails`, `campuses`, `service_labels`, `default_campus`, `default_first_service_label`, `default_second_service_label`) VALUES
('buck3y3girl13@gmail.com,stevvensa.550@gmail.com', '1|Main', '1|9:00 AM,2|10:30 AM', 1, 1, 2);


INSERT INTO Services (service_dt, campus, label)
(SELECT distinct
	`attendance_dt` as `service_dt`,
    1 as campus,
    1 as label
FROM 
	`attendance` a
ORDER BY 
	attendance_dt
 )
 
INSERT INTO Services (service_dt, campus, label)
(SELECT distinct
	`attendance_dt` as `service_dt`,
    1 as campus,
    2 as label
FROM 
	`attendance` a
ORDER BY 
	attendance_dt
 )
 
ALTER TABLE `attendance_test` ADD `service_id` INT NOT NULL ;
ALTER TABLE attendance_test DROP INDEX attendance_dt_attended_by;

UPDATE `attendance_test` a, services s SET service_id=s.id WHERE a.first=1 and s.service_dt=a.attendance_dt and s.label=1;

-- INSERT INTO attendance_test (attendance_dt, `attended_by`, `first`, `service_id`)
-- (SELECT
	-- `attendance_dt`,
    -- `attended_by`,
 	-- 1 as `first`,
 	-- s.id
-- FROM 
	-- `attendance` a
 	-- INNER JOIN services s on a.`attendance_dt`=s.service_dt
-- WHERE
 	-- a.second=1
    -- AND s.label=2
-- ORDER BY 
	-- attendance_dt
 -- )
 
INSERT INTO attendance_test (`attended_by`, `service_id`)
(SELECT
    `attended_by`,
 	s.id
FROM 
	`attendance` a
 	INNER JOIN Services s on a.`attendance_dt`=s.service_dt
WHERE
 	a.second=1
    AND s.label=2
ORDER BY 
	attendance_dt
 )

--delete FROM `attendance_test` where first=0
 
ALTER TABLE `attendance_test` DROP `attendance_dt`;
ALTER TABLE `attendance_test` DROP `first`;
ALTER TABLE `attendance_test` DROP `second`;
 


ALTER TABLE `attendance_test` ADD UNIQUE (`attended_by`, `service_id`);


