insert into PersonCampusAssociations (person_id, campus)
select id as person_id, 1 as campus
from People