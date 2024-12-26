# Dormitory Management System

A web-based application designed to streamline the management of dormitories, rooms, and student information. This system simplifies administrative tasks, improves organization, and saves time for dormitory administrators.

This project was developed for the Ford Hackathon at Sakarya University by **Mustafa Uğur Baskın**, where it won **2nd place**.

## Features

- **Room Management**
  - Add, edit, and delete rooms.
  - View occupancy statuses to optimize room assignments.

- **Student Management**
  - Register, update, or remove student information easily.

- **Dormitory Management**
  - Add new dormitories and manage their details.
  - Monitor capacity and overall status of dormitories.

- **Secure User Authentication**
  - Allow only authorized users to access the system.
  - Manage admin and staff access levels.

- **Data Visualization**
  - Display real-time occupancy and dormitory information in an organized interface.

## Languages Used

HTML, CSS, JavaScript, PHP, MySQL

## Installation

### Requirements

- PHP 7.4 or higher
- MySQL database
- Apache or a similar web server

### Steps

1. **Clone the repository**  
   ```bash
   git clone https://github.com/mustafaugurbaskin/dormitory-management-system.git
   cd dormitory-management-system
   ```

2. **Set up the database**
   - Create a new database in MySQL with 'utf8mb4_unicode_ci' character set and name it 'rekoryurdu'.
   - Import the `rekoryurdu.sql` file into your MySQL database.
   - This file includes all required tables and structures.

3. **Run the application**  
   - Deploy the project to your web server root directory.
   - Access it through your browser

## Usage

1. Log in with your admin credentials (In login.php, Kullanıcı adı (Username): `admin`, Şifre (Password): `123`).
2. Manage dormitories, rooms, and student records through the dashboard.
3. View occupancy and dormitory status updates in real-time.

## Directory Structure

```
dormitory-management-system/
│
├── ogrenci_resmi/         # Directory for storing student photos
├── upload/                # Directory for uploaded files
├── rekoryurdu.sql         # Database schema
├── index.php              # Main entry point
├── login.php              # User login page
├── logout.php             # Logout functionality
├── dashboard.php          # Admin dashboard
├── add_oda.php            # Add new room
├── add_ogrenci.php        # Add new student
├── add_yurt.php           # Add new dormitory
├── delete_oda.php         # Delete a room
├── delete_ogrenci.php     # Delete a student
├── delete_yurt.php        # Delete a dormitory
├── edit_oda.php           # Edit room details
├── edit_ogrenci.php       # Edit student details
├── edit_yurt.php          # Edit dormitory details
├── get_oda.php            # Fetch specific room details
├── get_oda_durumu.php     # Fetch room occupancy details
├── get_odalar.php         # Fetch all rooms
├── get_ogrenciler.php     # Fetch all students
├── view_oda.php           # View detailed information about a room
├── bg.jpg                 # Background image
├── dashboard-bg.jpg       # Dashboard background image
└── README.md              # Project documentation
```

## Contributing

Contributions are welcome! Feel free to fork this repository, make improvements, and submit a pull request. You can also open an issue for bug reports or feature requests.

## License

This project is licensed under the [Apache License](LICENSE).

## Acknowledgments

- Developed by Mustafa Uğur Baskın to enhance the efficiency of dormitory management processes.
- Special thanks to the organizers of the Ford Hackathon at Sakarya University for providing a platform to develop this project. Where this project achieved 2nd place.
