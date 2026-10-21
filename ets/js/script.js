const INITIAL_DUMMY_MEMBERS = [
    {
        nama: "Andi Pratama",
        nim: "230101001",
        prodi: "Informatika",
        jenis: "Mahasiswa",
        telp: "081234567890",
        email: "andi@email.com",
        alamat: "Jl. Pendidikan No. 1, Surabaya"
    },
    {
        nama: "Siti Rahma",
        nim: "230101002",
        prodi: "Sistem Informasi",
        jenis: "Mahasiswa",
        telp: "081234567891",
        email: "siti@email.com",
        alamat: "Jl. Flamboyan No. 12, Surabaya"
    },
    {
        nama: "Budi Santoso",
        nim: "19881234",
        prodi: "Dosen",
        jenis: "Dosen",
        telp: "081234567892",
        email: "budi@email.com",
        alamat: "Kompleks Dosen Blok B3, Surabaya"
    },
    {
        nama: "Maya Lestari",
        nim: "230101003",
        prodi: "Manajemen",
        jenis: "Mahasiswa",
        telp: "081234567893",
        email: "maya@email.com",
        alamat: "Jl. Dharmawangsa No. 45, Surabaya"
    },
    {
        nama: "Rizky Kurniawan",
        nim: "230101004",
        prodi: "Akuntansi",
        jenis: "Tenaga Kependidikan",
        telp: "081234567894",
        email: "rizky@email.com",
        alamat: "Jl. Menur No. 8, Surabaya"
    }
];

document.addEventListener('DOMContentLoaded', () => {
    initStorage();
    loadMembers();
    
    const regForm = document.getElementById('regForm');
    if (regForm) {
        regForm.addEventListener('submit', handleRegister);
    }

    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', handleUpdateMember);
    }

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', loadMembers);
    }

    const btnResetDummy = document.getElementById('btnResetDummy');
    if (btnResetDummy) {
        btnResetDummy.addEventListener('click', resetToDummyData);
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
});

function initStorage() {
    const existing = localStorage.getItem('library_members');
    if (!existing || JSON.parse(existing).length === 0) {
        localStorage.setItem('library_members', JSON.stringify(INITIAL_DUMMY_MEMBERS));
    }
}

function resetToDummyData() {
    if (confirm("Kembalikan data anggota ke 5 data dummy default? Data pendaftaran baru lainnya akan terhapus.")) {
        localStorage.setItem('library_members', JSON.stringify(INITIAL_DUMMY_MEMBERS));
        showNotification("Data berhasil direset ke data dummy bawaan.", "info");
        loadMembers();
    }
}

function showNotification(message, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    if (!toastEl) {
        alert(message);
        return;
    }

    const toastText = document.getElementById('toastText');
    const toastIcon = document.getElementById('toastIcon');

    toastEl.className = 'toast align-items-center text-white border-0 shadow-lg';
    
    if (type === 'success') {
        toastEl.classList.add('bg-success');
        toastIcon.className = 'fas fa-check-circle fs-5';
    } else if (type === 'danger') {
        toastEl.classList.add('bg-danger');
        toastIcon.className = 'fas fa-exclamation-triangle fs-5';
    } else {
        toastEl.classList.add('bg-primary');
        toastIcon.className = 'fas fa-info-circle fs-5';
    }

    toastText.textContent = message;
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
}

function getMembers() {
    try {
        return JSON.parse(localStorage.getItem('library_members')) || [];
    } catch (e) {
        return [];
    }
}

function saveMembers(members) {
    localStorage.setItem('library_members', JSON.stringify(members));
}

function handleRegister(e) {
    e.preventDefault();
    
    const members = getMembers();
    const newMember = {
        nama: document.getElementById('r_nama').value.trim(),
        nim: document.getElementById('r_nim').value.trim(),
        prodi: document.getElementById('r_prodi').value.trim(),
        jenis: document.getElementById('r_jenis').value,
        telp: document.getElementById('r_telp').value.trim(),
        email: document.getElementById('r_email').value.trim(),
        alamat: document.getElementById('r_alamat').value.trim(),
    };

    if (members.some(m => m.nim.toLowerCase() === newMember.nim.toLowerCase())) {
        showNotification("Pendaftaran Gagal! NIM/NIP " + newMember.nim + " sudah terdaftar.", "danger");
        return;
    }

    members.push(newMember);
    saveMembers(members);
    
    showNotification("Berhasil! " + newMember.nama + " telah terdaftar sebagai anggota.", "success");
    
    document.getElementById('regForm').reset();
    
    loadMembers();
    const adminSection = document.getElementById('admin');
    if (adminSection) {
        adminSection.scrollIntoView({ behavior: 'smooth' });
    }
}

function loadMembers() {
    const members = getMembers();
    const searchInput = document.getElementById('searchInput');
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const tbody = document.getElementById('memberTableBody');
    const countBadge = document.getElementById('memberCountBadge');
    
    if (!tbody) return;
    tbody.innerHTML = '';

    const filteredMembers = members.filter(m => 
        (m.nama && m.nama.toLowerCase().includes(searchQuery)) || 
        (m.nim && m.nim.toLowerCase().includes(searchQuery)) ||
        (m.prodi && m.prodi.toLowerCase().includes(searchQuery))
    );

    if (countBadge) {
        countBadge.textContent = "Total: " + filteredMembers.length + " Anggota";
    }

    if (filteredMembers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted fst-italic">Belum ada data anggota yang terdaftar atau sesuai pencarian.</td></tr>';
        return;
    }

    filteredMembers.forEach((m, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center fw-semibold text-muted">${index + 1}</td>
            <td class="fw-medium text-dark"></td>
            <td class="fw-semibold text-secondary font-monospace"></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-center">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary edit-btn" title="Edit Anggota">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger delete-btn" title="Hapus Anggota">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        tr.children[1].textContent = m.nama || '-';
        tr.children[2].textContent = m.nim || '-';
        tr.children[3].textContent = m.prodi || '-';
        tr.children[4].textContent = m.jenis || 'Mahasiswa';
        tr.children[5].textContent = m.telp || '-';
        tr.children[6].textContent = m.email || '-';

        const editBtn = tr.querySelector('.edit-btn');
        editBtn.addEventListener('click', () => openEditModal(m.nim));

        const deleteBtn = tr.querySelector('.delete-btn');
        deleteBtn.addEventListener('click', () => deleteMember(m.nim, m.nama));

        tbody.appendChild(tr);
    });
}

function openEditModal(nim) {
    const members = getMembers();
    const member = members.find(m => m.nim === nim);
    if (!member) {
        showNotification("Data anggota tidak ditemukan.", "danger");
        return;
    }

    document.getElementById('edit_old_nim').value = member.nim;
    document.getElementById('edit_nim').value = member.nim;
    document.getElementById('edit_nama').value = member.nama;
    document.getElementById('edit_prodi').value = member.prodi;
    document.getElementById('edit_jenis').value = member.jenis;
    document.getElementById('edit_email').value = member.email;
    document.getElementById('edit_telp').value = member.telp;
    document.getElementById('edit_alamat').value = member.alamat || '';

    const editModalEl = document.getElementById('editModal');
    const modal = new bootstrap.Modal(editModalEl);
    modal.show();
}

function handleUpdateMember(e) {
    e.preventDefault();

    const oldNim = document.getElementById('edit_old_nim').value;
    const newNim = document.getElementById('edit_nim').value.trim();
    const members = getMembers();

    if (newNim.toLowerCase() !== oldNim.toLowerCase()) {
        if (members.some(m => m.nim.toLowerCase() === newNim.toLowerCase())) {
            showNotification("Gagal mengubah! NIM/NIP " + newNim + " sudah digunakan anggota lain.", "danger");
            return;
        }
    }

    const index = members.findIndex(m => m.nim === oldNim);
    if (index === -1) {
        showNotification("Data anggota tidak ditemukan.", "danger");
        return;
    }

    members[index] = {
        nim: newNim,
        nama: document.getElementById('edit_nama').value.trim(),
        prodi: document.getElementById('edit_prodi').value.trim(),
        jenis: document.getElementById('edit_jenis').value,
        email: document.getElementById('edit_email').value.trim(),
        telp: document.getElementById('edit_telp').value.trim(),
        alamat: document.getElementById('edit_alamat').value.trim(),
    };

    saveMembers(members);
    showNotification("Data anggota " + members[index].nama + " berhasil diperbarui.", "success");

    const editModalEl = document.getElementById('editModal');
    const modalInstance = bootstrap.Modal.getInstance(editModalEl);
    if (modalInstance) {
        modalInstance.hide();
    }

    loadMembers();
}

function deleteMember(nim, nama) {
    const label = nama ? `"${nama}" (${nim})` : `NIM: ${nim}`;
    if (confirm("Apakah Anda yakin ingin menghapus data anggota " + label + "?")) {
        let members = getMembers();
        members = members.filter(m => m.nim !== nim);
        saveMembers(members);
        
        showNotification("Data anggota " + (nama || nim) + " berhasil dihapus.", "success");
        loadMembers();
    }
}

function handleLogin(e) {
    e.preventDefault();

    const usernameInput = document.getElementById('loginUsername');
    const passwordInput = document.getElementById('loginPassword');
    const alertBox = document.getElementById('loginAlert');
    const alertText = document.getElementById('loginAlertText');

    const username = usernameInput ? usernameInput.value.trim() : '';
    const password = passwordInput ? passwordInput.value : '';

    const validCredentials = [
        { user: 'admin', pass: 'admin123', name: 'Administrator' },
        { user: 'petugas', pass: 'petugas123', name: 'Petugas Perpustakaan' }
    ];

    const matchedAccount = validCredentials.find(
        acc => acc.user.toLowerCase() === username.toLowerCase() && acc.pass === password
    );

    if (matchedAccount) {
        if (alertBox) alertBox.classList.add('d-none');
        document.getElementById('loginForm').reset();

        const loginModalEl = document.getElementById('loginModal');
        const modalInstance = bootstrap.Modal.getInstance(loginModalEl);
        if (modalInstance) {
            modalInstance.hide();
        }

        showNotification("Login Berhasil! Selamat datang kembali, " + matchedAccount.name + ".", "success");
    } else {
        if (alertBox && alertText) {
            alertText.textContent = 'Username atau kata sandi tidak cocok. Silakan coba lagi.';
            alertBox.classList.remove('d-none');
        } else {
            showNotification('Login Gagal! Username atau password salah.', 'danger');
        }
    }
}
