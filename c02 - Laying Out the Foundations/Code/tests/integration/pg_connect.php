<!DOCTYPE html>

<html lang="en">

<head>
<title>Some relevant title</title>
</head>

<body>
<?php
define('LINE_BREAK', '<br/>');

echo "My first PHP script!";
echo LINE_BREAK;

echo "Connect to PostgreSQL";
echo LINE_BREAK;

echo "Connect to " . getenv('HATSHOP_DB_SERVER');
echo LINE_BREAK;

$conn = pg_connect(
    "host=" . getenv('HATSHOP_DB_SERVER') .
    " dbname=" . getenv('HATSHOP_DB_DATABASE') .
    " user=" . getenv('HATSHOP_DB_USERNAME') .
    " password=" . getenv('HATSHOP_DB_PASSWORD')
    );

// Check if connection was successful
if (!$conn) {
    echo "Error: Unable to connect to database.";
    echo LINE_BREAK;

    exit;
}


echo "Execute query";
echo LINE_BREAK;

$result = pg_query($conn, "SELECT 1 FROM department");

echo "Check if query was successful";
echo LINE_BREAK;

if (!$result) {
    echo "Error: Query failed.";
    echo LINE_BREAK;

    exit;
}

echo " script! Executed";
echo LINE_BREAK;


echo "Close the connection";
echo LINE_BREAK;

pg_close($conn);
echo "connection Closed";
echo LINE_BREAK;

?>

</body>

</html>
