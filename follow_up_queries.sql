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
ADD created_by int(11) AFTER creation_dt
