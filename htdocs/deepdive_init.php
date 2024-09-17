<?php
/**
 * @package    CCT
 * @file       cct_initialize.php
 * @author     Greg Parkin <parking@us.ibm.com>
 * @date       $Date:  $ GMT
 * @version    $Revision:  $
 *
 * $Log:  $
 *
 * $Source:  $
 */

//
// Called once when a user signs into CCT
// The purpose of this module is to figure out what time zone a user is in and to retrieve
// their personal information from cct7_mnet and cct7_users. $_SESSION[] variables are set
// to make the information available to all the programs during their session.    
//

set_include_path("/opt/ibmtools/www/DeepDive/classes");

//
// Class autoloader - Removes the need to add include and require statements
//
require_once __DIR__ . '/autoload.php';

if (session_id() == '')
	session_start();

ini_set("error_reporting",        "E_ALL & ~E_DEPRECATED & ~E_STRICT");
ini_set("log_errors",             1);
ini_set("error_log",              "/Users/gregparkin/www/DeepDive/logs/php-error.log");
ini_set("log_errors_max_len",     0);
ini_set("report_memleaks",        1);
ini_set("track_errors",           1);
ini_set("html_errors",            1);
ini_set("ignore_repeated_errors", 0);
ini_set("ignore_repeated_source", 0);

//if (isset($_SESSION['user_cuid']) && $_SESSION['user_cuid'] == 'gparkin')
//{
	ini_set('xdebug.collect_vars',    '5');
	ini_set('xdebug.collect_vars',    'on');
	ini_set('xdebug.collect_params',  '4');
	ini_set('xdebug.dump_globals',    'on');
	ini_set('xdebug.dump.SERVER',     'REQUEST_URI');
	ini_set('xdebug.show_local_vars', 'on');

	//$path = '/usr/lib/pear';
	//set_include_path(get_include_path() . PATH_SEPARATOR . $path);
//}
//else
//{
//	ini_set('display_errors', 'Off');
//}

if (isset($_SESSION['deepdive_init']))
    exit();

$_SESSION['BUILD_VERSION'] = "1.0";
$_SESSION['BUILD_DATE']    = "09/03/2024";

$_SESSION['DEEPDIVE_INIT'] = "READY";

$ora = new oracle();
$lib = new library();
date_default_timezone_set('America/Denver');

    $_SESSION['REMOTE_USER']       = "gparkin";
    $_SESSION['WWW']               = 'http://deepdive.local/cct7.php';
    $_SESSION['CCT_APPLICATION']   = 'DeepDive - MacOS';
    $_SESSION['CCT_SERVER']        = 'Development Server';
    $_SESSION['DOCUMENT_ROOT']     = $_SERVER['DOCUMENT_ROOT'];
    $_SESSION['HTML_TITLE']        = 'DeepDive - LENOVO';
    $_SESSION['COUNTER_FILE']      = '/Users/gregparkin/www/DeepDive/counters/deepdive_page_counts';


if ($_SESSION['REMOTE_USER'] === 'gparkin')
{
    $_SESSION['user_access_level'] = "admin";
    $_SESSION['is_debug_on']       = 'Y';
    $_SESSION['debug_level1']      = 'Y';
    $_SESSION['debug_level2']      = 'Y';
    $_SESSION['debug_level3']      = 'Y';
    $_SESSION['debug_level4']      = 'Y';
    $_SESSION['debug_level5']      = 'Y';
    $_SESSION['debug_path']        = '/opt/ibmtools/cct7/debug/';
    $_SESSION['debug_mode']        = 'w';

    $lib->debug_start('cct_init.html');
}
else
{
    $_SESSION['user_access_level'] = "user";
    $_SESSION['is_debug_on']       = 'N';
    $_SESSION['debug_level1']      = 'N';
    $_SESSION['debug_level2']      = 'N';
    $_SESSION['debug_level3']      = 'N';
    $_SESSION['debug_level4']      = 'N';
    $_SESSION['debug_level5']      = 'N';
    $_SESSION['debug_path']        = '/opt/ibmtools/cct7/debug/';
    $_SESSION['debug_mode']        = 'w';
}

//
// Contact Footer information
//
$_SESSION['NET_GROUP_NAME']  = "NG-SDS";   // Footer Remedy Assign Group for creating trouble tickets
$_SESSION['NET_PIN']         = "17340";
// Footer Net-Pin number for NET paging
$_SESSION['APP_ACRONYM']     = 'DeepDive'; // Change Coordination Tool acronym - CCT

//
// The time zone name (i.e. America/Chicago) is pasted as an argument to this script.
// Next we create a DateTimeZone object using the $local_timezone_name. Then we get
// a DateTime object so we can return the time zone abbreviation name and GMT offset.
//
$local_timezone_name      = $_GET['timezone'];
$local_dtz                = new DateTimeZone($local_timezone_name);
$local_dt                 = new DateTime('now', $local_dtz);
$local_timezone_abbr      = $local_dt->format('T'); // (i.e. MST)
$local_timezone_offset    = $local_dt->getOffset();

//
// Our Oracle function fn_number_to_date() needs a number (UNIX mktime) value and
// a valid time zone abbreviation name in order to convert a number to a date.
//
// Not all timezones are identified in Oracle (such as Bangalore, India), so we will
// use Denver (MST7MDT) as a baseline and add another offset value to the datetime
// number to convert it to the proper user's timezone. So below we get the baseline
// information for Denver, Colorado.
//
$baseline_timezone_name   = 'America/Denver';
$baseline_dtz             = new DateTimeZone($baseline_timezone_name);
$baseline_dt              = new DateTime('now', $baseline_dtz);
$baseline_timezone_abbr   = $baseline_dt->format('T'); // (i.e. MST)
$baseline_timezone_offset = $baseline_dt->getOffset();

//
// $sql_time_offset and $sql_time_zone are pasted to all the SQL SELECT builder code to convert
// dates (represented as numbers) into the local user dates.
//
// Exa.: select fn_number_to_date(cm_start_date + $sql_time_offset, '$sql_time_zone') as cm_start_time from cct7_tickets;
//       select fn_number_to_date(1428602318 + (-3600), 'MDT') as cm_start_time from cct7_tickets;
//
// $sql_time_offset can be a positive or negative number so we will put it in ( ) just to make sure Oracle
// can deal with the calculation.
//
$sql_zone_offset      = "(" . $baseline_timezone_offset - $local_timezone_offset . ")";
$sql_zone_abbr        = $baseline_timezone_abbr;

$_SESSION['remedy_timezone']           = $local_timezone_name  . " (" . $local_timezone_abbr . ")";
$_SESSION['remedy_timezone_name']      = $local_timezone_name;
$_SESSION['remedy_timezone_abbr']      = $local_timezone_abbr;
$_SESSION['remedy_timezone_offset']    = $local_timezone_offset;

//
// Put all this information in the user's session cache so it can be used by all the PHP programs.
//
// All this information gets recorded in the user's settings record found in table cct7_user_settings for
// debugging purposes.
//
// $_SESSION['local_timezone']           = $local_timezone_name  . " (" . $local_timezone_abbr . ")";
// $_SESSION['local_timezone_name']      = $local_timezone_name;
// $_SESSION['local_timezone_abbr']      = $local_timezone_abbr;
// $_SESSION['local_timezone_offset']    = $local_timezone_offset;

$_SESSION['local_timezone']           = $baseline_timezone_name . " (" . $baseline_timezone_abbr . ")";
$_SESSION['local_timezone_name']      = $baseline_timezone_name;
$_SESSION['local_timezone_abbr']      = $baseline_timezone_abbr;
$_SESSION['local_timezone_offset']    = $baseline_timezone_offset;

$_SESSION['sql_zone_offset']          = $sql_zone_offset;
$_SESSION['sql_zone_abbr']            = $sql_zone_abbr;

$_SESSION['baseline_timezone']        = $baseline_timezone_name . " (" . $baseline_timezone_abbr . ")";
$_SESSION['baseline_timezone_name']   = $baseline_timezone_name;
$_SESSION['baseline_timezone_abbr']   = $baseline_timezone_abbr;
$_SESSION['baseline_timezone_offset'] = $baseline_timezone_offset;

$_SESSION['time_difference']          = sprintf("%d seconds or %d hours", $sql_zone_offset, $sql_zone_offset / 3600);

//
// Retrieve this users list of NET group pins and Subscriber pins.

$user_groups_hash = array();
$remote_user = $_SESSION['REMOTE_USER'];

