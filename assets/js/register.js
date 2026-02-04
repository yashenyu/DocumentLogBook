document.getElementById('registerForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        document.getElementById('message').textContent = 'Passwords do not match!';
        return;
    }

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.action = 'register';

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
            alert('Registration successful! Redirecting to login...');
            window.location.href = 'login.html';
        } else {
            document.getElementById('message').textContent = result.message;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('message').textContent = 'An error occurred. Please try again.';
    }
});
