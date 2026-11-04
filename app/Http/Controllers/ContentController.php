<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(Request $request): View
    {
        $contents = collect([
            [
                'id' => 1,
                'title' => 'Cara Input Transaksi Pembelian yang Benar',
                'category' => 'tips',
                'status' => 'published',
                'author' => 'Administrator',
                'reading_time' => '5 menit',
                'image' => asset('images/content/content-1.jpg'),
                'date' => '14 Apr 2026',
                'location' => 'Dashboard',
                'accent' => 'from-indigo-500 via-violet-500 to-fuchsia-500',
                'icon' => 'document',
                'summary' => 'Panduan singkat untuk memasukkan transaksi pembelian material dengan data yang rapi dan valid.',
                'content' => 'Pastikan setiap transaksi pembelian diinput dengan informasi yang lengkap, mulai dari tanggal, supplier, proyek, nomor nota, hingga seluruh item material. Hindari mengosongkan harga atau qty karena akan memengaruhi total akhir transaksi. Untuk data yang berasal dari scan nota, admin tetap perlu melakukan validasi sebelum menekan tombol simpan transaksi.',
                'points' => [
                    'Periksa tanggal transaksi sebelum simpan.',
                    'Pastikan supplier dan proyek sesuai.',
                    'Cek qty, harga, dan subtotal setiap item.',
                    'Gunakan catatan untuk menandai item yang perlu review.',
                ],
            ],
            [
                'id' => 2,
                'title' => 'Tips Validasi Hasil Scan Nota Sebelum Disimpan',
                'category' => 'tips',
                'status' => 'published',
                'author' => 'Administrator',
                'reading_time' => '4 menit',
                                'image' => asset('images/content/content-2.jpg'),
                'date' => '14 Apr 2026',
                'location' => 'Scan Nota',
                'accent' => 'from-emerald-500 via-teal-500 to-cyan-500',
                'icon' => 'scan',
                'summary' => 'Cek qty, harga, subtotal, dan nama material agar hasil OCR tidak menyebabkan selisih total.',
                'content' => 'Hasil scan nota tidak selalu 100% akurat. Sistem bisa salah membaca angka, terutama pada nota dengan garis tabel, tulisan kecil, atau foto miring. Karena itu, admin wajib memeriksa setiap item hasil scan sebelum memasukkannya ke transaksi.',
                'points' => [
                    'Bandingkan harga hasil scan dengan nota asli.',
                    'Periksa item yang subtotalnya tidak sinkron.',
                    'Cek baris yang terbaca ganda atau hilang.',
                    'Pastikan grand total hasil import sama dengan nota.',
                ],
            ],
            [
                'id' => 3,
                'title' => 'Panduan Export PDF dan Excel Laporan Pembelian',
                'category' => 'guide',
                'status' => 'published',
                      'image' => asset('images/content/content-3.jpg'),
                'author' => 'Administrator',
                'reading_time' => '6 menit',
                'date' => '13 Apr 2026',
                'location' => 'Laporan',
                'accent' => 'from-amber-400 via-orange-500 to-rose-500',
                'icon' => 'export',
                'summary' => 'Langkah mudah untuk mengekspor laporan pembelian material menjadi file PDF maupun Excel.',
                'content' => 'Fitur export digunakan untuk membuat rekap pembelian menjadi dokumen yang lebih mudah dibagikan. Admin dapat memilih rentang data tertentu sebelum mengunduh laporan dalam format PDF atau Excel.',
                'points' => [
                    'Gunakan filter tanggal sebelum export.',
                    'Pilih format sesuai kebutuhan pengguna.',
                    'Cek kembali nilai total di laporan.',
                    'Simpan file rekap sebagai arsip bulanan.',
                ],
            ],
            [
                'id' => 4,
                'title' => 'FAQ: Kenapa Hasil OCR Bisa Berbeda dengan Nota Asli?',
                'category' => 'faq',
                'status' => 'published',
                'author' => 'Administrator',
                'reading_time' => '3 menit',
                                      'image' => asset('images/content/content-4.jpg'),
                'date' => '12 Apr 2026',
                'location' => 'Help Center',
                'accent' => 'from-slate-500 via-slate-700 to-slate-900',
                'icon' => 'faq',
                'summary' => 'Penjelasan penyebab hasil scan dapat berbeda karena posisi foto, kualitas gambar, garis tabel, dan angka yang ambigu.',
                'content' => 'OCR bekerja dengan membaca pola karakter dari gambar. Jika gambar buram, miring, atau banyak garis tabel, maka sistem dapat salah mengenali angka atau nama item. Oleh karena itu, hasil OCR harus dianggap sebagai draft awal yang tetap perlu dicek oleh admin.',
                'points' => [
                    'Foto harus cukup terang dan fokus.',
                    'Hindari posisi nota terlalu miring.',
                    'Pastikan angka tidak tertutup objek lain.',
                    'Selalu review item sebelum import transaksi.',
                ],
            ],
            // [
            //     'id' => 5,
            //     'title' => 'Announcement: Maintenance Sistem Input Pembelian',
            //     'category' => 'announcement',
            //     'status' => 'published',
            //     'author' => 'Administrator',
            //     'reading_time' => '2 menit',
            //     'date' => '11 Apr 2026',
            //     'location' => 'Pengumuman',
            //     'accent' => 'from-rose-500 via-pink-500 to-orange-400',
            //     'icon' => 'announcement',
            //     'summary' => 'Informasi jadwal maintenance sistem untuk peningkatan stabilitas fitur scan nota dan transaksi.',
            //     'content' => 'Akan dilakukan maintenance sistem pada akhir pekan untuk peningkatan performa fitur scan nota, validasi item, dan kestabilan export laporan. Selama maintenance, beberapa fitur dapat berjalan lebih lambat dari biasanya.',
            //     'points' => [
            //         'Lakukan input penting sebelum jadwal maintenance.',
            //         'Simpan draft yang sedang dikerjakan.',
            //         'Cek pengumuman terbaru setelah maintenance selesai.',
            //     ],
            // ],
            [
                'id' => 6,
                'title' => 'Tentang NOTAGO dan Alur Kerja Aplikasi',
                'category' => 'about',
                'status' => 'published',
                'author' => 'Administrator',
                'reading_time' => '5 menit',
                'date' => '10 Apr 2026',
                                                 'image' => asset('images/content/content-5.jpg'),
                'location' => 'About App',
                'accent' => 'from-sky-500 via-blue-600 to-indigo-700',
                'icon' => 'app',
                'summary' => 'Gambaran umum aplikasi NOTAGO, fitur utama, dan bagaimana modul pembelian serta scan nota saling terhubung.',
                'content' => 'NOTAGO adalah aplikasi operasional untuk membantu pencatatan pembelian material, pengelolaan supplier, proyek, master data, dan scan nota. Fokus utama sistem adalah mempercepat input pembelian sekaligus menjaga akurasi data.',
                'points' => [
                    'Kelola transaksi pembelian lebih cepat.',
                    'Scan nota sebagai draft input otomatis.',
                    'Master data membantu konsistensi item.',
                    'Laporan memudahkan rekap operasional.',
                ],
            ],
            [
                'id' => 7,
                'title' => 'Draft SOP Pengecekan Material Hasil Import',
                'category' => 'guide',
                'status' => 'draft',
                'author' => 'Administrator',
                'reading_time' => '7 menit',
                'date' => '09 Apr 2026',
                'location' => 'Internal',
                                                        'image' => asset('images/content/content-6.jpg'),
                'accent' => 'from-lime-500 via-green-500 to-emerald-600',
                'icon' => 'checklist',
                'summary' => 'Draft SOP internal untuk memeriksa item scan sebelum benar-benar dijadikan transaksi pembelian.',
                'content' => 'Dokumen ini merupakan draft SOP internal yang digunakan admin untuk melakukan pengecekan hasil import dari scan nota. SOP ini membantu menjaga akurasi sebelum transaksi resmi tersimpan di sistem.',
                'points' => [
                    'Review item satu per satu.',
                    'Pastikan harga sesuai nota asli.',
                    'Cek subtotal dan total akhir.',
                    'Tambahkan catatan bila ada koreksi manual.',
                ],
            ],
            [
                'id' => 8,
                'title' => 'Tips Menata Supplier, Satuan, dan Material',
                'category' => 'tips',
                'status' => 'published',
                'author' => 'Administrator',
                'reading_time' => '4 menit',
                'date' => '08 Apr 2026',
                   'image' => asset('images/content/content-7.jpg'),
                'location' => 'Master Data',
                'accent' => 'from-purple-500 via-indigo-500 to-blue-500',
                'icon' => 'master',
                'summary' => 'Tips merapikan master data agar input transaksi lebih cepat, konsisten, dan minim salah pilih material.',
                'content' => 'Master data yang rapi akan membuat proses input transaksi jauh lebih cepat. Nama material yang konsisten, kode yang jelas, dan satuan yang benar akan membantu admin saat mencari maupun menghubungkan hasil scan.',
                'points' => [
                    'Gunakan kode material yang konsisten.',
                    'Pisahkan kategori material dengan jelas.',
                    'Hindari duplikasi nama item.',
                    'Lengkapi satuan dan harga default bila perlu.',
                ],
            ],
        ]);

        $tab = $request->string('tab')->toString();
        $status = $request->string('status')->toString();
        $sort = $request->string('sort')->toString() ?: 'latest';

        $allContents = $contents;

        if ($tab && $tab !== 'all') {
            $contents = $contents->where('category', $tab)->values();
        }

        if ($status && $status !== 'all') {
            $contents = $contents->where('status', $status)->values();
        }

        $contents = match ($sort) {
            'title' => $contents->sortBy('title')->values(),
            'oldest' => $contents->reverse()->values(),
            default => $contents->values(),
        };

        $stats = [
            'all' => $allContents->count(),
            'published' => $allContents->where('status', 'published')->count(),
            'draft' => $allContents->where('status', 'draft')->count(),
            'tips' => $allContents->where('category', 'tips')->count(),
        ];

        return view('contents.index', [
            'contents' => $contents->values()->all(),
            'stats' => $stats,
            'filters' => [
                'tab' => $tab ?: 'all',
                'status' => $status ?: 'all',
                'sort' => $sort,
            ],
        ]);
    }
}