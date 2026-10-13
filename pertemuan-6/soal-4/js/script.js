const dataMerk = {
    desktop: ["Asus", "Dell", "Lenovo", "HP", "Acer"],
    laptop: ["MacBook", "ASUS ROG", "Lenovo ThinkPad", "Dell XPS", "HP Spectre"],
    smartphone: ["iPhone", "Samsung", "Xiaomi", "OPPO", "Vivo"]
};

const jenisSelect = document.getElementById("jenisProduk");
const merkSelect = document.getElementById("merkProduk");
const teksPilihan = document.getElementById("teksPilihan");

function updateMerk() {
    const jenisTerpilih = jenisSelect.value;

    merkSelect.innerHTML = '<option value="">Pilih Merk</option>';

    if (jenisTerpilih && dataMerk[jenisTerpilih]) {
        dataMerk[jenisTerpilih].forEach(merk => {
            const option = document.createElement("option");
            option.value = merk;
            option.textContent = merk;
            merkSelect.appendChild(option);
        });
    }
    updateTeksPilihan();
}

function updateTeksPilihan() {
    const jenisText = jenisSelect.value ? jenisSelect.options[jenisSelect.selectedIndex].text : "";
    const merkText = merkSelect.value ? merkSelect.options[merkSelect.selectedIndex].text : "";

    if (jenisText && merkText) {
        teksPilihan.textContent = `${jenisText} ${merkText}`;
    } else if (jenisText) {
        teksPilihan.textContent = `${jenisText}`;
    } else {
        teksPilihan.textContent = "-";
    }
}

jenisSelect.addEventListener("change", updateMerk);
merkSelect.addEventListener("change", updateTeksPilihan);