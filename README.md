# podio-php-view2ics

simple PHP script to export a podio view of events as ics

## Description

This simple php script attempts to export an existing view in Podio which consists of calendar items as an ICS (iCal) feed.
There are some limitations and some aspects for configuring to keep in mind.

## Limitations

Recurring events are not supported. In this case only the first occurrence may be used.

Handling of empty fields may not be taken fully into account and produce errors - so make sure most typically used fields are populated.
Events need to have both a start and end time.

Performance is bad since the script fetches data for the fields by multiple API calls where it could otherwise get it from the huge object containing this data.

There is a simple caching mechanism built-in so that the feed is not updated upon every single request but at most every 30 minutes or so.

## Installation, Configuration

This script uses the [Podio PHP API](https://github.com/podio-community/podio-php) so this needs to be set up first. How to do it should be described at their site.

For each view intended to use, a configuration file needs to be set up:

* You need to create a copy based on the file `expcal_a3_example.inc` replacing the string `example` with the view id you are using from podio, for example `345342344` -> this would result in `expcal_a3_345342344.inc` for example.
* This file must be edited in order to reflect the access credentials being used from Podio (i.e. client_id, client_secret, app_token, app_id). You get those values when configuring your Podio app within Podio as Developer.
* Next you need to set the field ids used for the fields as describe in this configuration file. There are also options to use some of the built-in fields instead of custom ones. You get those ids also at developer settings of the app in Podio.
* Finally the exported ICS file is available at `web.host/yourpath/expcal_a3.php?345342344` where `345342344` represents the id of the podio view you configured this script for. This can be tested in a web browser like Firefox, maybe with the add-on [open in browser](https://addons.mozilla.org/firefox/addon/open-in-browser/).

## Thanks

Many thanks to:

* [Podio PHP API](https://github.com/podio-community/podio-php)
* [Jean-Baptiste Jung via catswhocode](https://catswhocode.com/phpcache/)
* [OpenWebCalendar](https://openwebcalendar.herokuapp.com)
* people at stack exchange
* etc...
