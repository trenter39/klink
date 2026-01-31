# Klink

Klink is a web-application for accounting for employees in production and managing their tasks. The project was developed as a part of university work. The application was implemented using **PHP**, **MySQL**, classic server architecture and secure development principles.

## Setup Instructions

Clone the repository:
```
git clone https://github.com/trenter39/klink.git
cd klink
```

### Local setup with XAMPP

1. Create `klink` database in **MySQL**.
```
create database klink;
```

2. Set up the database by running the `schema.sql` schema file.
```
mysql -u root -p klink < schema.sql
```

3. Configure connection to **MySQL** by creating `.env` file in the root folder. `.env` file must contain fields (example with default values):
```
DB_HOST=localhost
DB_USER=root
DB_PASS=password
DB_NAME=klink
```

4. Move content to `htdocs` and start server with XAMPP.

5. Now you can visit website via `http://localhost`.

### Docker Setup (Recommended)

1. For a quick out-of-the-box experience without manual database setup, run with Docker Compose:
```
docker-compose up --build
```

2. Now you can visit website via `http://localhost:8080`.

![klink preview](./media/dashboard_preview_admin.png)