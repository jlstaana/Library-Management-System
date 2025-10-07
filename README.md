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

# Library Management System (Lab_Exam_LibSys)

Formal, well-structured README for the Library Management System. This project provides a simple web-based library management application implemented with Laravel and MySQL. It includes a responsive UI, RESTful API endpoints, and features for managing books, authors, categories, students, and borrowing transactions.

## Table of Contents
- About
- Key Features
- System Requirements
- Installation
- Configuration
- Database (schema summary)
- API Endpoints
- Usage
- Development & Contribution
- License
- Contact

## About

Lab_Exam_LibSys is a lightweight library management system intended as a demonstration and educational project. It implements CRUD operations for core entities and includes a simple dashboard with summary statistics and optional public API integration for additional content.

## Key Features
- Manage Students: create, read, update, delete student records.
- Manage Books: CRUD operations for books with metadata (author, publisher, edition, ISBN, category).
- Manage Authors and Categories.
- Borrow / Return workflow: record borrowing and returning of books.
- Dashboard: summary statistics (total books, students, borrowed/returned counts) and optional public API integration (e.g., weather/news).
- Simple RESTful API endpoints for programmatic access.

## System Requirements
- PHP 8.0+
- Composer
- Laravel 10.x (project is structured for Laravel applications)
- MySQL 5.7+ / MariaDB
- Node.js and npm (for frontend assets, optional)
- Web server (Apache/Nginx) or use the built-in Laravel server for development

## Installation
1. Clone the repository:

	git clone https://github.com/jlstaana/Lab_Exam_LibSys.git

2. Change into the project directory:

	cd Lab_Exam_LibSys

3. Install PHP dependencies:

	composer install

4. Install front-end dependencies (if required):

	npm install

5. Copy the environment file and update values:

	cp .env.example .env

	Update the following variables in `.env`:

	DB_DATABASE=library_db
	DB_USERNAME=your_db_user
	DB_PASSWORD=your_db_password

6. Generate the application key:

	php artisan key:generate

7. Run database migrations:

	php artisan migrate

8. (Optional) Seed the database if seeders are available:

	php artisan db:seed

9. Start the development server:

	php artisan serve

Visit http://127.0.0.1:8000 in your browser.

## Configuration
- Database connection: configured in `.env` (DB_DATABASE, DB_USERNAME, DB_PASSWORD).
- Additional configuration for mail, cache, and other Laravel services can be updated in `.env` and `config/` files.

## Database (schema summary)
The project uses a relational schema. Core tables include:
- `students` (student_id, name, email, username, password, ...)
- `authors` (author_id, name, email, ...)
- `books` (book_id, title, author_id, publisher, edition, isbn, category_id, ...)
- `categories` (category_id, description, ...)
- `borrowed_books` (id, book_id, student_id, date_borrowed, date_returned, lib_id, ...)
- `librarians` (lib_id, name, email, type_id, ...)

Refer to `sql/database.sql` for the full schema dump and sample data (if included).

## API Endpoints
The repository includes a simple public API under the `api/` folder. Example endpoints include:
- GET /api/books — list all books
- POST /api/books — create a new book
- PUT /api/books/{id} — update a book
- DELETE /api/books/{id} — delete a book

See `api/*.php` files for the exact endpoint implementations and request/response formats.

## Usage
- Use the UI pages (index, dashboard, students, books, authors, categories, borrow_return) to manage the library data.
- Use the API endpoints for automation or integration.

Quick tips:
- Ensure the database connection is correct in `.env` before running migrations.
- If you see permission or migration errors, verify your database user has appropriate privileges.

## Development & Contribution
Contributions are welcome. Suggested workflow:
1. Fork the repository.
2. Create a feature branch (git checkout -b feature/your-feature).
3. Make changes and add tests where appropriate.
4. Commit and push your branch.
5. Open a pull request describing your changes.

Please follow the existing code style and add descriptive commit messages.

## License
This project is licensed under the MIT License. See the included `LICENSE` file for details.

## Contact
Developer: jlstaana
GitHub: https://github.com/jlstaana



