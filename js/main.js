// Protection: Check Auth on protected pages
document.addEventListener('DOMContentLoaded', async () => {
    // We don't check auth strictly on index.html within main.js (we handle it in index.html)
    if (!window.location.pathname.endsWith('index.html') && window.location.pathname !== '/') {
        const res = await api.checkAuth();
        if (res.status !== 'success') {
            window.location.href = 'index.html';
        } else {
            const userNameDisplay = document.getElementById('userNameDisplay');
            const userRoleDisplay = document.getElementById('userRoleDisplay');
            if(userNameDisplay) userNameDisplay.textContent = res.user.username;
            if(userRoleDisplay) userRoleDisplay.textContent = res.user.role;
        }

        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', async () => {
                await api.logout();
                window.location.href = 'index.html';
            });
        }
    }
});

// Utility to handle modal toggling
function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    // Clear forms inside modal
    const form = document.querySelector(`#${modalId} form`);
    if(form) {
        form.reset();
        const idInput = form.querySelector('[name="id"]');
        if(idInput) idInput.value = ''; // Ensure hidden ID is cleared
    }
}

// Utility to handle form submissions
async function handleFormSubmit(e, endpoint, callback) {
    e.preventDefault();
    const form = e.target;
    // Basic array reduction for form data
    const elements = form.elements;
    const data = {};
    for (let i = 0; i < elements.length; i++) {
        let item = elements.item(i);
        if(item.name) {
            data[item.name] = item.value;
        }
    }
    
    // Convert ID string to number if updating
    if(data.id) data.id = parseInt(data.id);

    try {
        let res;
        if(data.id) {
            res = await api.update(endpoint, data);
        } else {
            res = await api.create(endpoint, data);
        }

        if(res.status === 'success') {
            hideModal(form.closest('.modal').id);
            if(callback) callback();
        } else {
            alert('Error: ' + (res.message || 'Action failed'));
        }
    } catch(err) {
        alert('Server error occurred');
        console.error(err);
    }
}

// Utility to delete item
async function deleteItem(endpoint, id, callback) {
    if(confirm('Are you sure you want to delete this item?')) {
        const res = await api.delete(endpoint, id);
        if(res.status === 'success') {
            if(callback) callback();
        } else {
            alert('Failed to delete item');
        }
    }
}

// Utility to populate form for editing
function populateForm(formId, data) {
    const form = document.getElementById(formId);
    if(!form) return;
    Object.keys(data).forEach(key => {
        const input = form.querySelector(`[name="${key}"]`);
        if(input) {
            input.value = data[key];
        }
    });
}
