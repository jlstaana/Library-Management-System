PROJECT OVERVIEW

The Library Management System (Lab_Exam_LibSys) is a web-based application developed using Laravel and MySQL.
It is designed to streamline common library operations such as managing books, authors, categories, students, and librarians.
The system also tracks borrowed and returned books, provides statistical summaries through a dashboard, and integrates a public API for extended functionality.

The project demonstrates full-stack web development concepts, RESTful API integration, and the application of Responsive Web Design (RWD) principles to ensure accessibility across various devices.

PROJECT OBJECTIVES

To design and implement a simple yet functional library management system.

To demonstrate database design and normalization through relational table structures.

To implement RESTful API endpoints for CRUD operations.

To apply Responsive Web Design (RWD) techniques for mobile and desktop compatibility.

To integrate a third-party public API to enhance dashboard functionality.

SYSTEM REQUIREMENTS

PHP 8.x or higher

Laravel Framework 10.x or higher

MySQL Database Server

Composer

Node.js and NPM

Web Browser (Google Chrome, Firefox, or Edge)

INSTALLATION AND SETUP GUIDE

Create Laravel Application
Create a new Laravel project named:
Lab_Exam_LibSys

Command:
composer create-project laravel/laravel Lab_Exam_LibSys

Database Configuration
Create a MySQL database and configure the .env file as follows:

DB_DATABASE=library_db  
DB_USERNAME=root  
DB_PASSWORD=


Database Structure
The database should include the following tables and their respective attributes:

Students (Student_ID, Name, Email, Username, Password)

Books (Book_ID, Title, Author_ID, Publisher, Edition, ISBN, Category_ID)

Authors (Author_ID, Name, Email)

Book_Categories (Category_ID, Description)

Borrowed_Books (ID, Book_ID, Student_ID, Date_Borrowed, Date_Returned, Lib_ID)

Librarian (Lib_ID, Name, Email, Type_ID)

Additional tables and relationships may be added as necessary.

Run Migrations
Execute the following command to create the database tables:
php artisan migrate

Start Development Server
Start the Laravel server with the command:
php artisan serve

Access the Application
Open the web browser and navigate to:
http://127.0.0.1:8000

SYSTEM FEATURES

1. Dashboard

Displays statistical summaries such as:
• Total Books
• Total Students
• Number of Borrowed Books
• Number of Returned Books

Includes a public API section (e.g., weather or news feed).

2. Students Module

Add, view, edit, and delete student records.

3. Books Module

Manage book inventory including title, author, publisher, edition, ISBN, and category.

4. Authors Module

Maintain author information and related book associations.

5. Categories Module

Manage book categories and their descriptions.

6. Borrow/Return Books Module

Record and track borrowed and returned books.

7. Librarian Module

Manage librarian details and user types.

RESPONSIVE WEB DESIGN (RWD)

All pages are designed following the core principles of Responsive Web Design:

Fluid Grids

Flexible Images

Media Queries

CSS frameworks such as Bootstrap, Tailwind CSS, UIKit, or Pure CSS may be used to achieve responsiveness and consistent UI design.

RESTFUL API ENDPOINTS

The system implements RESTful API routes supporting standard HTTP methods:

Method	Description	Example Endpoint
GET	Retrieve all records	/api/books
POST	Create a new record	/api/books
PUT	Update an existing record	/api/books/{id}
DELETE	Delete a record	/api/books/{id}
PUBLIC API INTEGRATION

The dashboard integrates one public API of the developer’s choice, such as:

OpenWeather API (https://openweathermap.org/api)

NewsAPI (https://newsapi.org/)

Spaceflight News API (https://spaceflightnewsapi.net/)

HOW TO RUN THE PROJECT

Clone the repository:
git clone https://github.com/jlstaana/Lab_Exam_LibSys.git

Navigate to the project folder:
cd Lab_Exam_LibSys

Install dependencies:
composer install
npm install

Set up the environment variables and database configuration in the .env file.

Run database migrations:
php artisan migrate

Start the application:
php artisan serve

Open the application in your browser at:
http://127.0.0.1:8000

DEVELOPER INFORMATION

Developed by: jlstaana
Email: staanajulianalouise44@gmail.com

GitHub: https://github.com/jlstaana

LICENSE

This project is open-source and distributed under the MIT License.
