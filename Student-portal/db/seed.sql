-- Seed data — ALL FICTITIOUS
-- Default password for every seeded account: Password@123

USE studentreg_db;

-- Password hash below is bcrypt for 'Password@123'
INSERT INTO users (full_name, email, password_hash, role, matric_no) VALUES
('Amina Bello', 'amina.bello@example.test', '$2b$12$nnBmACYfeHQUk4agxytkPOrHJuXU9xCdgwmi.7T9S3oCdFNeafgTS', 'student', 'FUT/CS/2021/001'),
('John Okafor', 'john.okafor@example.test', '$2b$12$nnBmACYfeHQUk4agxytkPOrHJuXU9xCdgwmi.7T9S3oCdFNeafgTS', 'student', 'FUT/CS/2021/002'),
('Grace Admin', 'admin@example.test', '$2b$12$nnBmACYfeHQUk4agxytkPOrHJuXU9xCdgwmi.7T9S3oCdFNeafgTS', 'admin', NULL);

INSERT INTO courses (code, title, capacity) VALUES
('CSC401', 'Software Engineering', 40),
('CSC405', 'Computer Security', 35),
('CSC410', 'Database Systems', 40),
('CSC415', 'Web Technologies', 30);

INSERT INTO enrolments (user_id, course_id, status) VALUES
(1, 1, 'registered'),
(1, 3, 'registered'),
(2, 2, 'registered');
