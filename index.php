<?php
require_once __DIR__ . "/src/PHPTelebot.php";
require_once __DIR__ . "/src/xc.php";
error_reporting(E_ALL); ini_set('display_errors', 1);
$banner = "<b>PHPTeleBotWrt</b>";

// Helper: get the standard bot options safely (always includes parse_mode)
function getBotOptions() {
    return ["parse_mode" => "html"];
}

// Global options used by all bot commands
$GLOBALS["options"] = getBotOptions();
$options = $GLOBALS["options"];

// Read token & username
function readToken($input)
{
    $data = file_get_contents("databot");
    $raw = explode("\n", $data);
    return $input == "token" ? $raw[0] : $raw[1];
}

// XL Saved Numbers helpers
function getUserNumbers($chatId) {
    $file = __DIR__ . "/databot_xlnumbers";
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return isset($data[$chatId]) ? $data[$chatId] : [];
}

function saveUserNumber($chatId, $number) {
    $file = __DIR__ . "/databot_xlnumbers";
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    if (!isset($data[$chatId])) $data[$chatId] = [];
    if (!in_array($number, $data[$chatId])) {
        $data[$chatId][] = $number;
        file_put_contents($file, json_encode($data));
    }
}

function deleteUserNumber($chatId, $number) {
    $file = __DIR__ . "/databot_xlnumbers";
    if (!file_exists($file)) return;
    $data = json_decode(file_get_contents($file), true);
    if (isset($data[$chatId])) {
        $data[$chatId] = array_values(array_diff($data[$chatId], [$number]));
        if (empty($data[$chatId])) unset($data[$chatId]);
        file_put_contents($file, json_encode($data));
    }
}

// Async Background Execution Helper
function async_exec($command, $startMessage, $chatId, $token) {
    // Remove escapeshellcmd as it can break complex commands with pipes/redirects
    $startMsgEscaped = escapeshellarg($startMessage);
    
    // Launch the async helper script in the background.
    // Use escapeshellarg for the command string itself so it's passed as one argument to async_exec.php
    shell_exec(
        "php " . __DIR__ . "/src/async_exec.php " .
        escapeshellarg($command) . " " .
        escapeshellarg($chatId) . " " .
        escapeshellarg($token) . " " .
        $startMsgEscaped .
        " >/dev/null 2>&1 &"
    );
}

// token user
$bot = new PHPTelebot(readToken("token"), readToken("username"));

// Ping Command
$bot->cmd("/ping", function () {
    $start_time = microtime(true);
	Bot::sendMessage("Pinging...", $GLOBALS["options"]);
    $end_time = microtime(true);
    $diff = round(($end_time - $start_time) * 1000);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"Ping time taken: " . $diff . "ms"
		. "\n\n" 
		,$GLOBALS["options"]);
});

// start bot
$bot->cmd("/start", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"Welcome to PHPTeleBotWrt!\nRun /cmdlist to see all available comands."
		. "\n\n" 
		,$GLOBALS["options"]);
});

// list of commands
$bot->cmd("/cmdlist", function () {
    $check_cron_stat = trim(shell_exec("grep -c 'PHPTeleBotWrt' '/etc/crontabs/root'"));
    if ($check_cron_stat === '0') {
        $cron_stat = "NOT ACTIVE";
    } else {
        $cron_stat = "ACTIVE";
    }
	unset($check_cron_stat);
    $check_boot_stat = trim(shell_exec("grep -c 'PHPTeleBotWrt' '/etc/rc.local'"));
    if ($check_boot_stat === '0') {
        $boot_stat = "NOT ACTIVE";
    } else {
        $boot_stat = "ACTIVE";
    }
	unset($check_boot_stat);
	Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
"📁PHPTeleBotWrt Manager
 ↳/botup : Update bot binaries
 ↳/botas : Add/remove bot to/from auto start on boot [$boot_stat]
 ↳/botcr : Add/remove bot to/from cron job [$cron_stat]
 
 📁Aria2 Commands
 ↳/aria2add : Add task
 ↳/aria2stats : Aria2 status
 ↳/aria2pause : Pause all
 ↳/aria2resume : Resume all
 
📁OpenClash Commands
 ↳/oc : OC Information
 ↳/ocst : Start/Restart Openclash
 ↳/ocsp : Stop Openclash
 ↳/ocpr : Proxies status 
 ↳/ocrl : Rule list 
 ↳/ocup : Update Openclash app only
 ↳/ocua : Update Openclash and all cores

📁File Manager
 ↳/ul : Upload a file to OpenWrt
 ↳/dl : Get/retrieve a file from OpenWrt
 ↳/cp : Copy a file to another folder
 ↳/mv : Move a file to another folder
 ↳/rm : Delete a file

📁vnstat commands
 ↳/vnstat : Lists of connected client devices
 ↳*-Supported old vnstat and vnstat2.

📁System
 ↳/sysinfo : System Information
 ↳/memory : Memory status 
 ↳/sh commandSample : Run custom command in bash terminal
 ↳/rs : List of compatible app restart
 ↳/rs appname : Restart app in init.d
 
📁Power System
 ↳/reboot : Reboot OpenWrt
 ↳/turnoff : Turn off OpenWrt
 
📁Network Information
 ↳/netcl : Lists of connected client devices
 ↳/fwlist : Firewall lists
 ↳/ifcfg interface : List of device interface 
 ↳/vnstat : Bandwidth usage 
 ↳/vnstati : Better Bandwidth usage 
 ↳/myip : Get ip details 
 ↳/speedtest : Speedtest 
 ↳/ping : Ping bot

📁ADB Features (required adb installed)
 ↳/adb commandSample : Run basic ADB command
 ↳/adbdev : ADB Android ID device lists
 ↳/adbinfo ADB_ID: Retrieve device information
 ↳/adbrestnet ADB_ID DELAY: Restart device network
 ↳/adbsms ADB_ID: Retrieve SMS from device ID
 ↳*-Replace [ADB_ID] with your device id, take from [adb devices] command.
 ↳*-You can check multiple [ADB_ID] by writing like [adbid001 adbid002 adbid003] with double quotes.
 ↳*-[DELAY] is a delay (seconds) between disabling and re-enabling airplane mode for network restart.
 
📁XL Commands
 ↳/myxl number : Check XL package status
 ↳*-Example: /myxl 087812345678"
 
		. "\n\n" 
		,$GLOBALS["options"]);
	unset($boot_stat);
	unset($cron_stat);
});

// when file uploaded
$bot->on('document', function() {
    $message = Bot::message();
	$fileName = $message['document']['file_name'];
    Bot::sendMessage(
		"File <code>$fileName</code> uploaded to Telegram server. Reply uploaded file with command <code>/ul /folder/folder_dest</code> to upload it to that folder. Change <code>/folder/folder_dest</code> to your own destination folder.". "\n\n" .
		"File <code>$fileName</code> telah diunggah ke server Telegram. Balas file yang sudah di unggah dengan perintah <code>/ul /folder/folder_dest</code> untuk mengunggahnya ke folder tersebut. Ubah <code>/folder/folder_dest</code> dengan folder tujuan anda."
		,$GLOBALS["options"]);
 });

//upload cmd
$bot->cmd("/ul", function ($filedir) {
    $token = readToken("token");
    $message = Bot::message();
	$filePath = $filedir;
	$fileInfo = $message['reply_to_message']['document'];
    $fileName = $fileInfo['file_name'];
    $fileId = $fileInfo['file_id'];
    $raw = json_decode(Bot::getFile($fileId),true);
    $file_server_path = $raw['result']['file_path'];
    $safeFiledir = escapeshellarg($filedir);
    $safeFileName = escapeshellarg($fileName);
	if (!is_null($filePath) && is_dir($filedir) && isset($fileName) && isset($file_server_path)) {
		$wget = shell_exec("wget -O $safeFiledir/$safeFileName \"https://api.telegram.org/file/bot$token/$file_server_path\"");
		$pesan_upf = "File <code>$fileName</code> uploaded to <code>$filedir</code> successfully!." . "\n\n" .
		"File <code>$fileName</code> berhasil diunggah ke folder <code>$filedir</code>!.";
	} else {
		$pesan_upf = "Directory<code>$filedir</code> is invalid!." . "\n" .
		"Folder<code>$filedir</code> tidak valid!." . "\n\n" .
		"<strong><u>OpenWrt File Uploader</u></strong>\n- Upload a file to this chat first.\n- Then reply uploaded file with command <code>/ul /folder/folder_dest</code> to upload it to that folder. Change <code>/folder/folder_dest</code> to your own destination folder.\n- Only support single file upload." . "\n\n" .
		"<strong><u>Pengunggah Berkas OpenWrt</u></strong>\n- Unggah file ke chat ini terlebih dahulu.\n- Lalu balas file yang sudah di unggah dengan perintah <code>/ul /folder/folder_dest</code> untuk mengunggahnya ke folder tersebut. Ubah <code>/folder/folder_dest</code> ke folder tujuan anda.\n- Hanya mendukung upload satu file saja.";
	}
	
	Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"$pesan_upf"
		. "\n\n" 
		,$GLOBALS["options"]);
	
	unset($token);
	unset($message);
	unset($filePath);
	unset($fileInfo);
	unset($fileName);
	unset($fileId);
	unset($raw);
	unset($file_server_path);
});

//download/retrieve file from openwrt cmd
$bot->cmd("/dl", function ($filedir) {
    $token = readToken("token");
    $message = Bot::message();
    $chat_dest = $message['from']['id'];
    if (strpos($filedir, '..') !== false) {
        Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"Access denied: Path traversal not allowed."
		. "\n\n" 
		,$GLOBALS["options"]);
        return;
    }
    $safe_filedir = escapeshellarg($filedir);
	if (file_exists($filedir)) {
		$curled = shell_exec("curl -F document=@$safe_filedir \"https://api.telegram.org/bot$token/sendDocument?chat_id=$chat_dest\"");
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"File <code>$filedir</code> retrieved successfully!.\n\nFile <code>$filedir</code> telah diterima."
			. "\n\n" 
			,$GLOBALS["options"]);
	} else {
		Bot::sendMessage(
		"Please input correct command. Example: <code>/dl /folder1/filename.ext</code>.\n Or file doesn't exists on the server.\n\nTulis perintah dengan benar. Contoh: <code>/dl /folder1/filename.ext</code>\n Atau mungkin file tidak ada di server."
		,$GLOBALS["options"]);
	}
	unset($token);
	unset($message);
	unset($chat_dest);
});

//copy file cmd
$bot->cmd("/cp", function ($cpold, $cpnew) {
    if (file_exists($cpold) && !file_exists($cpnew)) {
		$safe_cpold = escapeshellarg($cpold);
		$safe_cpnew = escapeshellarg($cpnew);
		$copied = shell_exec("cp $safe_cpold $safe_cpnew");
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"File <code>$cpold</code> copied to <code>$cpnew</code>!.\nFile <code>$cpold</code> telah dipindah ke <code>$cpnew</code>!."
			. "\n\n" 
			,$GLOBALS["options"]);
		unset($safe_cpold, $safe_cpnew);
    } else {
		Bot::sendMessage(
		"Please input correct command. Example: <code>/cp /oldfolder/file.txt /newfolder/file.txt</code>.\n Or file source/destination doesn't exists on the server.\n\nTulis perintah dengan benar. Contoh: <code>/cp /oldfolder/file.txt /newfolder/file.txt</code>\n Atau mungkin file asal/tujuan tidak ada di server."
		,$GLOBALS["options"]);
    }
	unset($cpold);
	unset($cpnew);
});

//move file cmd
$bot->cmd("/mv", function ($mvold, $mvnew) {
    if (file_exists($mvold) && !file_exists($mvnew)) {
		$safe_mvold = escapeshellarg($mvold);
		$safe_mvnew = escapeshellarg($mvnew);
		$copied = shell_exec("cp $safe_mvold $safe_mvnew && rm -f $safe_mvold");
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"File <code>$mvold</code> moved to <code>$mvnew</code>!.\nFile <code>$mvold</code> telah dipindah ke <code>$mvnew</code>!."
			. "\n\n" 
			,$GLOBALS["options"]);
		unset($safe_mvold, $safe_mvnew);
    } else {
		Bot::sendMessage(
		"Please input correct command. Example: <code>/mv /oldfolder/file.txt /newfolder/file.txt</code>.\n Or file source/destination doesn't exists on the server.\n\nTulis perintah dengan benar. Contoh: <code>/mv /oldfolder/file.txt /newfolder/file.txt</code>\n Atau mungkin file asal/tujuan tidak ada di server."
		,$GLOBALS["options"]);
    }
	unset($mvold);
	unset($mvnew);
});

//delete file cmd
$bot->cmd("/rm", function ($rmfile) {
    if (file_exists($rmfile)) {
		$safe_rmfile = escapeshellarg($rmfile);
		$copied = shell_exec("rm -f $safe_rmfile");
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"File <code>$rmfile</code> deleted!.\nFile <code>$rmfile</code> telah dihapus!."
			. "\n\n" 
			,$GLOBALS["options"]);
		unset($safe_rmfile);
    } else {
		Bot::sendMessage(
		"Please input correct command. Example: <code>/rm /folder/file.txt</code>.\n Or file source/destination doesn't exists on the server.\n\nTulis perintah dengan benar. Contoh: <code>/rm /folder/file.txt</code>\n Atau mungkin file asal/tujuan tidak ada di server."
		,$GLOBALS["options"]);
    }
	unset($rmfile);
});

//restart init file cmd
$bot->cmd("/rs", function ($app = '') {
    $appPath = "/etc/init.d/$app";
	if (empty($app) || !file_exists($appPath)) {
		$dtIX = shell_exec("src/plugins/getinitapp.sh > listInit && cat listInit");
		Bot::sendMessage(
			"This command allow you to restart an app listed below." . "\n" .
			"Example: <code>/rs openclash</code>" . "\n" .
			"List of supported apps:" . "\n" .
			"###########" . "\n" .
			"<code>" . $dtIX . "</code>..." . "\n" .
			"###########" 
			,$GLOBALS["options"]);
		unset($dtIX);
    } else {
		$safe_app = escapeshellarg($app);
		$safe_appPath = escapeshellarg($appPath);
		$grepST = trim(shell_exec("grep -c restart " . $safe_appPath));
		if ($grepST === '0') {
			$rextat = shell_exec($safe_appPath . " start >/dev/null 2>&1 &");
		} else {
			$rextat = shell_exec($safe_appPath . " restart >/dev/null 2>&1 &");
		}
		Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"Restarting <code>" . $app . "</code>..." . "\n\n" .
			"Run <code>/rs</code> to see listed supported apps"
			. "\n\n" 
			,$GLOBALS["options"]);
		unset($grepST);
		unset($safe_app, $safe_appPath);
    }
});

//bash cmd custom command terminal
$bot->cmd("/sh", function ($bashXmd) {
	$tzX = "sht.sh";
	$bashXmd = escapeshellarg($bashXmd);
	shell_exec("echo $bashXmd > $tzX && chmod 0755 $tzX");
	$runsh = shell_exec("./$tzX > rpbXz && cat rpbXz");
	shell_exec("rm -f $tzX rpbXz");
	
	Bot::sendMessage(
		$GLOBALS["banner"]
		,$GLOBALS["options"]);
	Bot::sendMessage(
		"<code>" . $runsh ."</code>"
		,$GLOBALS["options"]);
});


// OpenWRT Command
// OpenClash Proxies
$bot->cmd("/ocpr", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . OpenClashProxies() . "</code>"
		. "\n\n" 
		,$GLOBALS["options"]);
});

// OpenClash Start
$bot->cmd("/ocst", function () {
	Bot::sendMessage(
		"Start/Restarting Openclash ... "
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . shell_exec("uci set openclash.config.enable=1 && uci commit openclash && /etc/init.d/openclash restart >/dev/null 2>&1 &") . "</code>"
		. "Openclash started successfully!."
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// OpenClash Stop
$bot->cmd("/ocsp", function () {
	Bot::sendMessage(
		"Stopping Openclash ... "
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . shell_exec("uci set openclash.config.enable=0 && uci commit openclash && /etc/init.d/openclash stop >/dev/null 2>&1 &") . "</code>"
		. "Openclash stopped successfully!."
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// OpenClash Update
$bot->cmd("/ocup", function () {
    $message = Bot::message();
    $chatId = $message['chat']['id'];
    $token = readToken("token");

    $ocver = shell_exec("echo -e $(opkg status luci-app-openclash 2>/dev/null |grep 'Version' | awk -F 'Version: ' '{print$2}')");

    Bot::sendMessage(
        $GLOBALS["banner"] . "\n" .
        "Checking OpenClash version update...\nCurrent: $ocver\n\n" .
        "Starting update in background... You will be notified upon completion."
        ,$GLOBALS["options"]);

    async_exec(
        "/usr/share/openclash/openclash_update.sh && opkg status luci-app-openclash 2>/dev/null | grep 'Version' | awk -F 'Version: ' '{print \$2}'",
        "OpenClash update is running...",
        $chatId,
        $token
    );
});

// OpenClash Update All core
$bot->cmd("/ocua", function () {
    $message = Bot::message();
    $chatId = $message['chat']['id'];
    $token = readToken("token");

    $oc_app_old = shell_exec("echo -e $(opkg status luci-app-openclash 2>/dev/null |grep 'Version' | awk -F 'Version: ' '{print$2}')");
    $core_old = shell_exec("echo -e $(/etc/openclash/core/clash -v 2>/dev/null |awk -F ' ' '{print $2}' 2>/dev/null)");
    $core_tun_old = shell_exec("echo -e $(/etc/openclash/core/clash_tun -v 2>/dev/null |awk -F ' ' '{print $2}' 2>/dev/null)");
    $core_meta_old = shell_exec("echo -e $(/etc/openclash/core/clash_meta -v 2>/dev/null |awk -F ' ' '{print $3}' 2>/dev/null)");

    Bot::sendMessage(
        $GLOBALS["banner"] . "\n" .
        "Checking OpenClash and cores version update...\n\n" .
        "Current versions:\n" .
        "App: $oc_app_old\nDev Core: $core_old\nTUN Core: $core_tun_old\nMeta Core: $core_meta_old\n\n" .
        "Starting update in background... You will be notified upon completion."
        ,$GLOBALS["options"]);

    shell_exec("sh /usr/share/openclash/openclash_update.sh 'one_key_update' >/dev/null 2>&1 &");

    $completionScript = '/tmp/ocua_complete.sh';
    $scriptContent = "#!/bin/bash\n";
    $scriptContent .= "sleep 60\n";
    $scriptContent .= "oc_app_new=$(opkg status luci-app-openclash 2>/dev/null | grep 'Version' | awk -F 'Version: ' '{print \$2}')\n";
    $scriptContent .= "core_new=$(/etc/openclash/core/clash -v 2>/dev/null | awk -F ' ' '{print \$2}' 2>/dev/null)\n";
    $scriptContent .= "core_tun_new=$(/etc/openclash/core/clash_tun -v 2>/dev/null | awk -F ' ' '{print \$2}' 2>/dev/null)\n";
    $scriptContent .= "core_meta_new=$(/etc/openclash/core/clash_meta -v 2>/dev/null | awk -F ' ' '{print \$3}' 2>/dev/null)\n";
    $scriptContent .= "oc_app_info=\"OpenClash App is already at latest version\"\n";
    $scriptContent .= "[ \"\$oc_app_new\" != " . escapeshellarg($oc_app_old) . " ] && oc_app_info=\"OpenClash updated to \$oc_app_new\"\n";
    $scriptContent .= "core_new_info=\"Dev core is already at latest version\"\n";
    $scriptContent .= "[ \"\$core_new\" != " . escapeshellarg($core_old) . " ] && core_new_info=\"Dev core updated to \$core_new\"\n";
    $scriptContent .= "core_tun_info=\"TUN core is already at latest version\"\n";
    $scriptContent .= "[ \"\$core_tun_new\" != " . escapeshellarg($core_tun_old) . " ] && core_tun_info=\"TUN core updated to \$core_tun_new\"\n";
    $scriptContent .= "core_meta_info=\"Meta core is already at latest version\"\n";
    $scriptContent .= "[ \"\$core_meta_new\" != " . escapeshellarg($core_meta_old) . " ] && core_meta_info=\"Meta core updated to \$core_meta_new\"\n";
    $scriptContent .= "curl -s -X POST \"https://api.telegram.org/bot$token/sendMessage\" \\\n    -d \"chat_id=$chatId\" \\\n    -d \"text=<b>PHPTeleBotWrt</b>\\n\\n<b>OpenClash Update Complete!</b>\\n\\n\$oc_app_info\\n\$core_new_info\\n\$core_tun_info\\n\$core_meta_info\" \\\n    -d \"parse_mode=html\"\n";
    $scriptContent .= "rm \$0\n";

    file_put_contents($completionScript, $scriptContent);
    shell_exec("chmod +x $completionScript && bash $completionScript >/dev/null 2>&1 &");
});

// vnstat
$bot->cmd("/vnstat", function ($input) {
    $input = escapeshellarg($input);
    $output = shell_exec("vnstat " . $input . " 2>&1");
    if ($output === null) {
        Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"Invalid input or vnstat not found"
			. "\n" 
			,$GLOBALS["options"]);
    } else {
        Bot::sendMessage(
			$GLOBALS["banner"] . "\n" .
			"<code>" . $output . "</code>"
			. "\n" 
			,$GLOBALS["options"]);
    }
});

// vnstati
$bot->cmd("/vnstati", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" 
		,$GLOBALS["options"]);

    $image_files = [
        'summary' => 'vnstati -s -i br-lan -o summary.png',
        'hourly' => 'vnstati -h -i br-lan -o hourly.png',
        'daily' => 'vnstati -d -i br-lan -o daily.png',
        'monthly' => 'vnstati -m -i br-lan -o monthly.png',
        'yearly' => 'vnstati -y -i br-lan -o yearly.png',
        'top' => 'vnstati --top 5 -i br-lan -o top.png',
    ];
    
    foreach ($image_files as $image_file) {
        shell_exec($image_file);
    }
    
    foreach ($image_files as $file_name => $command) {
        Bot::sendPhoto($file_name . '.png');
    }
    
    shell_exec("rm *.png");
	
});


// Check RAM/Memory
$bot->cmd("/memory", function () {
    $meminfo = file("/proc/meminfo");
    $total = intval(trim(explode(":", $meminfo[0])[1])) / 1024;
    $free = intval(trim(explode(":", $meminfo[1])[1])) / 1024;
    $used = $total - $free;
    $percent = round(($used / $total) * 100);
    $bar = str_repeat("■", round($percent / 5));
    $bar .= str_repeat("□", 20 - round($percent / 5));
    $output =
		$GLOBALS["banner"] . "\n" .
        "<code>Memory usage: \nBar: " .
        $bar .
        "\nUsed: $used MB \nAvailable: $free MB \nTotal: $total MB \nUsage: $percent%</code>"
		. "\n\n" ;
    Bot::sendMessage($output, $GLOBALS["options"]);
});

// Systemm info
$bot->cmd("/sysinfo", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/sysinfo.sh -bw") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// Reboot openwrt
$bot->cmd("/reboot", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "Rebooting Openwrt..." .
        "<code>" . shell_exec("reboot") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// Turn off openwrt
$bot->cmd("/turnoff", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "Turning off Openwrt..." .
        "<code>" . shell_exec("halt && reboot -p") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// Network clients info
$bot->cmd("/netcl", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        shell_exec("src/plugins/netcl.sh")
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// Firewall rule lists
$bot->cmd("/fwlist", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/fwlist.sh") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// Ifconfig
$bot->cmd("/ifcfg", function ($iface) {
    if (empty($iface)) {
        $ex_ifcfg = shell_exec("ifconfig");
        $pesan_ifcfg = "Viewing all of interfaces";
    } else {
        $safe_iface = escapeshellarg($iface);
        $ex_ifcfg = shell_exec("ifconfig $safe_iface");
        $pesan_ifcfg = "Viewing info of $iface interface";
    }
	
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>$pesan_ifcfg\n\n$ex_ifcfg</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// OpenClash
$bot->cmd("/oc", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/oc.sh") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// My IP Address info
$bot->cmd("/myip", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . myip() . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// OpenClash Rules
$bot->cmd("/ocrl", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . OpenClashRules() . "</code>"
		. "\n" 
        ,$GLOBALS["options"]);
});

// Speedtest
$bot->cmd("/speedtest", function () {
    $message = Bot::message();
    $chatId = $message['chat']['id'];
    $token = readToken("token");
    
    Bot::sendMessage(
        $GLOBALS["banner"] . "\n" .
        "Speedtest started in background. You will receive the result shortly..."
        ,$GLOBALS["options"]);
    
    async_exec(
        "speedtest > result_SpeedTST && cat result_SpeedTST && rm result_SpeedTST",
        "Speedtest is running...",
        $chatId,
        $token
    );
});

//adb cmd
$bot->cmd("/adb_old", function () {
    Bot::sendMessage("<code>ADB on Progress</code>", $GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"<code>" . ADB() . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

//adb new cmd
$bot->cmd("/adb", function ($adbcmd1) {
    $adbcmd1 = escapeshellarg($adbcmd1);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("adb $adbcmd1") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

$bot->cmd("/adbdev", function ($adbcmd2) {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("adb devices") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});
//$runsh = shell_exec("./$tzX > rpbXz && cat rpbXz");
$bot->cmd("/adbinfo", function ($adbcmd3) {
    $adbcmd3 = escapeshellarg($adbcmd3);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/adb-deviceinfo.sh $adbcmd3 > tmpadbinfo && cat tmpadbinfo") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
	$rmrunsh = shell_exec("rm tmpadbinfo");
});

$bot->cmd("/adbsms", function ($adbcmd4) {
    $adbcmd4 = escapeshellarg($adbcmd4);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/adb-sms.sh $adbcmd4  > tmpadbsms && cat tmpadbsms") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
	$rmrunsh = shell_exec("rm tmpadbsms");
});

$bot->cmd("/adbrestnet", function ($adbcmd5, $adbcmd6, $adbcmd7, $adbcmd8, $adbcmd9) {
    $adbcmd5 = escapeshellarg($adbcmd5);
    $adbcmd6 = escapeshellarg($adbcmd6);
    $adbcmd7 = escapeshellarg($adbcmd7);
    $adbcmd8 = escapeshellarg($adbcmd8);
    $adbcmd9 = escapeshellarg($adbcmd9);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/adb-refresh-network.sh $adbcmd5 $adbcmd6 $adbcmd7 $adbcmd8 $adbcmd9 > tmpadbrestnet && cat tmpadbrestnet") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
	$rmrunsh = shell_exec("rm tmpadbrestnet");
});

// MyXL command
$bot->cmd("/myxl", function ($number) {
    $message = Bot::message();
    $chatId = $message['chat']['id'];
    $savedNums = getUserNumbers($chatId);
    
    if (empty($number)) {
        if (empty($savedNums)) {
            Bot::sendMessage(
                $GLOBALS["banner"] . "\n" .
                "📱 <b>XL Package Checker</b>\n\n" .
                "ℹ️ This command allows you to check your XL (Axis) number\\'s package info including remaining quota, expiry dates, and subscriber details.\n\n" .
                "<b>Usage:</b>\n" .
                "<code>/myxl 087812345678</code>\n\n" .
                "<b>Supported Number Formats:</b>\n" .
                "↳ With country code: <code>6287812345678</code>\n" .
                "↳ Without code: <code>087812345678</code>\n\n" .
                "<b>Example:</b>\n" .
                "↳ <code>/myxl 087812345678</code>\n" .
                "↳ <code>/myxl 6287812345678</code>\n\n" .
                "<b>Note:</b>\n" .
                "↳ Only supports XL and Axis numbers.\n" .
                "↳ Once you check a number, you can save it for quick access."
                ,$GLOBALS["options"]);
        } else {
            $keyboard = [];
            foreach ($savedNums as $num) {
                $keyboard[] = [["text" => "📱 $num", "callback_data" => "myxlcheck:$num"]];
            }
            $keyboard[] = [["text" => "📝 Save New Number", "callback_data" => "myxlsaveform"]];

            $opts = $GLOBALS["options"];
            $opts["reply_markup"] = ["inline_keyboard" => $keyboard];

            Bot::sendMessage(
                $GLOBALS["banner"] . "\n" .
                "📱 <b>Saved Numbers</b>\n\nSelect a number to check:",
                $opts
            );
        }
    } else {
        Bot::sendMessage(
            $GLOBALS["banner"] . "\n" .
            "Checking XL package for <code>$number</code>..."
            ,$GLOBALS["options"]);
        $result = MyXL($number);
        
        if (!in_array($number, $savedNums)) {
            $keyboard = [[["text" => "💾 Save This Number", "callback_data" => "myxlsave:$number"]]];
            $opts = $GLOBALS["options"];
            $opts["reply_markup"] = ["inline_keyboard" => $keyboard];
            Bot::sendMessage($result, $opts);
        } else {
            $keyboard = [[["text" => "🗑 Delete This Number", "callback_data" => "myxldel:$number"]]];
            $opts = $GLOBALS["options"];
            $opts["reply_markup"] = ["inline_keyboard" => $keyboard];
            Bot::sendMessage($result, $opts);
        }
    }
});

//Aria2 cmd
$bot->cmd("/aria2add", function ($url) {
    $url = escapeshellarg($url);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/add.sh $url") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

$bot->cmd("/aria2stats", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/stats.sh") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

$bot->cmd("/aria2pause", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/pause.sh") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

$bot->cmd("/aria2resume", function () {
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
        "<code>" . shell_exec("src/plugins/resume.sh") . "</code>"
		. "\n\n" 
        ,$GLOBALS["options"]);
});

//Aria2 cmd end

// phpbotmgr update
$bot->cmd("/botup", function () {
    Bot::sendMessage(
		"Updating PHPTeleBotWrt..."
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		"<code>" . shell_exec("chmod 0755 phpbotmgr && ./phpbotmgr u") . "</code>"
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"PHPTeleBotWrt updated..."
		. "\n\n" 
        ,$GLOBALS["options"]);
});

// phpbotmgr auto start
$bot->cmd("/botas", function () {
    $check_boot_stat = trim(shell_exec("grep -c 'PHPTeleBotWrt' '/etc/rc.local'"));
    if ($check_boot_stat === '0') {
        $boot_stat1 = "Activating";
        $boot_stat2 = "activated";
    } else {
        $boot_stat1 = "Deactivating";
        $boot_stat2 = "deactivated";
    }
	
    Bot::sendMessage(
		"$boot_stat1 PHPTeleBotWrt to/from auto start..."
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		"<code>" . shell_exec("chmod 0755 phpbotmgr && ./phpbotmgr a") . "</code>"
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"PHPTeleBotWrt auto start $boot_stat2..."
		. "\n\n" 
        ,$GLOBALS["options"]);
		
	unset($boot_stat1);
	unset($boot_stat2);
});

// phpbotmgr cron
$bot->cmd("/botcr", function () {
    $check_cron_stat = trim(shell_exec("grep -c 'PHPTeleBotWrt' '/etc/crontabs/root'"));
    if ($check_cron_stat === '0') {
        $cron_stat1 = "Activating";
        $cron_stat2 = "activated";
    } else {
        $cron_stat1 = "Deactivating";
        $cron_stat2 = "deactivated";
    }
	
    Bot::sendMessage(
		"$cron_stat1 PHPTeleBotWrt to/from cronjob scheduled task..."
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		"<code>" . shell_exec("chmod 0755 phpbotmgr && ./phpbotmgr t") . "</code>"
        ,$GLOBALS["options"]);
    Bot::sendMessage(
		$GLOBALS["banner"] . "\n" .
		"PHPTeleBotWrt cronjob scheduled task $cron_stat2..."
		. "\n\n" 
        ,$GLOBALS["options"]);

	unset($cron_stat1);
	unset($cron_stat2);
});

// Callback handler for MyXL saved numbers
$bot->on("callback", function ($data) {
    $message = Bot::message();
    $chatId = $message['message']['chat']['id'];
    $msgId = $message['message']['message_id'];

    if (strpos($data, 'myxlcheck:') === 0) {
        $number = substr($data, 10);
        Bot::answerCallbackQuery("Checking $number...");
        $result = MyXL($number);
        $savedNums = getUserNumbers($chatId);
        $keyboard = in_array($number, $savedNums)
            ? [[["text" => "🗑 Delete This Number", "callback_data" => "myxldel:$number"]]]
            : [[["text" => "💾 Save This Number", "callback_data" => "myxlsave:$number"]]];
        $opts = $GLOBALS["options"];
        $opts["reply_markup"] = ["inline_keyboard" => $keyboard];
        Bot::sendMessage($result, $opts);
    } elseif (strpos($data, 'myxlsave:') === 0) {
        $number = substr($data, 9);
        saveUserNumber($chatId, $number);
        Bot::answerCallbackQuery("Number $number saved!");
        Bot::editMessageReplyMarkup(["message_id" => $msgId, "reply_markup" => ["inline_keyboard" => [[["text" => "🗑 Delete This Number", "callback_data" => "myxldel:$number"]]]]]);
    } elseif (strpos($data, 'myxldel:') === 0) {
        $number = substr($data, 8);
        deleteUserNumber($chatId, $number);
        Bot::answerCallbackQuery("Number $number deleted!");
        Bot::editMessageReplyMarkup(["message_id" => $msgId, "reply_markup" => ["inline_keyboard" => [[["text" => "💾 Save This Number", "callback_data" => "myxlsave:$number"]]]]]);
    } elseif ($data === 'myxlsaveform') {
        Bot::answerCallbackQuery("Use /myxl 087812345678 to check and save a number");
    }
});

//inline command
$bot->on("inline", function ($cmd, $input) {
    $results = [];
    if ($cmd == "proxies") {
        $proxiesData = OpenClashProxies();
        $results[] = [
            "type" => "article",
            "id" => "unique_id1",
            "title" => "OpenClash Proxies",
            "parse_mode" => "html",
            "message_text" => "<code>" . $proxiesData . "</code>",
        ];
    } elseif ($cmd == "rules") {
        $rulesData = OpenClashRules();
        $results[] = [
            "type" => "article",
            "id" => "unique_id1",
            "title" => "OpenClash Rules",
            "parse_mode" => "html",
            "message_text" => "<code>" . $rulesData . "</code>",
        ];
    } elseif ($cmd == "myxl") {
        $myxlResult = MyXL($input);
        $results[] = [
            "type" => "article",
            "id" => "unique_id1",
            "title" => "XL Package Info",
            "parse_mode" => "html",
            "message_text" => "<code>" . $myxlResult . "</code>",
        ];
    }

    $localOptions = [
        "cache_time" => 3600,
    ];

    return Bot::answerInlineQuery($results, $localOptions);
});

$bot->run();
