# Libris - A Collaborative Library Management System

## Overview

Libris is a web-based library management system developed as a group project by 4 developers. Built using PHP, HTML, CSS, and JavaScript, it provides essential library functionalities.

## Features

*   **Book Management:**
*   **User Management:** 
*   **Borrowing/Returning:**
*   **Search Functionality:**

## Technologies Used

*   **Frontend:**
    *   HTML
    *   CSS
    *   JavaScript
*   **Backend:**
    *   PHP
*   **Database:** MySQL

## Installation

1.  **Clone the repository:**

    ```
    git clone https://github.com/bekircanan/Libris.git
    ```
2.  **Database Setup:**

    *   Create a database in your MySQL server.
    *   Import the database schema from `(path to database schema file if available)`.  If not available, create the necessary tables based on the application's needs.
3.  **Configuration:**

    *   Rename `.env.example` to `.env`
    *   Open `.env` and configure the database connection details:

        ```
        DB_HOST=your_database_host
        DB_DATABASE=your_database_name
        DB_USERNAME=your_database_username
        DB_PASSWORD=your_database_password
        ```
4.  **Install Dependencies:**

    *   If there are any PHP dependencies, install them using Composer:

        ```
        composer install
        ```

5.  **Web Server Configuration:**

    *   Configure your web server (e.g., Apache, Nginx) to point to the `Libris` directory.
    *   Make sure PHP is properly configured and enabled.
6.  **Access the Application:**

    *   Open your web browser and navigate to the URL where you've configured the web server (e.g., `http://localhost/Libris`).

## Contributing

Contributions are welcome! If you find a bug or have an idea for a new feature, please open an issue or submit a pull request.

1.  Fork the repository.
2.  Create a new branch for your feature or bug fix.
3.  Make your changes and commit them with descriptive messages.
4.  Submit a pull request.

## Contributors

*   Selsibil
*   Bekir
*   Senelet
*   Arthur

