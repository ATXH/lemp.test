<?php
$host = getenv('DB_HOST'); 
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: '5432';

$conn_str = "host=$host port=$port dbname=$db user=$user password=$pass connect_timeout=3";
$dbconn = @pg_connect($conn_str);

if (!$dbconn) {
    echo "<p class='error'>❌ Failed to connect to database.</p>";
    exit;
}

$result = pg_query($dbconn, "SELECT * FROM visitors ORDER BY visit_time DESC LIMIT 10");

if (pg_num_rows($result) > 0) {
    // UPDATED: Added "Guests" Header
    echo "<table>
            <tr><th>ID</th><th>Name</th><th>Guests</th><th>Visit Time</th></tr>";
    while ($row = pg_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['guest_count'] ?? '1') . "</td>
                <td>{$row['visit_time']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No visitors yet. Be the first!</p>";
}

pg_close($dbconn);
?>