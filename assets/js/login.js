document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.action = 'login'; // Add action for the switch case in PHP

    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.status === 'success') {
            window.location.href = 'dashboard.html';
        } else {
            document.getElementById('message').textContent = result.message;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('message').textContent = 'An error occurred. Please try again.';
    }
});

// Check if already logged in
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'check_session' })
        });
        const result = await response.json();
        if (result.status === 'logged_in') {
            window.location.href = 'dashboard.html';
        }
    } catch (e) {
        console.error('Session check failed', e);
    }
});
