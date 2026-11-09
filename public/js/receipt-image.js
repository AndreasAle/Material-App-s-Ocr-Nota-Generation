// Keep small camera JPEGs intact; reduce large gallery images before uploading.
async function prepareReceiptImage(file, maxSide = 1800, compress = false) {
    if (!compress) return file;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return file;

    const url = URL.createObjectURL(file);
    try {
        const image = new Image();
        await new Promise((resolve, reject) => {
            image.onload = resolve;
            image.onerror = reject;
            image.src = url;
        });
        const longest = Math.max(image.naturalWidth, image.naturalHeight);
        const limit = Math.max(1200, Number(maxSide) || 1800);
        if (longest <= limit && file.size <= 1024 * 1024) return file;

        const scale = Math.min(1, limit / longest);
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
        canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
        const context = canvas.getContext('2d');
        if (!context) return file;
        context.fillStyle = '#fff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.9));
        if (!blob || blob.size >= file.size) return file;
        return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', {
            type: 'image/jpeg', lastModified: file.lastModified,
        });
    } catch {
        return file;
    } finally {
        URL.revokeObjectURL(url);
    }
}
