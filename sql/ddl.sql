-- public.students definition

-- Drop table

-- DROP TABLE public.students;

CREATE TABLE public.students (
	"name" varchar(64) NULL,
	surname varchar(64) NULL,
	second_surname varchar(64) NULL,
	id int4 NOT NULL,
	CONSTRAINT students_id_not_null NOT NULL id,
	CONSTRAINT students_pkey PRIMARY KEY (id)
);

-- Permissions

ALTER TABLE public.students OWNER TO postgres;
GRANT ALL ON TABLE public.students TO postgres;

-- public.academic_groups definition

-- Drop table

-- DROP TABLE public.academic_groups;

CREATE TABLE public.academic_groups (
	"name" varchar(10) NOT NULL,
	student_ids int4 NULL,
	CONSTRAINT academic_groups_name_not_null NOT NULL name,
	CONSTRAINT academic_groups_pkey PRIMARY KEY (name),
	CONSTRAINT academic_groups_student_ids_fkey FOREIGN KEY (student_ids) REFERENCES public.students(id)
);

-- Permissions

ALTER TABLE public.academic_groups OWNER TO postgres;
GRANT ALL ON TABLE public.academic_groups TO postgres;

-- public.faculties definition

-- Drop table

-- DROP TABLE public.faculties;

CREATE TABLE public.faculties (
	"name" text NULL,
	name_abbr text NULL,
	acgroups varchar(10) NULL,
	CONSTRAINT faculties_acgroups_fkey FOREIGN KEY (acgroups) REFERENCES public.academic_groups("name")
);

-- Permissions

ALTER TABLE public.faculties OWNER TO postgres;
GRANT ALL ON TABLE public.faculties TO postgres;
