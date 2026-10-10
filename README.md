<p align="center">
  <img src="https://raw.githubusercontent.com/rootxnaila/fahrimart/main/docs/branding/fahrimart-logo.png" width="180" alt="Fahrimart Logo">
</p>

<h1 align="center">Fahrimart</h1>

<p align="center">
  <strong>Smart Retail. Simple Management.</strong><br>
  Web-based Point of Sales (POS) & Inventory Management System
</p>

<p align="center">
  A retail management system designed to simplify sales transactions and inventory operations.
</p>

---

## About

**Fahrimart** is a web-based Point of Sales (POS) and Inventory Management System developed for retail store operations.

The system aims to integrate sales transactions, product and customer management, stock purchasing, damaged-item recording, inventory monitoring, and sales reporting into one centralized application.

## Features

- Authentication and role-based access control
- Point of Sale and payment processing
- Product, supplier, and customer management
- Stock purchasing and inventory monitoring
- Damaged-item recording
- Sales reports and PDF export
- Transaction receipt generation
- User management by the Store Owner

## User Roles

| Role | Access |
|---|---|
| **Owner** | Full system access, including user management |
| **Admin** | Operational access, excluding user registration |
| **Cashier** | Sales transactions, payments, and receipts |

## Tech Stack

| Component | Technology |
|---|---|
| Backend Framework | Laravel 10.50.3 |
| Programming Language | PHP 8.3+ |
| Template Engine | Laravel Blade |
| UI Framework | Bootstrap 5 |
| Frontend Scripting | JavaScript |
| Asset Bundler | Vite |
| Database | MariaDB |
| Version Control | Git & GitHub |
| UI/UX Design | Figma |
| Testing Tools | Postman |

## Database Design

Fahrimart uses a relational database with the following core entities:

- `users` — user accounts and access roles
- `barang` — product and inventory data
- `supplier` — supplier information
- `transaksi` — sales transaction headers
- `detail_transaksi` — items within each transaction
- `pembelian_stok` — stock purchases
- `retur_rusak` — damaged-item records

The database design incorporates triggers for automatic stock updates, stored procedures for business operations, and views for sales reporting.

### Inventory Automation

| Operation | Stock Effect |
|---|---|
| Successful sales | Stock decreases |
| Stock purchases | Stock increases |
| Damaged-item recording | Stock decreases |

## System Architecture

Fahrimart follows a monolithic web application architecture.

```text
Users
  |
  v
Laravel Application
  |
  +-- Authentication & Authorization
  +-- Controllers & Business Logic
  +-- Laravel Blade Interface
  |
  v
MariaDB Database
  |
  +-- Relational Tables
  +-- Stored Procedures
  +-- Database Triggers
  +-- Reporting Views
```

## Core Business Flow

### Sales Transaction

```text
Cashier
   |
   v
Select Products
   |
   v
Shopping Cart
   |
   v
Payment Processing
   |
   v
Transaction & Details
   |
   v
Database Trigger
   |
   v
Stock Automatically Decreases
   |
   v
Receipt Generation
```

### Stock Purchase

```text
Admin / Owner
   |
   v
Select Supplier and Products
   |
   v
Record Stock Purchase
   |
   v
Database Trigger
   |
   v
Stock Automatically Increases
```

### Damaged Items

```text
Admin / Owner
   |
   v
Record Damaged Items
   |
   v
Database Trigger
   |
   v
Stock Automatically Decreases
```

## Project Documentation

Project documentation covers:

- Software Requirements Specification (SKPL)
- Use Case Diagram
- Data Flow Diagram (DFD)
- Class Diagram
- Activity Diagram
- Sequence Diagram
- Database Design
- UI/UX Prototype
- Software Testing Documentation

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js and npm
- MariaDB
- Git

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/rootxnaila/fahrimart.git
cd fahrimart
```

**2. Install dependencies**

```bash
composer install
npm install
```

**3. Configure the environment**

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

On Linux or macOS:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

**4. Configure the database**

Update the database settings in `.env` according to your local MariaDB configuration.

Before running migrations, make sure the database setup is compatible with the existing Fahrimart schema.

**5. Run the application**

```bash
php artisan serve
```

In a separate terminal, start the frontend development server:

```bash
npm run dev
```

Open the local URL displayed by Laravel in your browser.

## Development Team

| Name | Role |
|---|---|
| Naila Rizki Amalya | Project Lead, System Analyst & Full Stack Developer |
| Silfhana Dwi Aqqilla | Software Tester / Quality Assurance |
| Sayyid Sabiq Muhammad | UI/UX Designer |
| Wahyu Saputra | Database Engineer & System Support |

## Academic Project

**Undergraduate Informatics Engineering Program**  
Universitas Negeri Malang  
Department of Electrical Engineering and Informatics

---

<p align="center">
  <em>Developed for academic purposes.</em>
</p>