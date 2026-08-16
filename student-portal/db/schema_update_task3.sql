-- Task 3 migration: adds a free-text "bio" field to demonstrate output encoding.
-- Run after schema_update_task2.sql:
--   mysql -u root -p studentreg_db < db/schema_update_task3.sql

USE studentreg_db;

ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL;
