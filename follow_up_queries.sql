ALTER TABLE People
ADD visitor tinyint(1) NOT NULL DEFAULT 0 AFTER active,
ADD baptized tinyint(1) NOT NULL DEFAULT 0 AFTER visitor,
ADD saved tinyint(1) NOT NULL DEFAULT 0 AFTER baptized,
ADD member tinyint(1) NOT NULL DEFAULT 0 AFTER saved,
ADD street1 varchar(100) AFTER member,
ADD street2 varchar(100) AFTER street1,
ADD city varchar(100) AFTER street2,
ADD state varchar(30) AFTER city,
ADD zip varchar(5) AFTER state,
ADD email varchar(100) AFTER zip,
ADD primary_phone varchar(15) AFTER email,
ADD secondary_phone varchar(15) AFTER primary_phone;


CREATE TABLE FollowUps (
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  follow_up_to_person_id int(11) NOT NULL,
  type tinyint(1) NOT NULL DEFAULT '2',
  follow_up_date datetime,
  comments varchar(5000),
  FOREIGN KEY (follow_up_to_person_id) REFERENCES People(id)
);

CREATE TABLE FollowUpVisitors (
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  follow_up_id int(11) NOT NULL,
  person_id int(11) NOT NULL,
  FOREIGN KEY (follow_up_id) REFERENCES FollowUps(id),
  FOREIGN KEY (person_id) REFERENCES People(id)
);


