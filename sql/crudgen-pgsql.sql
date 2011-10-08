
-- SQL script to create applications database for phpPgAdmin
-- 
-- To run, type: psql template1 < crudgen-pgsql.sql
-- After run this script, you must add crudgen_admin role to your user 
-- if you wanna give it access to the CRUD Generator plugin

-- Creates phppgadmin database in case it doesn't exists

CREATE DATABASE phppgadmin; 

\connect phppgadmin

-- Creates crudgen's schema to avoid conflicts with others PPA's tables
CREATE SCHEMA crudgen;

CREATE ROLE crudgen_admin WITH NOSUPERUSER NOLOGIN INHERIT;

--Read-only role for reading some application info
CREATE ROLE crudgen_read WITH NOSUPERUSER NOCREATEDB NOCREATEROLE INHERIT LOGIN PASSWORD 'readonly';

CREATE TABLE crudgen.application (
  app_id SERIAL   NOT NULL ,
  app_name VARCHAR(63) UNIQUE NOT NULL,
  descr TEXT NULL,
  date_created DATE NOT NULL DEFAULT now(),
  theme_name VARCHAR(63) NOT NULL,
  app_owner VARCHAR(255) NOT NULL,
  db_user VARCHAR(63) NOT NULL DEFAULT 'crudgen_admin',
  db_pass VARCHAR(63) NOT NULL,
  db_host VARCHAR(255) NOT NULL DEFAULT 'localhost',
  db_name VARCHAR(255) NOT NULL,
  db_schema VARCHAR(255) NOT NULL,
  db_port SMALLINT NOT NULL DEFAULT 5432,
  auth_method VARCHAR(8) NULL,
  auth_table VARCHAR(255)NULL,
  auth_user_col VARCHAR(255)NULL,
  auth_pass_col VARCHAR(255)NULL,
  lang VARCHAR(2) NOT NULL DEFAULT 'en',
PRIMARY KEY(app_id));

CREATE TABLE crudgen.pages (
  page_id SERIAL   NOT NULL ,
  app_id SERIAL   NOT NULL ,
  page_filename VARCHAR(255)   NOT NULL ,
  page_title VARCHAR(255)    ,
  descr TEXT    ,
  date_created DATE  DEFAULT NOW() NOT NULL ,
  operation CHAR   NOT NULL ,
  in_main_menu BOOL  DEFAULT false NOT NULL ,
  completed BOOL  DEFAULT false NOT NULL ,
  page_text TEXT      ,
PRIMARY KEY(page_id)  ,
  FOREIGN KEY(app_id)
    REFERENCES crudgen.application(app_id) ON DELETE CASCADE);


CREATE INDEX FK_application_page ON crudgen.pages (app_id);


CREATE INDEX IFK_has ON crudgen.pages (app_id);


CREATE TABLE crudgen.page_tables (
  page_tables_id SERIAL   NOT NULL ,
  pages_page_id SERIAL   NOT NULL ,
  table_name VARCHAR(255)   NOT NULL   ,
PRIMARY KEY(page_tables_id)  ,
  FOREIGN KEY(pages_page_id)
    REFERENCES crudgen.pages(page_id) ON DELETE CASCADE);


CREATE INDEX FK_pages ON crudgen.page_tables (pages_page_id);


CREATE INDEX IFK_works_with ON crudgen.page_tables (pages_page_id);


CREATE TABLE crudgen.page_columns (
  page_column_id SERIAL   NOT NULL ,
  page_tables_id SERIAL   NOT NULL ,
  column_name VARCHAR(255)   NOT NULL ,
  page_order INT    ,
  display_name VARCHAR(40)    ,
  on_page BOOL  DEFAULT false  ,
  remote_table VARCHAR(255)    ,
  remote_column VARCHAR(255)      ,
PRIMARY KEY(page_column_id)  ,
  FOREIGN KEY(page_tables_id)
    REFERENCES crudgen.page_tables(page_tables_id) ON DELETE CASCADE);


CREATE INDEX FK_page_columns ON crudgen.page_columns (page_tables_id);

COMMENT ON COLUMN crudgen.page_columns.page_order IS 'appearance order inside the page ';

CREATE INDEX IFK_contains ON crudgen.page_columns (page_tables_id);


-- Allow to role crudgen_admin to access and do operation on the crudgen's schema
GRANT USAGE ON SCHEMA crudgen TO crudgen_admin;

GRANT SELECT,INSERT,UPDATE,DELETE ON 
    crudgen.page_columns,
    crudgen.application,
    crudgen.operations_security,
    crudgen.page_tables,
    crudgen.pages
    TO crudgen_admin;

-- Allow to role crudgen_read to access and do reading on the crudgen's schema
GRANT USAGE ON SCHEMA crudgen TO crudgen_read;

GRANT SELECT ON crudgen.pages TO crudgen_read;

--Allow to insert and use sequeneces under crudgen
GRANT SELECT,UPDATE ON  crudgen.page_columns_page_column_id_seq TO crudgen_admin;
GRANT SELECT,UPDATE ON  crudgen.application_app_id_seq TO crudgen_admin;
GRANT SELECT,UPDATE ON  crudgen.pages_page_id_seq TO crudgen_admin;
GRANT SELECT,UPDATE ON  crudgen.pages_app_id_seq TO crudgen_admin;
GRANT SELECT,UPDATE ON  crudgen.operations_security_operations_security_id_seq TO crudgen_admin;
GRANT SELECT,UPDATE ON  crudgen.operations_security_app_id_seq TO crudgen_admin;
GRANT SELECT,UPDATE ON  crudgen.page_tables_pages_page_id_seq TO crudgen_admin;
GRANT SELECT,UPDATE ON  crudgen.page_tables_page_tables_id_seq TO crudgen_admin;

CREATE LANGUAGE plpgsql;
--Trigger for protecting modifications to application from other users
CREATE OR REPLACE FUNCTION check_user() RETURNS trigger AS $application_user_privileges$
  BEGIN
    IF (current_user != OLD.app_owner) THEN
      RAISE EXCEPTION 'You are not the application owner';
    ELSE
        IF tg_op = 'DELETE' THEN
            RETURN OLD;
        ELSE
            IF  tg_op = 'UPDATE' THEN
                RETURN NEW;
            END IF;
        END IF;
    END IF;
  END;    
$application_user_privileges$ LANGUAGE 'plpgsql';

CREATE TRIGGER application_user_privileges
  BEFORE UPDATE OR DELETE ON crudgen.application
  FOR EACH ROW EXECUTE PROCEDURE check_user();

--Trigger for inserting current user on the database
CREATE OR REPLACE FUNCTION insert_user() RETURNS trigger AS $application_insert_user$
  BEGIN
      NEW.app_owner:= current_user;
      RETURN NEW;
  END;    
$application_insert_user$ LANGUAGE 'plpgsql';

CREATE TRIGGER application_insert_user
  BEFORE INSERT ON crudgen.application
  FOR EACH ROW EXECUTE PROCEDURE insert_user();
