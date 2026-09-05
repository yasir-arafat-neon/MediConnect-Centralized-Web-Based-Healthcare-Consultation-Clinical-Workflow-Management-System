===============================================================
MEDICONNECT: CENTRALIZED WEB-BASED HEALTHCARE CONSULTATION & CLINICAL WORKFLOW MANAGEMENT SYSTEM
XAMPP DEPLOYMENT QUICK START GUIDE
===============================================================

CONGRATULATIONS!
Your database (mediconnect_db) is already created in phpMyAdmin.

HOW TO RUN THIS APP IN XAMPP:
---------------------------------------------------------------
1. Extract all files from this ZIP directly into:
   C:\xampp\htdocs\mediconnect_db\

   Your folder structure should look like this:
   C:\xampp\htdocs\mediconnect_db\
     ├── index.php
     ├── config\
     │   └── db.php
     ├── auth\
     │   ├── login.php
     │   ├── register.php
     │   └── logout.php
     ├── dashboard\
     │   ├── patient.php
     │   ├── doctor.php
     │   └── admin.php
     ├── api\
     │   ├── patient_actions.php
     │   ├── doctor_actions.php
     │   └── admin_actions.php
     └── mediconnect_db.sql

2. Ensure Apache and MySQL are running (Green) in XAMPP Control Panel.

3. Open your browser (Chrome, Edge, Firefox) and navigate to:
   http://localhost/mediconnect_db/

4. Log in using any of the pre-seeded accounts:
   ------------------------------------------------------------
   ROLE                  EMAIL                          PASSWORD
   ------------------------------------------------------------
   User 1 (Patient)     sarah.patient@example.com      Password123#
   User 2 (Doctor)      dr.robert.chen@hospital.org    Password123#
   User 3 (Admin)       admin@mediconnect.local        Password123#
   ------------------------------------------------------------

All data entered through the web portal will automatically sync
with your MySQL database 'mediconnect_db' in phpMyAdmin!
===============================================================