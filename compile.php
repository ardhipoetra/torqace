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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>PBSWeb-Lite Program Compilation</title>
	</head>
	<body bgcolor="white">
		<h1><img src="img/littlepbsguy.jpg" border="0" height="102" width="92" alt="PBS Logo">PBSWeb-Lite Program Compilation </h1>

		<?php /* Put standard navigation menu */
			include ("navbar.php");
		?>
		<hr>

	<?php
		if ($_POST['host'] && $_POST['directory']) {

			$host = $_POST['host'];
			$directory = $_POST['directory'];
			$clean = $_POST['clean'];

			if ($clean == "yes") {
				$makeclean = `ssh -l $username $host 'cd ~/$PBSWEBUSERDIR/$directory; make clean; exit' 2>&1`;
				echo "<h2>make clean output:</h2>";
				echo "<pre>$makeclean</pre>";
			}
			$make = `ssh -l $username $host 'cd ~/$PBSWEBUSERDIR/$directory; make; exit' 2>&1`;
			echo "<h2>make output:</h2>";
			echo "<pre>$make</pre>";
			$compiled = TRUE;
		} else {
			echo "<p>Not enough information to process request</p>";
		}

		echo "<p>\n";
		echo "<b>Suggested Next Step:</b>\n";
		if ($compiled) {
			echo " <a href=\"scriptcreate.php?directory=$directory&host=$host\">Continue to Script Generation</a> | ";
		}
		echo "<a href=\"mainmenu.php\">Start Page</a>\n";
		echo "</p>\n";
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
		<!-- $Id: compile.php,v 1.13 2004/03/18 21:04:19 platin Exp $ -->
	</body>
</html>
