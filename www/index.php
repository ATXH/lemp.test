<?php
// Retrieve environment variables
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: '5432';

$conn_str = "host=$host port=$port dbname=$db user=$user password=$pass connect_timeout=5";
$dbconn = @pg_connect($conn_str);

if ($dbconn) {
    // UPDATED: Added guest_count column
    $table_query = "CREATE TABLE IF NOT EXISTS visitors (
        id SERIAL PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        guest_count INTEGER DEFAULT 1,
        visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    pg_query($dbconn, $table_query);

    // Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
        $name = pg_escape_string($dbconn, $_POST['name']);
        // UPDATED: Capture and escape the guest count
        $guests = (int)$_POST['guests']; 
        
        $insert_query = "INSERT INTO visitors (name, guest_count) VALUES ('$name', $guests)";
        $result = pg_query($dbconn, $insert_query);

        if ($result) {
            header("Location: index.php?success=" . urlencode($name));
            exit(); 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>LEMP Stack - PostgreSQL Replication - V1.2</title>
  <style>
    body { font-family: "Segoe UI", Roboto, sans-serif; background: #f5f7fa; color: #333; margin: 0; padding: 0; }
    .container { max-width: 800px; margin: 80px auto; background: #fff; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); padding: 40px; text-align: center; }
    h1 { color: #336791; }
    .status { font-size: 1.2em; margin: 15px 0; }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .form-container { margin: 30px 0; background: #f0f4f8; padding: 20px; border-radius: 10px; }
    input[type="text"], input[type="number"] { padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-right: 10px; }
    input[type="text"] { width: 40%; }
    input[type="number"] { width: 15%; }
    button { padding: 10px 20px; background-color: #336791; color: white; border: none; border-radius: 5px; cursor: pointer; transition: 0.3s; }
    button:hover { background-color: #274d6e; }
    .visitor-box { background: #fff; border-radius: 10px; margin-top: 20px; max-height: 300px; overflow-y: auto; box-shadow: inset 0 0 6px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background-color: #336791; color: white; position: sticky; top: 0; }
    .info { margin-top: 30px; background: #f0f4f8; padding: 15px; border-radius: 10px; font-size: 0.9em; text-align: left; }
  </style>
</head>
<body>
  <div class="container">
    <h1>🚀 LEMP Stack (PostgreSQL Edition V1.2)</h1>

    <?php if (!$dbconn): ?>
      <p class="status error">❌ PostgreSQL connection failed to host: <?= htmlspecialchars($host) ?></p>
    <?php else: ?>
      <p class="status success">✅ Connected to <strong>Primary Database</strong> successfully!</p>

      <?php if (isset($_GET['success'])): ?>
        <p class="success">🙌 Thanks for visiting, <strong><?= htmlspecialchars($_GET['success']) ?></strong>!</p>
      <?php endif; ?>

      <div class="form-container">
        <form method="POST" action="index.php">
          <input type="text" name="name" placeholder="Name" required>
          <input type="number" name="guests" placeholder="People" value="1" min="1" required>
          <button type="submit">Add Visitor</button>
        </form>
      </div>

      <h2>Recent Visitors</h2>
      <div class="visitor-box" id="visitor-table">
        <p>Loading visitors history...</p>
      </div>
    <?php endif; ?>

    <div class="info">
      <strong>Environment Info:</strong><br>
      Database Host: <?= htmlspecialchars($host) ?><br>
      PHP Version: <?= phpversion() ?><br>
    </div>
  </div>

  <script>
    async function loadVisitors() {
      try {
          const response = await fetch('fetch_visitor.php');
          const html = await response.text();
          document.getElementById('visitor-table').innerHTML = html;
      } catch (err) {
          console.error("Failed to load visitors", err);
      }
    }
    loadVisitors();
    setInterval(loadVisitors, 5000);
  </script>
</body>
</html>