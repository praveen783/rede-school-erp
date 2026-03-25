Project: School ERP MVP

Overview:
This is a School ERP (Enterprise Resource Planning) MVP developed as part of an internship project.
The system handles core school operations such as student management, attendance, exams, and role-based access.

Tech Stack:

* Backend: Laravel (PHP 8.1)
* Frontend: Blade Templates (integrated into Laravel)
* Database: MySQL (MariaDB via XAMPP)

Features Implemented:

* Authentication & Role-Based Access Control (Admin, Teacher, Student, Parent, Accountant)
* Student Information System (SIS)
* Admissions & Student Lifecycle Management
* Attendance Management
* Exams, Results, and Gradebook

Project Structure:
rede-school-erp/
│
├── backend/        → Laravel application
├── database/
│     └── rede_school_erp.sql   → Database dump
└── README.txt

Setup Instructions:

1. Prerequisites:

   * PHP >= 8.1
   * Composer
   * XAMPP / MySQL Server

2. Clone / Extract Project:
   Extract the ZIP file and navigate to the backend folder.

3. Install Dependencies:
   cd backend
   composer install

4. Environment Setup:
   cp .env.example .env
   php artisan key:generate

5. Configure Database (.env):
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=rede_school_erp
   DB_USERNAME=root
   DB_PASSWORD=

6. Import Database:

   * Open phpMyAdmin (http://127.0.0.1/phpmyadmin)
   * Create a database named: rede_school_erp
   * Import the file: database/rede_school_erp.sql

7. Run Application:
   php artisan serve

   Open in browser:
   http://127.0.0.1:8000

Notes:

* Frontend template has been integrated into Laravel Blade views.
* Ensure Apache and MySQL are running before importing the database.
* If port conflicts occur, stop other services like Laravel Herd.

Author:
Developed as part of Software Developer Internship.
