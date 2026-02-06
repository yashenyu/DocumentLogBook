<?php
// This partial is used by documents.php and an AJAX endpoint to render table rows and pagination
?>

<table class="doc-table">
    <thead>
        <tr>
            <th class="sortable-th" data-column="DocID" data-order="<?php echo($sortBy === 'DocID') ? ($sortOrder === 'ASC' ? 'DESC' : '') : 'ASC'; ?>">
                ID <?php if ($sortBy === 'DocID')
    echo $sortOrder === 'ASC' ? '↑' : '↓'; ?>
            </th>
            <th>Image</th>
            <th class="sortable-th" data-column="DocDate" data-order="<?php echo($sortBy === 'DocDate') ? ($sortOrder === 'ASC' ? 'DESC' : '') : 'ASC'; ?>">
                Date <?php if ($sortBy === 'DocDate')
    echo $sortOrder === 'ASC' ? '↑' : '↓'; ?>
            </th>
            <th class="sortable-th" data-column="Office" data-order="<?php echo($sortBy === 'Office') ? ($sortOrder === 'ASC' ? 'DESC' : '') : 'ASC'; ?>">
                Office <?php if ($sortBy === 'Office')
    echo $sortOrder === 'ASC' ? '↑' : '↓'; ?>
            </th>
            <th class="sortable-th" data-column="Subject" data-order="<?php echo($sortBy === 'Subject') ? ($sortOrder === 'ASC' ? 'DESC' : '') : 'ASC'; ?>">
                Subject <?php if ($sortBy === 'Subject')
    echo $sortOrder === 'ASC' ? '↑' : '↓'; ?>
            </th>
            <th class="sortable-th" data-column="ReceivedBy" data-order="<?php echo($sortBy === 'ReceivedBy') ? ($sortOrder === 'ASC' ? 'DESC' : '') : 'ASC'; ?>">
                Received By <?php if ($sortBy === 'ReceivedBy')
    echo $sortOrder === 'ASC' ? '↑' : '↓'; ?>
            </th>
            <th class="sortable-th" data-column="Status" data-order="<?php echo($sortBy === 'Status') ? ($sortOrder === 'ASC' ? 'DESC' : '') : 'ASC'; ?>">
                Status <?php if ($sortBy === 'Status')
    echo $sortOrder === 'ASC' ? '↑' : '↓'; ?>
            </th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="documentsTableBody">
        <?php if (count($documents) > 0): ?>
            <?php foreach ($documents as $row):
        // Fetch first attachment for this document (for preview)
        $stmtAttach = $pdo->prepare("SELECT AttachmentID, FileType FROM DocumentAttachments WHERE DocID = ? ORDER BY AttachmentID ASC LIMIT 1");
        $stmtAttach->execute([$row['DocID']]);
        $firstAttachment = $stmtAttach->fetch(PDO::FETCH_ASSOC);

        // Count total attachments
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM DocumentAttachments WHERE DocID = ?");
        $stmtCount->execute([$row['DocID']]);
        $attachmentCount = $stmtCount->fetchColumn();
?>
            <tr>
                <td>#<?php echo $row['DocID']; ?></td>
                <td>
                    <?php if ($firstAttachment):
            $isImage = strpos($firstAttachment['FileType'], 'image/') === 0;
            $attachmentUrl = 'view_attachment.php?id=' . $firstAttachment['AttachmentID'];
?>
                        <!-- Gallery trigger instead of direct link -->
                        <div class="gallery-trigger" data-docid="<?php echo $row['DocID']; ?>" title="View all attachments (<?php echo $attachmentCount; ?>)">
                            <?php if ($isImage): ?>
                                <img src="<?php echo htmlspecialchars($attachmentUrl); ?>" alt="Preview" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                            <?php
            else: ?>
                                <div style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: #fef2f2; border-radius: 4px; border: 1px solid #e2e8f0;">
                                    <i class="fa-regular fa-file-pdf" style="font-size: 1.5rem; color: #ef4444;"></i>
                                </div>
                            <?php
            endif; ?>
                            <?php if ($attachmentCount > 1): ?>
                                <span style="position: absolute; top: -5px; right: -5px; background: var(--accent-color); color: var(--primary-color); font-size: 0.65rem; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <?php echo $attachmentCount; ?>
                                </span>
                            <?php
            endif; ?>
                        </div>
                    <?php
        else: ?>
                        <span style="opacity: 0.3;">-</span>
                    <?php
        endif; ?>
                </td>
                <td><?php echo date('m/d/Y', strtotime($row['DocDate'])); ?></td>
                <td class="col-truncate col-office" title="<?php echo htmlspecialchars($row['Office']); ?>"><?php echo htmlspecialchars($row['Office']); ?></td>
                <td class="col-truncate col-subject" title="<?php echo htmlspecialchars($row['Subject']); ?>"><?php echo htmlspecialchars($row['Subject']); ?></td>
                <td class="col-truncate col-received" title="<?php echo htmlspecialchars($row['ReceivedBy']); ?>"><?php echo htmlspecialchars($row['ReceivedBy']); ?></td>
                <td>
                    <span class="badge badge-<?php echo strtolower($row['Status']); ?>">
                        <?php echo htmlspecialchars($row['Status']); ?>
                    </span>
                </td>
                <td>
                    <div class="actions">
                        <button class="action-btn view-btn" data-id="<?php echo $row['DocID']; ?>" title="View Details">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                        
                        <a href="delete_document.php?id=<?php echo $row['DocID']; ?>" class="action-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this document?');">
                            <i class="fa-regular fa-trash-can"></i>
                        </a>
                        
                        <button class="action-btn edit-btn" data-id="<?php echo $row['DocID']; ?>" title="Edit">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php
    endforeach; ?>
        <?php
else: ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 2rem;">No documents found.</td>
            </tr>
        <?php
endif; ?>
    </tbody>
</table>

<!-- Pagination (Shared logic) -->
<div class="pagination-area" id="paginationArea">
    <?php
$paginationQueryParams = [
    'search' => $search,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'office' => $officeFilter,
    'received_by' => $receivedFilter,
    'status' => $statusFilter,
    'sort_by' => $sortBy,
    'sort_order' => $sortOrder
];

$paginationQueryParams['page'] = $page - 1;
$prevUrl = '?' . http_build_query($paginationQueryParams);

$paginationQueryParams['page'] = $page + 1;
$nextUrl = '?' . http_build_query($paginationQueryParams);
?>

    <?php if ($page > 1): ?>
        <a href="<?php echo htmlspecialchars($prevUrl); ?>" class="page-nav">
            <img src="assets/images/chevron-left-icon.svg" alt="Prev" style="width: 14px; height: 14px; opacity: 0.6;">
        </a>
    <?php
else: ?>
        <div class="page-nav disabled">
            <img src="assets/images/chevron-left-icon.svg" alt="Prev" style="width: 14px; height: 14px; opacity: 0.2;">
        </div>
    <?php
endif; ?>

    <div class="pagination-links">
        <?php for ($i = 1; $i <= $totalPages; $i++):
    $paginationQueryParams['page'] = $i;
    $url = '?' . http_build_query($paginationQueryParams);
?>
            <a href="<?php echo htmlspecialchars($url); ?>" class="page-link <?php echo($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php
endfor; ?>
    </div>

    <?php if ($page < $totalPages): ?>
        <a href="<?php echo htmlspecialchars($nextUrl); ?>" class="page-nav">
            <img src="assets/images/chevron-right-icon.svg" alt="Next" style="width: 14px; height: 14px; opacity: 0.6;">
        </a>
    <?php
else: ?>
        <div class="page-nav disabled">
            <img src="assets/images/chevron-right-icon.svg" alt="Next" style="width: 14px; height: 14px; opacity: 0.2;">
        </div>
    <?php
endif; ?>
</div>
