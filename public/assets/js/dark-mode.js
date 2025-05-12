// Mendapatkan elemen berdasarkan ID
const darkModeToggle = document.getElementById("darkModeToggle");
const sidebar = document.getElementById("sidebar-main");
const sidebarLight = document.getElementById("sidebar-light");
const sidebarDark = document.getElementById("sidebar-dark");

// Menambahkan style pointer pada tombol toggle
darkModeToggle.style.cursor = "pointer";

// Fungsi untuk mengaktifkan dark mode
function enableDarkMode() {
    document.body.classList.add("dark-only");
    // sidebar.classList.remove('bg-white');
    // sidebarDark.removeAttribute("disabled"); // Aktifkan sidebar dark
    // sidebarLight.setAttribute("disabled", "disabled"); // Nonaktifkan sidebar light
    localStorage.setItem("darkMode", "enabled");
}

// Fungsi untuk menonaktifkan dark mode
function disableDarkMode() {
    document.body.classList.remove("dark-only");
    // sidebar.classList.add("bg-white");
    // sidebarLight.removeAttribute("disabled"); // Aktifkan sidebar light
    // sidebarDark.setAttribute("disabled", "disabled"); // Nonaktifkan sidebar dark
    localStorage.setItem("darkMode", "disabled");
}

// Ketika halaman dimuat, cek preferensi mode dark dari localStorage
if (localStorage.getItem("darkMode") === "enabled") {
    enableDarkMode(); // Aktifkan dark mode jika sebelumnya diaktifkan
} else {
    disableDarkMode(); // Nonaktifkan dark mode jika sebelumnya tidak diaktifkan
}

// Tambahkan event listener untuk tombol toggle
darkModeToggle.addEventListener("click", function () {
    // Cek apakah dark mode sedang aktif
    if (document.body.classList.contains("dark-only")) {
        disableDarkMode(); // Jika aktif, nonaktifkan
    } else {
        enableDarkMode(); // Jika tidak aktif, aktifkan
    }
});
