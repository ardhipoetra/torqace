<?php
    include_once ("config.php");
	include_once 'constant.php';
	
	session_name($PBSWEBNAME);
	session_set_cookie_params($PBSWEBEXPTIME, $PBSWEBPATH);
	session_start();
	setcookie(session_name(), session_id(), time() + $PBSWEBEXPTIME, $PBSWEBPATH);
	
	include_once ("auth.php");
	auth_page();
	
	$username = $_SESSION['username'];
	if (empty($_SESSION['isadmin'])) {
		error_page("Not Authorized.");
		exit();
	}
	
	$daptar = shell_exec("/usr/local/sbin/listusers");
	$arr =  explode("\n", $daptar);
	
	if ($_GET['delwho']) {
		$dieduser = $_GET['delwho'];
		$x = file_put_contents('/var/www/html/data/deluser', "$dieduser".PHP_EOL, FILE_APPEND);
		foreach ($arr as $key => $uname) {
			if ($uname == $dieduser) {
				unset($arr[$key]);
				$arr = array_values($arr);
			}	
		}
	}
	
	
?>
<html>	
<body bgcolor="white">
	<h1>
	<img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo"><?php echo $TITLE_ADMIN; ?></h1>
	<?php include_once 'constant.php'; ?>
	<hr>
	<table style="height: 40px; width: 100%;">
		<td bgcolor="#CCDF9D">
		<center>
			<!-- <b>Navigation:</b> -->
			<a href="mainmenu.php"><?php echo $TITLE_MAINMENU; ?></a> || 
			<a href="upload.php"><?php echo $TITLE_UPLOAD; ?></a> || 
			<a href="qstat.php"><?php echo $TITLE_QSTAT; ?></a> ||
			<a href="dirview.php"><?php echo $TITLE_VIEWDIR; ?></a> ||
			<a href="admins.php"><?php echo $TITLE_ADMIN; ?></a> ||  
			<a href="logout.php">Logout</a>
		</center></td>
	</table>
	<hr/>
	<table border="1">
		<tr>
			<th>Username</th>
			<th>Delete?</th>
		</tr>
		<?php
			foreach ($arr as $uname) {
				if (!empty($uname)) {
					echo "<tr><td>";
					echo $uname."</td>";
					echo '<td><a href="admins.php?delwho='.$uname.'" onclick="return confirm(\'Are you sure to delete user '.$uname.'?\');">Delete</a></td>';
					echo "</tr>";
				}
			}
		
		?>
	</table>
	<hr/>
	<?php include_once("footer.php"); ?>
</body>
</html>