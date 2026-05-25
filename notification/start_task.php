<?php
require __DIR__ . '/db.php';

$taskId = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;
$hasTask = $taskId > 0;
$updateUrl = $hasTask ? '/notification/update_task.php?task_id=' . urlencode((string)$taskId) . '&status=In%20Progress&ajax=1' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Starting Task...</title>
  <link rel="stylesheet" href="/notification/notification/assets/css/style.css" />
</head>
<body class="loader-body">
  <div class="loader-card">
    <div class="spinner" aria-hidden="true"></div>
    <h1>Loading...</h1>
    <p>Please wait.</p>
  </div>

  <script>
    function showStatusMessage(message) {
      document.querySelector('.loader-card').innerHTML = '<h1>' + message + '</h1><p>This window will close automatically.</p>';
    }

    function closeWindowSoon() {
      setTimeout(function () {
        window.open('', '_self');
        window.close();
      }, 1200);
    }

    (async function () {
      <?php if (!$hasTask): ?>
      showStatusMessage('No task selected');
      closeWindowSoon();
      return;
      <?php endif; ?>
      try {
        const response = await fetch('<?php echo $updateUrl; ?>', { credentials: 'same-origin' });
        const data = await response.json();
        if (data && data.success) {
          showStatusMessage('Status updated');
          closeWindowSoon();
          return;
        }
        showStatusMessage('Unable to update task');
        closeWindowSoon();
      } catch (error) {
        showStatusMessage('Network error');
        closeWindowSoon();
      }
    })();
  </script>
</body>
</html>