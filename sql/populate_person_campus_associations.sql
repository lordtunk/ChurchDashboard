CREATE TABLE IF NOT EXISTS `PersonCampusAssociations` (
  `person_id` int(11) NOT NULL,
  `campus` int(11) NOT NULL,
  PRIMARY KEY (`person_id`,`campus`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

insert into PersonCampusAssociations (person_id, campus)
select id as person_id, 1 as campus
from People