<?php
// via https://catswhocode.com/phpcache/
$url = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $url);
$file = $break[count($break) - 1];
$cachefile = 'cached-' . substr_replace($file, "", -4) . '_vid_' . $vid . '.ics';
// cache time in seconds
$cachetime = 1800; //= 0,5h
// $cachetime = 10;

// Serve from the cache if it is younger than $cachetime
if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
  readfile($cachefile);
  echo "X-COMMENT:<!-- Cached copy, generated " . date('Y-m-d H:i:s', filemtime($cachefile)) . " - newly generated at most every " . $cachetime . "sec. -->\n";
  exit;
}
ob_start(); // Start the output buffer
?>
