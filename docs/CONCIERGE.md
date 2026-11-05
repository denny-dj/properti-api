# Panduan Operasional Concierge

Model "Managed Concierge Marketplace": admin platform menjadi garda depan. Calon klien tidak berhubungan langsung dengan mitra;
semua minat masuk lewat WhatsApp admin, pembayaran lewat tautan resmi platform, dan dana mitra dilepas setelah klien puas.

## 1. Alur kerja harian

| Langkah | Siapa | Di mana |
|---|---|---|
| 1. Klien menekan **Konsultasi via WhatsApp Admin** (detail properti/portofolio) atau mengisi **Ajukan KPR** | Klien | Situs publik |
| 2. Prospek tercatat dengan kode `LEAD-2026-XXXX`; WhatsApp admin terbuka dengan pesan siap kirim | Sistem | `/admin/prospek` |
| 3. Admin menghubungi klien, mengubah status (Dihubungi, Survei, Sepakat, Batal) dan mencatat hasilnya | Admin | `/admin/prospek` |
| 4. Klien sepakat: admin menekan **Buat invoice**, memastikan klien punya akun, lalu mengirim pesan WhatsApp berisi tautan bayar | Admin | `/admin/invoice-cepat` |
| 5. Klien membayar (VA, kartu, e-wallet). Properti otomatis *Dipesan*; untuk desain, mitra diberi tahu | Sistem | Webhook Midtrans |
| 6. Mitra arsitek mengunggah berkas ke Google Drive, mengirim tautan, dan klien memeriksa | Mitra, Klien | `/mitra/pesanan-desain`, `/transaksi/{kode}` |
| 7. Klien menekan **Konfirmasi Penerimaan Berkas** -> dana mitra dilepas ke saldo siap cair | Klien | `/transaksi/{kode}` |
| 8. Admin mentransfer saldo ke rekening mitra lalu mencatatnya | Admin | `/admin/pencairan` |
| 9. Rekap komisi dan ekspor CSV untuk keuangan | Admin | `/admin/pendapatan`, `/admin/transaksi` |

## 2. Aturan uang

- **Properti**: management fee **2% dari harga properti** (`config/concierge.php`). Booking fee hanyalah uang muka; fee sering lebih besar
  dari booking fee, sehingga hak bersih mitra dari booking fee bisa 0 dan selisih komisi ditagih saat akad.
- **Arsitek**: komisi **10-15% dari nilai invoice**, ditentukan admin per invoice (bawaan 12,5%). Klien tidak melihat komisi.
- **Saldo mitra** = hak bersih transaksi desain yang sudah dikonfirmasi klien dikurangi pencairan yang dicatat admin.
  Dana yang belum dikonfirmasi klien tidak dapat dicairkan.
- Biaya Payment Gateway aktual **tidak** tersimpan di aplikasi. Ambil dari laporan settlement Midtrans.
- Pajak (PPN/PPh) belum dimodelkan.

## 3. Pengaturan (`.env`)

| Variabel | Fungsi |
|---|---|
| `ADMIN_WA_NUMBER` | Nomor WhatsApp admin, format `62812xxxxxxxx` tanpa `+`. Kosong -> tombol WhatsApp menjawab error 503. |
| `CONCIERGE_LEGACY_UI` | `true` = chat langsung, konsultasi per jam, dan bayar mandiri tetap aktif. `false` = ditutup (lihat bagian 4). |

Setelah memperbarui kode yang menambah izin baru (mis. `concierge.manage`, `payouts.manage`), jalankan lagi:

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan config:cache
php artisan app:preflight
```

## 4. Mode `CONCIERGE_LEGACY_UI=false`

Fitur lama **ditutup, bukan dihapus**; data tetap utuh dan mode dapat dibalik kapan saja.

| Area | Perilaku |
|---|---|
| `/arsitek`, `/arsitek/{slug}` | Dialihkan ke `/portofolio` |
| `/chat`, `/chat/{id}`, API `conversations*` | Web dialihkan ke Cara Kerja; API menjawab **410** |
| `/pesanan`, API `bookings*`, `payments/architect-bookings` | Web dialihkan ke `/transaksi`; API **410** |
| API `payments/property-booking-fee` | **410**; tagihan dibuat admin lewat `POST /api/v1/admin/invoices/quick-booking` |
| `/mitra/layanan`, `/mitra/jadwal`, `/mitra/pesanan`, API `partner/bookings`, `availabilities`, `architect-services` | Dialihkan ke `/mitra/portofolio`; API **410** |
| Menu pembeli, mitra, admin | Item lama disembunyikan |
| `/admin/pesanan` (riwayat konsultasi lama) | Tetap dapat dibuka admin sebagai arsip |
| Transaksi, webhook Midtrans, `transactions*` | Tidak berubah |

Transaksi konsultasi lama yang sudah lunas tetap tampil di `/transaksi`.

## 5. Ekspor CSV

`/admin/transaksi` -> **Ekspor CSV**.

- **Properti**: kolom A-J identik dengan sheet `PROPERTY_TRANSACTIONS` (tempel A:J). Kolom sesudahnya (komisi dan hak mitra tercatat,
  status transaksi, kode prospek) hanya rujukan.
  - *Status Penjualan*: `Terjual`, `Booking Aktif`, atau `Batal` (dikembalikan, dilepas, atau sudah digantikan transaksi lain pada properti yang sama).
    `Perlu dicek` bila properti tidak ditemukan.
  - *Metode Bayar* dipetakan ke VA, Kartu Kredit, E-Wallet, atau QRIS; kosong bila tidak dikenali.
  - *Nama Partner* harus sama dengan daftar mitra di sheet ASUMSI agar dropdown dan payout cocok.
- **Desain**: nilai, komisi, hak bersih, status serah terima, dan tanggal konfirmasi.
- Pemisah bawaan titik koma (Excel Indonesia); pilih koma untuk Google Sheets. Berkas berbentuk UTF-8 dengan BOM.
- Sel teks yang diawali `=`, `+`, `-`, atau `@` diberi apostrof untuk mencegah CSV injection.

## 6. Pemecahan masalah

| Gejala | Penyebab dan solusi |
|---|---|
| Halaman admin Prospek/Invoice/Pencairan menampilkan 403 | Izin belum ada: jalankan `RoleSeeder` |
| Tombol WhatsApp menampilkan "Nomor WhatsApp admin belum dikonfigurasi" | Isi `ADMIN_WA_NUMBER` lalu `php artisan config:cache` |
| Invoice ditolak "Akun pembeli tidak ditemukan" | Minta klien mendaftar dahulu (email tidak harus terverifikasi) |
| Mitra tidak dapat mengirim tautan berkas | Pesanan belum lunas, atau host tautan bukan Google Drive (`config/concierge.php` -> `deliverable_hosts`) |
| Pencairan ditolak "melebihi saldo siap dicairkan" | Dana belum dikonfirmasi klien, atau sudah dicairkan sebagian |
| `app:preflight` GAGAL pada "Izin admin concierge" | Jalankan `RoleSeeder` |

## 7. Belum tercakup

- Penyimpanan data rekening mitra (saat ini diminta lewat WhatsApp dan dicatat di kolom referensi pencairan).
- Rilis dana otomatis bila klien tidak merespons (disarankan 7-14 hari, lewat command terjadwal).
- Pengelolaan banner iklan lewat antarmuka admin (saat ini lewat seeder atau tinker).
- Pengembalian dana otomatis, pajak, dan integrasi bank rekanan KPR (prospek diteruskan manual oleh admin).
