# Otaku Nexus - Anime Club Management System

A PHP and MySQL web application for **ANIME CLUB - OTAKU NEXUS**. The platform serves as a centralized hub for anime enthusiasts, allowing members to join the club, participate in discussions, manage profiles, coordinate events, and engage with club activities through a modern web interface.

## Author

Name: Torikul Islam 

Roll: 2207078

## What It Does

* Shows the Anime Club homepage with information about the club and its activities.
* Allows users to create accounts and log in securely.
* Lets users manage their personal profiles.
* Provides a discussion platform where members can interact and share opinions about anime, manga, and club activities.
* Supports role-based access control with **Admin**, **Moderator**, **Event Coordinator**, and **Member** roles.
* Allows administrators to promote members to moderators.
* Allows administrators to promote members to event coordinators.
* Allows moderators to manage community members.
* Allows moderators and administrators to accept membership requests.
* Allows moderators and administrators to remove members when necessary.
* Allows event coordinators to create and manage club events.
* Provides an administrative dashboard for managing users and community activities.
* Displays club activities such as cosplay labs, manga library sessions, watch parties, and convention trips.
* Supports image uploads for user profiles and community content.

## Technologies Used

* PHP
* MySQL / MariaDB
* HTML
* CSS
* JavaScript
* XAMPP or any local PHP/MySQL environment

## Project Structure

```text
.
|-- config/
|   `-- db.php                    # Database connection configuration
|
|-- images/
|   |-- convention trip.jpg       # Convention trip activity image
|   |-- cosplay lab.jpg           # Cosplay workshop activity image
|   |-- manga library.jpg         # Manga reading session image
|   `-- watch parties.jpg         # Anime watch party image
|
|-- includes/
|   |-- header.php                # Shared website header
|   `-- footer.php                # Shared website footer
|
|-- uploads/                      # User uploads and profile images
|
|-- admin.php                     # Admin, moderator, and coordinator dashboard
|-- anime.js                      # Frontend JavaScript functionality
|-- animestyle.css                # Main stylesheet
|-- discussions.php               # Community discussion page
|-- index.php                     # Homepage
|-- login.php                     # User login page
|-- logout.php                    # User logout handler
|-- profile.php                   # User profile page
`-- register.php                  # User registration page
```

## Local Setup

### 1. Install Requirements

Install and start a local PHP/MySQL environment. XAMPP is recommended because the project uses a standard PHP and MySQL setup.

Required services:

* Apache
* MySQL

### 2. Place the Project in the Server Directory

Copy or move the project folder into your local web server directory.

For XAMPP:

```text
D:\xampp\htdocs\Otaku-Nexus
```

### 3. Create the Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Create a database named:

```text
otaku_nexus
```

Import the SQL file for the project if one is available.

### 4. Check Database Configuration

The database connection settings are configured in:

```text
config/db.php
```

Example configuration:

```php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'otaku_nexus';
```

If your MySQL username or password is different, update this file accordingly.

### 5. Run the Project

Start Apache and MySQL from XAMPP, then visit:

```text
http://localhost/Otaku-Nexus/index.php
```

If the folder name is different, replace **Otaku-Nexus** with your actual project folder name.

You can also run the project using PHP's built-in server from the project root:

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000/index.php
```

MySQL must still be running for database-related features to work.

## Main Pages

* **index.php** - Homepage with club information, featured activities, and navigation links.
* **register.php** - User registration page.
* **login.php** - User authentication page.
* **logout.php** - User logout handler.
* **profile.php** - User profile management page.
* **discussions.php** - Community discussion and interaction page.
* **admin.php** - Administrative dashboard for managing users, roles, memberships, and events.

## User Roles

### Admin

* Full system access.
* Promote members to moderators.
* Promote members to event coordinators.
* Manage all users.
* Approve membership requests.
* Remove members.
* Manage community activities.
* Manage club events.
* Access administrative tools.

### Moderator

* Approve membership requests.
* Manage members.
* Remove members when necessary.
* Moderate community discussions.
* Assist with community management.

### Event Coordinator

* Approve membership requests.
* Manage members.
* Remove members when necessary.
* Create club events.
* Edit event details.
* Manage event schedules.
* Coordinate club activities.
* Assist with event planning and execution.

### Member

* Participate in discussions.
* Manage personal profile.
* Access club activities.
* Join club events.
* Interact with the community.

## Admin Setup

The application uses the same login system for all users.

Register a normal account first, then promote it through phpMyAdmin or MySQL:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your_email@example.com';
```

After logging in, administrators can access:

```text
admin.php
```

## File Uploads

Uploaded files and profile images are stored under:

```text
uploads/
```

Ensure that the directory has the necessary write permissions enabled.

## Notes

* Some features require users to be logged in.
* Administrative functions are restricted to authorized users.
* The application uses role-based access control.
* MySQL must be running for authentication and database functionality.
* Uploaded images and files are stored locally.
* The project was developed for **ANIME CLUB - OTAKU NEXUS**.
* XAMPP is the recommended development environment for local deployment.
