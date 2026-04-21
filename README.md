<h1 align="center">
Yrgopelag - Four Walls
</h1>
<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/JavaScript-yellow?logo=javascript">
  <img src="https://img.shields.io/badge/SQLite-003B57?logo=sqlite&logoColor=white">


<img width="857" height="348" alt="Screenshot 2026-04-20 at 11 08 45" src="https://github.com/user-attachments/assets/23efe737-ed7d-47c8-8e12-420390ddc322" />
</p>

</br>

Four Walls is a hotel booking website for a fictional hotel in the Yrgopelag archipelago.

Visitors can book one of three available rooms (Economy, Standard, or Luxury) for stays in January 2026. A booking includes selecting arrival and departure dates, optional hotel features, and completing payment using a transfer code from a mock banking service. 

Selecting a room or adding extras updates the hotel’s graphics in real time, letting visitors see their choices reflected on the site. The calendar also adjusts automatically to show availability for the chosen room. All graphics were created by the author.

The system prevents overlapping bookings, applies a 10% discount for returning guests, and calculates the total price based on room, number of nights, and selected features. Successful bookings are stored in a SQLite database and confirmed with a receipt shown to the user.

The project is built using PHP, SQLite, HTML, CSS, and JavaScript, with payment handling integrated via the Yrgopelag Central Bank API.

The application is designed for desktop use only.

---

## Prerequisites

Before installing Four Walls, ensure you have the following installed on your system:

- **PHP** (version 8.0 or higher)
- **Composer** (PHP dependency manager)
- **SQLite3** (usually included with PHP)

## Installation

Follow these steps to set up Four Walls locally:

### 1. Clone the Repository
```bash
git clone https://github.com/Timalm90/Yrgopelago-FourWalls.git
cd Yrgopelago-FourWalls
```

### 2. Install PHP Dependencies
```bash
composer install
```

This will install the required packages:
- `vlucas/phpdotenv` - For environment variable management
- `guzzlehttp/guzzle` - For HTTP requests to the Central Bank API

### 3. Set Up Environment Variables
Create a `.env` file in the root directory with your Central Bank API configuration:
```
BANK_API_URL=https://api.yrgopelag.local
BANK_API_KEY=your_api_key_here
```

### 4. Initialize the Database
The SQLite database is included at `backend/database/database.db`. If needed, you can reinitialize it by running:
```bash
php loadData.php
```

### 5. Run a Local Server
Start a PHP development server:
```bash
php -S localhost:8000
```

Then open your browser and navigate to:
```
http://localhost:8000
```
