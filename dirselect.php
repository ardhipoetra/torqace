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
include_once ("error.php");

auth_page();
$username = $_SESSION['username'];

if (!isset($_GET['action']) || $_GET['action'] == "") {
	// defult action is forscript
	$action = "script";
} else {
	$action = $_GET['action'];
	if ($action != "compile" && $action != "script") {
		error_page("Action unknow!");
		exit();
	}
}
if ($action == "compile") {
	$nextstep = "compile.php";
} else {
	$nextstep = "scriptcreate.php";
}

// get all hostnames defined in the config.php
$i = 0;
$host_names = array();
foreach ($PBSWEBHOSTLIST as $hostname => $hostdata) {
	$host_names[$i] = $hostname;
	$i = $i + 1;
}

$javascript_str = "\n\tvar directories=new Array(" . sizeof($host_names) . ");";
for ($i = 0; $i < sizeof($host_names); $i++) {

	// get directories
	$labellist = `ssh -l $username $host_names[$i] 'ls -p ~/$PBSWEBUSERDIR; exit' 2>&1`;
	$larray = explode("\n", $labellist);
	$j = 0;
	$dir_names = array();
	foreach ($larray as $dir) {
		if (preg_match("/\/+$/", $dir)) {
			$dir_names[$j] = preg_replace("/\/+$/", "", $dir);
			$j = $j + 1;
		}
	}

	// now fill in java program contents
	$javascript_str .= "\n\tdirectories[$i]=new Array(" . sizeof($dir_names) . ");";
	for ($j = 0; $j < sizeof($dir_names); $j++) {
		$javascript_str .= "\n\tdirectories[$i][$j]='$dir_names[$j]';";
	}
}
$javascript_str .= "\n\n";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>PBSWeb-Lite Directory Selection Page</title>

		<script language="JavaScript"><!--<?php echo $javascript_str; ?>
		function populateDirectories() {
			var selected = window.document.forms[0].host.selectedIndex;
			var hostname = window.document.forms[0].host.options[selected].value;
	
			// clear select box
			window.document.forms[0].directory.options.length = 0;
	
			for (var i = 0; i < directories[selected].length; i++) {
				window.document.forms[0].directory.options[i] = new Option();
				window.document.forms[0].directory.options[i].value = directories[selected][i];
				window.document.forms[0].directory.options[i].text = directories[selected][i];
			}
		}
	
		//-->
		</script>

	</head>
	<body bgcolor="white">
		<h1><img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo">
	<?php
		if ($action == "compile") {
			echo "Select a Directory to Compile";
		}
		if ($action == "script") {
			echo "Select a Directory to Create Job";
		}
	?>
		</h1>

		<?php /* Put standard navigation menu */
			include ("navbar.php");
		?>

		<hr>
		<table width="100%">
			<tr>
				<td>
				<form name="mainform" action="<?php print($nextstep); ?>" method="post">
					<table border="0">
						<tr>
							<td>
							<select name="host" size="8" onChange="javascript:populateDirectories()">
								<?php
									foreach ($host_names as $host) {
										echo "\n<option value='$host'>$host";
									}
							?>
							</select></td><td>
							<select name="directory" size="8">
								<option>------ No host name selected ------
							</select></td>
						</tr>
						<tr>
							<?php
								if ($action == "compile") {
									echo "<td>make clean first? <INPUT name=\"clean\" type=\"checkbox\" value=\"yes\"></td>";
									echo "<td align=\"right\"><input type=submit value=\"Compile\"></td>";
								}
								if ($action == "script") {
									echo "<td align=\"right\"><input type=submit value=\"Create Job\"></td>";
								}
						?>
						</tr>
					</table>
				</form></td>
			</tr>
		</table>
		<hr>
		<p>
			Send questions and comments to
			<?php
				echo "<a href=\"mailto:" . $PBSWEBMAIL . "\">";
				echo $PBSWEBMAIL . "</a>\n";
			?>
			You can find <a href='help.html'>help here </a>.
		</p>
		<!-- $Id: dirselect.php,v 1.7 2004/03/18 21:04:19 platin Exp $ -->
	</body>
</html>
