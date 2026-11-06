const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

function setup({ width = 4000, height = 3000, blobSize = 100000, broken = false } = {}) {
    let revoked = 0;
    const canvas = {
        getContext: () => ({ fillRect() {}, drawImage() {} }),
        toBlob: callback => callback(blobSize === null ? null : new Blob(['x'.repeat(blobSize)], { type: 'image/jpeg' })),
    };
    const context = vm.createContext({
        File, Blob,
        URL: { createObjectURL: () => 'blob:test', revokeObjectURL: () => revoked++ },
        document: { createElement: () => canvas },
        Image: class {
            naturalWidth = width;
            naturalHeight = height;
            set src(value) { queueMicrotask(() => broken ? this.onerror() : this.onload()); }
        },
    });
    vm.runInContext(readFileSync('public/js/receipt-image.js', 'utf8'), context);
    return { prepare: context.prepareReceiptImage, canvas, revoked: () => revoked };
}

test('large photo is reduced proportionally without exceeding the configured side', async () => {
    const app = setup();
    const original = new File(['x'.repeat(2000000)], 'nota.png', { type: 'image/png' });
    const result = await app.prepare(original, 1800);
    assert.equal(app.canvas.width, 1800);
    assert.equal(app.canvas.height, 1350);
    assert.equal(result.type, 'image/jpeg');
    assert.equal(result.name, 'nota.jpg');
    assert.ok(result.size < original.size);
    assert.equal(app.revoked(), 1);
});

test('small camera images retain their original bytes', async () => {
    const app = setup({ width: 1600, height: 1200 });
    const original = new File(['photo'], 'nota.jpg', { type: 'image/jpeg' });
    assert.equal(await app.prepare(original), original);
    assert.equal(app.revoked(), 1);
});

test('PDF bypasses image conversion', async () => {
    const app = setup();
    const original = new File(['pdf'], 'nota.pdf', { type: 'application/pdf' });
    assert.equal(await app.prepare(original), original);
    assert.equal(app.revoked(), 0);
});

test('decode failure and null encoder result preserve the upload and release the URL', async () => {
    const original = new File(['photo'], 'nota.jpg', { type: 'image/jpeg' });
    for (const options of [{ broken: true }, { blobSize: null }]) {
        const app = setup(options);
        assert.equal(await app.prepare(original), original);
        assert.equal(app.revoked(), 1);
    }
});

test('conversion that increases file size keeps original', async () => {
    const app = setup();
    const original = new File(['photo'], 'nota.jpg', { type: 'image/jpeg' });
    assert.equal(await app.prepare(original), original);
});
