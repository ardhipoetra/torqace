<?php
/*

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
<?php include_once 'constant.php'; ?>
<hr>
<table style="height: 40px; width: 100%;">
	<td bgcolor="#CCDF9D">
	<center>
		<!-- <b>Navigation:</b> -->
		<a href="mainmenu.php"><?php echo $TITLE_MAINMENU; ?></a> || 
		<a href="upload.php"><?php echo $TITLE_UPLOAD; ?></a> || <!--
		<a href="dirselect.php?action=compile">Compile Uploaded Files</a> ||
		<a href="dirselect.php?action=script">Script Creation and Submission</a> ||
		-->
		<a href="qstat.php"><?php echo $TITLE_QSTAT; ?></a> ||
		<a href="dirview.php"><?php echo $TITLE_VIEWDIR; ?></a> || <!-- <a href="index.html">Login</a> ||
		-->
		<a href="logout.php">Logout</a>
	</center></td>
</table>
