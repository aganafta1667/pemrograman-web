let students = [
    { id: 1, nim: '5025251055', nama: 'Andi Pratama', jurusan: 'Teknik Informatika', email: '5025251055@student.its.ac.id' },
    { id: 2, nim: '5026251098', nama: 'Siti Aisyah', jurusan: 'Sistem Informasi', email: '5026251098@student.its.ac.id' },
    { id: 3, nim: '5024251141', nama: 'Budi Santoso', jurusan: 'Teknik Komputer', email: '5024251141@student.its.ac.id' },
    { id: 4, nim: '5022251062', nama: 'Nina Marlina', jurusan: 'Teknik Elektro', email: '5022251062@student.its.ac.id' },
    { id: 5, nim: '5025251134', nama: 'Rizky Pratama', jurusan: 'Teknik Informatika', email: '5025251134@student.its.ac.id' }
];

let editingId = null;

const form = document.getElementById('studentForm');
const studentIdInput = document.getElementById('studentId');
const nimInput = document.getElementById('nim');
const namaInput = document.getElementById('nama');
const jurusanInput = document.getElementById('jurusan');
const emailInput = document.getElementById('email');
const tbody = document.getElementById('studentTableBody');
const btnCancel = document.getElementById('btnCancel');
const btnReset = document.getElementById('btnReset');
const searchInput = document.getElementById('searchInput');

function renderTable(dataToRender = students) {
    tbody.innerHTML = '';

    if (dataToRender.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Tidak ada data ditemukan</td></tr>';
        return;
    }

    dataToRender.forEach((student, index) => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${student.nim}</td>
            <td>${student.nama}</td>
            <td>${student.jurusan}</td>
            <td>${student.email}</td>
            <td>
                <div class="actions">
                    <button type="button" class="action edit" title="Edit" data-id="${student.id}">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="action delete" title="Delete" data-id="${student.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        
        tr.querySelector('.edit').addEventListener('click', () => editStudent(student.id));
        tr.querySelector('.delete').addEventListener('click', () => deleteStudent(student.id));

        tbody.appendChild(tr);
    });
}

form.addEventListener('submit', function(e) {
    e.preventDefault();

    const newStudent = {
        id: editingId ? editingId : Date.now(),
        nim: nimInput.value,
        nama: namaInput.value,
        jurusan: jurusanInput.value,
        email: emailInput.value
    };

    if (editingId) {
        const index = students.findIndex(s => s.id === editingId);
        if (index !== -1) {
            students[index] = newStudent;
        }
    } else {
        students.push(newStudent);
    }

    renderTable();
    resetFormState();
});

function editStudent(id) {
    const student = students.find(s => s.id === id);
    if (student) {
        editingId = student.id;
        nimInput.value = student.nim;
        namaInput.value = student.nama;
        jurusanInput.value = student.jurusan;
        emailInput.value = student.email;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
function deleteStudent(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data mahasiswa ini?')) {
        students = students.filter(s => s.id !== id);
        renderTable();
        if (editingId === id) {
            resetFormState();
        }
    }
}

function resetFormState() {
    form.reset();
    editingId = null;
}

btnCancel.addEventListener('click', resetFormState);
btnReset.addEventListener('click', resetFormState);

searchInput.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filteredData = students.filter(student => 
        student.nama.toLowerCase().includes(searchTerm) || 
        student.nim.toLowerCase().includes(searchTerm) ||
        student.jurusan.toLowerCase().includes(searchTerm)
    );
    renderTable(filteredData);
});

renderTable();