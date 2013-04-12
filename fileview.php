<?php /*

 PBSWeb-Lite: A Simple Web-based Interface to PBS

 Copyright (C) 2003, 2004 Yuan-Chung Cheng

 PBSWeb-Lite is based on the PBSWeb code written by Paul Lu et al.
 See History for more detailes.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

 */
?>
<?php
include_once ("config.php");

session_name($PBSWEBNAME);
session_set_cookie_params($PBSWEBEXPTIME, $PBSWEBPATH);
session_start();
setcookie(session_name(), session_id(), time() + $PBSWEBEXPTIME, $PBSWEBPATH);

include_once ("auth.php");

auth_page();

$username = $_SESSION['username'];

$host = $_GET['host'];
$filename = str_replace("/../", "/", "/" . $_GET['filename']);
$filename = str_replace("*", "", $filename);
$filename = str_replace("?", "", $filename);
$filename = preg_replace("/\s/", "", $filename);
// white space is not welcome!
$filename = preg_replace("/\/+$/", "", $filename);
$filename = preg_replace("/^\/+/", "", $filename);
if (strpos($filename, "/")) {
	$dirup = preg_replace("/\/.*$/", "", $filename);
} else {
	$dirup = "";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Output File <?php print($filename); ?></title>
  </head>
  <body bgcolor="white">
    <h1>
      <img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo">Contents of File <?php print($filename); ?>
    </h1>

<?php /* Put standard navigation menu */
include ("navbar.php");
?>
    <hr>

    <?php
		$fileview_cmd = "cat ~/" . $PBSWEBUSERDIR . "/" . $filename;
		$fileview_result = `ssh -l $username $host '$fileview_cmd; exit' 2>&1`;
		print("<pre>\n$fileview_result\n</pre>");
    ?>
    
    <p>
    <a href=<?php print("\"dirview.php?host=$host&dir=$dirup\""); ?>>Back</a>
    </p>
<hr>
<p>Send questions and comments to 
<?php
	echo "<a href=\"mailto:" . $PBSWEBMAIL . "\">";
	echo $PBSWEBMAIL . "</a>\n";
?>
You can find <a href='help.html'>help here </a>.</p>
<!-- $Id: fileview.php,v 1.11 2004/03/18 21:04:19 platin Exp $ -->
  </body>
</html>
