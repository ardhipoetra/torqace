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
<?php
include_once ("config.php");
include_once 'constant.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html>
	<head>
		<title><?php echo $TITLE_INDEX; ?></title>
	</head>
	<body bgcolor="white">
		<h1><img src="<?php echo $PBSWEBHEADERLOGO; ?>" border="0" height="102" width="92" alt="PBS Logo"> <?php echo $TITLE_INDEX; ?> </h1>
		<hr>
		<table>
			<tr>
				<td>
				<table border="5" cellpadding="6">
					<tr>
						<td height="460" width="400" valign="top"><h1>Login</h1><font size="+2">
							<FORM METHOD="POST"  ACTION="mainmenu.php" ENCTYPE="application/x-www-form-urlencoded">
								<INPUT TYPE="hidden" NAME="loginnow" VALUE="true">
								<TABLE>
									<TR ALIGN="LEFT">
										<TH>User Name: </TH>
										<TD>
										<INPUT TYPE="text" NAME="username"  SIZE=30>
										</TD>
									</TR>
									<TR ALIGN="LEFT">
										<TH>Password: </TH>
										<TD>
										<INPUT TYPE="password" NAME="password"  SIZE=30>
										</TD>
									</TR>
									<TR ALIGN="LEFT">
										<TD>
										<INPUT TYPE="submit" NAME=".submit" VALUE="Login">
										</TD>
									</TR>
								</TABLE>
								<hr width="400">
								Doesn't have account yet? Register <a href="regist.php">here</a>!
							</FORM> </font></td>
					</tr>
				</table></td>
				<td>
				<table border="5">
					<tr>
						<td height="460" width="400" valign="center">
						<center>
							<img src="<?php print($PBSWEBMAINLOGO); ?>" border="0" height="450" width="390" alt="Main Page Logo">
						</center></td>
					</tr>
				</table></td>
			</tr>
		</table>
		<hr>
		<? include_once("footer.php"); ?>
		<!-- $Id: index.php,v 1.6 2004/03/18 21:04:19 platin Exp $ -->
	</body>
</html>
