<!DOCTYPE html>

<html>
<body>
<?php
echo "My first PHP script!";
echo "<br/>";

echo "Connect to PostgreSQL";
echo "<br/>";

$conn = pg_connect("host=" . getenv('HATSHOP_DB_SERVER') .
                   " dbname=" . getenv('HATSHOP_DB_DATABASE') .
                   " user=" . getenv('HATSHOP_DB_USERNAME') .
                   " password=" . getenv('HATSHOP_DB_PASSWORD'));

// Check if connection was successful
if (!$conn) {
    echo "Error: Unable to connect to database.";
    echo "<br/>";

    exit;
}


echo "Execute query";
echo "<br/>";

$result = pg_query($conn, "SELECT 1 FROM department");

echo "Check if query was successful";
echo "<br/>";

if (!$result) {
    echo "Error: Query failed.";
    echo "<br/>";

    exit;
}

echo " script! Executed";
echo "<br/>";


echo "Close the connection";
echo "<br/>";

pg_close($conn);
echo "connection Closed";
echo "<br/>";

?>

</body>

</html>
