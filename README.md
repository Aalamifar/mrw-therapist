# MRW Therapist

یک پلتفرم مدیریت درمانی و آموزشی مبتنی بر نقش که با استفاده از Laravel، Livewire و Vite ساخته شده است. این سیستم از سه نقش کاربری پشتیبانی می‌کند: مدیر (admin)، درمانگر (therapist) و دانش‌آموز (student)، و مدیریت کلاس، ارائه درس، تکالیف، آزمون‌ها، پیام‌رسانی و گزارش‌گیری را پوشش می‌دهد.

## معرفی کلی

این پروژه به مدیریت یک محیط درمانی و آموزشی کمک می‌کند که در آن:

- مدیران، کاربران و کلاس‌ها را مدیریت می‌کنند
- درمانگران بر کلاس‌ها، درس‌ها، تکالیف، آزمون‌ها و ارسال‌های دانش‌آموزان نظارت دارند
- دانش‌آموزان به محتوای آموزشی، تقویم، تکالیف، آزمون‌ها و پیام‌رسانی خود دسترسی دارند

## ویژگی‌ها

- کنترل دسترسی مبتنی بر نقش برای مدیر، درمانگر و دانش‌آموز
- ایجاد کلاس و مدیریت ثبت‌نام دانش‌آموزان
- مدیریت درس‌ها همراه با دسترسی به فایل و بازی‌های آموزشی
- ایجاد تکلیف و پیگیری ارسال‌های دانش‌آموزان
- مدیریت آزمون و سؤالات همراه با مدیریت فایل‌های پاسخ
- پیگیری و گزارش‌گیری نتایج آزمون
- پیام‌رسانی خصوصی و پشتیبانی از مکالمات
- داشبورد دانش‌آموز و صفحات نمای کلی کلاس‌ها
- داشبورد درمانگر و صفحات نظارت بر کلاس‌ها
- پشتیبانی از تاریخ جلالی برای فرآیندهای مبتنی بر تقویم فارسی
- رابط کاربری تعاملی مبتنی بر Livewire

## پشته فناوری (Tech Stack)

- PHP 8.2
- Laravel 12
- Livewire 4 + Volt
- Vite
- Tailwind CSS
- پایگاه داده سازگار با Laravel (MySQL / PostgreSQL / SQLite)
- پکیج تاریخ جلالی (`morilog/jalali`)

## ساختار پروژه

- `app/Models` — مدل‌های برنامه مانند کاربران، کلاس‌ها، درس‌ها، تکالیف، آزمون‌ها و مکالمات
- `app/Http/Controllers` — کنترلرهای مربوط به دانلود فایل و اقدامات اختصاصی برنامه
- `resources/views` — ویوهای Blade و Livewire
- `routes/web.php` — تعریف مسیرها بر اساس نقش کاربر
- `database/migrations` — ساختار پایگاه داده و ساختار داده اولیه (seed)
- `tests/` — تست‌های خودکار

## نصب

1. کلون کردن مخزن (repository):

   ```bash
   git clone https://github.com/<your-username>/mrw-therapist.git
   cd mrw-therapist
   ```

2. نصب وابستگی‌های PHP:

   ```bash
   composer install
   ```

3. نصب وابستگی‌های فرانت‌اند:

   ```bash
   npm install
   ```

4. ساخت فایل محیطی (environment):

   ```bash
   cp .env.example .env
   ```

5. تولید کلید برنامه:

   ```bash
   php artisan key:generate
   ```

6. اجرای مایگریشن‌های پایگاه داده:

   ```bash
   php artisan migrate
   ```

7. ساخت فایل‌های فرانت‌اند:

   ```bash
   npm run build
   ```

## اجرای پروژه

می‌توانید برنامه را با اسکریپت پروژه اجرا کنید:

```bash
composer run dev
```

این دستور، برنامه Laravel و فرآیند توسعه فرانت‌اند را با هم اجرا می‌کند.

اگر می‌خواهید سرویس‌ها را به‌صورت دستی اجرا کنید:

```bash
php artisan serve
npm run dev
```

## نقش‌های پیش‌فرض

این برنامه بر اساس این نقش‌های کاربری سازمان‌دهی شده است:

- `admin`
- `therapist`
- `student`

گروه‌های مسیر (route groups) در پروژه، دسترسی به هر داشبورد و ماژول را تعریف می‌کنند.

---
<br>

# MRW Therapist

A role-based educational therapy management platform built with Laravel, Livewire, and Vite. The system supports three user roles: admin, therapist, and student, and covers class management, lesson delivery, assignments, exams, messaging, and reporting.

## Overview

This project helps manage a therapy and learning environment where:

- admins manage users and classes
- therapists oversee classes, lessons, assignments, exams, and submissions
- students access their academic content, calendar, assignments, exams, and messaging

## Features

- Role-based access control for admin, therapist, and student
- Class creation and student enrollment management
- Lesson management with file and game access
- Assignment creation and student submission tracking
- Exam and question management with answer file handling
- Exam result tracking and reporting
- Private messaging and conversation support
- Student dashboard and class overview pages
- Therapist dashboard and class monitoring pages
- Jalali date support for Persian calendar workflows
- Livewire-based interactive UI

## Tech Stack

- PHP 8.2
- Laravel 12
- Livewire 4 + Volt
- Vite
- Tailwind CSS
- MySQL / PostgreSQL / SQLite-compatible Laravel database
- Jalali date package (`morilog/jalali`)

## Project Structure

- `app/Models` — application models such as users, classes, lessons, assignments, exams, and conversations
- `app/Http/Controllers` — controllers for file downloads and app-specific actions
- `resources/views` — Blade and Livewire views
- `routes/web.php` — route definitions by user role
- `database/migrations` — database schema and seed structure
- `tests/` — automated tests

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/<your-username>/mrw-therapist.git
   cd mrw-therapist
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Install frontend dependencies:

   ```bash
   npm install
   ```

4. Create the environment file:

   ```bash
   cp .env.example .env
   ```

5. Generate the application key:

   ```bash
   php artisan key:generate
   ```

6. Run database migrations:

   ```bash
   php artisan migrate
   ```

7. Build frontend assets:

   ```bash
   npm run build
   ```

## Run the Project

You can start the app with the project script:

```bash
composer run dev
```

This runs the Laravel app and frontend development workflow together.

If you want to run services manually:

```bash
php artisan serve
npm run dev
```

## Default Roles

The app is organized around these user roles:

- `admin`
- `therapist`
- `student`

Route groups in the project define access to each dashboard and module.