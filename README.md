## AutoCalls assignment

A Laravel-based application for managing automated calling campaigns and leads. Includes functionality for importing campaign data from an Excel file.

---

##  Getting Started

Follow the steps below to get the project up and running on your local machine.

---

### 1.  Clone the Repository

### 2. Install dependencies
    composer install

### 3. Set up your .env file

### 4.Run database migrations
    php artisan migrate

### 5.Create an admin user (via Filament)
    php artisan make:filament-user

### 6.Seed the database with campaigns and leads
    php artisan db:seed --class=CampaignAndLeadSeeder

### 7!.Import leads from Excel (CSV) A sample file for import is included in the project root:
    import.csv
