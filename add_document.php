<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/helpers/OfficeHelper.php';
$officeOptions = OfficeHelper::getOffices();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Document - Document LogBook</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page" style="display: block; overflow-y: auto;">
    <!-- Static Blobs for consistency -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="container" style="max-width: 600px; padding-top: 4rem; padding-bottom: 4rem; position: relative; z-index: 10;">
        <div class="auth-card" style="display: block;">
            <div class="auth-content">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <h2 style="margin-bottom: 0;">Add New Document</h2>
                </div>
                <p class="subtitle">Enter document details below to log it.</p>

                <?php if (isset($_SESSION['error'])): ?>
                    <div style="background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #ff8a80; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                        <?php echo $_SESSION['error'];
    unset($_SESSION['error']); ?>
                    </div>
                <?php
endif; ?>

                <form action="process_add_document.php" method="POST" enctype="multipart/form-data">
                    <div style="margin-bottom: 1.2rem;">
                        <label for="office">Office / Department</label>
                        <select id="office" name="office" required style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 0.375rem; background-color: #ffffff; color: #334155; font-size: 0.9rem; font-family: 'Poppins', sans-serif;">
                            <option value="" disabled selected>Select Office</option>
                            <?php foreach ($officeOptions as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 1.2rem;">
                        <label for="doc_name">Document Name / Subject</label>
                        <input type="text" id="doc_name" name="doc_name" required placeholder="e.g. Budget Report 2026">
                    </div>

                    <div style="margin-bottom: 1.2rem;">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required rows="3" style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 0.375rem; background-color: #ffffff; color: #334155; font-size: 0.9rem; font-family: 'Poppins', sans-serif;" placeholder="Briefly describe the document..."></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                        <div>
                            <label for="status">Status</label>
                            <select id="status" name="status" onchange="toggleReceivedBy()" style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 0.375rem; background-color: #ffffff; color: #334155; font-size: 0.9rem; font-family: 'Poppins', sans-serif;">
                                <option value="Incoming">Incoming</option>
                                <option value="Outgoing">Outgoing</option>
                            </select>
                        </div>
                        <div>
                            <label for="received_by">Received By <span id="received_req" style="color: #ff6b6b; display: none;">*</span></label>
                            <input type="text" id="received_by" name="received_by" placeholder="Name" disabled style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 0.375rem; background-color: #ffffff; color: #334155; font-size: 0.9rem; font-family: 'Poppins', sans-serif;">
                        </div>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <label for="doc_image">Upload Document (Image/PDF)</label>
                        <input type="file" id="doc_image" name="doc_image" accept="image/*,.pdf" style="padding: 0.5rem 0; color: #cbd5e1; font-size: 0.85rem;">
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button type="submit" class="btn btn-auth" style="flex: 1;">Save Document</button>
                        <a href="documents.php" class="auth-link" style="padding: 0.6rem 0; text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleReceivedBy() {
            const status = document.getElementById('status').value;
            const receivedLabel = document.getElementById('received_req');
            const receivedInput = document.getElementById('received_by');
            
            if (status === 'Outgoing') {
                receivedLabel.style.display = 'inline';
                receivedInput.required = true;
                receivedInput.disabled = false;
            } else {
                receivedLabel.style.display = 'none';
                receivedInput.required = false;
                receivedInput.disabled = true;
                receivedInput.value = '';
            }
        }
        // Initialize
        toggleReceivedBy();
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
