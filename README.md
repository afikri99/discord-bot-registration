# 🤖 Discord Bot Registration System

Sistem registrasi dan verifikasi member berbasis Discord Bot yang dibangun dengan **Laravel 12 + DiscordPHP**.

---

## ✨ Fitur
- 🔐 Verifikasi akun Discord secara otomatis
- ✅ Registrasi member mandiri melalui slash command
- 📊 Penyimpanan data user terintegrasi database
- ⚡ Bot berjalan sebagai worker background Laravel
- 🛡️ Role assignment otomatis setelah verifikasi berhasil
- 📝 Log aktivitas lengkap

---

## 🚀 Persyaratan Sistem
| Software | Versi Minimum |
|---|---|
| PHP | `8.2+` |
| Composer | `2.5+` |
| Node.js | `18.0+` |
| SQLite / MySQL | - |

---

## ⚙️ Instalasi

1.  **Clone / Download project**
    ```bash
    git clone <repository-url>
    cd discord-bot-registration
    ```

2.  **Install dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Setup Environment**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Konfigurasi Discord Bot**
    Buka file `.env` lalu isi variabel berikut:
    ```env
    DISCORD_BOT_TOKEN=your_bot_token_here
    DISCORD_GUILD_ID=your_server_id_here
    DISCORD_VERIFIED_ROLE_ID=role_id_after_verify
    ```

5.  **Jalankan migrasi database**
    ```bash
    php artisan migrate
    ```

---

## ▶️ Menjalankan Bot

Untuk menjalankan bot discord:
```bash
php artisan discord:run
```

> 💡 Untuk production gunakan Supervisor / Systemd agar bot berjalan otomatis sebagai service background

---

## 🛠️ Development
Untuk menjalankan dev server web + vite:
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

---

## 📌 Command Bot Tersedia
| Command | Keterangan |
|---|---|
| `/daftar` | Memulai proses registrasi member |
| `/verify` | Verifikasi akun yang sudah terdaftar |
| `/profil` | Melihat profil data user sendiri |
| `/help` | Menampilkan panduan perintah |

---

## 📂 Struktur Project Penting
```
discord-bot-registration/
├── app/
│   ├── Console/Commands/DiscordBot.php  # Entry point bot
│   ├── Discord/                          # Semua logika handler bot
│   ├── Models/User.php                   # Model anggota
│   └── Http/Controllers/
├── database/migrations/                  # Struktur tabel
└── routes/
    └── console.php                       # Register command artisan
```

---

## 📝 Catatan Penting
1.  **Bot Token** bisa dibuat di [Discord Developer Portal](https://discord.com/developers/applications)
2.  Pastikan bot sudah di invite ke server dengan `applications.commands` scope
3.  Berikan permission `Manage Roles` kepada bot untuk fitur assign role
4.  Jangan commit file `.env` ke repository

---

## 📜 Lisensi
Project ini menggunakan lisensi **MIT** sama seperti framework Laravel.
```