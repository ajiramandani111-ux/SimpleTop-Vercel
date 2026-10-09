// ===== Hamburger menu =====
function initNavToggle() {
    const toggleBtn = document.getElementById("nav-toggle-btn");
    const nav = document.querySelector("header nav");

    if (!toggleBtn || !nav) return;

    toggleBtn.addEventListener("click", function () {
        nav.classList.toggle("nav-open");
    });
}

// ===== Konfirmasi hapus (kartu katalog / item ranking) =====
// Tombol Hapus ada di dalam <form> (khusus admin): bila dikonfirmasi, form dikirim
// ke hapus.php dan server yang menghapus dari database. Bila batal, pengiriman dibatalkan.
function initHapusConfirm() {
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".btn-hapus");
        if (!btn) return;

        const item = btn.closest(".searchable-item");
        const nama = item ? item.querySelector(".item-name")?.textContent : "data ini";
        const yakin = confirm("Hapus \"" + nama + "\" dari katalog?\nData tetap tersimpan dan bisa ditampilkan lagi oleh admin.");

        if (!yakin) {
            e.preventDefault();
            return;
        }

        // Tanpa form (perilaku lama): hanya hapus dari tampilan.
        if (!btn.closest("form") && item) {
            item.remove();
        }
    });
}

// ===== Filter katalog/ranking (kolom pencarian) =====
function initCatalogFilter() {
    const input = document.getElementById("search-input");
    const container = document.getElementById("catalog-container");
    const noResultMsg = document.getElementById("no-result-msg");

    if (!input || !container) return;

    input.addEventListener("keyup", function () {
        const keyword = input.value.toLowerCase().trim();
        const items = container.querySelectorAll(".searchable-item");
        let jumlahTampil = 0;

        items.forEach(function (item) {
            const cari = (item.getAttribute("data-search") || item.textContent).toLowerCase();
            const cocok = cari.includes(keyword);
            item.style.display = cocok ? "" : "none";
            if (cocok) jumlahTampil++;
        });

        if (noResultMsg) {
            noResultMsg.style.display = jumlahTampil === 0 ? "block" : "none";
        }
    });
}

// ===== Toggle tampilkan/sembunyikan password (login & register) =====
function initTogglePassword() {
    document.querySelectorAll(".toggle-password").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const targetId = btn.getAttribute("data-target");
            const input = document.getElementById(targetId);
            if (!input) return;

            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            btn.innerHTML = isHidden
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';
        });
    });
}

// ===== Jalankan semua fungsi =====
document.addEventListener("DOMContentLoaded", function () {
    initNavToggle();
    initHapusConfirm();
    initCatalogFilter();
    initTogglePassword();
});
