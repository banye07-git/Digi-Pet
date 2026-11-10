# Digi-Pet
school project

## Journal database setup

The journal page saves one entry per PHP browser session in MySQL database `block`.
The `journal_entries` table is created automatically, so the configured MySQL
account needs permission to create tables and to select, insert, and update rows.

Create the database if it does not exist:

```sql
CREATE DATABASE block CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Set the connection settings in the PHP server environment. For example, in
PowerShell before starting the local PHP server:

```powershell
$env:DIGIPET_DB_HOST = "127.0.0.1"
$env:DIGIPET_DB_USER = "your_mysql_username"
$env:DIGIPET_DB_PASSWORD = "your_mysql_password"
php -S localhost:8000
```

Open `http://localhost:8000/journal.php`. Do not put database credentials in
the source code or commit them to the repository.
