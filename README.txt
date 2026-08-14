AGRO WORK - PHP + MYSQL + HTML/CSS/JS
=====================================

This version uses NO Python, NO FastAPI and NO React.
It is designed to run directly from XAMPP htdocs.

IMPORTANT FOLDER NAME
---------------------
Keep the extracted project folder name exactly:

    agro_work

because the PHP base URL is /agro_work.

INSTALL / RUN
-------------
1. Extract/rename the folder to:

   C:\xampp\htdocs\agro_work

   or your XAMPP htdocs path, e.g.
   E:\Xampp\xampp\htdocs\agro_work

2. Start XAMPP:
   - Apache -> Start
   - MySQL  -> Start

3. Open phpMyAdmin:
   http://localhost/phpmyadmin

4. Import:
   database\agro_work.sql

   WARNING: the SQL file drops and recreates agro_work_BD.

5. Open the app:
   http://localhost/agro_work

DEMO LOGIN
----------
Admin:
NID: 1111111111
Password: admin123

Farmer:
NID: 2222222222
Password: farmer123

Driver/Worker:
NID: 3333333333
Password: driver123

MAIN BUSINESS FLOW
------------------
Farmer:
- Register/Login
- Create/Edit/Delete Farm
- Post/Edit/Delete Job
- Job contains workers needed + wage/day + dates
- See workers who accepted jobs
- Record/Delete payments

Driver:
- Register/Login
- Browse consumer-friendly job cards
- Click Accept Job
- JOB_DRIVER assignment is created automatically
- Wage is copied from JOB.wage_per_day
- Update assignment status
- View payment history

Admin:
- Dashboard
- View/Edit/Delete users when FK rules allow
- Monitor jobs / change job status / delete unreferenced jobs
- CRUD Job Types

DATABASE
--------
PERSON has three disjoint subtypes:
ADMIN, FARMER, DRIVER.

REVIEW is intentionally not included.

SECURITY / DB NOTES
-------------------
- PDO prepared statements are used for user inputs.
- PHP password_hash/password_verify are used for passwords.
- MySQL foreign keys prevent unsafe deletes.
- Main CRUD only is implemented to keep the term project simple.
