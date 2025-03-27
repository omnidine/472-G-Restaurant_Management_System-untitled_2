<h1 align="center">OmniDine Restaurant (Back-end)</h1>
<p align="center">
    <b>Project ภาคปลาย 2568 ประจำรายวิชา CS441/CS472</b> <br>
    <i>An open-source,</i> Restaurant Front and Management Program.<br>
    <br>
    <a href="#-about">❓ About</a>‎ ‎ |‎ ‎ 
    <a href="#-team-members">👥 Team Members</a>‎ ‎ |‎ ‎ 
    <a href="#%EF%B8%8F-installation">⚙️ Installation</a>‎ ‎ |‎ ‎ 
    <a href="#-sample-user-data">📋 Sample Data</a>‎ ‎ |‎ ‎ 
    <a href="#%EF%B8%8F-release-tag">🏷️ Release Tag</a>
</p>

---

## ❓ About
โครงงานชิ้นนี้เป็นโครงงานที่ทำควบกันระหว่างรายวิชา Web Technology (CS441) และ Agile and DevOps (CS472) เป็นระบบร้านอาหาร และระบบสำหรับการจัดการร้านอาหารที่มีชื่อว่า OmniDine

โดยลูกค้าที่สนใจเข้ามาใช้บริการร้านอาหารสามารถกดจองโต๊ะ และสามารถเลือกสั่งอาหารได้ผ่านทางหน้าเว็บ อีกทั้งมีการเก็บประวัติออเดอร์ (พร้อมรายละเอียดอาหารที่สั่ง) และการจองโต๊ะสำหรับผู้ใช้งาน พร้อมระบบหลังบ้านในการจัดการ Stock และ Order status สำหรับพนักงานประจำร้าน และระบบการเพิ่มเมนูอาหารสำหรับเจ้าของร้าน

## 👥 Team Members
- 6510451085 อภิสิทธิ์ ประเสริฐเวศยากร (<a href="https://github.com/Professors001">Professors001</a>) Feat: Reservation

- 6510405610 นพปฎล ฐานะปฏิพล (<a href="https://github.com/n-thnptp">n-thnptp</a>) Feat: Menu, Order

- 6510405733 ภาพตะวัน สุขุม (<a href="https://github.com/fahh11">fahh11</a>) Feat: Staff Menu Edit

- 6510405776 วรพา จันทร์กลาง (<a href="https://github.com/worapha05">worapha05</a>) Feat: None (Product Owner)

## ⚙️ Installation
### Requirements
- Windows 10 or Windows 11 (Latest Version) with Hyper-V function enabled
- <a href="https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe?utm_source=docker&utm_medium=webreferral&utm_campaign=dd-smartbutton&utm_location=module">Docker Desktop</a>
- <a href="https://ubuntu.com/desktop/wsl">WSL Ubuntu (WSL2)</a> (For Windows)

1. Clone the project

```bash
git clone https://github.com/Professors001/omnidine-backend
cd omnidine-backend
```

2. Copy .env.example file and replace with the following content:
```bash
cp .env.example .env
```

Replace with:
```env
# set application timezone to match Thailand's timezone
APP_TIMEZONE=Asia/Bangkok
```

```env
# Configure application language settings
# APP_LOCALE: set primary language to Thai
# APP_FALLBACK_LOCALE: use Thai as fallback language if primary fails
# APP_FAKER_LOCALE: use Thai locale for generating fake data
APP_LOCALE=th
APP_FALLBACK_LOCALE=th
APP_FAKER_LOCALE=th_TH
```

```env
# defining parameters for database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=omnidine
DB_USERNAME={ CHANGE THIS }
DB_PASSWORD={ CHANGE THIS }
```

3. Executing Docker Command
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

4. Run Sail Commands
```bash
sail up -d
sail artisan key:generate
sail artisan migrate:fresh --seed
```

5. Use <a href="https://www.postman.com/downloads/">Postman</a> or other programs to verify if back-end is running through the following API endpoint using GET method:
```
http://localhost/api

# OR enter this into browser to access phpMyAdmin page
http://localhost:8090/
```

## 📋 Sample User Data
<pre>
admin@admin.com : 123       # admin account
staff@staff.com : 123       # staff account
user@user.com : 123         # user account
</pre>

## 🏷️ Release Tag
- Release Tag ที่เสร็จสมบูรณ์และใช้ในการนำเสนอ: <a href="https://github.com/omnidine/omnidine-backend/releases/tag/1.0.0">1.0.0</a>