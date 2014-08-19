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
  attendance_dt DESC LIMIT 0 , 2

// Another query to get the dates for the last two sunday services
SELECT DISTINCT
  attendance_dt
FROM
  Attendance
WHERE
  attendance_dt IN
      (SELECT DISTINCT
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
              AND a1.attendance_dt <= a2.attendance_dt) IN (1,2))



// Get all people who missed the last two sunday services
SELECT DISTINCT
  p.id,
  p.first_name,
  p.last_name,
  p.description
FROM
  People p
  inner join Attendance a on p.id=a.attended_by
WHERE
  a.attendance_dt IN
    (SELECT DISTINCT
        attendance_dt
    FROM
        Attendance
    WHERE
        attendance_dt IN
            (SELECT DISTINCT
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
                    AND a1.attendance_dt <= a2.attendance_dt) IN (1,2)))
  AND a.first=0
  AND a.second=0
  AND p.adult=1