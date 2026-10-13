const dataMahasiswa = [
    "Aga Nafta Filadelfiano",  
    "Alessandro Almaz Filemon",  
    "Althof Rahmatullah",  
    "Asher Yedijah Hoesono",  
    "Ayudya Devina Azzahra",  
    "Aziz Adi Pramana",  
    "Azka Fairus Syamsa",  
    "Bayu Setyo Nugroho",  
    "Benedictus Imanuel Wicaksono",  
    "Boy Steven Benaya Aritonang",  
    "Chairunnisa` Tsabitah",  
    "Chaniel Daniello Junitona Tarigan",  
    "Claresta Amelinda Hutauruk",  
    "Daffa Arya Satyatma",  
    "Darryl Ardan Wicaksono",  
    "Dewa Kade Aditya Swibawa",  
    "Faizaturrahmah Baity",  
    "Fajar Baskoro",  
    "Farrel Marvellino Sugianto",  
    "Ferdyan Dimas Satria",  
    "Gabriel Mesly Managam Siahaan",  
    "Hadryan Rizky Dimas Saputra",  
    "Hamizan Rifqi Afandi",  
    "Husna `Akifah",  
    "I Dewa Gede Putra Susila",  
    "I Nyoman Gede Anargya Sean Budhi Yasa",  
    "Irsyad Akbar",  
    "Irsyad Ansyari Hakim",  
    "Jessica Febiola",  
    "Jonathan Evan Roestamadji",  
    "Jovan Steve Antony",  
    "Kevinansyah Salviano Rachmadewa",  
    "Kevin Jonathan Messakh",  
    "Khalisya Zahra Putria Rahman",  
    "Lina Mushlihah",  
    "Maida Aqillah Putri Nurandani",  
    "Marco Marcelino",  
    "Mas Ayu Lana Afiah",  
    "Matthew Adrian Putra",  
    "Maulana Bagas Rizqi Pratama",  
    "M. Haziq Ridwan Parsa",  
    "Mochammad Raja Defana",  
    "Muhammad Afzal Fulvian Handoni",  
    "Muhammad Alhady Rizq",  
    "Muhammad Faqih",  
    "Muhammad Faza Ismail",  
    "Muhammad Raihan Ar Royyan Daryanto",  
    "Mushallina Dzikri Rozana",  
    "Nabil Khairie",  
    "Nichola Matthew Hutabarat",  
    "Putu Dylan Pryana",  
    "Putu Pradipta Ananda",  
    "Radhit Akriandra",  
    "Rado Putra Yustisiawan",  
    "Raffa Atha Maulana",  
    "Rafi Atha Maulana",  
    "Rahmavida Novita Setiani",  
    "Raka Rajendra Dipo Alam",  
    "Ronald Ruben Sitopu",  
    "Sanchia Revana Koasasi",  
    "Sayyidah Fatimah Azzahrah Rakhmatullah",  
    "Tristan Athala Rizqullah Al Farisi",  
    "Vijvika Nala Sanuratri",  
    "Yudhistira Eka Pratama",  
    "Mahirah Yasmin Aulia",
    "Daniel Yunus "
];

const inputNama = document.getElementById("nama");
const rekomendasiContainer = document.getElementById("rekomendasi-nama");
const form = document.getElementById("registrasiForm");

const popupOverlay = document.getElementById("customPopup");
const popupMessage = document.getElementById("popupMessage");
const closePopupBtn = document.getElementById("closePopupBtn");

function showPopup(pesan) {
    popupMessage.innerText = pesan;
    popupOverlay.style.display = "flex";
}

closePopupBtn.addEventListener("click", function() {
    popupOverlay.style.display = "none";
});

inputNama.addEventListener("input", function() {
    const nilaiInput = this.value;
    rekomendasiContainer.innerHTML = ""; 

    if (!nilaiInput) return false;

    const dataTersaring = dataMahasiswa.filter(nama => 
        nama.toLowerCase().includes(nilaiInput.toLowerCase())
    );

    dataTersaring.forEach(nama => {
        const item = document.createElement("div");
        const regex = new RegExp(`(${nilaiInput})`, "gi");
        item.innerHTML = nama.replace(regex, "<strong>$1</strong>");

        item.addEventListener("click", function() {
            inputNama.value = nama; 
            rekomendasiContainer.innerHTML = ""; 
        });

        rekomendasiContainer.appendChild(item);
    });
});

document.addEventListener("click", function(e) {
    if (e.target !== inputNama) {
        rekomendasiContainer.innerHTML = "";
    }
});

form.addEventListener("submit", function(e) {
    e.preventDefault(); 

    const nama = document.getElementById("nama").value.trim();
    const email = document.getElementById("email").value.trim();
    const jurusan = document.getElementById("jurusan").value;
    const nim = document.getElementById("nim").value.trim();
    const matkul = document.getElementById("matkul").value.trim();
    const dosen = document.getElementById("dosen").value.trim();
    const formatEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (nama === "") return showPopup("Nama Tidak Boleh Kosong");
    if (email === "") return showPopup("Email Tidak Boleh Kosong");
    if (!formatEmail.test(email)) return showPopup("Format Email Tidak Valid (contoh: user@domain.com)");
    if (jurusan === "") return showPopup("Silakan Pilih Jurusan Terlebih Dahulu");
    if (nim === "") return showPopup("NIM Tidak Boleh Kosong");
    if (matkul === "") return showPopup("Mata Kuliah Tidak Boleh Kosong");
    if (dosen === "") return showPopup("Dosen Tidak Boleh Kosong");

    showPopup("Registrasi Berhasil Disimpan!");
    form.reset();
});