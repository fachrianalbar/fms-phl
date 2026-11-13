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

// konversi string angka menjadi int/decimal
function submitForm(id) {
    const input = document.getElementById(id);

    if (!input) {
        return true;
    }

    let value = String(input.value).trim();

    if (value.includes(",")) {
        // Format id-ID: 1.500,50 -> 1500.50
        value = value.replace(/\./g, "").replace(",", ".");
    } else if (/^-?\d{1,3}(\.\d{3})+$/.test(value)) {
        // Pemisah ribuan: 1.500.000 -> 1500000
        value = value.replace(/\./g, "");
    }

    input.value = value;

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
