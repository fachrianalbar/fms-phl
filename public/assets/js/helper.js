// Fungsi untuk memformat angka dengan titik sebagai pemisah ribuan, mendukung desimal
function formatAngka(input) {
    // Jika input berakhir dengan koma atau titik, jangan format dulu
    if (input.value.endsWith(',') || input.value.endsWith('.')) {
        return;
    }

    // Menghapus titik yang sudah ada di dalam input, kecuali titik desimal
    let angka = input.value.replace(/\./g, "").replace(",", ".");

    // Mengecek apakah input adalah angka valid dengan opsional desimal, atau titik di akhir
    if (!/^\d+(\.\d{0,2})?$/.test(angka) && !/^\d*\.$/.test(angka)) {
        // Jika tidak valid, potong ke 2 desimal jika ada
        if (angka.includes(".")) {
            let parts = angka.split(".");
            if (parts[1] && parts[1].length > 2) {
                angka = parts[0] + "." + parts[1].substring(0, 2);
            }
        }
        let parsed = parseFloat(angka);
        if (!isNaN(parsed)) {
            input.value = new Intl.NumberFormat("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parsed);
        } else {
            input.value = "";
        }
        return;
    }

    // Jika valid, format
    let parsed = parseFloat(angka);
    if (!isNaN(parsed)) {
        let formatted = new Intl.NumberFormat("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parsed);
        input.value = formatted;
    }
}

// konversi string angka menjadi int
function submitForm(id) {
    // Ambil nilai input
    const input = document.getElementById(id);

    // Hilangkan titik pemisah ribuan sebelum submit
    const angkaAsli = input.value.replace(/\./g, "");

    // Ubah nilai input menjadi angka asli (tanpa titik)
    input.value = angkaAsli;

    // Setelah itu form bisa dikirim
    return true; // Return true untuk melanjutkan submit form
}

function generateCode(
    dateInputSelector,
    displaySelector,
    hiddenSelector,
    ajaxUrl
) {
    let selectedDate = $(dateInputSelector).val();
    $.ajax({
        url: ajaxUrl,
        method: "GET",
        data: {
            date: selectedDate,
        },
        success: function (response) {
            $(displaySelector).val(response.code);
            $(hiddenSelector).val(response.code);
        },
    });
}
