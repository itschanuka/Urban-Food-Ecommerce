<?php
@include 'config.php';

if (!isset($_GET['table'])) {
    die('No table selected.');
}

$table = $_GET['table'];

// Get table creation SQL
$createTable = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC)['Create Table'];

// Fetch all data
$data = $conn->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);

// Start building SQL content
$output = "-- Table structure for table `$table`\n\n";
$output .= $createTable . ";\n\n";

$output .= "-- Dumping data for table `$table`\n\n";

foreach ($data as $row) {
    $values = array_map(function($value) use ($conn) {
        return $conn->quote($value);
    }, $row);
    $output .= "INSERT INTO `$table` (`" . implode('`,`', array_keys($row)) . "`) VALUES (" . implode(',', $values) . ");\n";
}

// Send headers
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $table . '.sql"');
echo $output;
exit();
?>
