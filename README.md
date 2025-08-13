# Swag Labs — Behat + Mink + Selenium Test Suite

This repository contains a **Behavior-Driven Development (BDD)** test suite for the [Swag Labs demo site](https://www.saucedemo.com), built using **PHP**, **Behat**, **Mink**, and **Selenium**.  
It automates key e-commerce flows including login, product sorting, and checkout.

## 📋 Features Covered

- **Login Functionality**
  - Invalid credentials
  - Locked-out user
  - Error message validation
- **Product Sorting**
  - Sort products by Name (Z → A)
  - Validate reverse alphabetical order
- **Checkout Process**
  - Add specific items to cart
  - Verify subtotal and tax calculations
  - Complete checkout and confirm order

---

## 🛠 Tech Stack

- [PHP](https://www.php.net/) 8+
- [Composer](https://getcomposer.org/)
- [Behat](https://behat.org/)
- [Mink](https://mink.behat.org/) with Selenium2 driver
- [Selenium Standalone Chrome](https://hub.docker.com/r/selenium/standalone-chrome)

---

## 🚀 Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/4ayushsinghal/swaglabs-behat-tests.git
cd swaglabs-behat-tests
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Start Selenium Standalone server for chrome

Options (any one):

#### A. Using Docker:

```bash
docker run -d -p 4444:4444 --shm-size 2g --name selenium-server selenium/standalone-chrome:4.21.0
export WD_HOST=http://127.0.0.1:4444/wd/hub
```

#### B. Running it locally (Install chromedriver for that):

Install Chrome Driver (ignore if already installed)

```bash
brew install chromedriver
```

```bash
export WD_HOST=http://127.0.0.1:4444
chromedriver --port=4444 &
```

        OR

You can use nohup to run it in the background

```bash
export WD_HOST=http://127.0.0.1:4444
nohup chromedriver --port=4444 &
```

> ⚠️ For headed mode, remove --headless from Chrome options in behat.yml.

### 4. Run Tests

```bash
composer test
```

### 5. Open the HTML report

```bash
open ./build/html/index.html
```

## 📁 Project Structure

```pgsql
swaglabs-behat-tests/
├── composer.json
├── behat.yml
├── features/
│   ├── login.feature
│   ├── sorting.feature
│   ├── checkout.feature
│   └── bootstrap/
│       └── FeatureContext.php
└── README.md
```

## ⚙ Configuration

- Base URL: https://www.saucedemo.com (set in behat.yml)

- Browser: Chrome via Selenium

- Credentials (from SauceDemo docs):

- Standard user: standard_user / secret_sauce

- Locked-out user: locked_out_user / secret_sauce

## 🧪 Example Test Command

Run only login scenarios:

```bash
vendor/bin/behat features/login.feature
```
