-- Test Data Setup for StudySmart Platform
-- This file contains sample data for testing the platform

-- Clear existing data (optional - uncomment if needed)
-- DELETE FROM user_sessions;
-- DELETE FROM resource_access;
-- DELETE FROM enrollments;
-- DELETE FROM resources;
-- DELETE FROM sessions;
-- DELETE FROM courses;
-- DELETE FROM users;

-- Insert test users
INSERT INTO users (username, email, password_hash, first_name, last_name, role, phone, whatsapp_number, is_active, created_at) VALUES
-- Admin user
('admin', 'admin@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', '+1234567890', '+1234567890', 1, NOW()),

-- Lecturer users
('lecturer1', 'lecturer1@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', 'lecturer', '+1234567891', '+1234567891', 1, NOW()),
('lecturer2', 'lecturer2@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', 'lecturer', '+1234567892', '+1234567892', 1, NOW()),
('lecturer3', 'lecturer3@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Brown', 'lecturer', '+1234567893', '+1234567893', 1, NOW()),

-- Student users
('student1', 'student1@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Wilson', 'student', '+1234567894', '+1234567894', 1, NOW()),
('student2', 'student2@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob', 'Davis', 'student', '+1234567895', '+1234567895', 1, NOW()),
('student3', 'student3@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carol', 'Miller', 'student', '+1234567896', '+1234567896', 1, NOW()),
('student4', 'student4@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David', 'Garcia', 'student', '+1234567897', '+1234567897', 1, NOW()),
('student5', 'student5@studysmart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma', 'Martinez', 'student', '+1234567898', '+1234567898', 1, NOW());

-- Insert test courses
INSERT INTO courses (title, course_code, description, lecturer_id, is_active, created_at) VALUES
('Introduction to Computer Science', 'CS101', 'Fundamental concepts of computer science and programming', 2, 1, NOW()),
('Advanced Mathematics', 'MATH201', 'Advanced mathematical concepts and problem solving', 3, 1, NOW()),
('English Literature', 'ENG101', 'Study of classic and contemporary literature', 4, 1, NOW()),
('Physics Fundamentals', 'PHY101', 'Basic principles of physics and mechanics', 2, 1, NOW()),
('Business Management', 'BUS101', 'Introduction to business management principles', 3, 1, NOW()),
('Web Development', 'WEB101', 'Modern web development technologies and practices', 4, 1, NOW());

-- Insert test enrollments
INSERT INTO enrollments (student_id, course_id, enrolled_at, is_active) VALUES
-- Student 1 enrollments
(5, 1, NOW(), 1),
(5, 3, NOW(), 1),
(5, 5, NOW(), 1),

-- Student 2 enrollments
(6, 1, NOW(), 1),
(6, 2, NOW(), 1),
(6, 4, NOW(), 1),

-- Student 3 enrollments
(7, 2, NOW(), 1),
(7, 3, NOW(), 1),
(7, 6, NOW(), 1),

-- Student 4 enrollments
(8, 1, NOW(), 1),
(8, 4, NOW(), 1),
(8, 5, NOW(), 1),

-- Student 5 enrollments
(9, 2, NOW(), 1),
(9, 3, NOW(), 1),
(9, 6, NOW(), 1);

-- Insert test resources
INSERT INTO resources (title, description, file_path, file_size, type, course_id, lecturer_id, views_count, downloads_count, is_active, created_at) VALUES
('CS101 Syllabus', 'Course syllabus and schedule for Introduction to Computer Science', 'uploads/cs101_syllabus.pdf', 1024000, 'pdf', 1, 2, 15, 8, 1, NOW()),
('Programming Basics', 'Introduction to programming concepts and syntax', 'uploads/programming_basics.pdf', 2048000, 'pdf', 1, 2, 25, 12, 1, NOW()),
('Math Formulas', 'Collection of important mathematical formulas', 'uploads/math_formulas.pdf', 1536000, 'pdf', 2, 3, 18, 10, 1, NOW()),
('Literature Analysis', 'Guide to analyzing literary works', 'uploads/literature_analysis.pdf', 1280000, 'pdf', 3, 4, 12, 6, 1, NOW()),
('Physics Lab Manual', 'Laboratory manual for physics experiments', 'uploads/physics_lab.pdf', 2560000, 'pdf', 4, 2, 20, 15, 1, NOW()),
('Business Case Studies', 'Real-world business case studies', 'uploads/business_cases.pdf', 3072000, 'pdf', 5, 3, 14, 9, 1, NOW()),
('Web Development Tutorial', 'Step-by-step web development tutorial', 'uploads/web_tutorial.pdf', 4096000, 'pdf', 6, 4, 30, 18, 1, NOW()),
('Programming Video', 'Video tutorial on programming basics', 'uploads/programming_video.mp4', 51200000, 'video', 1, 2, 35, 20, 1, NOW()),
('Math Lecture', 'Video lecture on advanced mathematics', 'uploads/math_lecture.mp4', 61440000, 'video', 2, 3, 22, 14, 1, NOW());

-- Insert test sessions
INSERT INTO sessions (title, description, course_id, lecturer_id, session_date, duration, meeting_link, is_active) VALUES
('Programming Workshop', 'Hands-on programming workshop for beginners', 1, 2, DATE_ADD(NOW(), INTERVAL 2 DAY), 120, 'https://meet.google.com/abc-defg-hij', 1),
('Math Problem Solving', 'Advanced problem solving techniques', 2, 3, DATE_ADD(NOW(), INTERVAL 3 DAY), 90, 'https://meet.google.com/xyz-uvw-rst', 1),
('Literature Discussion', 'Group discussion on assigned readings', 3, 4, DATE_ADD(NOW(), INTERVAL 1 DAY), 60, 'https://meet.google.com/lmn-opq-uvw', 1),
('Physics Lab Session', 'Virtual physics laboratory session', 4, 2, DATE_ADD(NOW(), INTERVAL 4 DAY), 150, 'https://meet.google.com/def-ghi-jkl', 1),
('Business Strategy', 'Business strategy and planning workshop', 5, 3, DATE_ADD(NOW(), INTERVAL 5 DAY), 120, 'https://meet.google.com/mno-pqr-stu', 1),
('Web Development Demo', 'Live web development demonstration', 6, 4, DATE_ADD(NOW(), INTERVAL 6 DAY), 90, 'https://meet.google.com/vwx-yz1-234', 1);

-- Insert test notifications
INSERT INTO notifications (title, message, target_type, target_ids, created_at) VALUES
('Welcome to StudySmart!', 'Welcome to our learning platform. We hope you have a great learning experience.', 'all', NULL, NOW()),
('New Course Available', 'Introduction to Computer Science is now available for enrollment.', 'students', NULL, NOW()),
('Session Reminder', 'Your programming workshop is scheduled for tomorrow at 2 PM.', 'specific', '5,6,8', NOW()),
('Resource Uploaded', 'New study materials have been uploaded for your course.', 'specific', '5,6,7,8,9', NOW());

-- Insert test resource access records
INSERT INTO resource_access (resource_id, student_id, access_type, accessed_at) VALUES
(1, 5, 'view', NOW()),
(1, 5, 'download', NOW()),
(1, 6, 'view', NOW()),
(1, 8, 'view', NOW()),
(2, 5, 'view', NOW()),
(2, 6, 'view', NOW()),
(2, 6, 'download', NOW()),
(3, 6, 'view', NOW()),
(3, 7, 'view', NOW()),
(3, 7, 'download', NOW()),
(4, 5, 'view', NOW()),
(4, 7, 'view', NOW()),
(5, 6, 'view', NOW()),
(5, 8, 'view', NOW()),
(5, 8, 'download', NOW()),
(6, 5, 'view', NOW()),
(6, 8, 'view', NOW()),
(7, 7, 'view', NOW()),
(7, 9, 'view', NOW()),
(7, 9, 'download', NOW());

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
('app_name', 'StudySmart'),
('app_description', 'A comprehensive online tutoring and learning platform'),
('contact_email', 'admin@studysmart.com'),
('max_file_size', '10'),
('allowed_file_types', 'pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,mp4,avi,mov'),
('session_timeout', '24'),
('enable_notifications', '1'),
('enable_analytics', '1');

-- Display login credentials
SELECT '=== LOGIN CREDENTIALS ===' as info;
SELECT 'All passwords are: password' as note;
SELECT '' as blank;

SELECT 'ADMIN LOGIN:' as role;
SELECT 'Username: admin' as username;
SELECT 'Email: admin@studysmart.com' as email;
SELECT 'Password: password' as password;
SELECT '' as blank;

SELECT 'LECTURER LOGINS:' as role;
SELECT 'Username: lecturer1' as username;
SELECT 'Email: lecturer1@studysmart.com' as email;
SELECT 'Password: password' as password;
SELECT '' as blank;
SELECT 'Username: lecturer2' as username;
SELECT 'Email: lecturer2@studysmart.com' as email;
SELECT 'Password: password' as password;
SELECT '' as blank;
SELECT 'Username: lecturer3' as username;
SELECT 'Email: lecturer3@studysmart.com' as email;
SELECT 'Password: password' as password;
SELECT '' as blank;

SELECT 'STUDENT LOGINS:' as role;
SELECT 'Username: student1' as username;
SELECT 'Email: student1@studysmart.com' as email;
SELECT 'Password: password' as password;
SELECT '' as blank;
SELECT 'Username: student2' as username;
SELECT 'Email: student2@studysmart.com' as email;
SELECT 'Password: password' as password;
SELECT '' as blank;
SELECT 'Username: student3' as username;
SELECT 'Email: student3@studysmart.com' as email;
SELECT 'Password: password' as password;
SELECT '' as blank;
SELECT 'Username: student4' as username;
SELECT 'Email: student4@studysmart.com' as email;
SELECT 'Password: password' as password;
SELECT '' as blank;
SELECT 'Username: student5' as username;
SELECT 'Email: student5@studysmart.com' as email;
SELECT 'Password: password' as password; 