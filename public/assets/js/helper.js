// Fungsi untuk memformat angka dengan titik sebagai pemisah ribuan
function formatAngka(input) {
    // Menghapus titik yang sudah ada di dalam input
    let angka = input.value.replace(/\./g, "");

    // Mengecek apakah input hanya berisi angka
    if (!/^\d+$/.test(angka)) {
        // Jika tidak hanya berisi angka, set input menjadi string kosong
        input.value = "";
        return;
    }

    // Mengubah ke format angka dan menambahkan titik pemisah ribuan
    angka = new Intl.NumberFormat("id-ID").format(angka);

    // Mengembalikan nilai yang sudah diformat ke input
    input.value = angka;
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
