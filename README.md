# PHPTeleBotWrt

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-blue.svg" alt="PHP">
  <img src="https://img.shields.io/badge/OpenWrt-21+-green.svg" alt="OpenWrt">
  <img src="https://img.shields.io/badge/Telegram-Bot API-blue.svg" alt="Telegram">
</p>

**PHPTeleBotWrt** is a powerful Telegram bot framework written in PHP designed specifically for OpenWRT routers. It provides a comprehensive set of commands to manage your router, monitor system resources, control network services, and automate various tasks directly from Telegram.

## ✨ Features

### 🔧 Bot Management
| Command | Description |
|---------|-------------|
| `/ping` | Check bot response time |
| `/botup` | Update PHPTeleBotWrt binaries |
| `/botas` | Toggle auto-start on boot |
| `/botcr` | Toggle cron job scheduling |

### 📁 File Manager
| Command | Description |
|---------|-------------|
| `/ul` | Upload files to OpenWrt |
| `/dl` | Download/retrieve files from OpenWrt |
| `/cp` | Copy files to another folder |
| `/mv` | Move files to another folder |
| `/rm` | Delete files |

### 🌐 OpenClash Control
| Command | Description |
|---------|-------------|
| `/oc` | OpenClash information |
| `/ocst` | Start/Restart OpenClash |
| `/ocsp` | Stop OpenClash |
| `/ocpr` | View proxy status |
| `/ocrl` | View rule lists |
| `/ocup` | Update OpenClash app only |
| `/ocua` | Update OpenClash and all cores |

### 📥 Aria2 Downloads
| Command | Description |
|---------|-------------|
| `/aria2add [url]` | Add download task |
| `/aria2stats` | View Aria2 status |
| `/aria2pause` | Pause all downloads |
| `/aria2resume` | Resume all downloads |

### 📊 System & Network
| Command | Description |
|---------|-------------|
| `/sysinfo` | Full system information |
| `/memory` | Memory usage status |
| `/netcl` | Network client list |
| `/vnstat` | Bandwidth statistics |
| `/vnstati` | Visual bandwidth charts |
| `/fwlist` | Firewall rule list |
| `/ifcfg` | Network interface info |
| `/myip` | Public IP details |
| `/speedtest` | Run network speed test |
| `/ping` | Ping check |

### 🤖 Android ADB Support
| Command | Description |
|---------|-------------|
| `/adb [cmd]` | Run ADB commands |
| `/adbdev` | List connected devices |
| `/adbinfo [id]` | Get device information |
| `/adbsms [id]` | Retrieve SMS messages |
| `/adbrestnet [id] [delay]` | Restart device network |

### ⚡ System Control
| Command | Description |
|---------|-------------|
| `/sh [command]` | Run custom shell commands |
| `/rs [app]` | Restart services |
| `/reboot` | Reboot the router |
| `/turnoff` | Shutdown the router |

### 🔄 Inline Queries
- `@botname proxies` - Quick proxy status
- `@botname rules` - Quick rules lookup

## 📦 Requirements

### Packages
- `php8-cli` or `php7-cli` (with curl module)
- `git` / `git-http`
- `bc`
- `screen`
- `httping`
- `vnstati` or `vnstati2` (for bandwidth visualization)

### Optional
- `openclash` - Clash proxy integration
- `aria2` - Download manager
- `android-tools-adb` - ADB functionality

## 🚀 Installation

### Quick Install
```bash
# Connect to your OpenWRT router via SSH
ssh root@192.168.1.1

# Clone the repository
git clone https://github.com/helmiau/PHPTeleBotWrt.git
cd PHPTeleBotWrt

# Run the installer
chmod +x phpbotmgr
./phpbotmgr i
```

### Setup
1. Create a bot via [@BotFather](https://t.me/BotFather) on Telegram
2. Get your bot token and username
3. Get your Telegram User ID via [@userinfobot](https://t.me/userinfobot)
4. Create `databot` file:
```
token:your_bot_token
username:your_bot_username
uid:your_telegram_user_id
```

### Usage
```bash
# Run bot manually
./phpbotmgr r
# or
php index.php

# Background mode
screen -S PHPTeleBotWrt php index.php
```

## 📂 Project Structure

```
PHPTeleBotWrt/
├── index.php           # Main bot entry point
├── phpbotmgr          # Installer/Manager script
├── src/
│   ├── PHPTelebot.php # Core bot framework
│   ├── Bot.php        # Bot API wrapper
│   ├── xc.php         # Extended functions
│   ├── async_exec.php # Background task executor
│   └── plugins/       # Command scripts
│       ├── adb-*.sh   # ADB plugins
│       ├── oc.sh      # OpenClash info
│       ├── sysinfo.sh # System info
│       └── ...
├── databot            # Bot configuration (create this)
└── README.md
```

## 🛠 Configuration

### Auto-Start Options

**rc.local (Boot):**
```bash
./phpbotmgr a
```

**Cron Job (Scheduled):**
```bash
./phpbotmgr t
```

### Update
```bash
./phpbotmgr u
```

## 📖 Documentation

For detailed information including:
- Full installation guide with translations
- Screenshots and video previews
- Feature descriptions
- Troubleshooting

Visit: **[https://www.helmiau.com/blog/phptelebotwrt](https://www.helmiau.com/blog/phptelebotwrt)**

## 💝 Support & Donations

If you find this project useful, please consider supporting the developer:

[![Donate](https://img.shields.io/badge/Donate-Click%20Here-red.svg)](https://www.helmiau.com/pay/index_en.html)

## 📜 License

Copyright © 2023 [Helmi Amirudin](https://www.helmiau.com)  
Licensed under the terms of the LICENSE file.

---

<p align="center">
  Made with ❤️ for OpenWRT users
</p>