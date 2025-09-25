CREATE DATABASE IF NOT EXISTS library_system;
USE library_system;

-- Librarian Types
CREATE TABLE IF NOT EXISTS librarian_types (
    Type_ID INT AUTO_INCREMENT PRIMARY KEY,
    Description VARCHAR(50) NOT NULL
);

-- Librarians
CREATE TABLE IF NOT EXISTS librarians (
    Lib_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Type_ID INT,
    FOREIGN KEY (Type_ID) REFERENCES librarian_types(Type_ID)
);

-- Students
CREATE TABLE IF NOT EXISTS students (
    Student_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL
);

-- Authors
CREATE TABLE IF NOT EXISTS authors (
    Author_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL
);

-- Book Categories
CREATE TABLE IF NOT EXISTS book_categories (
    Category_ID INT AUTO_INCREMENT PRIMARY KEY,
    Description VARCHAR(100) NOT NULL
);

-- Books
CREATE TABLE IF NOT EXISTS books (
    Book_ID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Author_ID INT,
    Publisher VARCHAR(100),
    Edition VARCHAR(50),
    ISBN VARCHAR(20) UNIQUE,
    Category_ID INT,
    FOREIGN KEY (Author_ID) REFERENCES authors(Author_ID),
    FOREIGN KEY (Category_ID) REFERENCES book_categories(Category_ID)
);

-- Borrowed Books
CREATE TABLE IF NOT EXISTS borrowed_books (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Book_ID INT,
    Student_ID INT,
    Date_Borrowed DATE NOT NULL,
    Date_Returned DATE,
    Lib_ID INT,
    FOREIGN KEY (Book_ID) REFERENCES books(Book_ID),
    FOREIGN KEY (Student_ID) REFERENCES students(Student_ID),
    FOREIGN KEY (Lib_ID) REFERENCES librarians(Lib_ID)
);

-- Insert sample data
INSERT INTO librarian_types (Description) VALUES 
('Head Librarian'), ('Assistant Librarian'), ('Intern');

INSERT INTO librarians (Name, Email, Password, Type_ID) VALUES 
('John Smith', 'john@library.com', 'password123', 1),
('Jane Doe', 'jane@library.com', 'password123', 2);

INSERT INTO students (Name, Email, Username, Password) VALUES 
('Alice Johnson', 'alice@student.com', 'alicej', 'student123'),
('Bob Williams', 'bob@student.com', 'bobw', 'student123');

INSERT INTO authors (Name, Email) VALUES 
('George Orwell', 'george@author.com'),
('J.K. Rowling', 'jk@author.com');

INSERT INTO book_categories (Description) VALUES 
('Fiction'), ('Non-Fiction'), ('Science'), ('Technology');

INSERT INTO books (Title, Author_ID, Publisher, Edition, ISBN, Category_ID) VALUES 
('1984', 1, 'Secker & Warburg', '1st', '9780451524935', 1),
('Harry Potter', 2, 'Bloomsbury', '1st', '9780747532743', 1);

INSERT INTO borrowed_books (Book_ID, Student_ID, Date_Borrowed, Date_Returned, Lib_ID) VALUES 
(1, 1, '2023-10-01', NULL, 1),
(2, 2, '2023-10-02', '2023-10-10', 2);