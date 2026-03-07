# StudySmart Platform - Login Credentials

## Overview
The StudySmart platform has three user roles: Admin, Lecturer, and Student. Each role has access to different dashboards and functionalities.

## Database Status
✅ Database: `studysmart` - **EXISTS**  
✅ Tables: All required tables created successfully  
✅ Test Data: Sample users, courses, and resources loaded  

## Login Credentials

### 🔐 All accounts use the password: `password`

### 👑 Admin Users
| Username | Email | Name | Role | Status |
|----------|-------|------|------|--------|
| `admin` | admin@studysmart.com | Admin User | admin | ✅ Active |

**Admin Dashboard Access:** `/admin/dashboard.php`

### 👨‍🏫 Lecturer Users
| Username | Email | Name | Role | Status |
|----------|-------|------|------|--------|
| `lecturer1` | lecturer1@studysmart.com | John Smith | lecturer | ✅ Active |
| `lecturer2` | lecturer2@studysmart.com | Sarah Johnson | lecturer | ✅ Active |
| `lecturer3` | lecturer3@studysmart.com | Michael Brown | lecturer | ✅ Active |

**Lecturer Dashboard Access:** `/lecturer/courses.php`

### 👨‍🎓 Student Users
| Username | Email | Name | Role | Status |
|----------|-------|------|------|--------|
| `student1` | student1@studysmart.com | Alice Wilson | student | ✅ Active |
| `student2` | student2@studysmart.com | Bob Davis | student | ✅ Active |
| `student3` | student3@studysmart.com | Carol Miller | student | ✅ Active |
| `student4` | student4@studysmart.com | David Garcia | student | ✅ Active |
| `student5` | student5@studysmart.com | Emma Martinez | student | ✅ Active |

**Student Dashboard Access:** `/student/courses.php`

## How to Access

### 1. Login Page
- **URL:** `http://localhost/studysmart/login.php`
- **Features:** Modern responsive design with role-based redirects

### 2. Direct Dashboard Access
After login, users are automatically redirected to their appropriate dashboard based on their role.

### 3. Logout
- **URL:** `http://localhost/studysmart/logout.php`
- Automatically clears sessions and redirects to login page

## Dashboard Features

### Admin Dashboard (`/admin/`)
- User management (students, lecturers, admins)
- Course management
- Resource management
- Session scheduling
- Analytics and reports
- System settings
- Notifications

### Lecturer Dashboard (`/lecturer/`)
- Course management
- Student management
- Resource uploads
- Session scheduling
- Analytics

### Student Dashboard (`/student/`)
- Course enrollment
- Resource downloads
- Session attendance
- Grades and progress
- Calendar view

## Test Results
✅ **Admin Login:** Working - User ID: 1  
✅ **Lecturer Login:** Working - User ID: 2  
✅ **Student Login:** Working - User ID: 5  
✅ **Role Verification:** All roles properly verified  
✅ **Session Management:** Working with automatic redirects  

## Security Features
- Password hashing using bcrypt
- Session-based authentication
- Role-based access control
- Device tracking and session management
- Remember me functionality (30 days)

## File Structure
```
studysmart/
├── config/
│   └── database.php          # Database configuration
├── classes/
│   ├── Auth.php             # Authentication class
│   ├── Course.php           # Course management
│   ├── Resource.php         # Resource management
│   └── Notification.php     # Notification system
├── admin/                   # Admin dashboard files
├── lecturer/                # Lecturer dashboard files
├── student/                 # Student dashboard files
├── uploads/                 # File upload directory
├── login.php                # Login page
├── logout.php               # Logout handler
└── index.php                # Home page
```

## Quick Start
1. Ensure XAMPP is running (Apache + MySQL)
2. Navigate to `http://localhost/studysmart/login.php`
3. Use any of the credentials above
4. You'll be automatically redirected to your role-specific dashboard

## Notes
- All demo accounts are fully functional
- The password for all accounts is `password`
- Sessions are automatically managed
- Role-based access is enforced at the dashboard level
- File uploads are supported for various document types

---
*Generated on: <?php echo date('Y-m-d H:i:s'); ?>*
*Platform: StudySmart Tutoring Website*
