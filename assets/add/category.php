<?php
/**
 * /assets/add/category.php
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
include("../../_includes/start-session.inc.php");
include("../../_includes/config.inc.php");
include("../../_includes/database.inc.php");
include("../../_includes/software.inc.php");
include("../../_includes/auth/auth-check.inc.php");
include("../../_includes/timestamps/current-timestamp.inc.php");
include("../../_includes/classes/Error.class.php");

$error = new DomainMOD\Error();

$page_title = "Adding A New Category";
$software_section = "categories-add";

// Form Variables
$new_category = $_POST['new_category'];
$new_stakeholder = $_POST['new_stakeholder'];
$new_notes = $_POST['new_notes'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($new_category != "") {

        $sql = "INSERT INTO categories
				(name, stakeholder, notes, insert_time) VALUES 
				('" . mysqli_real_escape_string($connection, $new_category) . "', '" . mysqli_real_escape_string($connection, $new_stakeholder) . "', '" . mysqli_real_escape_string($connection, $new_notes) . "', '" . $current_timestamp . "')";
        $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

        $_SESSION['result_message'] = "Category <font class=\"highlight\">" . $new_category . "</font> Added<BR>";

        header("Location: ../categories.php");
        exit;

    } else {

        $_SESSION['result_message'] .= "Please enter the category name<BR>";

    }

}
?>
<?php include("../../_includes/doctype.inc.php"); ?>
<html>
<head>
<title><?php echo $software_title . " :: " . $page_title; ?></title>
<?php include("../../_includes/layout/head-tags.inc.php"); ?>
</head>
<body onLoad="document.forms[0].elements[0].focus()";>
<?php include("../../_includes/layout/header.inc.php"); ?>
<form name="add_category_form" method="post" action="<?php echo $PHP_SELF; ?>">
<strong>Category Name (150)</strong><a title="Required Field"><font class="default_highlight"><strong>*</strong></font></a><BR><BR>
<input name="new_category" type="text" value="<?php echo $new_category; ?>" size="50" maxlength="150">
<BR><BR>
<strong>Stakeholder (100)</strong><BR><BR>
<input name="new_stakeholder" type="text" value="<?php echo $new_stakeholder; ?>" size="50" maxlength="100">
<BR><BR>
<strong>Notes</strong><BR><BR>
<textarea name="new_notes" cols="60" rows="5"><?php echo $new_notes; ?></textarea>
<BR><BR>
<input type="submit" name="button" value="Add This Category &raquo;">
</form>
<?php include("../../_includes/layout/footer.inc.php"); ?>
</body>
</html>
