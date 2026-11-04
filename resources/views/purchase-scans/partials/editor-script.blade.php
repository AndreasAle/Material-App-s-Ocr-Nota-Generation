{{-- Perhitungan total, penanda "Diubah", hapus & kembalikan baris. Dipakai semua halaman Cek Hasil Scan. --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = Array.from(document.querySelectorAll('.compare-row'));

    function toNumber(value) {
        let str = String(value ?? '').trim().replace(/[^0-9,.\-]/g, '');
        if (!str) return 0;
        const hasDot = str.includes('.'), hasComma = str.includes(',');
        if (hasDot && hasComma) return Number(str.replace(/\./g, '').replace(',', '.')) || 0;
        if (hasDot) {
            const parts = str.split('.');
            // 1.250.000 → ribuan; 12.5 → desimal
            if (parts.length > 1 && parts.slice(1).every(p => p.length === 3)) return Number(parts.join('')) || 0;
        }
        if (hasComma) return Number(str.replace(',', '.')) || 0;
        return Number(str) || 0;
    }

    const rupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(n || 0)));
    const same = (a, b) => String(a ?? '').trim() === String(b ?? '').trim();

    function rowSubtotal(row) {
        const sub = toNumber(row.querySelector('.compare-subtotal')?.value);
        if (sub > 0) return sub;
        return toNumber(row.querySelector('.compare-qty')?.value) * toNumber(row.querySelector('.compare-harga')?.value);
    }

    function refreshRow(row) {
        if (row.dataset.deleted === '1') return;
        let changed = false;
        row.querySelectorAll('.compare-input').forEach(function (input) {
            const diff = !same(input.value, input.dataset.original);
            input.classList.toggle('!border-amber-400', diff);
            input.classList.toggle('!bg-amber-50', diff);
            if (diff) changed = true;
        });
        row.querySelector('.row-changed')?.classList.toggle('hidden', !changed);
    }

    function refreshTotals() {
        let total = 0, count = 0;
        rows.forEach(function (row) {
            if (row.dataset.deleted === '1') return;
            count++;
            total += rowSubtotal(row);
        });
        document.querySelectorAll('[data-grand-total]').forEach(el => el.textContent = rupiah(total));
        document.querySelectorAll('[data-item-count]').forEach(el => el.textContent = count);
        const importBtn = document.getElementById('btn-save-continue');
        if (importBtn) importBtn.disabled = count === 0;
    }

    function setDeleted(row, deleted) {
        row.dataset.deleted = deleted ? '1' : '0';
        row.querySelector('.delete-flag').value = deleted ? '1' : '0';
        row.querySelectorAll('.compare-input').forEach(i => i.disabled = deleted);
        row.classList.toggle('opacity-50', deleted);
        const overlay = row.querySelector('.deleted-overlay');
        overlay?.classList.toggle('hidden', !deleted);
        overlay?.classList.toggle('flex', deleted);
        if (!deleted) refreshRow(row);
        refreshTotals();
    }

    function resetRow(row) {
        row.querySelectorAll('.compare-input').forEach(i => { i.value = i.dataset.original ?? ''; });
        refreshRow(row);
    }

    rows.forEach(function (row) {
        row.querySelectorAll('.compare-input').forEach(i => i.addEventListener('input', () => { refreshRow(row); refreshTotals(); }));
        row.querySelector('.delete-row')?.addEventListener('click', () => setDeleted(row, true));
        row.querySelector('.undo-delete')?.addEventListener('click', () => setDeleted(row, false));
        row.querySelector('.reset-row')?.addEventListener('click', () => { resetRow(row); refreshTotals(); });
    });

    document.getElementById('reset-all-btn')?.addEventListener('click', function () {
        if (!confirm('Kembalikan semua baris seperti hasil scan awal?')) return;
        rows.forEach(function (row) { if (row.dataset.deleted === '1') setDeleted(row, false); resetRow(row); });
        refreshTotals();
    });

    rows.forEach(refreshRow);
    refreshTotals();
});
</script>
