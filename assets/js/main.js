// Main JavaScript file for Document LogBook
document.addEventListener('DOMContentLoaded', () => {
    console.log('Document LogBook JS loaded and ready.');

    // Helper to setup modal logic
    const setupModal = (modalId, openBtnId, closeClass) => {
        const modal = document.getElementById(modalId);
        const openBtn = document.getElementById(openBtnId);
        const closeBtns = document.querySelectorAll('.' + closeClass);

        if (openBtn && modal) {
            openBtn.addEventListener('click', (e) => {
                e.preventDefault();
                modal.classList.add('active');
            });
        }

        if (modal) {
            if (closeBtns) {
                closeBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        modal.classList.remove('active');
                    });
                });
            }

            // Close on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        }
    };

    // Setup Modals
    setupModal('filterModal', 'openFilterModal', 'close-filter');
    setupModal('addModal', 'openAddModal', 'close-add');
    setupModal('editModal', null, 'close-edit'); // Edit modal opens via delegation

    // Additional case for Cancel button in Add Modal
    const closeAddBtn = document.querySelector('.close-add-btn');
    if (closeAddBtn) {
        closeAddBtn.addEventListener('click', () => {
            const modal = document.getElementById('addModal');
            if (modal) modal.classList.remove('active');
        });
    }
    // Cancel button in Edit Modal
    const closeEditBtn = document.querySelector('.close-edit-btn');
    if (closeEditBtn) {
        closeEditBtn.addEventListener('click', () => {
            const modal = document.getElementById('editModal');
            if (modal) modal.classList.remove('active');
        });
    }

    // --- Reusable File Upload Logic ---
    function setupFileUpload(triggerId, inputId, sidebarId) {
        const uploadTrigger = document.getElementById(triggerId);
        const fileInput = document.getElementById(inputId);
        const previewSidebar = document.getElementById(sidebarId);

        if (!uploadTrigger || !fileInput || !previewSidebar) return;

        let dt = new DataTransfer();

        // 1. Trigger hidden input on box click
        uploadTrigger.addEventListener('click', () => {
            fileInput.click();
        });

        // 2. Drag & Drop Support
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadTrigger.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadTrigger.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadTrigger.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadTrigger.classList.add('highlight');
            uploadTrigger.style.borderColor = '#34d399';
            uploadTrigger.style.backgroundColor = '#f1f5f9';
        }

        function unhighlight(e) {
            uploadTrigger.classList.remove('highlight');
            uploadTrigger.style.borderColor = '#cbd5e1';
            uploadTrigger.style.backgroundColor = '#f8fafc';
        }

        uploadTrigger.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const droppedFiles = e.dataTransfer.files;
            handleFiles(droppedFiles);
        }

        // 3. Handle File Selection (Change Event)
        fileInput.addEventListener('change', function () {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            for (let i = 0; i < files.length; i++) {
                // Simple duplicate check
                let exists = false;
                for (let j = 0; j < dt.items.length; j++) {
                    if (dt.files[j].name === files[i].name && dt.files[j].size === files[i].size) {
                        exists = true;
                        break;
                    }
                }
                if (!exists) {
                    dt.items.add(files[i]);
                }
            }
            fileInput.files = dt.files;
            renderPreviews();
        }

        function renderPreviews() {
            previewSidebar.innerHTML = '';
            for (let i = 0; i < dt.files.length; i++) {
                const file = dt.files[i];
                const reader = new FileReader();
                reader.onload = function (e) {
                    const previewItem = document.createElement('div');
                    previewItem.classList.add('preview-item');

                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        previewItem.appendChild(img);
                    } else {
                        const icon = document.createElement('i');
                        icon.className = 'fa-solid fa-file-pdf';
                        icon.style.fontSize = '2rem';
                        icon.style.color = '#ef4444';
                        previewItem.appendChild(icon);
                    }

                    const removeBtn = document.createElement('div');
                    removeBtn.className = 'preview-remove';
                    removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                    removeBtn.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        removeFile(i);
                    });

                    previewItem.appendChild(removeBtn);
                    previewSidebar.appendChild(previewItem);
                }
                reader.readAsDataURL(file);
            }
        }

        function removeFile(index) {
            const newDt = new DataTransfer();
            for (let i = 0; i < dt.files.length; i++) {
                if (i !== index) {
                    newDt.items.add(dt.files[i]);
                }
            }
            dt = newDt;
            fileInput.files = dt.files;
            renderPreviews();
        }

        // Expose a reset function if needed
        fileInput.resetConfig = () => {
            dt = new DataTransfer();
            fileInput.files = dt.files;
            previewSidebar.innerHTML = '';
        }
    }

    // Initialize Upload Logic for ADD
    setupFileUpload('uploadTrigger', 'doc_image', 'previewSidebar');
    // Initialize Upload Logic for EDIT
    setupFileUpload('editUploadTrigger', 'edit_doc_image', 'editPreviewSidebar');


    // --- Edit Modal Logic ---
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');

    // Delegation for Edit Buttons
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.edit-btn');
        if (btn) {
            e.preventDefault();
            const id = btn.dataset.id;

            // Clear previous form data
            // (Functionality to reset file input would be nice here)
            const editFileInput = document.getElementById('edit_doc_image');
            if (editFileInput.resetConfig) editFileInput.resetConfig();
            document.getElementById('edit_id').value = id;
            document.getElementById('existingFilesContainer').innerHTML = LOADING_SPINNER;

            editModal.classList.add('active');

            try {
                const response = await fetch(`get_document.php?id=${id}`);
                const data = await response.json();

                if (data.error) {
                    alert(data.error);
                    editModal.classList.remove('active');
                    return;
                }

                // Populate Fields
                document.getElementById('edit_doc_name').value = data.doc.Subject;
                document.getElementById('edit_office').value = data.doc.Office;
                document.getElementById('edit_description').value = data.doc.Description || '';
                document.getElementById('edit_status').value = data.doc.Status;
                document.getElementById('edit_received_by').value = data.doc.ReceivedBy || '';

                // Render Existing Attachments
                renderExistingFiles(data.attachments);

            } catch (err) {
                console.error(err);
                alert('Failed to fetch document details.');
                editModal.classList.remove('active');
            }
        }
    });

    const LOADING_SPINNER = '<div style="padding:10px; text-align:center; color:#64748b;">Loading...</div>';

    function renderExistingFiles(attachments) {
        const container = document.getElementById('existingFilesContainer');
        container.innerHTML = '';

        if (!attachments || attachments.length === 0) {
            container.innerHTML = '<div style="font-size:0.9rem; color:#94a3b8; font-style:italic;">No existing files.</div>';
            return;
        }

        attachments.forEach(att => {
            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.alignItems = 'center';
            row.style.justifyContent = 'space-between';
            row.style.padding = '8px';
            row.style.border = '1px solid #e2e8f0';
            row.style.borderRadius = '4px';
            row.style.background = '#fff';

            // Identify type
            const ext = att.FilePath.split('.').pop().toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(ext);

            const leftDiv = document.createElement('div');
            leftDiv.style.display = 'flex';
            leftDiv.style.alignItems = 'center';
            leftDiv.style.gap = '10px';

            if (isImage) {
                const img = document.createElement('img');
                img.src = att.FilePath;
                img.style.width = '40px';
                img.style.height = '40px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '4px';
                leftDiv.appendChild(img);
            } else {
                const icon = document.createElement('i');
                icon.className = 'fa-solid fa-file-pdf';
                icon.style.color = '#ef4444';
                icon.style.fontSize = '1.5rem';
                leftDiv.appendChild(icon);
            }

            const nameSpan = document.createElement('span');
            nameSpan.textContent = att.FilePath.split('/').pop().split('_').slice(2).join('_'); // Try to strip timestamp
            nameSpan.style.fontSize = '0.9rem';
            nameSpan.style.color = '#334155';
            nameSpan.style.maxWidth = '200px';
            nameSpan.style.overflow = 'hidden';
            nameSpan.style.textOverflow = 'ellipsis';
            nameSpan.style.whiteSpace = 'nowrap';
            leftDiv.appendChild(nameSpan);

            row.appendChild(leftDiv);

            // Trash Button
            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.innerHTML = '<i class="fa-regular fa-trash-can"></i>';
            delBtn.style.background = 'none';
            delBtn.style.border = 'none';
            delBtn.style.color = '#ef4444';
            delBtn.style.cursor = 'pointer';
            delBtn.title = 'Remove File';

            delBtn.addEventListener('click', () => {
                // Hide Row
                row.style.display = 'none';

                // Append hidden input
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_attachments[]';
                input.value = att.AttachmentID;
                editForm.appendChild(input);
            });

            row.appendChild(delBtn);
            container.appendChild(row);
        });
    }

    // --- View Modal Logic ---
    const viewModal = document.getElementById('viewModal');
    setupModal('viewModal', null, 'close-view'); // close-view class on X

    // Cancel button in View Modal (Close)
    const closeViewBtn = document.querySelector('.close-view-btn');
    if (closeViewBtn) {
        closeViewBtn.addEventListener('click', () => {
            viewModal.classList.remove('active');
        });
    }

    // Delegation for View Buttons
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.view-btn');
        if (btn) {
            e.preventDefault();
            const id = btn.dataset.id;

            // Loading State
            viewModal.classList.add('active');
            const attachContainer = document.getElementById('view_attachments');
            if (attachContainer) attachContainer.innerHTML = LOADING_SPINNER;

            try {
                const response = await fetch(`get_document.php?id=${id}`);
                const data = await response.json();

                if (data.error) {
                    alert(data.error);
                    viewModal.classList.remove('active');
                    return;
                }

                // Populate Fields
                document.getElementById('view_subject').textContent = data.doc.Subject;
                document.getElementById('view_id_badge').textContent = `ID: #${data.doc.DocID}`;
                document.getElementById('view_office').textContent = data.doc.Office;
                document.getElementById('view_date').textContent = new Date(data.doc.DocDate).toLocaleDateString();
                document.getElementById('view_received_by').textContent = data.doc.ReceivedBy || '-';
                document.getElementById('view_description').textContent = data.doc.Description || 'No description provided.';

                // Status Badge
                const statusDiv = document.getElementById('view_status');
                statusDiv.innerHTML = `<span class="badge badge-${data.doc.Status.toLowerCase()}">${data.doc.Status}</span>`;

                // Render Attachments
                renderViewAttachments(data.attachments);

            } catch (err) {
                console.error(err);
                alert('Failed to fetch document details.');
                viewModal.classList.remove('active');
            }
        }
    });

    function renderViewAttachments(attachments) {
        const container = document.getElementById('view_attachments');
        if (!container) return;
        container.innerHTML = '';

        if (!attachments || attachments.length === 0) {
            container.innerHTML = '<div style="font-size:0.85rem; color:#94a3b8; font-style:italic; padding: 10px; text-align: center;">No attachments found.</div>';
            return;
        }

        attachments.forEach(att => {
            const item = document.createElement('a');
            item.href = att.FilePath;
            item.target = '_blank';
            item.style.display = 'flex';
            item.style.alignItems = 'center';
            item.style.gap = '10px';
            item.style.padding = '8px';
            item.style.border = '1px solid #e2e8f0';
            item.style.borderRadius = '6px';
            item.style.textDecoration = 'none';
            item.style.transition = 'all 0.2s';
            item.style.background = '#ffffff';

            // Hover effects
            item.onmouseover = () => {
                item.style.background = '#f8fafc';
                item.style.borderColor = '#34d399';
                item.style.transform = 'translateY(-1px)';
            };
            item.onmouseout = () => {
                item.style.background = '#ffffff';
                item.style.borderColor = '#e2e8f0';
                item.style.transform = 'translateY(0)';
            };

            // Identify type
            const ext = att.FilePath.split('.').pop().toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(ext);

            if (isImage) {
                const img = document.createElement('img');
                img.src = att.FilePath;
                img.style.width = '40px';
                img.style.height = '40px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '4px';
                img.style.border = '1px solid #f1f5f9';
                item.appendChild(img);
            } else {
                const iconContainer = document.createElement('div');
                iconContainer.style.width = '40px';
                iconContainer.style.height = '40px';
                iconContainer.style.display = 'flex';
                iconContainer.style.alignItems = 'center';
                iconContainer.style.justifyContent = 'center';
                iconContainer.style.background = '#fff5f5';
                iconContainer.style.borderRadius = '4px';

                const icon = document.createElement('i');
                icon.className = 'fa-solid fa-file-pdf';
                icon.style.color = '#ef4444';
                icon.style.fontSize = '1.2rem';
                iconContainer.appendChild(icon);
                item.appendChild(iconContainer);
            }

            const infoDiv = document.createElement('div');
            infoDiv.style.flex = '1';
            infoDiv.style.minWidth = '0'; // Allow truncation

            const name = document.createElement('div');
            // Try to extract clean filename
            const parts = att.FilePath.split('/');
            const filename = parts.pop();
            const cleanName = filename.includes('_') ? filename.split('_').slice(2).join('_') : filename;

            name.textContent = cleanName || filename;
            name.style.color = '#1e293b';
            name.style.fontWeight = '500';
            name.style.fontSize = '0.85rem';
            name.style.whiteSpace = 'nowrap';
            name.style.overflow = 'hidden';
            name.style.textOverflow = 'ellipsis';

            const sub = document.createElement('div');
            sub.textContent = isImage ? 'Image' : 'PDF Document';
            sub.style.fontSize = '0.7rem';
            sub.style.color = '#94a3b8';

            infoDiv.appendChild(name);
            infoDiv.appendChild(sub);
            item.appendChild(infoDiv);

            // Small external link icon
            const extIcon = document.createElement('i');
            extIcon.className = 'fa-solid fa-chevron-right';
            extIcon.style.color = '#cbd5e1';
            extIcon.style.fontSize = '0.75rem';

            item.appendChild(extIcon);

            container.appendChild(item);
        });
    }

    // --- Dynamic Search Logic ---
    function initDynamicSearch() {
        const searchInput = document.querySelector('.search-input');
        const tableArea = document.getElementById('tableArea');
        if (!searchInput || !tableArea) return;

        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(async () => {
                const query = searchInput.value;
                // Preserve other filters from URL if possible
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('search', query);
                urlParams.set('page', 1); // Reset to page 1

                try {
                    const response = await fetch(`api/get_documents.php?${urlParams.toString()}`);
                    const html = await response.text();

                    // Replace the table area content
                    tableArea.innerHTML = html;

                    // Update URL without reload
                    window.history.replaceState(null, '', `?${urlParams.toString()}`);
                } catch (err) {
                    console.error('Search failed:', err);
                }
            }, 300); // 300ms debounce
        });
    }

    // Initialize New Features
    initDynamicSearch();

});
