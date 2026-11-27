BEGIN;
DELETE FROM academic_groups WHERE NOT EXISTS (
    SELECT * FROM students WHERE students.group_name = academic_groups.name
);
COMMIT;
