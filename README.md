# Digi-Pet
school project

## MySQL setup

The sign-up page creates the `users` table automatically. It stores password
hashes, not plain-text passwords. The journal page also creates its
`journal_entries` table automatically. The configured MySQL account needs
permission to create tables and to select, insert, and update rows.

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

Open `http://localhost:8000/sign_in.php` to sign in or create an account.
Successful sign-in opens the journal. Do not put database credentials in the
source code or commit them to the repository.
