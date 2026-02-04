<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Document - Document LogBook</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <header style="margin-bottom: 2rem;">
            <h1>Add New Document</h1>
        </header>

        <div class="card">
            <form action="process_add_document.php" method="POST">
                <!-- Email (Auto-fillable later) -->
                <div style="margin-bottom: 1rem;">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           placeholder="user@example.com">
                </div>

                <!-- Document Name -->
                <div style="margin-bottom: 1rem;">
                    <label for="doc_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Document Name</label>
                    <input type="text" id="doc_name" name="doc_name" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           placeholder="e.g. Invoice #12345">
                </div>

                <!-- Description -->
                <div style="margin-bottom: 1rem;">
                    <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Description</label>
                    <textarea id="description" name="description" rows="4" 
                              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                              placeholder="Brief details about the document..."></textarea>
                </div>

                <!-- Received By -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="received_by" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Received By</label>
                    <input type="text" id="received_by" name="received_by" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;"
                           value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>"
                           placeholder="Name of receiver">
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="documents.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Document</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
