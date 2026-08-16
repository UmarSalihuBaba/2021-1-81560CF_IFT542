-- Task 3 migration: adds a free-text "bio" field to demonstrate output encoding.

USE studentreg_db;

ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL;
