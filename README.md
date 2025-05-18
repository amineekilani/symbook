# 📚 SymBook – Full-Featured E-commerce Bookstore

SymBook is a complete e-commerce web application for buying and selling books online, developed as part of an academic project. It supports user-friendly book discovery, secure shopping features, and a powerful admin dashboard to manage books, users, and orders.

> ✅ All core and bonus functionalities have been successfully implemented.

---

## 🧰 Tech Stack

- **Backend**: Symfony 5.10
- **Frontend**: Twig + Tailwind CSS
- **Database**: MySQL
- **Email Service**: Brevo
- **Payment Integration**: Stripe
- **Data Visualization**: Google Charts
- **Containerization**: Docker
- **Authentication**: Native + OAuth2 (Google Login)
- **Search Optimization**: AI-powered search

---

## ✨ Features

### 👤 User Features

- **Registration with Email Verification**
- **Login / Logout / Secure Sessions**
- **Password Reset via Email**
- **Browse Books with Images**
- **Search by Title, Author, Category**
- **View Book Details (Price, Description, etc.)**
- **Add to Cart & Update Quantities**
- **Place Orders**
- **Secure Online Payment (Stripe)**
- **Order History**
- **Order Confirmation by Email**

---

### 🛠️ Admin Features

- **Book Management (Add, Edit, Delete)**
- **Category Management**
- **User Management (View, Edit, Delete Users)**
- **Order Management (Pending, Processing, Completed)**
- **Interactive Dashboard with Charts**:
  - Top Selling Books
  - Order Trends over Time

---

### 🚀 Bonus Features

- ✅ Dockerized Deployment (`Dockerfile` and `docker-compose.yml`)
- ✅ OAuth2 Authentication (Google)
- ✅ AI-enhanced Search Experience

---

## 🧪 Installation & Usage

### 🐳 Run with Docker

```bash
git clone https://github.com/amineekilani/symbook
cd symbook
docker-compose up --build
```

### 🛠️ Manual Setup (Run with Docker)

1. Clone the repo:

```bash
git clone https://github.com/amineekilani/symbook
cd symbook
```

2. Install dependencies:

```bash
composer install
```

3. Configure `.env` for DB, email & Stripe keys. 

4. Run migrations and seed data:

```bash
php bin/console doctrine:migrations:migrate
```

5. Start local server:

```bash
symfony serve -d
```

---

## 📅 Project Info

- **Instructor**: Mrs. [Nidhal Cherif](https://github.com/NidhalCherif) 
- **Academic Year**: 2024–2025

---

## 📜 License

This project is for educational purposes and is not licensed for commercial use.

---

## 🤝 Contributing

Pull requests and feedback are welcome!  
Fork the repo, create a feature branch, and submit a pull request.

---

## 📬 Contact

For inquiries, you can reach us at [aminekilani@rades.r-iset.tn](mailto:aminekilani@rades.r-iset.tn) or [ghadaazizi2023@gmail.com](mailto:ghadaazizi2023@gmail.com).