ALTER TABLE People
ADD visitor tinyint(1) AFTER active NOT NULL DEFAULT 0,
ADD baptized tinyint(1) AFTER visitor NOT NULL DEFAULT 0,
ADD saved tinyint(1) AFTER baptized NOT NULL DEFAULT 0,
ADD member tinyint(1) AFTER saved NOT NULL DEFAULT 0,
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

select
  f.type follow_up_type,
  f.follow_up_date,
  f.comments follow_up_comments,
  v.follow_up_id,
  v.person_id
from
  FollowUps f 
  inner join FollowUpVisitors v on f.id=v.follow_up_id 
where
  f.follow_up_to_person_id=:id


SELECT
  p.id,
  p.first_name,
  p.last_name,
  p.description,
  p.active,
  p.adult,
  p.saved,
  p.baptized,
  p.member,
  p.visitor,
  p.street1,
  p.street2,
  p.city,
  p.state,
  p.zip,
  p.email,
  p.primary_phone,
  p.secondary_phone,
  f.type follow_up_type,
  f.follow_up_date,
  f.comments follow_up_comments,
  v.follow_up_id,
  v.person_id,
  fp.first_name follow_up_first_name,
  fp.last_name follow_up_last_name,
  fp.description follow_up_description
FROM
  People p
  inner join FollowUps f on f.follow_up_to_person_id=p.id
  inner join FollowUpVisitors v on f.id=v.follow_up_id
  inner join People fp on fp.id=v.person_id
WHERE
  p.id=:id