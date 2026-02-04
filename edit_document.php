<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$doc = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM DocumentLog WHERE DocID = ?");
    $stmt->execute([$_GET['id']]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$doc) {
    header('Location: documents.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Document - Document LogBook</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <header style="margin-bottom: 2rem;">
            <h1>Edit Document</h1>
        </header>

        <div class="card">
            <form action="process_edit_document.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $doc['DocID']; ?>">
                
                <!-- Office -->
                <div style="margin-bottom: 1rem;">
                    <label for="office" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Office / Department</label>
                    <input type="text" id="office" name="office" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           value="<?php echo htmlspecialchars($doc['Office']); ?>">
                </div>

                <!-- Document Name -->
                <div style="margin-bottom: 1rem;">
                    <label for="doc_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Document Name</label>
                    <input type="text" id="doc_name" name="doc_name" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           value="<?php echo htmlspecialchars($doc['Subject']); ?>">
                </div>

                <!-- Description -->
                <div style="margin-bottom: 1rem;">
                    <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Description</label>
                    <textarea id="description" name="description" rows="4" 
                              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"><?php echo htmlspecialchars($doc['Description']); ?></textarea>
                </div>

                <!-- Received By -->
                <div style="margin-bottom: 1rem;">
                    <label for="received_by" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Received By <span id="received_req" style="color: red; display: <?php echo $doc['Status'] == 'Outgoing' ? 'inline' : 'none'; ?>;">*</span></label>
                    <input type="text" id="received_by" name="received_by" 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           value="<?php echo htmlspecialchars($doc['ReceivedBy']); ?>">
                </div>
                
                <!-- Status -->
                 <div style="margin-bottom: 1.5rem;">
                    <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Status</label>
                    <select id="status" name="status" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;" onchange="toggleReceivedBy()">
                        <option value="Incoming" <?php echo $doc['Status'] == 'Incoming' ? 'selected' : ''; ?>>Incoming</option>
                        <option value="Outgoing" <?php echo $doc['Status'] == 'Outgoing' ? 'selected' : ''; ?>>Outgoing</option>
                    </select>
                </div>
                
                <script>
                    function toggleReceivedBy() {
                        const status = document.getElementById('status').value;
                        const receivedInput = document.getElementById('received_by');
                        const receivedReq = document.getElementById('received_req');
                        
                        if (status === 'Outgoing') {
                            receivedInput.required = true;
                            receivedReq.style.display = 'inline';
                        } else {
                            receivedInput.required = false;
                            receivedReq.style.display = 'none';
                        }
                    }
                    // We rely on the inline style for initial load, but this ensures dynamics working
                </script>

                <!-- Actions -->
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="documents.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Document</button>
                </div>
            </form>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
