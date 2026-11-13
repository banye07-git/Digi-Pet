# Digi-Pet
school project

## MySQL setup

The journal page creates its `journal_entries` table automatically. The
configured MySQL account needs permission to create tables and to select,
insert, and update rows. Sign-in and sign-up authentication are temporarily
commented out; those pages are currently display-only and do not submit
credentials.

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
$env:DIGIPET_DB_NAME = "block"
php -S localhost:8000
```

Open `http://localhost:8000/sign_in.php` or `http://localhost:8000/signup.php`
to view the sign-in and sign-up forms. The journal remains available at
`http://localhost:8000/journal.php` and uses MySQL for entries. Do not put
database credentials in the source code or commit them to the repository.
