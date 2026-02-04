// Auth Check
async function checkAuth() {
    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'check_session' })
        });
        const result = await response.json();
        if (result.status !== 'logged_in') {
            window.location.href = 'login.html';
        } else {
            document.getElementById('welcomeUser').textContent = `Welcome, ${result.user}`;
            // Show Admin Button if role is Admin
            if (result.role === 'Admin') {
                const adminBtn = document.getElementById('adminBtn');
                if (adminBtn) adminBtn.style.display = 'inline-block';
            }
        }
    } catch (e) {
        window.location.href = 'login.html';
    }
}

checkAuth();

// Logout
document.getElementById('logoutBtn').addEventListener('click', async () => {
    await fetch('api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'logout' })
    });
    window.location.href = 'login.html';
});

// Document Listing
const tableBody = document.getElementById('documentTableBody');

async function fetchDocuments(search = '') {
    let url = 'api/documents.php';
    if (search) {
        url += `?search=${encodeURIComponent(search)}`;
    }

    try {
        const response = await fetch(url);
        const result = await response.json();

        if (result.status === 'success') {
            renderTable(result.data);
        }
    } catch (e) {
        console.error('Error fetching documents:', e);
    }
}

function renderTable(documents) {
    tableBody.innerHTML = '';
    documents.forEach(doc => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${doc.DocDate}</td>
            <td>${doc.Office}</td>
            <td>${doc.Subject}</td>
            <td>${doc.Status}</td>
            <td>
                <button class="btn btn-sm" onclick="openEditModal(${doc.DocID})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteDocument(${doc.DocID})">Delete</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Initial Load
fetchDocuments();

// Search
document.getElementById('searchInput').addEventListener('input', (e) => {
    fetchDocuments(e.target.value);
});

// Modal & Forms
const modal = document.getElementById('documentModal');
const form = document.getElementById('documentForm');
const modalTitle = document.getElementById('modalTitle');
const closeBtn = document.querySelector('.close-btn');

document.getElementById('addBtn').addEventListener('click', () => {
    form.reset();
    document.getElementById('DocID').value = '';
    modalTitle.textContent = 'Add Document';
    modal.classList.add('show');
});

closeBtn.addEventListener('click', () => {
    modal.classList.remove('show');
});

window.onclick = (event) => {
    if (event.target === modal) {
        modal.classList.remove('show');
    }
};

// Add/Edit Submit
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const docId = data.DocID;

    const method = docId ? 'PUT' : 'POST';

    try {
        const response = await fetch('api/documents.php', {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.status === 'success') {
            modal.classList.remove('show');
            fetchDocuments(); // Refresh list
        } else {
            alert('Error: ' + result.message);
        }
    } catch (e) {
        console.error('Error saving document:', e);
    }
});

// Window helper functions for inline onclick events
window.openEditModal = async (id) => {
    // Ideally fetch single document, but for now we can filter from existing or fetch list
    // To be precise let's fetch list (or modify API to get one). 
    // Easier hack for this level: get row data from memory if we stored it, or just re-fetch everything?
    // Let's just quick fetch specific one isn't implemented in API yet, so let's rely on list refresh or add get-one logic.
    // Actually, let's just grab it from a "data" attribute? No, let's just loop through what we have if we store it.
    // Simpler: Just Fetch All and find it.

    const response = await fetch('api/documents.php');
    const result = await response.json();
    if (result.status === 'success') {
        const doc = result.data.find(d => d.DocID == id);
        if (doc) {
            document.getElementById('DocID').value = doc.DocID;
            document.getElementById('DocDate').value = doc.DocDate;
            document.getElementById('Office').value = doc.Office;
            document.getElementById('Subject').value = doc.Subject;
            document.getElementById('Description').value = doc.Description;
            document.getElementById('ReceivedBy').value = doc.ReceivedBy;
            document.getElementById('Status').value = doc.Status;

            modalTitle.textContent = 'Edit Document';
            modal.classList.add('show');
        }
    }
};

window.deleteDocument = async (id) => {
    if (!confirm('Are you sure you want to delete this document?')) return;

    try {
        const response = await fetch('api/documents.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ DocID: id })
        });

        const result = await response.json();
        if (result.status === 'success') {
            fetchDocuments();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (e) {
        console.error('Error deleting document:', e);
    }
};

// Admin: Add User Logic
const userModal = document.getElementById('userModal');
const userForm = document.getElementById('userForm');
const closeUserModal = document.getElementById('closeUserModal');

const adminBtn = document.getElementById('adminBtn');
if (adminBtn) {
    adminBtn.addEventListener('click', () => {
        userForm.reset();
        userModal.classList.add('show');
    });
}

if (closeUserModal) {
    closeUserModal.addEventListener('click', () => {
        userModal.classList.remove('show');
    });
}

// Close User Modal on outside click
window.addEventListener('click', (event) => {
    if (event.target === userModal) {
        userModal.classList.remove('show');
    }
});

userForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(userForm);
    const data = Object.fromEntries(formData.entries());

    // Add action for API
    data.action = 'create_user';

    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.status === 'success') {
            alert('User user created successfully!');
            userModal.classList.remove('show');
            userForm.reset();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (e) {
        console.error('Error creating user:', e);
        alert('An error occurred.');
    }
});
