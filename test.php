<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<style type="text/css">
body {background-color: #fff; color: #222; font-family: sans-serif;}
pre {margin: 0; font-family: monospace;}
a:link {color: #009; text-decoration: none; background-color: #fff;}
a:hover {text-decoration: underline;}
table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
.center {text-align: center;}
.center table {margin: 1em auto; text-align: left;}
.center th {text-align: center !important;}
td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccf; width: 300px; font-weight: bold;}
.h {background-color: #99c; font-weight: bold;}
.v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
.v i {color: #999;}
img {float: right; border: 0;}
hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
</style>
<title>misp-test</title><meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" /></head>
<body>


<?php

// Cake Specifics
/**
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
/**
 * These defines should only be edited if you have cake installed in
 * a directory layout other than the way it is distributed.
 * When using custom settings be sure to use the DS and do not add a trailing DS.
 */
/**
 * The full path to the directory which holds "app", WITHOUT a trailing DS.
 *
 */
if (!defined('ROOT')) {
	define('ROOT', dirname(dirname(dirname(__FILE__))));
}
/**
 * The actual directory name for the "app".
 *
 */
if (!defined('APP_DIR')) {
	define('APP_DIR', basename(dirname(dirname(__FILE__))));
}
/**
 * The absolute path to the "cake" directory, WITHOUT a trailing DS.
 *
 * Un-comment this line to specify a fixed path to CakePHP.
 * This should point at the directory containing `Cake`.
 *
 * For ease of development CakePHP uses PHP's include_path.  If you
 * cannot modify your include_path set this value.
 *
 * Leaving this constant undefined will result in it being defined in Cake/bootstrap.php
 */
	define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . APP_DIR . DS .'Lib' . DS . 'cakephp' . DS . 'lib');
/**
 * Editing below this line should NOT be necessary.
 * Change at your own risk.
 *
 */
if (!defined('WEBROOT_DIR')) {
	define('WEBROOT_DIR', basename(dirname(__FILE__)));
}
if (!defined('WWW_ROOT')) {
	define('WWW_ROOT', dirname(__FILE__) . DS);
}
if (!defined('CAKE_CORE_INCLUDE_PATH')) {
	if (function_exists('ini_set')) {
		ini_set('include_path', ROOT . DS . 'lib' . PATH_SEPARATOR . ini_get('include_path'));
	}
	if (!include ('Cake' . DS . 'bootstrap.php')) {
		$failed = true;
	}
} else {
	if (!include (CAKE_CORE_INCLUDE_PATH . DS . 'Cake' . DS . 'bootstrap.php')) {
		$failed = true;
	}
}
if (!empty($failed)) {
	trigger_error("CakePHP core could not be found.  Check the value of CAKE_CORE_INCLUDE_PATH in APP/webroot/index.php.  It should point to the directory containing your " . DS . "cake core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
}

// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

/** variables_begin **/

$wrFiles=array(
  '/tmp',
  '/var/www/MISP/app/tmp',
  '/var/www/MISP/app/files',
  '/var/www/MISP/app/files/scripts/tmp',
  '/var/www/MISP/app/tmp/csv_all',
  '/var/www/MISP/app/tmp/csv_sig',
  '/var/www/MISP/app/tmp/md5',
  '/var/www/MISP/app/tmp/sha1',
  '/var/www/MISP/app/tmp/snort',
  '/var/www/MISP/app/tmp/suricata',
  '/var/www/MISP/app/tmp/text',
  '/var/www/MISP/app/tmp/xml',
  '/var/www/MISP/app/tmp/files',
  '/var/www/MISP/app/tmp/logs',
  '/var/www/MISP/app/tmp/bro',
  '/var/www/MISP/app/Config/config.php'
);

$reFiles=array(
  '/var/www/MISP/app/files/scripts/stixtest.py'
);

$execCmds=array(
  'git status',
  'python',
  'gpg --version',
  'whoami',
  'ps',
  APP . 'Console' . DS . 'cake admin getSetting GnuPG.binary',
  'awk -V',
  'grep -V',
  'kill'
);

/** variables_end **/


/** functions_begin **/

/**
 * 解析 PHP info
 *
 * @return array
 */
function parse_phpinfo() {
    ob_start(); phpinfo(INFO_MODULES); $s = ob_get_contents(); ob_end_clean();
    $s = strip_tags($s, '<h2><th><td>');
    $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/', '<info>\1</info>', $s);
    $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/', '<info>\1</info>', $s);
    $t = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
    $r = array(); $count = count($t);
    $p1 = '<info>([^<]+)<\/info>';
    $p2 = '/'.$p1.'\s*'.$p1.'\s*'.$p1.'/';
    $p3 = '/'.$p1.'\s*'.$p1.'/';
    for ($i = 1; $i < $count; $i++) {
        if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $t[$i], $matchs)) {
            $name = trim($matchs[1]);
            $vals = explode("\n", $t[$i + 1]);
            foreach ($vals AS $val) {
                if (preg_match($p2, $val, $matchs)) { // 3cols
                    $r[$name][trim($matchs[1])] = array(trim($matchs[2]), trim($matchs[3]));
                } elseif (preg_match($p3, $val, $matchs)) { // 2cols
                    $r[$name][trim($matchs[1])] = trim($matchs[2]);
                }
            }
        }
    }
    return $r;
}

// Return human readable sizes
function humanSize($Bytes)
{
  $Type=array("", "kilo", "mega", "giga", "tera", "peta", "exa", "zetta", "yotta");
  $Index=0;
  while($Bytes>=1024)
  {
    $Bytes/=1024;
    $Index++;
  }
  return("".round($Bytes, 2)." ".$Type[$Index]."bytes");
}

// check if extension is available
function checkExtensions ($extensions) {
  $results = array();
  $results['phpversion'] = phpversion();
  foreach ($extensions as $extension) {
    $results['extensions'][$extension] = extension_loaded($extension);
  }
  return $results;
  return json_encode($results);
}

// Diagnose ZMQ
function zmqRunning() {
  App::uses('File', 'Utility');
  $pidFile = new File(APP . 'files' . DS . 'scripts' . DS . 'mispzmq' . DS . 'mispzmq.pid');
  $pid = $pidFile->read(true, 'r');
  if ($pid === false || $pid === '') {
    return false;
  }
  if (!is_numeric($pid)) {
    throw new Exception('Internal error (invalid PID file for the MISP zmq script)');
  }
  return $pid;
}

// Diagnose gpg issues
function gpgDiag() {
  if (Configure::read('GnuPG.email') && Configure::read('GnuPG.homedir')) {
    $continue = true;
    try {
      require_once 'Crypt/GPG.php';
      $gpg = new Crypt_GPG(array('homedir' => Configure::read('GnuPG.homedir'), 'gpgconf' => Configure::read('
GnuPG.gpgconf'), 'binary' => (Configure::read('GnuPG.binary') ? Configure::read('GnuPG.binary') : '/usr/bin/gpg')));
    } catch (Exception $e) {
      echo $e;
      $gpgStatus = 2;
      $continue = false;
    }
    if ($continue) {
      try {
        $key = $gpg->addSignKey(Configure::read('GnuPG.email'), Configure::read('GnuPG.password'));
      } catch (Exception $e) {
        echo $e;
        $gpgStatus = 3;
        $continue = false;
      }
    }
    if ($continue) {
      try {
        $gpgStatus = 0;
        $signed = $gpg->sign('test', Crypt_GPG::SIGN_MODE_CLEAR);
      } catch (Exception $e) {
        echo $e;
        $gpgStatus = 4;
      }
    }
  } else {
    $gpgStatus = 1;
  }
  if ($gpgStatus != 0) {
    if (isset($key)) {
      print("<pre>".print_r($key,true)."</pre>");
    }
  }
  return $gpgStatus;
}

// recursively check directory size
function folderSize ($dir)
{
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : folderSize($each);
    }
    return $size;
}

function stest($ip, $portt) {
    $fp = @fsockopen($ip, $portt, $errno, $errstr, 0.1);
    if (!$fp) {
        return false;
    } else {
        fclose($fp);
        return true;
    }
}

function check($proxy=null) {
        $proxy=  explode(':', $proxy);
        $host = $proxy[0]; 
        $port = $proxy[1]; 
        $waitTimeoutInSeconds = 10; 
        if($fp = @fsockopen($host,$port,$errCode,$errStr,$waitTimeoutInSeconds)){   
           return 'yes';
        } else {
           return 'false';
        } 
        fclose($fp);
}

/** functions_end **/

if (php_sapi_name() == "cli") {
  // In cli-mode
  echo "You are in CLI mode" . PHP_EOL;
  $phpInfo=phpinfo();
  //echo json_encode($phpInfo, JSON_PRETTY_PRINT);
} else {
  // Not in cli-mode
  //phpinfo(INFO_MODULES);
  $phpInfo=parse_phpinfo();
  //header('Content-Type: application/json');
  //echo json_encode($phpInfo, JSON_PRETTY_PRINT);
  $extensions=array('redis', 'gd', 'gnupg');
  $extJSON=checkExtensions($extensions);
  foreach ($extensions as $extension) {
    if ($extJSON['extensions'][$extension] != 1) {
      echo $extension . " is missing<br />";
    }
  }

}

echo "<h1>General info</h1>";

$PATH_TO_MISP=ROOT;

$folders=array($PATH_TO_MISP."/app/tmp/logs",$PATH_TO_MISP."/venv",$PATH_TO_MISP."/files");

echo '$PATH_TO_MISP -> <b>' . $PATH_TO_MISP . '</b> (also ROOT)<br />';
echo '$PATH_TO_MISP has <b>' . humanSize(disk_free_space($PATH_TO_MISP)) . '</b> of free disk space<br />';
echo '<br />';
echo '$PATH_TO_MISP has a total size of <b>' . humanSize(folderSize($PATH_TO_MISP)) . "</b><br />";
echo '<br />';
echo 'ROOT/PyMISP has a size of <b>' . humanSize(folderSize($PATH_TO_MISP. DS . "PyMISP")) . "</b><br />";
echo 'ROOT/app has a size of <b>' . humanSize(folderSize($PATH_TO_MISP. DS . "app")) . "</b><br />";
echo 'ROOT/app/files has a size of <b>' . humanSize(folderSize($PATH_TO_MISP. DS . "app/files")) . "</b><br />";
echo 'ROOT/venv has a size of <b>' . humanSize(folderSize($PATH_TO_MISP. DS . "venv")) . "</b><br />";

echo "<h2>Checking if various 'cake' specific variables are set</h2>";
echo (defined(DS) ? "DS is NOT set<br />" : "DS is set: <b>". DS . "</b><br />");
echo (defined(APP) ? "APP is NOT set<br />" : "APP is set: <b>" . APP . "</b><br />");
echo (defined(APP_DIR) ? "APP_DIR is NOT set<br />" : "APP_DIR is set: <b>" . APP_DIR . "</b><br />");
echo (defined(WWW_ROOT) ? "WWW_ROOT is NOT set<br />" : "WWW_ROOT is set: <b>" . WWW_ROOT . "</b><br />");
echo (defined(WEBROOT_DIR) ? "WEBROOT_DIR is NOT set<br />" : "WEBROOT_DIR is set: <b>" . WEBROOT_DIR . "</b><br />");
echo (defined(ROOT) ? "ROOT is NOT set<br />" : "ROOT is set: <b>" . ROOT . "</b><br />");

echo "<h2>Testing redis</h2>";
$redis = new Redis();
try {
  $redis->connect("127.0.0.1",6379);
  echo "Redis <b>OK</b><br />";
} catch (Exception $e) {
  echo "Cannot connect to redis server: ". $e->getMessage() . "<br />";
  // (Condition)?(thing's to do if condition true):(thing's to do if condition false);
  echo (stest('127.0.0.1', '6379') ? 'We can reach port 6379 (redis) from PHP, maybe the redis extension is missing.<br />' : 'Cannot reach port 6379 (redis) from PHP.<br />Under CentOS/RHEL you might need to:<br />sudo setsebool -P httpd_can_network_connect on<br />');
}

echo "<h2>Testing exec() calls</h2>";
foreach ($execCmds as &$cmd) {
  if ($cmd == 'kill') {
    exec('sleep 1 & kill $$', $retArr, $retVal);
  } else {
    exec($cmd, $retArr, $retVal);
  }
  if ($retVal == 127) {
    echo "'$cmd' does not exist.";
    echo '<br />';
  } else {
  echo ($retVal != 0 and $retVal != 15) ? "Command '$cmd' exited with '$retVal'" : "$cmd <b>OK</b><br />";
  }
}

echo "<h2>Testing GnuPG</h2>";
echo "GnuPG diagnostics error code: <b>" . gpgDiag() . "</b><br />";

echo "<h2>Testing ZMQ</h2>";
echo "ZMQ running with PID (from file) number: <b>" . zmqRunning() . "</b><br />";
//phpinfo();

echo check("http://localhost:8888")

?>
</body></html>
