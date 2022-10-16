# Image Uploader

Image Uploader created in PHP.

## Setup with XAMPP on Windows 11

* Copy files to "C:\xampp\htdocs"
* Start XAMPP Apache and MySQL
* Open "http://localhost/phpmyadmin" in your browser
* Create a new database "imageuploader" and import "sql/imageuploader.sql"
* Insert a new user in users table

## Features

* Easy to install with XAMPP
* Create, edit and save albums to MySQL database
* Upload images
* Users accounts

## Bootstrap

https://github.com/twbs/bootstrap

## Help

If you get this error at uploading files: <em>Fatal error: Uncaught mysqli_sql_exception: Got a packet bigger than 'max_allowed_packet' bytes</em>

You can run these commands in phpMyAdmin SQL:
```
set global net_buffer_length=1000000;
set global max_allowed_packet=1000000000;
```