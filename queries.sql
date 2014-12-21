// Get all people who missed the last service
SELECT
  *
FROM
  Attendance
WHERE
  attendance_dt=
    (SELECT
        MAX(attendance_dt)
      FROM
        Attendance)
  AND first=0
  AND second=0

// Get the dates for the last two services
SELECT DISTINCT
  attendance_dt
FROM
  Attendance
ORDER BY
  attendance_dt DESC
LIMIT 0 , 2

// Get the dates for the last two sunday services
SELECT DISTINCT
  attendance_dt
FROM
  Attendance
WHERE
  DAYOFWEEK(attendance_dt) = 1
ORDER BY
  attendance_dt DESC 
LIMIT 2

// Another query to get the dates for the last two sunday services
SELECT DISTINCT
  attendance_dt
FROM
  Attendance as a1
WHERE
  (SELECT
      COUNT(DISTINCT(attendance_dt))
    FROM
      Attendance as a2
    WHERE
      DAYOFWEEK(a2.attendance_dt) = 1
      AND DAYOFWEEK(a1.attendance_dt) = 1
      AND a1.attendance_dt <= a2.attendance_dt) IN (1,2)



// Get all people who missed the last two sunday services
SELECT DISTINCT
  p.id,
  p.first_name,
  p.last_name,
  p.description
FROM
  People p
  LEFT OUTER JOIN Attendance a ON p.id=a.attended_by AND a.attendance_dt IN
    (SELECT DISTINCT
        attendance_dt
      FROM
        Attendance AS a1
      WHERE
        (SELECT
            COUNT(DISTINCT(attendance_dt))
          FROM
            Attendance AS a2
          WHERE
            DAYOFWEEK(a2.attendance_dt) = 1
            AND DAYOFWEEK(a1.attendance_dt) = 1
            AND a1.attendance_dt <= a2.attendance_dt) IN (1,2))
WHERE
  a.attendance_dt IS NULL
  AND p.adult=1
  AND p.active=1
ORDER BY
  p.last_name IS NOT NULL DESC,
  p.description IS NOT NULL DESC,
  p.last_name,
  p.first_name,
  p.description
  
  
// Another way to get all people who missed the last two sunday services
SELECT DISTINCT
  p.id,
  p.first_name,
  p.last_name,
  p.description
FROM
  People p
  LEFT OUTER JOIN Attendance a ON p.id=a.attended_by
  LEFT OUTER JOIN (SELECT DISTINCT
		  attendance_dt
		FROM
		  Attendance
		WHERE
		  DAYOFWEEK(attendance_dt) = 1
		ORDER BY
		  attendance_dt DESC 
		LIMIT 2) at ON a.attendance_dt = at.attendance_dt
WHERE
  a.attendance_dt IS NULL
  AND p.adult=1
  AND p.active=1
ORDER BY
  p.last_name IS NOT NULL DESC,
  p.description IS NOT NULL DESC,
  p.last_name,
  p.first_name,
  p.description
