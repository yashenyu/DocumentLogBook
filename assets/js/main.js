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
                console.log(`Opening modal: ${modalId}`);
                modal.style.display = ''; // Clear inline styles preventing display
                modal.classList.add('active');
            });
        } else {
            if (!modal) console.warn(`Modal not found: ${modalId}`);
            if (!openBtn && openBtnId) console.warn(`Open button not found: ${openBtnId}`);
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
    setupModal('staffModal', 'openStaffModal', 'close-staff');
    setupModal('logoModal', 'changeLogoTrigger', 'close-logo');
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

    // Cancel button in Staff Modal
    const staffCancel = document.querySelector('.close-staff-btn');
    if (staffCancel) {
        staffCancel.addEventListener('click', () => {
            const modal = document.getElementById('staffModal');
            if (modal) modal.classList.remove('active');
        });
    }

    // Cancel button in Logo Modal
    const logoCancel = document.querySelector('.close-logo-btn');
    if (logoCancel) {
        logoCancel.addEventListener('click', () => {
            const modal = document.getElementById('logoModal');
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
            uploadTrigger.style.borderColor = '#FFB81C';
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
                // Trigger change event to update Received By state
                document.getElementById('edit_status').dispatchEvent(new Event('change'));
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

            // Identify type from FileType (MIME type)
            const isImage = att.FileType && att.FileType.startsWith('image/');
            const attachmentUrl = `view_attachment.php?id=${att.AttachmentID}`;

            const leftDiv = document.createElement('div');
            leftDiv.style.display = 'flex';
            leftDiv.style.alignItems = 'center';
            leftDiv.style.gap = '10px';

            if (isImage) {
                const img = document.createElement('img');
                img.src = attachmentUrl;
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
            nameSpan.textContent = `Attachment #${att.AttachmentID}`;
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
                renderViewAttachments(data.attachments, data.doc.Subject);

            } catch (err) {
                console.error(err);
                alert('Failed to fetch document details.');
                viewModal.classList.remove('active');
            }
        }
    });

    // Store attachments for gallery opening
    let currentViewAttachments = [];
    let currentViewTitle = '';

    function renderViewAttachments(attachments, title = 'Attachments') {
        const container = document.getElementById('view_attachments');
        if (!container) return;
        container.innerHTML = '';

        // Store for gallery use
        currentViewAttachments = attachments;
        currentViewTitle = title;

        if (!attachments || attachments.length === 0) {
            container.innerHTML = '<div style="font-size:0.85rem; color:#94a3b8; font-style:italic; padding: 10px; text-align: center;">No attachments found.</div>';
            return;
        }

        attachments.forEach((att, index) => {
            const isImage = att.FileType && att.FileType.startsWith('image/');
            const attachmentUrl = `view_attachment.php?id=${att.AttachmentID}`;

            const item = document.createElement('div');
            item.style.display = 'flex';
            item.style.alignItems = 'center';
            item.style.gap = '10px';
            item.style.padding = '8px';
            item.style.border = '1px solid #e2e8f0';
            item.style.borderRadius = '6px';
            item.style.cursor = 'pointer';
            item.style.transition = 'all 0.2s';
            item.style.background = '#ffffff';

            // Hover effects
            item.onmouseover = () => {
                item.style.background = '#f8fafc';
                item.style.borderColor = '#FFB81C';
                item.style.transform = 'translateY(-1px)';
            };
            item.onmouseout = () => {
                item.style.background = '#ffffff';
                item.style.borderColor = '#e2e8f0';
                item.style.transform = 'translateY(0)';
            };

            // Click to open gallery at this index
            item.addEventListener('click', () => {
                if (window.openGalleryWithAttachments) {
                    window.openGalleryWithAttachments(currentViewAttachments, index, currentViewTitle, true);
                }
            });

            if (isImage) {
                const img = document.createElement('img');
                img.src = attachmentUrl;
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
            name.textContent = `Attachment #${att.AttachmentID}`;
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

            // View icon instead of external link
            const viewIcon = document.createElement('i');
            viewIcon.className = 'fa-solid fa-eye';
            viewIcon.style.color = '#cbd5e1';
            viewIcon.style.fontSize = '0.85rem';

            item.appendChild(viewIcon);

            container.appendChild(item);
        });
    }

    // --- Shared AJAX Refresh Logic ---
    async function refreshTable(params) {
        const tableArea = document.getElementById('tableArea');
        if (!tableArea) return;

        try {
            const response = await fetch(`api/get_documents.php?${params.toString()}`);
            const html = await response.text();

            tableArea.innerHTML = html;
            window.history.replaceState(null, '', `?${params.toString()}`);
        } catch (err) {
            console.error('Refresh failed:', err);
        }
    }

    // --- Dynamic Search Logic ---
    function initDynamicSearch() {
        const searchInput = document.querySelector('.search-input');
        if (!searchInput) return;

        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('search', searchInput.value);
                urlParams.set('page', 1);
                refreshTable(urlParams);
            }, 300);
        });
    }

    // --- Dynamic Sorting Logic ---
    function initSorting() {
        document.addEventListener('click', (e) => {
            const th = e.target.closest('.sortable-th');
            if (!th) return;

            const column = th.dataset.column;
            const order = th.dataset.order;

            const urlParams = new URLSearchParams(window.location.search);

            if (order) {
                urlParams.set('sort_by', column);
                urlParams.set('sort_order', order);
            } else {
                urlParams.delete('sort_by');
                urlParams.delete('sort_order');
            }

            urlParams.set('page', 1);
            refreshTable(urlParams);
        });
    }

    // --- Dynamic Pagination Logic ---
    function initPagination() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('.page-link, .page-nav');
            if (!link || link.classList.contains('disabled')) return;

            if (link.closest('#paginationArea')) {
                e.preventDefault();
                const url = new URL(link.href);
                const urlParams = new URLSearchParams(url.search);
                refreshTable(urlParams);

                document.querySelector('.table-container').scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // --- Multi-select Tag & Autocomplete Logic (In Modal) ---
    function initMultiSelect() {
        const containers = document.querySelectorAll('.multi-select-container');

        containers.forEach(container => {
            const type = container.dataset.type;
            const tagContainer = container.querySelector('.tag-container');
            const input = container.querySelector('.autocomplete-input');
            const dropdown = container.querySelector('.autocomplete-dropdown');
            const hiddenInput = container.querySelector('input[type="hidden"]');

            let selectedValues = [];

            if (hiddenInput.value) {
                selectedValues = hiddenInput.value.split(',').filter(v => v.trim() !== '');
                renderTags();
            }

            function renderTags() {
                tagContainer.innerHTML = '';
                selectedValues.forEach(val => {
                    const tag = document.createElement('div');
                    tag.className = 'tag';
                    tag.innerHTML = `
                        ${val}
                        <i class="fa-solid fa-xmark remove-tag" data-val="${val}"></i>
                    `;
                    tagContainer.appendChild(tag);
                });
                hiddenInput.value = selectedValues.join(',');
            }

            // Click anywhere in container focuses input
            container.addEventListener('click', (e) => {
                if (e.target !== dropdown && !dropdown.contains(e.target)) {
                    input.focus();
                }
            });

            input.addEventListener('focus', () => container.classList.add('focused'));
            input.addEventListener('blur', () => container.classList.remove('focused'));

            tagContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-tag')) {
                    e.stopPropagation(); // Don't focus input when clicking x
                    const valToRemove = e.target.dataset.val;
                    selectedValues = selectedValues.filter(v => v !== valToRemove);
                    renderTags();
                }
            });

            let debounceTimer;
            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                const query = input.value.trim();

                if (query.length < 2) {
                    dropdown.style.display = 'none';
                    return;
                }

                debounceTimer = setTimeout(async () => {
                    try {
                        const response = await fetch(`api/get_suggestions.php?type=${type}&query=${encodeURIComponent(query)}`);
                        const suggestions = await response.json();

                        if (suggestions.length > 0) {
                            dropdown.innerHTML = '';
                            suggestions.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'autocomplete-item';
                                div.textContent = item;
                                div.onclick = (e) => {
                                    e.stopPropagation();
                                    if (!selectedValues.includes(item)) {
                                        selectedValues.push(item);
                                        renderTags();
                                    }
                                    dropdown.style.display = 'none';
                                    input.value = '';
                                    input.focus();
                                };
                                dropdown.appendChild(div);
                            });
                            dropdown.style.display = 'block';
                        } else {
                            dropdown.style.display = 'none';
                        }
                    } catch (err) {
                        console.error('Suggestions fetch failed:', err);
                    }
                }, 300);
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const val = input.value.trim();
                    if (val && !selectedValues.includes(val)) {
                        selectedValues.push(val);
                        renderTags();
                        input.value = '';
                        dropdown.style.display = 'none';
                    }
                }
            });

            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) dropdown.style.display = 'none';
            });
        });
    }

    // --- Filter Modal AJAX Logic ---
    function initFilterForm() {
        const filterForm = document.getElementById('filterForm');
        if (!filterForm) return;

        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(filterForm);
            const urlParams = new URLSearchParams(window.location.search);

            for (const [key, value] of formData.entries()) {
                if (value) urlParams.set(key, value);
                else urlParams.delete(key);
            }

            urlParams.set('page', 1);
            refreshTable(urlParams);

            const modal = document.getElementById('filterModal');
            if (modal) {
                modal.classList.remove('active');
                // setTimeout(() => modal.style.display = 'none', 300); // Clean up: This causes issues with reopening
            }
        });
    }

    // --- Limit Selector Logic ---
    function initLimitSelector() {
        const selector = document.getElementById('limitSelector');
        if (!selector) return;

        selector.addEventListener('change', () => {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('limit', selector.value);
            urlParams.set('page', 1); // Reset to page 1
            refreshTable(urlParams);
        });
    }

    // Initialize New Features
    initDynamicSearch();
    initSorting();
    initPagination();
    initMultiSelect();
    initFilterForm();
    initLimitSelector();
    initCharCounters();

    // --- Character Counter Logic ---
    function initCharCounters() {
        // Target all inputs and textareas with maxlength in modals
        const inputs = document.querySelectorAll('.modal-content input[maxlength], .modal-content textarea[maxlength]');

        inputs.forEach(input => {
            const maxLen = parseInt(input.getAttribute('maxlength'), 10);
            if (!maxLen) return;

            // Create counter element
            const counter = document.createElement('span');
            counter.className = 'char-counter';
            counter.textContent = `0 / ${maxLen}`;

            // Find associated label
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (label) {
                // Insert into label (CSS handles alignment via flexbox)
                label.appendChild(counter);
            } else {
                // Fallback: put it after the input
                input.parentNode.insertBefore(counter, input.nextSibling);
            }

            // Update counter on input
            const updateCounter = () => {
                const len = input.value.length;
                counter.textContent = `${len} / ${maxLen}`;

                // Add warning class when >= 90% of max
                if (len >= maxLen * 0.9) {
                    counter.classList.add('char-warning');
                } else {
                    counter.classList.remove('char-warning');
                }
            };

            input.addEventListener('input', updateCounter);
            // Initial update in case of pre-filled values
            updateCounter();
        });
    }

    // --- Gallery Lightbox Logic ---
    function initGalleryLightbox() {
        const galleryModal = document.getElementById('galleryModal');
        const galleryImage = document.getElementById('galleryImage');
        const galleryPdf = document.getElementById('galleryPdf');
        const galleryLoading = document.getElementById('galleryLoading');
        const galleryThumbnails = document.getElementById('galleryThumbnails');
        const galleryCounter = document.getElementById('galleryCounter');
        const galleryTitle = document.getElementById('galleryTitle');
        const prevBtn = document.getElementById('galleryPrev');
        const nextBtn = document.getElementById('galleryNext');
        const closeBtn = document.querySelector('.close-gallery');
        const downloadOneBtn = document.getElementById('galleryDownloadOne');
        const downloadAllBtn = document.getElementById('galleryDownloadAll');

        if (!galleryModal) return;

        let currentAttachments = [];
        let currentIndex = 0;
        let currentDocId = null;
        let openedFromViewModal = false; // Track if opened from view modal

        // Close gallery function
        function closeGallery() {
            galleryModal.classList.remove('active');
            galleryImage.style.display = 'none';
            galleryPdf.style.display = 'none';
            galleryPdf.src = '';
            resetZoom();
            // openedFromViewModal is reset when opening, not closing
        }

        // Close gallery
        if (closeBtn) {
            closeBtn.addEventListener('click', closeGallery);
        }

        // Close on outside click
        galleryModal.addEventListener('click', (e) => {
            if (e.target === galleryModal) {
                closeGallery();
            }
        });

        // Download single attachment
        if (downloadOneBtn) {
            downloadOneBtn.addEventListener('click', () => {
                if (currentAttachments.length > 0 && currentAttachments[currentIndex]) {
                    const att = currentAttachments[currentIndex];
                    window.location.href = `download_attachment.php?id=${att.AttachmentID}`;
                }
            });
        }

        // Download all attachments (as ZIP if multiple)
        if (downloadAllBtn) {
            downloadAllBtn.addEventListener('click', () => {
                if (currentDocId) {
                    window.location.href = `download_attachment.php?doc_id=${currentDocId}`;
                }
            });
        }

        // Navigation
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                showAttachment(currentIndex);
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentIndex < currentAttachments.length - 1) {
                currentIndex++;
                showAttachment(currentIndex);
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!galleryModal.classList.contains('active')) return;

            if (e.key === 'ArrowLeft' && currentIndex > 0) {
                currentIndex--;
                showAttachment(currentIndex);
            } else if (e.key === 'ArrowRight' && currentIndex < currentAttachments.length - 1) {
                currentIndex++;
                showAttachment(currentIndex);
            } else if (e.key === 'Escape') {
                closeGallery();
            }
        });

        let zoomScale = 1;
        let isDragging = false;
        let translateX = 0, translateY = 0;

        function updateImageTransform() {
            galleryImage.style.transform = `translate(${translateX}px, ${translateY}px) scale(${zoomScale})`;
            // Toggle zoomed class for cursor state
            galleryImage.classList.toggle('zoomed', zoomScale > 1);
        }

        function resetZoom() {
            zoomScale = 1;
            translateX = 0;
            translateY = 0;
            updateImageTransform();
        }

        // Scroll to Zoom
        galleryImage.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomSpeed = 0.15;
            const delta = e.deltaY > 0 ? -zoomSpeed : zoomSpeed;
            const newScale = Math.min(Math.max(1, zoomScale + delta), 5); // Min scale is 1 (fit)

            if (newScale !== zoomScale) {
                // When zooming out to fit, reset position
                if (newScale === 1) {
                    translateX = 0;
                    translateY = 0;
                }
                zoomScale = newScale;
                updateImageTransform();
            }
        }, { passive: false });

        // Pan Logic - using movementX/Y for smoother panning
        galleryImage.addEventListener('mousedown', (e) => {
            if (zoomScale > 1) {
                isDragging = true;
                galleryImage.style.cursor = 'grabbing';
                e.preventDefault(); // Prevent image drag behavior
            }
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            translateX += e.movementX;
            translateY += e.movementY;
            updateImageTransform();
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                if (zoomScale > 1) {
                    galleryImage.style.cursor = 'grab';
                }
            }
        });

        function showAttachment(index) {
            const att = currentAttachments[index];
            const isImage = att.FileType && att.FileType.startsWith('image/');
            const url = `view_attachment.php?id=${att.AttachmentID}`;

            // Show loading
            galleryLoading.style.display = 'block';
            galleryImage.style.display = 'none';
            galleryPdf.style.display = 'none';

            resetZoom(); // Reset on every change

            if (isImage) {
                galleryImage.onload = () => {
                    galleryLoading.style.display = 'none';
                    galleryImage.style.display = 'block';
                };
                galleryImage.src = url;
            } else {
                galleryLoading.style.display = 'none';
                galleryPdf.src = url;
                galleryPdf.style.display = 'block';
            }

            // Update counter
            galleryCounter.textContent = `${index + 1} / ${currentAttachments.length}`;

            // Update nav buttons
            prevBtn.disabled = index === 0;
            nextBtn.disabled = index === currentAttachments.length - 1;

            // Update thumbnail active state
            const thumbs = galleryThumbnails.querySelectorAll('.lightbox-thumb');
            thumbs.forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
        }

        function renderThumbnails() {
            galleryThumbnails.innerHTML = '';
            currentAttachments.forEach((att, i) => {
                const isImage = att.FileType && att.FileType.startsWith('image/');
                const url = `view_attachment.php?id=${att.AttachmentID}`;

                const thumb = document.createElement('div');
                thumb.className = `lightbox-thumb ${i === currentIndex ? 'active' : ''}`;

                if (isImage) {
                    const img = document.createElement('img');
                    img.src = url;
                    thumb.appendChild(img);
                } else {
                    thumb.classList.add('lightbox-thumb-pdf');
                    thumb.innerHTML = '<i class="fa-solid fa-file-pdf"></i>';
                }

                thumb.addEventListener('click', () => {
                    currentIndex = i;
                    showAttachment(currentIndex);
                });

                galleryThumbnails.appendChild(thumb);
            });
        }

        // Open gallery via delegation (clicking on gallery-trigger elements)
        document.addEventListener('click', async (e) => {
            const trigger = e.target.closest('.gallery-trigger');
            if (trigger) {
                e.preventDefault();
                const docId = trigger.dataset.docid;

                if (!docId) return;

                openedFromViewModal = false; // Opening from table, not view modal

                // Show modal with loading state
                galleryModal.classList.add('active');
                galleryLoading.style.display = 'block';
                galleryImage.style.display = 'none';
                galleryPdf.style.display = 'none';
                galleryThumbnails.innerHTML = '<div style="color: #94a3b8;">Loading...</div>';

                try {
                    const response = await fetch(`get_document.php?id=${docId}`);
                    const data = await response.json();

                    if (data.error || !data.attachments || data.attachments.length === 0) {
                        galleryThumbnails.innerHTML = '<div style="color: #94a3b8;">No attachments found.</div>';
                        galleryLoading.style.display = 'none';
                        return;
                    }

                    currentAttachments = data.attachments;
                    currentIndex = 0;
                    currentDocId = docId; // Store for download all functionality


                    // Update title
                    galleryTitle.textContent = data.doc.Subject || 'Document Attachments';

                    renderThumbnails();
                    showAttachment(0);

                } catch (err) {
                    console.error('Failed to load attachments:', err);
                    galleryThumbnails.innerHTML = '<div style="color: #ef4444;">Failed to load attachments.</div>';
                    galleryLoading.style.display = 'none';
                }
            }
        });

        // Expose function to open gallery with pre-loaded attachments (for view modal)
        window.openGalleryWithAttachments = function (attachments, startIndex, title, fromViewModal = false) {
            if (!attachments || attachments.length === 0) return;

            currentAttachments = attachments;
            currentIndex = startIndex || 0;
            currentDocId = null; // Not from doc trigger
            openedFromViewModal = fromViewModal;

            galleryTitle.textContent = title || 'Attachments';
            galleryModal.classList.add('active');
            galleryLoading.style.display = 'none';

            renderThumbnails();
            showAttachment(currentIndex);
        };
    }

    // --- Manage Staff Modal Tabs & Account Management ---
    function initStaffManagement() {
        const staffModal = document.getElementById('staffModal');
        if (!staffModal) return;

        const tabBtns = staffModal.querySelectorAll('.tab-btn');
        const tabContents = staffModal.querySelectorAll('.tab-content');
        const triggerManageAccounts = document.getElementById('triggerManageAccounts');
        const userListContainer = document.getElementById('userListContainer');

        // Tab Switching Logic
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.dataset.tab;

                // Toggle Buttons
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Toggle Content
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === targetTab) content.classList.add('active');
                });

                // If switching to Manage Accounts, load the users
                if (targetTab === 'tab-manage-accounts') {
                    loadUserList();
                }
            });
        });

        // Load User List Function
        async function loadUserList() {
            userListContainer.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 2rem; color: #64748b;"><i class="fa-solid fa-spinner fa-spin"></i> Loading users...</td></tr>';

            try {
                const response = await fetch('api/get_users.php');
                const users = await response.json();

                if (users.error) {
                    userListContainer.innerHTML = `<tr><td colspan="3" style="text-align: center; color: #ef4444; padding: 1rem;">${users.error}</td></tr>`;
                    return;
                }

                if (users.length === 0) {
                    userListContainer.innerHTML = '<tr><td colspan="3" style="text-align: center; color: #64748b; padding: 1rem;">No users found.</td></tr>';
                    return;
                }

                userListContainer.innerHTML = '';
                users.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="font-weight: 600; color: #1e293b;">${user.username}</td>
                        <td>
                            <span class="badge ${user.role === 'Admin' ? 'badge-outgoing' : 'badge-incoming'}" style="padding: 0.4rem 0.8rem; border-radius: 6px;">
                                ${user.role}
                            </span>
                        </td>
                        <td>
                            <div class="user-actions" id="actions-${user.UserId}">
                                <button class="btn-action btn-action-pass change-pass-btn" data-id="${user.UserId}" data-username="${user.username}" title="Change Password">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                                ${user.UserId != 1 ? `<button class="btn-action btn-action-delete delete-user-btn" data-id="${user.UserId}" title="Delete Account">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>` : ''}
                            </div>
                            <!-- Inline Password Editor (Hidden by default) -->
                            <div class="inline-pass-editor" id="editor-${user.UserId}" style="display: none;">
                                <input type="password" class="inline-pass-input" placeholder="New Password" id="input-${user.UserId}">
                                <button class="btn-inline-save save-pass-btn" data-id="${user.UserId}">Save</button>
                                <button class="btn-inline-cancel cancel-pass-btn" data-id="${user.UserId}">Cancel</button>
                            </div>
                        </td>
                    `;
                    userListContainer.appendChild(tr);
                });

            } catch (err) {
                console.error('Failed to load users:', err);
                userListContainer.innerHTML = '<tr><td colspan="3" style="text-align: center; color: #ef4444; padding: 1rem;">Failed to fetch user list.</td></tr>';
            }
        }

        // Action Delegation (Change Password / Delete / Save / Cancel)
        userListContainer.addEventListener('click', async (e) => {
            const passBtn = e.target.closest('.change-pass-btn');
            const delBtn = e.target.closest('.delete-user-btn');
            const saveBtn = e.target.closest('.save-pass-btn');
            const cancelBtn = e.target.closest('.cancel-pass-btn');

            if (passBtn) {
                const userId = passBtn.dataset.id;
                document.getElementById(`actions-${userId}`).style.display = 'none';
                document.getElementById(`editor-${userId}`).style.display = 'flex';
                document.getElementById(`input-${userId}`).focus();
            }

            if (cancelBtn) {
                const userId = cancelBtn.dataset.id;
                document.getElementById(`actions-${userId}`).style.display = 'flex';
                document.getElementById(`editor-${userId}`).style.display = 'none';
                document.getElementById(`input-${userId}`).value = '';
            }

            if (saveBtn) {
                const userId = saveBtn.dataset.id;
                const newPass = document.getElementById(`input-${userId}`).value;

                if (newPass && newPass.trim().length > 0) {
                    const formData = new FormData();
                    formData.append('user_id', userId);
                    formData.append('action', 'change_password');
                    formData.append('new_password', newPass);

                    try {
                        const response = await fetch('api/update_user.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        if (result.success) {
                            if (typeof showToast === 'function') showToast(result.success, 'success');
                            // Reset UI
                            document.getElementById(`actions-${userId}`).style.display = 'flex';
                            document.getElementById(`editor-${userId}`).style.display = 'none';
                            document.getElementById(`input-${userId}`).value = '';
                        } else {
                            if (typeof showToast === 'function') showToast(result.error, 'error');
                        }
                    } catch (err) {
                        console.error('Update failed:', err);
                    }
                } else {
                    if (typeof showToast === 'function') showToast('Password cannot be empty', 'error');
                }
            }

            if (delBtn) {
                const userId = delBtn.dataset.id;
                if (confirm('Are you sure you want to delete this account? This cannot be undone.')) {
                    const formData = new FormData();
                    formData.append('user_id', userId);
                    formData.append('action', 'delete_user');

                    try {
                        const response = await fetch('api/update_user.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        if (result.success) {
                            if (typeof showToast === 'function') showToast(result.success, 'success');
                            loadUserList(); // Refresh table
                        } else {
                            if (typeof showToast === 'function') showToast(result.error, 'error');
                        }
                    } catch (err) {
                        console.error('Delete failed:', err);
                    }
                }
            }
        });
    }

    // --- Add Modal Tabs Logic ---
    function initAddModalTabs() {
        const addModal = document.getElementById('addModal');
        if (!addModal) return;

        const tabBtns = addModal.querySelectorAll('.tab-btn');
        const tabContents = addModal.querySelectorAll('.tab-content');
        const modalTitle = document.getElementById('addModalTitle');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.dataset.tab;

                // Toggle Buttons
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Toggle Content
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === targetTab) content.classList.add('active');
                });

                // Update Title optionally
                if (modalTitle) {
                    modalTitle.textContent = targetTab === 'tab-add-doc' ? 'Add New Document' : 'Add New Office';
                }
            });
        });

        // Optional: Reset to first tab when modal opens
        const openAddModalBtn = document.getElementById('openAddModal');
        if (openAddModalBtn) {
            openAddModalBtn.addEventListener('click', () => {
                // Reset tabs
                tabBtns.forEach((btn, idx) => {
                    if (idx === 0) btn.classList.add('active');
                    else btn.classList.remove('active');
                });
                tabContents.forEach((content, idx) => {
                    if (idx === 0) content.classList.add('active');
                    else content.classList.remove('active');
                });
                if (modalTitle) modalTitle.textContent = 'Add New Document';
            });
        }
    }

    // --- Office Management Logic ---
    function initOfficeManagement() {
        const editBtns = document.querySelectorAll('.edit-office-btn');
        const deleteBtns = document.querySelectorAll('.delete-office-btn');

        const editModal = document.getElementById('editOfficeModal');
        const deleteModal = document.getElementById('deleteOfficeModal');

        const closeEditBtns = document.querySelectorAll('.close-edit-office');
        const closeDeleteBtns = document.querySelectorAll('.close-delete-office');

        // Close functions
        const closeModals = () => {
            if (editModal) editModal.classList.remove('active');
            if (deleteModal) deleteModal.classList.remove('active');
        };

        closeEditBtns.forEach(btn => btn.addEventListener('click', closeModals));
        closeDeleteBtns.forEach(btn => btn.addEventListener('click', closeModals));

        // Edit Button
        editBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const name = btn.dataset.name;
                document.getElementById('edit_office_old_name').value = name;
                document.getElementById('edit_office_new_name').value = name;

                // Fetch stats
                try {
                    const response = await fetch(`ajax/get_office_stats.php?office=${encodeURIComponent(name)}`);
                    const data = await response.json();
                    const warning = document.getElementById('edit_office_warning');
                    if (data.count > 0) {
                        document.getElementById('edit_office_affected_count').textContent = data.count;
                        warning.style.display = 'block';
                    } else {
                        warning.style.display = 'none';
                    }
                } catch (err) {
                    console.error('Failed to fetch stats', err);
                }

                editModal.classList.add('active');
            });
        });

        // Delete Button
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const name = btn.dataset.name;
                document.getElementById('delete_office_name_input').value = name;
                document.getElementById('delete_office_display_name').textContent = name;

                // Fetch stats
                try {
                    const response = await fetch(`ajax/get_office_stats.php?office=${encodeURIComponent(name)}`);
                    const data = await response.json();
                    const warning = document.getElementById('delete_office_warning');
                    if (data.count > 0) {
                        document.getElementById('delete_office_affected_count').textContent = data.count;
                        warning.style.display = 'block';
                    } else {
                        warning.style.display = 'none';
                    }
                } catch (err) {
                    console.error('Failed to fetch stats', err);
                }

                deleteModal.classList.add('active');
            });
        });
    }

    initStaffManagement();
    initGalleryLightbox();
    initAddModalTabs();
    initOfficeManagement();
});
