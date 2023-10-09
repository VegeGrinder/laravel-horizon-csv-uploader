# Laravel Horizon CSV Uploader
This is a Laravel 10 project to process CSV files using Laravel Horizon.

## Installation
If you are using a Windows machine, you may use Laravel Homestead to run this project.
https://laravel.com/docs/10.x/homestead

The PHP version specified in the Homestead.yaml file is "8.2".
You will need to SSH into your virtual machine CLI to switch its PHP-CLI to version 8.2 first.
```
php82
```

Run the command line below to install the packages required for this project.
```
composer install
```

Use the command lines below to create a new database for this project.
```
mysql
create database `laravel_horizon_csv_uploader`;
```

Copy .env.example to .env file, and change the timezone of the project if needed.
```
APP_TIMEZONE=Asia/Kuala_Lumpur
```

## Run Laravel Horizon
```
php artisan horizon
```

## Queue and Job Optimization
If you encounter errors where a Job fails to complete due to timeout, you may edit some codes to optimize your machine.
In `app\Exports\CsvFilesExport.php`, edit the following function's chunk return size:
```
public function chunkSize(): int
{
    return 1000;
}
```
Or add new class variables ($timeout and $retryAfter):
```
public Carbon $jobTimestamp;
public $jobId;
public $timeout = 1000; // New
public $retryAfter = 1200; // New
```

## Race Condition Solution
Whenever a new CSV file is uploaded, the datetime of the Job created is recorded in the 'created_at' field.
The upsert of Products are determined by this datetime, where the more recent Job is considered as newer/final.
Each Product will be updated if its 'updated_at' field is older than the Job, and overwritten by the 'created_at' from the Job.

This means that no matter when the Jobs are running, each Product will always be from the latest/newest CSV file uploaded which contain the Product row data.

## Log Events
There are a few outputs to `storage\logs\laravel.log` when running Jobs
1) csvFileId: X job processing.
2) productId: X updated.
3) csvFileId: X, row number: X (first row of every chunk only)
4) csvFileId: X job success.
5) csvFileId: X job attempt failed.
6) csvFileId: X job fully failed.
