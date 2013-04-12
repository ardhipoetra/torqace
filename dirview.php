<?php /*

 Torqace : Torque Interface

 Copyright (C) 2013, Ardhi Putra Pratama

 Torqace is based on the PBSWeb-Lite code written by Yuan-Chung Cheng.
 PBSWeb-Lite is based on the PBSWeb code written by Paul Lu et al.
 See History for more details.

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
<?php //
// dirview.php
// A simple file manager used to manage the files under ~/pbsweb
//
include_once ("config.php");

session_name($PBSWEBNAME);
session_set_cookie_params($PBSWEBEXPTIME, $PBSWEBPATH);
session_start();
setcookie(session_name(), session_id(), time() + $PBSWEBEXPTIME, $PBSWEBPATH);

include_once ("auth.php");

auth_page();

$username = $_SESSION['username'];

if (!$_GET['host']) {
	$host = $PBSWEBDEFAULTHOST;
} else {
	$host = $_GET['host'];
}

if (!$_GET['dir']) {
	$dirnow = "";
	$dirup = "";
} else {
	$dirnow = str_replace("/../", "/", "/" . $_GET['dir']);
	$dirnow = str_replace("*", "", $dirnow);
	$dirnow = str_replace("?", "", $dirnow);
	$dirnow = preg_replace("/\s/", "", $dirnow);
	// white space is not welcome!
	$dirnow = preg_replace("/\/+$/", "", $dirnow);
	$dirnow = preg_replace("/^\/+/", "", $dirnow);
	if (strpos($dirnow, "/")) {
		$dirup = preg_replace("/\/.*$/", "", $dirnow);
	} else {
		$dirup = "";
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<META HTTP-EQUIV="no-cache">
		<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
		<title>Lihat Berkas</title>
	</head>
	<body bgcolor="white">
		<h1><img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo">Lihat Berkas </h1>

		<?php /* Put standard navigation menu */
			include ("navbar.php");
		?>

		<hr>
		<form name="reloadform" method="GET" action="dirview.php">
			<table>
				<tr>
					<th>Host:</th><td>
					<select name="host" onChange="window.document.reloadform.submit()">
						<?php // get the list of hosts
						foreach ($PBSWEBHOSTLIST as $hostname => $hostdata) {
							if ($hostname == $host) {
								echo "<OPTION VALUE=\x22$hostname\x22 SELECTED>$hostname";
							} else {
								echo "<OPTION VALUE=\x22$hostname\x22>$hostname";
							}
						}
						?>
					</select></td>
					<td>
					<INPUT TYPE="submit" VALUE="Reload">
					</td>
				</tr>
			</table>
		</form>
		<?php 
			echo "<h2>";
			echo $host . ": " . $dirnow;
			echo "</h2>";

			$dirlist_cmd = "ls -lA ~/" . $PBSWEBUSERDIR . "/" . $dirnow;
			$dirlist_cmd = $dirlist_cmd . " | sed \"s/ ->.*\$//g\" ";
			$dirlist_cmd = $dirlist_cmd . " | tr -s \" \" \" \"  | cut -d\" \" -f1,5-";
			$dirlist_result = `ssh -l $username $host '$dirlist_cmd; exit' 2>&1`;
		?>
		<table border=0>
			<tr>
				<td align="center">Name</td><td align="center">Permission</td><td align="center">Size</td><td align="center">Time</td><td>&nbsp;</td><td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center">--------------</td><td align="center">------------------</td><td align="center">--------------</td><td align="center">--------------------</td><td>&nbsp;</td><td>&nbsp;</td>
			</tr>
			<tr>
				<td align="left"><a href=<?php print("\"dirview.php?host=$host&dir=$dirup\""); ?>> <img src="img/parent.gif" border=0 alt="parent directory">..</a></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			</tr>
			<?php 
				$listarray = explode("\n", $dirlist_result);
				for ($i = 0; $i < sizeof($listarray); $i = $i + 1) {
					$first = $listarray[$i]{0};
					if ($first == "-" || $first == "d") {
						// regular file or directory
						print("<tr>");
						$linearray = explode(" ", $listarray[$i]);
						$nsize = sizeof($linearray);
						$fperm = $linearray[0];
						$fsize = $linearray[1];
						$fname = $linearray[$nsize - 1];
						$fdate = "";
						for ($j = 2; $j < $nsize - 1; $j = $j + 1) {
							// date/time is everything in between
							$fdate = $fdate . " " . str_pad($linearray[$j], 2, " ", STR_PAD_LEFT) . " ";
						}
						$fdate = str_replace(" ", "&nbsp;", $fdate);
						print("<td align=\"left\">");
						$fullname = $dirnow . "/" . $fname;
						if ($first == "d") {
							print("<a href=\"dirview.php?host=$host&dir=$fullname\">");
							print("<img src=\"img/foldericon.gif\" border=0 alt=\"$fname\">$fname");
							print("</a>");
						} else {
							print("<a href=\"fileview.php?host=$host&filename=$fullname\">");
							print("<img src=\"img/fileicon.gif\" border=0 alt=\"$fname\">$fname");
							print("</a>");
						}
						print("</td>");
						print("<td align=\"left\">$fperm</td>");
						print("<td align=\"right\">$fsize</td>");
						print("<td align=\"right\">$fdate</td>");
						print("<td align=\"center\">");
						print("&nbsp;&nbsp;<a href=\"filedownload.php?host=$host&filename=$fullname\" target=\"_new\">Download</a>&nbsp;&nbsp;");
						print("</td>");
						print("<td align=\"center\">");
						print("&nbsp;&nbsp;<a href=\"filedel.php?host=$host&filename=$fullname\">Delete</a>&nbsp;&nbsp;");
						print("</td>");
						print("<td align=\"center\">");
						if ($first == "d") {
							print("&nbsp;&nbsp;<a href=\"scriptcreate.php?directory=$fullname&host=$host\">Generate Script</a>&nbsp;&nbsp;");
						}						
						print("</td>");
						print("</tr>");
					}
				}
			?>
		</table>

		<?php
		include ("navbar.php");
		?>
		<hr>
		<p>
			Send questions and comments to
			<?php
				echo "<a href=\"mailto:" . $PBSWEBMAIL . "\">";
				echo $PBSWEBMAIL . "</a>\n";
			?>
			You can find <a href='help.html'>help here </a>.
		</p>
		<!-- $Id: dirview.php,v 1.8 2004/03/18 21:04:19 platin Exp $ -->
	</body>
</html>
