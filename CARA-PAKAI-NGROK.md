# ATURAN PAKAI — NOTAGO + NGROK

## Kondisi & Perintah

| Kondisi | Perintah |
|---------|----------|
| Dev di laptop (normal) | `npm run dev` |
| Mau share via ngrok / HP | `npm run share` atau klik `share.bat` |
| Habis `npm run dev`, mau ngrok | `npm run share` lagi |

---

## Intinya

> Setiap kali habis `npm run dev` lalu mau buka di HP via ngrok
> → jalankan **`npm run share`** dulu.
> Sekali jalan, refresh HP, langsung CSS muncul.

---

## Kenapa?

`npm run dev` membuat file `public/hot` yang redirect CSS/JS ke localhost:5173.
HP tidak bisa akses localhost → CSS tidak muncul.

`npm run share` otomatis:
1. Hapus `public/hot`
2. Build assets jadi file statis
3. Clear cache Laravel

---

## Shortcut

```bash
# Terminal
npm run share

# Atau double-click file ini di folder project:
share.bat
```
