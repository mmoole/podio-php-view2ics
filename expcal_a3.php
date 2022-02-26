<?php
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=calendar.ics');

//  read view id from parameter this script is called
$vid_url = $_SERVER['QUERY_STRING'];
$vid = (int)filter_var($vid_url, FILTER_SANITIZE_NUMBER_INT);
if (!isset($vid)) {
  print "could not retrieve required parameter\r\n";
  exit();
}
// debug
// print "variable vid is: " . $vid . "\r\n";


// via https://catswhocode.com/phpcache/
include('cache_top_vid.php');

// regular script begins here

// Function for breaking longer lines
function bline(string $longtext): string
{
  $linewrap = 70;
  $linebreaker = "\r\n\t";
  $retline = $longtext;

  $retline = chunk_split($longtext, $linewrap - 1, $linebreaker);
  $retline = rtrim($retline, $linebreaker);
  return ("$retline" . "\r\n");
}

// TODO: bekannte Probleme:
// - Wiederholungen werden nicht eingeschlossen - es wird jeweils nur der erste Termin ausgegeben


// print head of calendar file
print "BEGIN:VCALENDAR\r\n";
print "VERSION:2.0\r\n";
print "PRODID:-//Podio API//EN//view-exporter//" . $vid . "\r\n";
print "METHOD:REQUEST\r\n";
print "X-WR-CALNAME:view" . $vid . "\r\n";
print "X-WR-TIMEZONE:Europe/Berlin\r\n";
print "CALSCALE:GREGORIAN\r\n";
print "X-COMMENT-USAGE:This calendar does not contain recurring events!\r\n";
print "X-COMMENT-GENERATOR:PHP Version " . phpversion() . " \r\n";


//  include file for reading specific configuration
//  infer filename from this scripts filename and given value for view id
$s_url = $_SERVER["SCRIPT_NAME"];
$s_file = basename($s_url);
$s_ifile = preg_split("/\.php$/", $s_file);
$i_file = $s_ifile[0] . "_" . $vid . ".inc";
// debug:
// print "i_file ist " . $i_file . "\r\n";

if (file_exists($i_file)) {
  require($i_file);
}
else {
  print "calendar not found\r\n";
  exit();
}


// load Podio API
require __DIR__ . '/vendor/autoload.php';

// authenticate and get connection to Podio
Podio::setup($client_id, $client_secret);

try {
  Podio::authenticate_with_app($app_id, $app_token);
// Authentication was a success, now you can start making API calls.
}
catch (PodioError $e) {
  // Something went wrong. Examine $e->body['error_description'] for a description of the error.
  print "could not connect to Podio, exiting </br>";
  print $e;
  exit();
}

$view_id = $vid;
$item_collection = PodioItem::filter_by_view($app_id, $view_id, $attributes = array());
// Result is a huge php object containing objects and arrays

// debug:
// print "My app has " . count($item_collection) . " items";
// print_r ($item_collection);


//  loop over item_collection array for calendar items
foreach ($item_collection as $theitem)
{ //foreach element in $item_collection
  //  debug print whole item
  // print_r ($theitem);

  // head of event
  print "BEGIN:VEVENT\r\n";
  print "STATUS:CONFIRMED\r\n";
  print "SEQUENCE:0\r\n";
  print "TRANSP:OPAQUE\r\n";

  // declare this as a public or private event
  print "CLASS:PUBLIC\r\n";

  // use item id as UID
  print "UID:" . $theitem->item_id . "\r\n";

  // get title from field previously set per variable
  if ($usepodiotitle == 'true') {
    $vtitle = ($theitem->{ "title"});
  }
  else {
    $vtitle = (PodioItem::get_field_value($theitem->item_id, $field_summary))[0]["value"];
  }
  print bline("SUMMARY:" . $vtitle . "\r\n");

  // field for description of the event
  $v_description_html = (PodioItem::get_field_value($theitem->item_id, $field_description))[0]["value"];
  $v_description_htmencoded = rawurlencode("<body>" . ($v_description_html) . "</body>");
  $v_description = $v_description_html;

  // plain text in default field for DESCRIPTION
  $v_description_plain = strip_tags($v_description);
  // print "DESCRIPTION:" . $v_description_plain . "\r\n";


  if ($field_description_only_html == 'true') {
    // DEscription field contains only html contents no plain text
    print bline("DESCRIPTION:" . $v_description_html . "\r\n");
  }
  else {
    // HTML formatted Description field in general being followed by plaintext Description:
    //  via https://stackoverflow.com/a/67844673
    // DESCRIPTION;ALTREP="data:text/html;<h1>Some text</h1>":Some text

    // print bline("DESCRIPTION;ALTREP=\"data:text/html," . $v_description_htmencoded . "\r\n \":\n " . addslashes($v_description_plain) . "\r\n");
    print bline("DESCRIPTION;ALTREP=\"data:text/html," . $v_description_htmencoded . "\":" . addslashes($v_description_plain) . "\r\n");
  }
  //  styled description via https://www.rfc-editor.org/rfc/rfc9073.html#section-6.5
  // STYLED-DESCRIPTION;VALUE=URI:http://example.org/desc001.html
  // commenting this out since it does not work with pypi.python.org/pypi/icalendar
  // print bline("X-STYLED-DESCRIPTION;VALUE=\"data:text/html:" . $v_description_htmencoded . "\"\r\n");

  // HTML formatted Description field for Outlook:
  print bline("X-ALT-DESC;FMTTYPE=text/html:<html><head></head><body>" . $v_description . "</body></html>" . "\r\n");

  // HTML formatted description for Busycal - dows not work - needs to be plist00 encoded!
  // print "X-BUSYMAC-DESCRIPTION;ENCODING=BASE64;FMTTYPE=application/x-nsarchive;VA\nLUE=BINARY:" . base64_encode($v_description) . "\r\n";

  // use location field only if it is set
  if ($field_ownlink_disabled == 'false') {
    // URL of the event in Podio
    if ($usepodiourl == 'true') {
      $vownlink = $theitem->{ "link"};
      print bline("URL:" . $vownlink . "\r\n");
    }
    else {
      // get link per field id
      if ((PodioItem::get_field_value($theitem->item_id, $field_ownlink))) {
        $vownlink = (PodioItem::get_field_value($theitem->item_id, $field_ownlink))[0]['embed']['resolved_url'] . " "; // wieso auch immer scheint es hier nützlich, ein Leerzeichen anzuhängen
      }
      else {
        $vownlink = "";
      }
      if ($vownlink != "") {
        print bline("URL:" . $vownlink . "\r\n");
      }
    }
  }

  // use location field only if it is set
  if ($field_location != "") {
    if ((PodioItem::get_field_value($theitem->item_id, $field_location))) {
      $vlocation = (PodioItem::get_field_value($theitem->item_id, $field_location))[0]['formatted']; // wieso auch immer scheint es hier nützlich, ein Leerzeichen anzuhängen
      print bline("LOCATION:" . $vlocation . "\r\n");
    }

  }


  $start_datetime = array_values(((($theitem->{ "fields"})[2])->{ "values"}))[0]; //    zusatzfeld time start
  $end_datetime = array_values(((($theitem->{ "fields"})[2])->{ "values"}))[1]; //    zusatzfeld time ende
  print "DTSTART:" . $start_datetime->format('Ymd') . "T" . $start_datetime->format('His') . "Z\r\n";
  print "DTEND:" . $end_datetime->format('Ymd') . "T" . $end_datetime->format('His') . "Z\r\n";

  // timestamp of now
  print "DTSTAMP:" . date('Ymd') . "T" . date('His') . "Z\r\n";

  print "END:VEVENT\r\n";
}
// End of the loop over all items in the array

print "END:VCALENDAR\r\n";


include('cache_bottom.php');
?>
