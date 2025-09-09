<?php
echo "<h2>PHP Extensions Check</h2>";

// Check if SQLite3 is available
if (extension_loaded('sqlite3')) {
    echo "<p style='color: green;'>SQLite3 extension: Available</p>";
} else {
    echo "<p style='color: red;'>SQLite3 extension: Not Available</p>";
}

// Check if PDO with SQLite is available
if (extension_loaded('pdo_sqlite')) {
    echo "<p style='color: green;'>PDO SQLite extension: Available</p>";
} else {
    echo "<p style='color: red;'>PDO SQLite extension: Not Available</p>";
}

// Check if MySQL extensions are available
if (extension_loaded('mysqli')) {
    echo "<p style='color: green;'>MySQLi extension: Available</p>";
} else {
    echo "<p style='color: red;'>MySQLi extension: Not Available</p>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<p style='color: green;'>PDO MySQL extension: Available</p>";
} else {
    echo "<p style='color: red;'>PDO MySQL extension: Not Available</p>";
}

// Show all loaded extensions
echo "<h3>All Loaded Extensions:</h3>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $extension) {
    echo "<p>$extension</p>";
}
?>