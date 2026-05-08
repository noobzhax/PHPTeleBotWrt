# PHPTeleBotWrt

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-blue.svg" alt="PHP">
  <img src="https://img.shields.io/badge/OpenWrt-21+-green.svg" alt="OpenWrt">
  <img src="https://img.shields.io/badge/Telegram-Bot API-blue.svg" alt="Telegram">
</p>

**PHPTeleBotWrt** is a powerful Telegram bot framework written in PHP designed specifically for OpenWRT routers. It provides a comprehensive set of commands to manage your router, monitor system resources, control network services, and automate various tasks directly from Telegram.

## 📋 Table of Contents

- [✨ Features](#-features)
  - [🔧 Bot Management](#-bot-management)
  - [📁 File Manager](#-file-manager)
  - [🌐 OpenClash Control](#-openclash-control)
  - [📥 Aria2 Downloads](#-aria2-downloads)
  - [📱 XL Commands](#-xl-commands)
  - [📊 System & Network](#-system--network)
  - [🤖 Android ADB Support](#-android-adb-support)
  - [⚡ System Control](#-system-control)
  - [🔄 Inline Queries](#-inline-queries)
- [📦 Requirements](#-requirements)
- [🚀 Installation](#-installation)
  - [Quick Install](#quick-install)
  - [Setup](#setup)
  - [Usage](#usage)
- [📂 Project Structure](#-project-structure)
- [🛠 Management & Configuration](#-management--configuration)
  - [`phpbotmgr` Command Usage](#phpbotmgr-command-usage)
  - [Quick Start Workflow](#quick-start-workflow)
  - [Other Common Operations](#other-common-operations)
- [📖 Documentation](#-documentation)
- [📜 License](#-license)

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

### 📱 XL Commands
| Command | Description |
|---------|-------------|
| `/myxl [number]` | Check XL/Axis package & quota info |

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
git clone https://github.com/noobzhax/PHPTeleBotWrt.git
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

## 🛠 Management & Configuration

### `phpbotmgr` Command Usage

The `phpbotmgr` script is a comprehensive management tool for the bot's lifecycle on your router.

| Command | Action | Description |
| :--- | :--- | :--- |
| `./phpbotmgr i` | **Install** | Installs all required OpenWRT packages, clones the repo, and sets up `databot` configuration. |
| `./phpbotmgr u` | **Update** | Backs up your config, pulls the latest changes from GitHub, and updates the manager itself. |
| `./phpbotmgr e` | **Edit Config** | Interactively update your Bot Token, Username, or Personal UID in the `databot` file. |
| `./phpbotmgr r` | **Run** | Starts the bot using `nohup` and `screen` for background execution. |
| `./phpbotmgr s` | **Stop** | Kills all running PHP processes associated with the bot. |
| `./phpbotmgr c` | **Check Status** | Checks if the bot process is currently running. |
| `./phpbotmgr a` | **Auto-start** | Toggles the bot's presence in `/etc/rc.local` (starts on boot). |
| `./phpbotmgr t` | **Cron Job** | Toggles a scheduled task in root crontab (restarts every 4 mins). |
| `./phpbotmgr ra` | **Uninstall All** | Removes the bot and all associated configuration data. |
| `./phpbotmgr rx` | **Uninstall App** | Removes the bot but keeps a backup of your `databot` configuration. |

---

### 📦 `./phpbotmgr i` — Install

Performs a full installation of the bot including:
1. Checks for and installs required packages via `opkg` if missing:
   - `git`, `git-http`, `bc`, `screen`, `adb`, `httping`
   - `php8-cli` (or `php7-cli` if `php8` is unavailable) with `curl` module
   - `vnstati` or `vnstati2` (for bandwidth charts)
2. Removes any old installation at `/root/PHPTeleBotWrt` (backs up existing `databot` if found)
3. Clones the latest version from GitHub
4. Sets file permissions (`chmod 0755`)
5. Enables Auto-Start on Boot (`./phpbotmgr a`)
6. Enables Scheduled Restart via Cron (`./phpbotmgr t`)
7. Creates the `databot` configuration file (interactively asks for Bot Token, Username, and UID)
8. Sends a test notification to your Telegram

```bash
./phpbotmgr i
```

> **Note:** If you already have a `databot` file from a previous install, it will be automatically restored.

---

### 🔄 `./phpbotmgr u` — Update

Updates the bot to the latest version from GitHub:
1. Backs up your existing `databot` file
2. Runs `git reset --hard` and `git pull` inside `/root/PHPTeleBotWrt`
3. Resets file permissions
4. Restores your `databot` backup
5. Copies and updates the `phpbotmgr` script itself to `/root/phpbotmgr`

```bash
./phpbotmgr u
```

> **Warning:** This will overwrite any local changes to the code.

---

### ✏️ `./phpbotmgr e` — Edit Config

Interactively edit the `databot` file to update your Telegram credentials:
1. Shows your current Bot Token, Username, and UID
2. Prompts for each value (press Enter to keep the current one)
3. Updates only the fields you enter
4. Sends a confirmation message to your Telegram

```bash
./phpbotmgr e
```

Example interaction:
```
Editing PHPTeleBotWrt databot...
Bot Token Example: 52123745:ABeN1H9jc0I_7lIeyu_4aE8BZiV_fXt9TGk
💬 Enter New Bot Token: (leave empty if no change)
==>>
🤖 Enter New Bot Username (without @): (leave empty if no change)
==>>
🤖 Enter Your New Personal Account UID: (leave empty if no change)
==>>
✔️ PHPTeleBotWrt telegram data updated successfully.
```

---

### ▶️ `./phpbotmgr r` — Run

Starts the bot in the background using `nohup` and `screen`:
1. Starts a `screen` session named `bot`
2. Runs the bot using `php8-cli` or `php7-cli` depending on what's installed
3. Output is redirected to `/dev/null`
4. Shows confirmation message

```bash
./phpbotmgr r
```

> **Note:** Requires `screen` package to be installed.

---

### ⏹️ `./phpbotmgr s` — Stop

Stops the running bot by killing all PHP processes:
1. Finds all running `php-cli` or `php8-cli` processes
2. Kills them all at once
3. Shows confirmation message

```bash
./phpbotmgr s
```

---

### 🔍 `./phpbotmgr c` — Check Status

Checks whether the bot is currently running:
- If running → Shows `✔️ PHPTeleBotWrt is running` and lists job PIDs
- If not running → Shows `✔️ PHPTeleBotWrt is not running.`

```bash
./phpbotmgr c
```

---

### ⚡ `./phpbotmgr a` — Auto-start (Boot)

Toggles the bot's presence in `/etc/rc.local`:
- **If NOT in rc.local:** Adds the startup command to `/etc/rc.local` so the bot starts automatically when the router boots
- **If ALREADY in rc.local:** Removes the entry (disables auto-start)

```bash
./phpbotmgr a
```

**What it adds to `/etc/rc.local`:**
```bash
#PHPTeleBotWrt-Start
cd /root/PHPTeleBotWrt && if [[ $(ls {/bin,/usr/bin,/usr/sbin} | grep -c "^php8-cli") > 0 && $(ps aux | grep -c "PHPTeleBotWrt") < 2 ]];then php8-cli index.php &>dev/null;else php-cli index.php &>dev/null; fi
#PHPTeleBotWrt-End
exit 0
```

---

### ⏰ `./phpbotmgr t` — Cron Job (Scheduled)

Toggles the bot's presence in the root crontab:
- **If NOT in crontab:** Adds a cron entry to restart the bot every 4 minutes (`*/4 * * * *`)
- **If ALREADY in crontab:** Removes the entry

```bash
./phpbotmgr t
```

**What it adds to `/etc/crontabs/root`:**
```bash
#PHPTeleBotWrt-Start
*/4 * * * * cd /root/PHPTeleBotWrt && if [[ $(ls {/bin,/usr/bin,/usr/sbin} | grep -c "^php8-cli") > 0 && $(ps aux | grep -c "PHPTeleBotWrt") < 2 ]];then php8-cli index.php;else php-cli index.php; fi
```

> **Why:** The cron job acts as a watchdog — it restarts the bot every 4 minutes if it crashed or stopped running.

---

### 🗑️ `./phpbotmgr ra` — Uninstall All

Completely removes the bot and all data:
1. Deletes the entire `/root/PHPTeleBotWrt` directory
2. Removes auto-start entries from `/etc/rc.local`
3. Removes cron entries from `/etc/crontabs/root`

```bash
./phpbotmgr ra
```

> **Warning:** This deletes everything including your `databot` configuration.

---

### 🗑️ `./phpbotmgr rx` — Uninstall (Keep Config)

Removes the bot application but keeps your `databot` backup:
1. Backs up `databot` to `/root/PHPTeleBotWrt-databot.bak`
2. Deletes the entire `/root/PHPTeleBotWrt` directory
3. Removes auto-start and cron entries
4. You can restore `databot` for later use

```bash
./phpbotmgr rx
```

---

### Quick Start Workflow

```bash
# 1. Make it executable
chmod +x phpbotmgr

# 2. Install (packages + repo + setup)
./phpbotmgr i

# 3. Enable Auto-Start on Boot (rc.local)
./phpbotmgr a

# 4. Enable Scheduled Restart (Cron - every 4 mins)
./phpbotmgr t

# 5. Start the bot now
./phpbotmgr r

# 6. Check if it's running
./phpbotmgr c
```

### Other Common Operations

```bash
# Stop the bot
./phpbotmgr s

# Update to latest version
./phpbotmgr u

# Edit Bot Token / Username / UID
./phpbotmgr e

# Full uninstall (removes everything including config)
./phpbotmgr ra

# Uninstall but keep databot backup
./phpbotmgr rx
```

## 📖 Documentation

For detailed information including:
- Full installation guide with translations
- Screenshots and video previews
- Feature descriptions
- Troubleshooting

Visit: **[https://www.helmiau.com/blog/phptelebotwrt](https://www.helmiau.com/blog/phptelebotwrt)**

## 📜 License

This is a fork of [PHPTeleBotWrt](https://github.com/helmiau/PHPTeleBotWrt) by [Helmi Amirudin](https://www.helmiau.com). 

For the original project license, please refer to the [LICENSE](/LICENSE) file or visit the [main repository](https://github.com/helmiau/PHPTeleBotWrt).

---

<p align="center">
  Made with ❤️ for OpenWRT users
</p>
