# Vendor (Pembayaran ke Vendor Armada Eksternal)

**Menu utama:** Vendor (di sidebar, sejajar FAKTUR / BANK / INVENTORY)
**Sub menu:**

| Sub Menu | Isi | Alamat |
|---|---|---|
| **Order Menunggu Nota** | Order yang belum dibuat nota — di sini nota/invoice digenerate | `http://phl.test/vendor/order/waiting` |
| **Invoice Belum Lunas** | Nota yang belum lunas / terbayar sebagian (DP, cicilan) | `http://phl.test/vendor/invoice/unpaid` |
| **Invoice Lunas** | Arsip nota yang sudah dibayar 100% | `http://phl.test/vendor/invoice/paid` |
| **Daftar Pembayaran** | Riwayat semua transaksi pembayaran (DP, cicilan, pelunasan) | `http://phl.test/vendor/payment` |

> 🔄 Menu lama **Finance → Vendor Payment** sudah tidak dipakai lagi. Kalau ada yang membuka alamat lamanya (`/finance/vendor-payment`), sistem otomatis mengarahkan ke halaman **Invoice Belum Lunas** yang baru.

---

## 1. Apa Itu Menu Ini?

Menu **Vendor** adalah pusat pengelolaan **kewajiban pembayaran ke perusahaan armada (fleet) yang bertipe External** — yaitu perusahaan kendaraan luar/partner, bukan armada milik sendiri.

Setiap kali ada order yang dikerjakan menggunakan kendaraan milik vendor eksternal, perusahaan kita **berutang biaya sewa (tagihan vendor)** ke vendor tersebut. Semua prosesnya — dari pembuatan nota, pembayaran, sampai riwayat — sekarang dikerjakan dari satu menu ini:

1. **Membuat nota** (pengelompokan beberapa order jadi satu tagihan resmi)
2. **Melakukan pembayaran** (lunas, DP, atau dicicil)
3. **Mencetak nota** dalam bentuk PDF
4. **Membatalkan** nota atau pembayaran jika ada kesalahan
5. **Melihat riwayat** semua transaksi pembayaran

**Masing-masing langkah punya sub menu sendiri sesuai urutan kerja:**

```
Order Selesai (vendor eksternal)
  → [Order Menunggu Nota]  : kumpulkan order, klik Generate Nota
  → [Invoice Belum Lunas]  : bayar DP / cicilan / lunas, cetak PDF
  → [Invoice Lunas]        : arsip nota lunas
  → [Daftar Pembayaran]    : riwayat semua transaksi
```

> Order yang kendaraannya berasal dari perusahaan **internal** tidak akan muncul di menu ini.

---

## 2. Konsep Dasar: Nota

Sebelum membayar, order-order harus dikumpulkan dulu menjadi **nota** — mirip seperti membuat tagihan resmi yang akan dikirim ke vendor.

**Ciri-ciri satu nota:**

- Berisi **satu atau beberapa order** sekaligus
- Semua order harus punya **vendor (perusahaan kendaraan) yang sama**
- Semua order harus punya **format perusahaan yang sama** (Pribadi / PHL / WTMS)
- Satu nota = satu dokumen tagihan = satu target pembayaran

**Format nomor nota otomatis mengikuti jenis perusahaan:**

| Format Perusahaan | Contoh Nomor Nota |
|---|---|
| Pribadi | `P/00001/2026` |
| WTMS / WT | `WTMS/00001/2026` |
| PHL (umum) | `PHL/00001/2026` |

Nomor urut berjalan otomatis dan **reset ke 00001 setiap tahun baru**.

**Siklus hidup nota:**

```
Order selesai (vendor eksternal)
        │
        ▼
  [Order Menunggu Nota]  ── Generate Nota ──►  [Nota Pending]
                                                  │
                                     Bayar DP / cicilan
                                                  ▼
                                             [Nota Partial]
                                                  │
                                            Pelunasan sisa
                                                  ▼
                                        [Nota Lunas] ✅ (pindah ke sub menu Invoice Lunas)
```

---

## 3. Sub Menu 1: Order Menunggu Nota

Titik awal semua proses. Berisi **order eksternal yang belum pernah dibuatkan nota**, diurut dari tanggal pesanan paling lama.

**Kartu ringkasan:** Order Menunggu Nota, Total Nilai Tagihan, Vendor Terlibat.

Kolom tabel: tanggal pesanan, nopol, perusahaan kendaraan, pengendara, pelanggan, asal/tujuan, tagihan, terbayar, sisa, status.

Dari sini user bisa:
- ✅ **Generate Nota** — centang beberapa order → klik tombol *Generate Nota* → pilih akun bank → simpan (lihat Bagian 5)

**Aturan memilih order (dicek otomatis oleh sistem):**

- ✅ Semua order harus **vendor-nya sama** (perusahaan kendaraan sama)
- ✅ Semua order harus **format perusahaannya sama** (Pribadi / PHL / WTMS)
- ❌ Kalau dicampur, sistem menolak dengan peringatan yang jelas

---

## 4. Sub Menu 2: Invoice Belum Lunas

Halaman kerja pembayaran sehari-hari. Berisi **nota yang belum lunas** — baik yang belum dibayar sama sekali (Pending) maupun sudah terbayar sebagian (Partial). Satu baris = satu nota, bukan satu order.

**Kartu ringkasan:** Nota Belum Lunas (berapa baru / parsial), Total Order di dalam nota, Sudah Terbayar, Sisa Harus Dibayar.

Kolom tabel: nomor nota, tanggal nota, vendor, jumlah order, nopol, tagihan, terbayar, sisa, status bayar.

Dari tabel ini user bisa:
- 💰 **Bayar Nota Terpilih** — DP / cicilan / lunas (lihat Bagian 6)
- 🖨️ **Cetak Terpilih** — cetak PDF nota
- 👁️ **Detail** — lihat rincian nota + riwayat pembayaran
- ❌ **Batal Pembayaran** — jika sudah terlanjur bayar
- 🗑️ **Batal Nota** — jika nota belum pernah dibayar sama sekali

> Centang di header tabel = pilih semua nota di halaman itu. Nota adalah satu kesatuan — membayar/mencetak satu nota otomatis melibatkan semua order di dalamnya.

---

## 5. Langkah 1 — Generate Nota (WAJIB duluan)

Order **belum bisa dibayar sebelum punya nomor nota**. Proses ini dilakukan dari sub menu **Order Menunggu Nota**.

**Cara:**

1. Buka menu **Vendor → Order Menunggu Nota**
2. Centang order yang ingin digabungkan (vendor dan format perusahaannya harus sama)
3. Klik tombol **"Generate Nota"**
4. Muncul popup berisi ringkasan order terpilih (jumlah order, format, vendor, daftar kode order, subtotal/DPP)
5. **Input PPN & PPh secara manual** (nominal rupiah, lihat penjelasan di bawah)
6. Pilih **akun bank** yang ditargetkan untuk pembayaran nota ini
7. Klik **"Generate Nota Sekarang!"**
8. Sistem memberikan **nomor nota otomatis**, contoh: `PHL/00012/2026`
9. Nota langsung muncul di sub menu **Invoice Belum Lunas** dengan status **Pending**

### 💰 PPN & PPh (Input Manual)

Modal generate nota mengakomodasi **PPN** (Pajak Pertambahan Nilai) dan **PPh** (Pajak Penghasilan) dengan **input manual nominal rupiah** — tidak dihitung otomatis:

- **Subtotal (DPP)** = total tagihan order yang dicentang
- **PPN** = diinput manual, ditambahkan ke total bayar
- **PPh** = diinput manual, dipotong dari total bayar
- **Total Bayar = Subtotal (DPP) + PPN − PPh** (dihitung otomatis, live di modal)

**Contoh:**

| Komponen | Nilai |
|---|---|
| Subtotal (DPP) | Rp 15.000.000 |
| PPN (input manual) | Rp 1.650.000 |
| PPh (input manual) | Rp 300.000 |
| **Total Bayar** | **Rp 16.350.000** |

**Perilaku sistem:**

- PPN & PPh didistribusikan **proporsional ke tiap order** dalam nota (tanpa selisih pembulatan), sehingga tagihan per order dan total nota tetap konsisten
- Nilai PPN/PPh **tersimpan di nota** dan terlihat di kolom tabel Invoice Belum Lunas, popup pembayaran, popup detail, serta **tercetak di PDF nota**
- Pembayaran (DP/cicilan/lunas) mengikuti **Total Bayar** — bayar penuh = lunas termasuk pajak
- Isi `0` apabila nota tidak dikenakan pajak
- PPh tidak boleh membuat total bayar negatif (sistem menolak)

**Aturan penting saat menggabungkan order ke satu nota:**

- ✅ Semua order harus **vendor-nya sama** — tidak boleh campur vendor berbeda
- ✅ Semua order harus **format perusahaannya sama** (Pribadi / PHL / WTMS)
- ❌ Order yang **sudah punya nota** tidak bisa dipindahkan ke nota lain
- ❌ Kalau aturan dilanggar, sistem menolak dengan pesan peringatan yang jelas

---

## 6. Langkah 2 — Bayar Nota (DP / Cicilan / Lunas)

**Cara:**

1. Di tabel *Nota Belum Lunas*, centang nota yang mau dibayar (boleh lebih dari satu nota sekaligus)
2. Klik tombol **"Bayar Nota Terpilih"**
3. Muncul popup **Data Pembayaran** berisi ringkasan:
   - Jumlah & kode order yang akan dibayar (semua order dalam nota terpilih)
   - Nomor nota
   - Tagihan vendor, sudah terbayar, dan sisa tagihan
4. Isi **Total Pembayaran**:
   - Default: terisi **sisa tagihan penuh** (langsung lunas)
   - Bisa **diubah jadi lebih kecil** untuk bayar sebagian (DP)
   - Tidak boleh melebihi sisa tagihan (otomatis dibatasi)
5. Isi **Tanggal Pembayaran**
6. Pilih **Sumber Dana (Bank)** — dari rekening mana uang keluar
7. Isi deskripsi (opsional) → klik **Simpan**

**Yang terjadi setelah pembayaran disimpan:**

- Saldo bank berkurang sesuai nominal (tercatat sebagai uang keluar)
- Status nota berubah: **Pending → Partial** (kalau masih ada sisa) atau **Pending → Paid** (kalau lunas)
- Jika lunas, **status semua order di nota itu otomatis jadi Paid** dan nota **pindah ke sub menu Invoice Lunas**
- Setiap pembayaran tercatat sebagai satu transaksi di **Daftar Pembayaran**

💡 **Tips:** Bayar sebagian bisa dilakukan berkali-kali (DP dulu, cicilan, terakhir pelunasan). Status nota tetap **Partial** sampai sisanya habis.

---

## 7. Langkah 3 — Cetak Nota (PDF)

1. Centang nota (di Invoice Belum Lunas atau Invoice Lunas)
2. Klik tombol **"Cetak Terpilih"**
3. PDF nota terbuka di tab baru, siap dicetak/disimpan

**Yang perlu diketahui:**

- Mencentang satu nota = **semua order dalam nota itu ikut tercetak** (tidak bisa cetak sebagian dari satu nota)
- Nota yang dicetak bersamaan harus **format perusahaannya sama**
- Tampilan nota menyesuaikan format: **Pribadi**, **WTMS**, atau **PHL**
- Isi nota: rincian order, subtotal, biaya tambahan, potongan PPh, dan grand total

---

## 8. Sub Menu 3: Invoice Lunas

Arsip nota yang sudah dibayar 100%. Berguna untuk:

- Mengecek kembali nota yang sudah selesai
- Mencetak ulang PDF nota
- **Membatalkan pembayaran** jika ada kesalahan (pembatalan mengembalikan nota ke *Invoice Belum Lunas*)

**Kartu ringkasannya:** Nota Lunas, Total Order Terbayar, Total Pembayaran Keluar.

---

## 9. Sub Menu 4: Daftar Pembayaran

Buku kas khusus pembayaran vendor. **Satu baris = satu transaksi** — jadi satu nota yang dibayar DP + 2 cicilan akan muncul sebagai **3 baris transaksi** berbeda.

Kolomnya: tanggal pembayaran, kode transaksi (batch), nomor nota, order, vendor, nominal, bank sumber dana, keterangan.

**Kartu ringkasannya:** Jumlah Transaksi, Total Nominal Keluar, Nota Terlibat, Vendor Berbeda.

> Halaman ini hanya untuk **melihat riwayat** — pembatalan transaksi tetap dilakukan dari halaman Invoice (Belum Lunas / Lunas).

---

## 10. Melihat Detail & Riwayat Pembayaran

Di kolom **Aksi** tabel nota, klik ikon 👁️ (mata) untuk membuka popup detail berisi:

- Kode pembayaran & status pembayaran
- Nomor nota kalender
- Semua kode order yang tergabung dalam nota yang sama
- Nopol, perusahaan kendaraan, driver, pelanggan
- Nominal tagihan, terbayar, dan sisa
- Bank sumber dana
- **Riwayat pembayaran** — tanggal, jumlah, bank, keterangan setiap transaksi (berguna kalau bayar dicicil/DP beberapa kali)

---

## 11. Pembatalan

Ada **dua jenis pembatalan** dengan fungsi berbeda:

### 🔸 Batal Nota

- **Kapan bisa:** Hanya jika nota **belum pernah dibayar sama sekali** (status Pending)
- **Cara:** Klik ikon 🗑️ (kuning) di kolom Aksi → konfirmasi
- **Efek:** Seluruh order dalam nota dibebaskan — nomor nota hilang, order kembali ke *Order Menunggu Nota*, bisa di-generate nota ulang kapan saja

### 🔸 Batal Pembayaran

- **Kapan bisa:** Jika sudah ada pembayaran yang terlanjur dilakukan
- **Cara:** Klik ikon ❌ (merah) di kolom Aksi → konfirmasi
- **Efek:**
  - **Saldo bank dikembalikan penuh** — termasuk kalau tadinya sudah bayar DP + cicilan beberapa kali, semuanya ditarik kembali
  - Catatan mutasi keuangan dihapus
  - Status nota di-reset ke **Pending**, nominal terbayar kembali nol
  - Nota lunas akan **kembali ke Invoice Belum Lunas**
- ⚠️ **Perhatian:** Pembatalan bersifat **permanen** — riwayat transaksinya ikut terhapus. Tapi nomor nota **tetap tersimpan**, jadi tidak perlu generate nota ulang.

> Urutan pembatalan kalau nota sudah dibayar: **Batal Pembayaran dulu** → baru bisa **Batal Nota**.

---

## 12. Istilah Penting

| Istilah | Arti |
|---|---|
| **Vendor** | Perusahaan pemilik armada eksternal yang kita sewa kendaraannya |
| **Nota** | Dokumen tagihan resmi. Satu nota = beberapa order + satu vendor + satu format perusahaan |
| **Nomor Nota Kalender** | Nomor unik nota dengan format `PREFIX/URUT/TAHUN` |
| **Batch Pembayaran** | Satu kode untuk satu transaksi pembayaran (boleh bayar banyak order sekaligus dalam satu kali submit) |
| **DP / Cicilan** | Membayar sebagian dari total tagihan; sisanya dibayar di kesempatan lain |
| **Pending** | Nota belum ada pembayaran sama sekali |
| **Partial** | Sudah dibayar sebagian, masih ada sisa |
| **Paid / Lunas** | Tagihan sudah terbayar penuh |
| **Sumber Dana** | Rekening bank perusahaan yang dipakai untuk membayar vendor |

---

## 13. Contoh Skenario Lengkap

> **Situasi:** Ada 5 order pengiriman minggu ini, semuanya pakai kendaraan vendor "CV Jaya Transport" (eksternal), customer format PHL. Total tagihan Rp 50 juta.

1. **Buka menu Vendor → Order Menunggu Nota** → 5 order terlihat, status Pending
2. **Generate Nota:** centang 5 order → klik *Generate Nota* → pilih bank BCA → simpan → muncul pesan sukses dengan nomor `PHL/00027/2026`
3. **Bayar DP:** centang nota → klik *Bayar Nota Terpilih* → ubah nominal jadi Rp 30 juta → pilih sumber dana → simpan → status nota jadi **Partial** (terbayar 30 juta, sisa 20 juta). Di *Daftar Pembayaran* muncul 1 transaksi.
4. **Cetak:** klik *Cetak Terpilih* → PDF nota `PHL/00027/2026` terbuka berisi kelima order, rincian, PPh, dan total
5. **Pelunasan minggu depan:** buka *Invoice Belum Lunas* → centang nota yang sama → bayar Rp 20 juta → status jadi **Paid**, order ikut Paid, nota **pindah ke sub menu Invoice Lunas**. Di *Daftar Pembayaran* sekarang ada 2 transaksi.
6. **Kalau salah bayar:** buka nota di *Invoice Lunas* (atau *Belum Lunas*) → klik ikon ❌ → saldo bank kembali penuh, status reset ke **Pending**, nota kembali tinggal dibayar ulang dengan nominal yang benar.
