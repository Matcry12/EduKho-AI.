# Hướng dẫn Deploy EduKho-AI lên nền tảng miễn phí

> Hướng dẫn triển khai ứng dụng **Quản lý Thiết bị Dạy học** (Laravel 11 + MySQL) lên các nền tảng hosting miễn phí.

---

## Mục lục

- [Tổng quan](#tổng-quan)
- [Chuẩn bị trước khi deploy](#chuẩn-bị-trước-khi-deploy)
- [Option 1: Railway (Khuyến nghị)](#option-1-railway-khuyến-nghị)
- [Option 2: Render](#option-2-render)
- [Option 3: Vercel + PlanetScale/Neon](#option-3-vercel)
- [Option 4: Fly.io](#option-4-flyio)
- [Database miễn phí](#database-miễn-phí)
- [Cấu hình sau deploy](#cấu-hình-sau-deploy)
- [Xử lý lỗi thường gặp](#xử-lý-lỗi-thường-gặp)

---

## Tổng quan

| Nền tảng | Database | Độ khó | Free Tier | Phù hợp nhất |
|----------|----------|--------|-----------|---------------|
| **Railway** | MySQL tích hợp | Dễ | $5 credit/tháng | Deploy nhanh, all-in-one |
| **Render** | PostgreSQL tích hợp | Trung bình | Web service miễn phí | Ổn định lâu dài |
| **Vercel** | Cần DB ngoài | Khó | Serverless miễn phí | Đã quen Vercel |
| **Fly.io** | Cần DB ngoài | Trung bình | 3 VM miễn phí | Cần kiểm soát server |

**Lưu ý:** EduKho-AI là ứng dụng Laravel (PHP), nên **Railway** hoặc **Render** là lựa chọn tốt nhất. Vercel chủ yếu hỗ trợ Node.js/Python, deploy PHP phức tạp hơn.

---

## Chuẩn bị trước khi deploy

### 1. Đảm bảo code sẵn sàng

```bash
# Build frontend assets
npm install
npm run build

# Kiểm tra không có lỗi
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Tạo file Procfile (cho Railway/Render)

Tạo file `Procfile` ở thư mục gốc:

```
web: vendor/bin/heroku-php-apache2 public/
```

### 3. Đảm bảo có file `.env.example` đầy đủ

File `.env.example` trong project đã có sẵn các biến cần thiết.

### 4. Thêm file `nixpacks.toml` (cho Railway)

Tạo file `nixpacks.toml` ở thư mục gốc:

```toml
[phases.setup]
nixPkgs = ["php82", "php82Extensions.pdo_mysql", "php82Extensions.mbstring", "php82Extensions.xml", "php82Extensions.curl", "php82Extensions.zip", "php82Extensions.gd", "nodejs_18", "npm-9_x"]

[phases.install]
cmds = ["composer install --no-dev --optimize-autoloader", "npm install"]

[phases.build]
cmds = ["npm run build", "php artisan config:cache", "php artisan route:cache", "php artisan view:cache"]

[start]
cmd = "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"
```

---

## Option 1: Railway (Khuyến nghị)

Railway là lựa chọn tốt nhất cho Laravel vì hỗ trợ PHP native và có MySQL tích hợp.

### Bước 1: Tạo tài khoản Railway

1. Truy cập [railway.app](https://railway.app)
2. Đăng ký bằng GitHub
3. Bạn nhận được **$5 credit miễn phí mỗi tháng** (đủ cho project nhỏ)

### Bước 2: Tạo project mới

1. Click **"New Project"**
2. Chọn **"Deploy from GitHub Repo"**
3. Kết nối và chọn repository EduKho-AI

### Bước 3: Thêm MySQL Database

1. Trong project, click **"+ New"** → **"Database"** → **"MySQL"**
2. Railway tự động tạo database và cung cấp connection string
3. Click vào MySQL service → tab **"Variables"** để xem thông tin kết nối:
   - `MYSQL_HOST`
   - `MYSQL_PORT`
   - `MYSQL_DATABASE`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`

### Bước 4: Cấu hình Environment Variables

Click vào web service → tab **"Variables"** → thêm các biến:

```env
APP_NAME="Quan ly Thiet bi Day hoc"
APP_ENV=production
APP_KEY=base64:... (tạo bằng lệnh php artisan key:generate --show)
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app
APP_TIMEZONE=Asia/Ho_Chi_Minh
APP_LOCALE=vi

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-1.5-flash
GEMINI_TIMEOUT=15

LOG_CHANNEL=stderr
```

> **Mẹo:** Dùng cú pháp `${{MySQL.MYSQL_HOST}}` để Railway tự động liên kết biến từ MySQL service.

### Bước 5: Tạo APP_KEY

Chạy lệnh trên máy local:

```bash
php artisan key:generate --show
```

Copy kết quả (dạng `base64:xxxxxxx...`) và paste vào biến `APP_KEY` trên Railway.

### Bước 6: Deploy

Railway tự động deploy khi bạn push code lên GitHub. Kiểm tra logs để đảm bảo:

1. Migration chạy thành công
2. Không có lỗi kết nối database
3. App khởi động trên đúng port

### Bước 7: Chạy Seeder (nếu cần)

Trong Railway, vào tab **"Settings"** → **"Railway CLI"** hoặc dùng Railway CLI:

```bash
# Cài Railway CLI
npm install -g @railway/cli

# Login
railway login

# Link project
railway link

# Chạy migration và seeder
railway run php artisan migrate --force
railway run php artisan db:seed --force
```

---

## Option 2: Render

### Bước 1: Tạo tài khoản

1. Truy cập [render.com](https://render.com)
2. Đăng ký bằng GitHub

### Bước 2: Tạo PostgreSQL Database

1. Dashboard → **"New"** → **"PostgreSQL"**
2. Chọn **Free** plan
3. Đặt tên: `edukho-db`
4. Chọn Region gần nhất
5. Lưu lại **Internal Database URL**

> **Lưu ý:** Render free tier chỉ có PostgreSQL. EduKho-AI hỗ trợ PostgreSQL (đã có trong `config/database.php`). Bạn cần đổi `DB_CONNECTION=pgsql`.

### Bước 3: Tạo file `render.yaml` (Blueprint)

Tạo file `render.yaml` ở thư mục gốc:

```yaml
services:
  - type: web
    name: edukho-ai
    runtime: php
    buildCommand: |
      composer install --no-dev --optimize-autoloader
      npm install
      npm run build
      php artisan config:cache
      php artisan route:cache
      php artisan view:cache
    startCommand: |
      php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
    envVars:
      - key: APP_NAME
        value: "Quan ly Thiet bi Day hoc"
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: "false"
      - key: APP_KEY
        generateValue: true
      - key: APP_TIMEZONE
        value: Asia/Ho_Chi_Minh
      - key: APP_LOCALE
        value: vi
      - key: DB_CONNECTION
        value: pgsql
      - key: DATABASE_URL
        fromDatabase:
          name: edukho-db
          property: connectionString
      - key: SESSION_DRIVER
        value: database
      - key: CACHE_STORE
        value: database
      - key: LOG_CHANNEL
        value: stderr

databases:
  - name: edukho-db
    plan: free
```

### Bước 4: Deploy

1. Dashboard → **"New"** → **"Blueprint"**
2. Kết nối GitHub repo
3. Render tự động đọc `render.yaml` và deploy

### Bước 5: Thêm biến môi trường

Vào service **edukho-ai** → **"Environment"** → thêm:

```env
GEMINI_API_KEY=your_key_here
APP_KEY=base64:... (tạo local bằng php artisan key:generate --show)
APP_URL=https://edukho-ai.onrender.com
```

---

## Option 3: Vercel

> **Cảnh báo:** Vercel không hỗ trợ PHP native. Cần dùng package `vercel-php` (community). Đây là option phức tạp nhất.

### Bước 1: Cài đặt Vercel CLI

```bash
npm install -g vercel
```

### Bước 2: Tạo file `vercel.json`

```json
{
  "version": 2,
  "framework": null,
  "functions": {
    "api/*.php": {
      "runtime": "vercel-php@0.7.2"
    }
  },
  "routes": [
    {
      "src": "/(css|js|images|fonts|favicon\\.ico)(.*)",
      "dest": "/public/$1$2"
    },
    {
      "src": "/(.*)",
      "dest": "/api/index.php"
    }
  ],
  "outputDirectory": "public"
}
```

### Bước 3: Tạo API entry point

Tạo file `api/index.php`:

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
```

### Bước 4: Cấu hình Database cho Vercel

Vì Vercel là serverless, bạn cần database bên ngoài:

**Option A: Neon (PostgreSQL miễn phí)**
1. Đăng ký tại [neon.tech](https://neon.tech)
2. Tạo database mới
3. Copy connection string

**Option B: TiDB Cloud (MySQL miễn phí)**
1. Đăng ký tại [tidbcloud.com](https://tidbcloud.com)
2. Tạo Serverless cluster (miễn phí)
3. Copy connection details

### Bước 5: Thêm Environment Variables trên Vercel

```bash
vercel env add APP_KEY
vercel env add DB_CONNECTION
vercel env add DB_HOST
vercel env add DB_PORT
vercel env add DB_DATABASE
vercel env add DB_USERNAME
vercel env add DB_PASSWORD
vercel env add GEMINI_API_KEY
```

### Bước 6: Deploy

```bash
vercel --prod
```

> **Hạn chế Vercel với Laravel:**
> - Serverless = không có persistent storage (file upload cần dùng S3/Cloudinary)
> - Cold start chậm hơn
> - Không hỗ trợ queue worker
> - Session driver nên dùng `database` hoặc `cookie`

---

## Option 4: Fly.io

### Bước 1: Cài Fly CLI

```bash
curl -L https://fly.io/install.sh | sh
fly auth signup
```

### Bước 2: Tạo Dockerfile

Tạo `Dockerfile` ở thư mục gốc:

```dockerfile
FROM php:8.2-apache

# Cài extensions
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cài Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Cấu hình Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Copy source
WORKDIR /var/www/html
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

CMD php artisan migrate --force && apache2-foreground
```

### Bước 3: Tạo file `fly.toml`

```toml
app = "edukho-ai"
primary_region = "sin"  # Singapore - gần Việt Nam nhất

[build]

[http_service]
  internal_port = 8080
  force_https = true
  auto_stop_machines = true
  auto_start_machines = true
  min_machines_running = 0

[env]
  APP_NAME = "Quan ly Thiet bi Day hoc"
  APP_ENV = "production"
  APP_DEBUG = "false"
  APP_TIMEZONE = "Asia/Ho_Chi_Minh"
  APP_LOCALE = "vi"
  DB_CONNECTION = "mysql"
  SESSION_DRIVER = "database"
  CACHE_STORE = "database"
  LOG_CHANNEL = "stderr"
```

### Bước 4: Tạo app và set secrets

```bash
fly launch

# Set secrets (environment variables nhạy cảm)
fly secrets set APP_KEY=$(php artisan key:generate --show)
fly secrets set DB_HOST=your_db_host
fly secrets set DB_PORT=3306
fly secrets set DB_DATABASE=your_db_name
fly secrets set DB_USERNAME=your_db_user
fly secrets set DB_PASSWORD=your_db_password
fly secrets set GEMINI_API_KEY=your_gemini_key
fly secrets set APP_URL=https://edukho-ai.fly.dev
```

### Bước 5: Deploy

```bash
fly deploy
```

---

## Database miễn phí

### MySQL

| Dịch vụ | Free Tier | Giới hạn |
|---------|-----------|----------|
| **TiDB Cloud Serverless** | Miễn phí vĩnh viễn | 5GB storage, 50M Request Units/tháng |
| **Railway MySQL** | $5 credit/tháng | Chia sẻ với app |
| **PlanetScale** | Đã ngừng free tier | - |
| **Aiven MySQL** | Miễn phí | 1GB storage |

### PostgreSQL (thay thế MySQL)

| Dịch vụ | Free Tier | Giới hạn |
|---------|-----------|----------|
| **Neon** | Miễn phí vĩnh viễn | 0.5GB storage, auto-suspend |
| **Supabase** | Miễn phí | 500MB, pause sau 7 ngày không dùng |
| **Render PostgreSQL** | Miễn phí 90 ngày | 1GB storage |
| **ElephantSQL** | Miễn phí | 20MB (quá nhỏ) |

### Cấu hình PostgreSQL cho EduKho-AI

Nếu dùng PostgreSQL thay MySQL, đổi trong `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=your_pg_host
DB_PORT=5432
DB_DATABASE=your_db_name
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

> App đã hỗ trợ PostgreSQL trong `config/database.php`. Hầu hết migration tương thích. Nếu gặp lỗi `enum` type, thay bằng `string` với validation.

### Khuyến nghị: TiDB Cloud (MySQL tương thích)

1. Đăng ký tại [tidbcloud.com](https://tidbcloud.com)
2. Tạo **Serverless Cluster** → chọn region gần nhất
3. Tạo database: `equipment_management`
4. Lấy connection string từ **"Connect"** → **"General"**
5. Cấu hình trong `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_DATABASE=equipment_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
```

---

## Cấu hình sau deploy

### 1. Chạy Migration

```bash
# Railway
railway run php artisan migrate --force

# Render (dùng Shell tab)
php artisan migrate --force

# Fly.io
fly ssh console -C "php artisan migrate --force"
```

### 2. Chạy Seeder (tạo dữ liệu mẫu)

```bash
php artisan db:seed --force
```

### 3. Tạo tài khoản Admin

```bash
php artisan tinker
```

```php
use App\Models\User;

User::create([
    'name' => 'Admin',
    'email' => 'admin@truong.edu.vn',
    'password' => bcrypt('your_secure_password'),
    'role' => 'admin',
    'department_id' => 1,
    'is_active' => true,
]);
```

### 4. Cấu hình GEMINI API Key

1. Truy cập [aistudio.google.com](https://aistudio.google.com)
2. Tạo API Key miễn phí
3. Thêm vào environment variable: `GEMINI_API_KEY=your_key`

### 5. Cấu hình Mail (tùy chọn)

Dùng **Mailtrap** (miễn phí) cho testing hoặc **Resend** cho production:

```env
# Resend (miễn phí 3000 email/tháng)
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_USERNAME=resend
MAIL_PASSWORD=your_resend_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### 6. File Storage (nếu có upload)

Trên serverless/container, file system không persistent. Dùng **Cloudinary** (miễn phí):

```env
FILESYSTEM_DISK=cloudinary
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
```

---

## Xử lý lỗi thường gặp

### Lỗi: "No application encryption key has been specified"

```bash
php artisan key:generate --show
# Copy kết quả vào biến APP_KEY
```

### Lỗi: "SQLSTATE[HY000] [2002] Connection refused"

- Kiểm tra `DB_HOST`, `DB_PORT` đúng chưa
- Nếu dùng Railway, dùng cú pháp `${{MySQL.MYSQL_HOST}}`
- Kiểm tra database service đang chạy

### Lỗi: "The stream or file storage/logs/laravel.log could not be opened"

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Hoặc đổi log channel sang stderr:
```env
LOG_CHANNEL=stderr
```

### Lỗi: "Vite manifest not found"

Assets chưa được build. Thêm vào build command:

```bash
npm install && npm run build
```

### Lỗi: Migration fail với PostgreSQL

Một số kiểu `enum` trong MySQL không tương thích trực tiếp với PostgreSQL. Sửa migration:

```php
// Thay
$table->enum('status', ['pending', 'approved', 'rejected']);

// Bằng
$table->string('status')->default('pending');
```

### Lỗi: "Class maatwebsite/excel not found"

Package Excel export cần thêm extension:

```bash
# Thêm vào nixpacks.toml hoặc Dockerfile
php82Extensions.zip
php82Extensions.gd
```

---

## So sánh chi phí hàng tháng

| Cấu hình | Chi phí | Ghi chú |
|-----------|---------|---------|
| Railway (App + MySQL) | $0 - $5 | Trong free credit |
| Render + Neon | $0 | Free tier cả hai |
| Fly.io + TiDB Cloud | $0 | Free tier cả hai |
| Vercel + Neon | $0 | Phức tạp nhất |

---

## Khuyến nghị cuối cùng

Cho project EduKho-AI, thứ tự ưu tiên:

1. **Railway** - Dễ nhất, hỗ trợ PHP + MySQL native, deploy trong 5 phút
2. **Render + Neon** - Miễn phí hoàn toàn, ổn định, nhưng cần dùng PostgreSQL
3. **Fly.io + TiDB Cloud** - Linh hoạt nhất, cần biết Docker
4. **Vercel** - Chỉ nên dùng nếu đã quen, nhiều hạn chế với PHP/Laravel

> **Tip:** Cho cuộc thi "Đổi mới sáng tạo với AI trong dạy học 2025-2026", Railway là lựa chọn nhanh nhất để demo. Nếu cần chạy lâu dài miễn phí, dùng Render + Neon.
