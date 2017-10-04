<?php
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 13/07/17
 * Time: 06:57
 */
// contains utility functions mb_stripos_all() and apply_highlight()
require_once '../autocomplete/local_utils.php';
require_once "../../class_config.php";
$config = new config();

// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}

// get what user typed in autocomplete input
$term = trim($_GET['term']);

$a_json = array();
$a_json_row = array();

$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Only letters and digits are permitted..."));
$json_invalid = json_encode($a_json_invalid);

// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);

// SECURITY HOLE ***************************************************************
// allow space, any unicode letter and digit, underscore and dash
if (preg_match("/[^\040\pL\pN_-]/u", $term)) {
    print $json_invalid;
    exit;
}
// *****************************************************************************

// database connection
$conn = new mysqli($config->mysql_config['host'], $config->mysql_config['username'], $config->mysql_config['password'], $config->mysql_config['dbname']);

if ($conn->connect_error) {
    echo 'Database connection failed...' . 'Error: ' . $conn->connect_errno . ' ' . $conn->connect_error;
    exit;
} else {
    $conn->set_charset('utf8');
}

$parts = explode(' ', $term);
$cnt = count($parts);

/**
 * Create SQL
 */
// Ќадо учесть, что если в отображаемом значении не будет строки поиска (которое выдел€етс€) то оно не будет выведено
// »ными словами надо чтобы $row['name'] содержало $part
$sql = "SELECT CONCAT_WS(' ',shifr,name,obozn,kod_elem) AS name,kod_elem FROM elem WHERE del=0";
for ($i = 0; $i < $cnt; $i++) {
    $part = $conn->real_escape_string($parts[$i]);
    $sql .= " AND CONCAT_WS(' ',shifr,name,obozn,kod_elem) LIKE '%$part%'";
}

$rs = $conn->query($sql);
if ($rs === false) {
    $user_error = 'Wrong SQL: ' . $sql . 'Error: ' . $conn->errno . ' ' . $conn->error;
    trigger_error($user_error, E_USER_ERROR);
}

while ($row = $rs->fetch_assoc()) {
    $a_json_row["id"] = $row['kod_elem'];
    $a_json_row["value"] = $row['name'];
    $a_json_row["label"] = $row['name'];
    array_push($a_json, $a_json_row);
}

// highlight search results
$a_json = apply_highlight($a_json, $parts);

$json = json_encode($a_json);
print $json;