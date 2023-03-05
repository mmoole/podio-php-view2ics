<?php

// This configuration is for:
// example use case

// Set variables for accessing via Podio API
$client_id = "cal-exp-01";
$client_secret = "5ituv35t8o7gznv38957gzn489g5z489g5z4789g5zn7demo";
$app_id = "95649564";
$app_token = "95z8gnwv9w45z9gw54z9gzf349gzn9w45demo";
$friendly_name = "FriendlyName of the Calendar displayed in App";

// set names of fields in Podio to export to calendar items

// set to true if the default title field should be used - otherwhise enter
// title field ext.name or field id in following variable
$usepodiotitle = 'true';
// field id for calendar item title aka SUMMARY:
$field_summary = "578567653";

// field id for calendar item details aka DESCRIPTION:
$field_description = "235463456";

// set to true if only html formatted description should be used
$field_description_only_html = 'false';

// field id for calendar item location aka LOCATION:
$field_location = "223453453";

// set to true if the URL should point to the item itself in podio
$usepodiourl = 'true';
// otherwhise set field id used for URL field in event
$field_ownlink = "453453435";

// or set this to true to not use the URL field
$field_ownlink_disabled = 'false';


?>
