<?php
/**
 * /system/admin/edit/domain-field.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (C) 2010-2015 Greg Chetcuti <greg@chetcuti.com>
 *
 * Project: http://domainmod.org   Author: http://chetcuti.com
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
?>
<?php
include("../../../_includes/start-session.inc.php");

// If the user isn't an administrator, redirect them to $full_redirect
$full_redirect = "../../../invalid.php";
include("../../../_includes/auth/admin-user-check.inc.php");

include("../../../_includes/config.inc.php");
include("../../../_includes/database.inc.php");
include("../../../_includes/software.inc.php");
include("../../../_includes/timestamps/current-timestamp.inc.php");
include("../../../_includes/auth/auth-check.inc.php");
include("../../../_includes/classes/Error.class.php");

$error = new DomainMOD\Error();

$page_title = "Editing A Custom Domain Field";
$software_section = "admin-domain-field-edit";

$del = $_GET['del'];
$really_del = $_GET['really_del'];

$cdfid = $_GET['cdfid'];

$new_name = $_POST['new_name'];
$new_description = $_POST['new_description'];
$new_cdfid = $_POST['new_cdfid'];
$new_notes = $_POST['new_notes'];

if ($new_cdfid == "") $new_cdfid = $cdfid;

$sql = "SELECT id
		FROM domain_fields
		WHERE id = '" . $cdfid . "'";
$result = mysqli_query($connection, $sql);

if (mysqli_num_rows($result) == 0) {

		$_SESSION['result_message'] .= "You're trying to edit an invalid Custom Domain Field<BR>";
		
		header("Location: ../domain-fields.php");
		exit;

}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $new_name != "") {

	$sql = "UPDATE domain_fields
			SET name = '" . mysqli_real_escape_string($connection, $new_name) . "',
				description = '" . mysqli_real_escape_string($connection, $new_description) . "',
				notes = '" . mysqli_real_escape_string($connection, $new_notes) . "',
				update_time = '" . $current_timestamp . "'
			WHERE id = '" . $new_cdfid . "'";
	$result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);
	
	$sql = "SELECT field_name
			FROM domain_fields
			WHERE id = '" . $new_cdfid . "'";
	$result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);
	while ($row = mysqli_fetch_object($result)) { $temp_field_name = $row->field_name; }
	
	$_SESSION['result_message'] .= "Custom Domain Field <font class=\"highlight\">" . $new_name . " (" . $temp_field_name . ")</font> Updated<BR>";
	
	header("Location: ../domain-fields.php");
	exit;

} else {

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
		if ($new_name == "") $_SESSION['result_message'] .= "Enter the display name<BR>";
		
	} else {

		$sql = "SELECT f.name, f.field_name, f.description, f.notes, f.insert_time, f.update_time, t.name AS type
				FROM domain_fields AS f, custom_field_types AS t
				WHERE f.type_id = t.id
				  AND f.id = '" . $cdfid . "'
				ORDER BY f.name";
		$result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

		while ($row = mysqli_fetch_object($result)) {
			
			$new_name = $row->name;
			$new_field_name = $row->field_name;
			$new_description = $row->description;
			$new_field_type = $row->type;
			$new_notes = $row->notes;

		}

	}

}

if ($del == "1") {

	$_SESSION['result_message'] = "Are you sure you want to delete this Custom Domain Field?<BR><BR><a href=\"" . $PHP_SELF . "?cdfid=" . $cdfid . "&really_del=1\">YES, REALLY DELETE THIS CUSTOM DOMAIN FIELD</a><BR>";

}

if ($really_del == "1") {

	if ($cdfid == "") {

		$_SESSION['result_message'] = "The Custom Domain Field cannot be deleted<BR>";

	} else {
		
		$sql = "SELECT name, field_name
				FROM domain_fields
				WHERE id = '" . $cdfid . "'";
		$result = mysqli_query($connection, $sql);
		while ($row = mysqli_fetch_object($result)) {
			$temp_name = $row->name;
			$temp_field_name = $row->field_name;
		}

		$sql = "ALTER TABLE `domain_field_data`
				DROP `" . $temp_field_name . "`";
		$result = mysqli_query($connection, $sql);

		$sql = "DELETE FROM domain_fields
				WHERE id = '" . $cdfid . "'";
		$result = mysqli_query($connection, $sql);

		$_SESSION['result_message'] = "Custom Domain Field <font class=\"highlight\">" . $temp_name . " (" . $temp_field_name . ")</font> Deleted<BR>";
		
		header("Location: ../domain-fields.php");
		exit;

	}

}
?>
<?php include("../../../_includes/doctype.inc.php"); ?>
<html>
<head>
<title><?php echo $software_title . " :: " . $page_title; ?></title>
<?php include("../../../_includes/layout/head-tags.inc.php"); ?>
</head>
<body>
<?php include("../../../_includes/layout/header.inc.php"); ?>
<form name="edit_user_form" method="post" action="<?php echo $PHP_SELF; ?>">
<strong>Display Name (75)</strong><a title="Required Field"><font class="default_highlight">*</font></a><BR><BR><input name="new_name" type="text" size="30" maxlength="75" value="<?php if ($new_name != "") echo htmlentities($new_name); ?>"><BR><BR>
<strong>Database Field Name</strong><BR><BR><?php echo $new_field_name; ?><BR><BR>
<strong>Data Type</strong><BR><BR>
<?php echo $new_field_type; ?>
<BR><BR>
<strong>Description (255)</strong></a><BR><BR><input name="new_description" type="text" size="50" maxlength="255" value="<?php if ($new_description != "") echo htmlentities($new_description); ?>">
<BR><BR>
<strong>Notes</strong><BR><BR>
<textarea name="new_notes" cols="60" rows="5"><?php echo $new_notes; ?></textarea>
<input type="hidden" name="new_cdfid" value="<?php echo $cdfid; ?>">
<BR><BR>
<input type="submit" name="button" value="Update Custom Field &raquo;">
</form>
<BR><BR><a href="<?php echo $PHP_SELF; ?>?cdfid=<?php echo $cdfid; ?>&del=1">DELETE THIS CUSTOM DOMAIN FIELD</a>
<?php include("../../../_includes/layout/footer.inc.php"); ?>
</body>
</html>
