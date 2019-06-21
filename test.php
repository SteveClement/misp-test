<?php

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

function checkExtensions ($extensions) {
	//$extensions = array('redis', 'gd');
	$results = array();
	$results['phpversion'] = phpversion();
	foreach ($extensions as $extension) {
		$results['extensions'][$extension] = extension_loaded($extension);
	}
	return $results;
	return json_encode($results);
}

function folderSize ($dir)
{
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : folderSize($each);
    }
    return $size;
}

$PATH_TO_MISP='/var/www/MISP';

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
  $extensions=array('redis', 'gd');
  $extJSON=checkExtensions($extensions);
  foreach ($extensions as $extension) {
    if ($extJSON['extensions'][$extension] != 1) {
      echo $extension . " is missing";
    }
  }

}

$folders=array($PATH_TO_MISP."/app/tmp/logs",$PATH_TO_MISP."/venv",$PATH_TO_MISP."/files");
echo humanSize(disk_free_space($PATH_TO_MISP));
echo '<br />';
echo humanSize(folderSize($PATH_TO_MISP));

?>
