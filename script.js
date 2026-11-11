const API_URL = 'api/profiles.php';

const form           = document.getElementById('profileForm');
const listBox        = document.getElementById('profiles');
const messageEl      = document.getElementById('message');
const cancelBtn      = document.getElementById('cancelBtn');
const formTitle      = document.getElementById('formTitle');
const searchInput    = document.getElementById('searchInput');
const clearSearchBtn = document.getElementById('clearSearchBtn');

// Prevent HTML injection when showing database text
function esc(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function showMessage(text, ok = true) {
    messageEl.textContent = text;
    messageEl.className = 'message ' + (ok ? 'ok' : 'err');
}

// READ + SEARCH: get profiles (filtered if the search box has text)
async function loadProfiles() {
    try {
        const term = searchInput.value.trim();
        const res = await fetch(`${API_URL}?search=${encodeURIComponent(term)}`);
        const profiles = await res.json();

        if (!Array.isArray(profiles) || profiles.length === 0) {
            listBox.innerHTML = term
                ? `<p class="panel">No profiles match "${esc(term)}".</p>`
                : '<p class="panel">No profiles yet. Add one above.</p>';
            return;
        }

        listBox.innerHTML = profiles.map(p => {
            const services = (p.services || '')
                .split(',')
                .map(s => s.trim())
                .filter(Boolean)
                .map(s => `<li>${esc(s)}</li>`)
                .join('');

            return `
            <div class="profile-card">
                <h2>${esc(p.name)}</h2>
                <p>${esc(p.tagline)}</p>

                <h3>Our Services</h3>
                <ul>${services}</ul>

                <h3>Contact Us</h3>
                <p>Location: ${esc(p.location)}</p>
                <p>Email: ${esc(p.email)}</p>

                <button class="btn btn-edit" onclick="editProfile(${p.id})">Edit</button>
                <button class="btn btn-delete" onclick="deleteProfile(${p.id})">Delete</button>
            </div>`;
        }).join('');
    } catch (err) {
        listBox.innerHTML = '<p class="panel">Could not load data. Is Apache/MySQL running?</p>';
    }
}

// CREATE or UPDATE
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = document.getElementById('profileId').value;
    const data = {
        name:     document.getElementById('name').value,
        tagline:  document.getElementById('tagline').value,
        location: document.getElementById('location').value,
        email:    document.getElementById('email').value,
        services: document.getElementById('services').value,
    };

    const method = id ? 'PUT' : 'POST';
    const url    = id ? `${API_URL}?id=${id}` : API_URL;

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        const result = await res.json();

        if (!res.ok) {
            showMessage(result.error || 'Something went wrong', false);
            return;
        }
        showMessage(result.message);
        resetForm();
        loadProfiles();
    } catch (err) {
        showMessage('Server not reachable', false);
    }
});

// Load one profile into the form for editing (READ one)
async function editProfile(id) {
    const res = await fetch(`${API_URL}?id=${id}`);
    const p = await res.json();

    document.getElementById('profileId').value = p.id;
    document.getElementById('name').value      = p.name;
    document.getElementById('tagline').value   = p.tagline;
    document.getElementById('location').value  = p.location;
    document.getElementById('email').value     = p.email;
    document.getElementById('services').value  = p.services;

    formTitle.textContent = 'Edit Profile';
    cancelBtn.hidden = false;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// DELETE
async function deleteProfile(id) {
    if (!confirm('Delete this profile?')) return;

    const res = await fetch(`${API_URL}?id=${id}`, { method: 'DELETE' });
    const result = await res.json();
    showMessage(result.message || result.error, res.ok);
    loadProfiles();
}

function resetForm() {
    form.reset();
    document.getElementById('profileId').value = '';
    formTitle.textContent = 'Add New Profile';
    cancelBtn.hidden = true;
}

cancelBtn.addEventListener('click', () => {
    resetForm();
    messageEl.textContent = '';
});

// SEARCH: runs shortly after the user stops typing
let searchTimer;
searchInput.addEventListener('input', () => {
    clearSearchBtn.hidden = searchInput.value === '';
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadProfiles, 300);
});

clearSearchBtn.addEventListener('click', () => {
    searchInput.value = '';
    clearSearchBtn.hidden = true;
    loadProfiles();
});

loadProfiles();
