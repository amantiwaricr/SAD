const api = {
    async fetchJSON(url, options = {}) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                console.error('API Error: ', response.status);
            }
            return await response.json();
        } catch (error) {
            console.error('Fetch Failed', error);
            throw error;
        }
    },

    // Auth
    login: (username, password) => {
        return api.fetchJSON('api/auth.php?action=login', {
            method: 'POST',
            body: JSON.stringify({ username, password })
        });
    },
    register: (username, password) => {
        return api.fetchJSON('api/auth.php?action=register', {
            method: 'POST',
            body: JSON.stringify({ username, password })
        });
    },
    logout: () => api.fetchJSON('api/auth.php?action=logout'),
    checkAuth: () => api.fetchJSON('api/auth.php?action=check'),

    // Generic CRUD
    get: (endpoint) => api.fetchJSON(`api/${endpoint}.php`),
    getOne: (endpoint, id) => api.fetchJSON(`api/${endpoint}.php?id=${id}`),
    create: (endpoint, data) => api.fetchJSON(`api/${endpoint}.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }),
    update: (endpoint, data) => api.fetchJSON(`api/${endpoint}.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }),
    delete: (endpoint, id) => api.fetchJSON(`api/${endpoint}.php?id=${id}`, { method: 'DELETE' })
};
