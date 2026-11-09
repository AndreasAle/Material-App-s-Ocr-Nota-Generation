@props(['target', 'append' => false, 'max' => 5])

{{--
    Tombol "Ambil Foto" untuk nota: kamera langsung (webcam/kamera HP lewat getUserMedia),
    dengan cadangan input kamera bawaan HP. Hasil foto dimasukkan ke <input type="file" id="{{ $target }}">
    lalu event "change" dikirim, sehingga pratinjau & proses upload yang sudah ada tetap jalan.
--}}
<div x-data="cameraCapture(@js($target), @js((bool) $append), @js((int) $max))" {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-2']) }}>
    <button type="button" @click="open()"
            class="inline-flex min-h-[52px] items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 text-[15px] font-bold text-white shadow-sm shadow-brand-600/30 active:scale-[0.98]">
        <x-icon name="camera" class="h-6 w-6" />
        Ambil Foto
    </button>
    <button type="button" @click="document.getElementById(target).click()"
            class="inline-flex min-h-[52px] items-center justify-center gap-2 rounded-xl border border-charcoal-200 bg-white px-4 text-[15px] font-bold text-charcoal-800 active:scale-[0.98]">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
        Galeri / File
    </button>

    {{-- Cadangan: kamera bawaan HP --}}
    <input type="file" accept="image/*" capture="environment" class="hidden" x-ref="nativeCamera" @change="useNative($event)">

    {{-- Jendela kamera langsung --}}
    <template x-teleport="body">
        <div x-show="isOpen" x-cloak class="fixed inset-0 z-[100] flex flex-col bg-black" @keydown.escape.window="close()">
            <div class="flex items-center justify-between px-4 py-3 text-white">
                <span class="text-[15px] font-bold" x-text="shot ? 'Periksa foto nota' : 'Arahkan kamera ke nota'"></span>
                <button type="button" @click="close()" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/10" aria-label="Tutup">✕</button>
            </div>

            <div class="relative flex flex-1 items-center justify-center overflow-hidden">
                <video x-ref="video" x-show="!shot" playsinline muted autoplay class="max-h-full max-w-full object-contain"></video>
                <img x-ref="preview" x-show="shot" :src="shotUrl" alt="Foto nota" class="max-h-full max-w-full object-contain">
                {{-- Bingkai bantu agar nota pas di layar --}}
                <div x-show="!shot && ready" class="pointer-events-none absolute inset-6 rounded-2xl border-2 border-dashed border-white/60"></div>
                <p x-show="!ready && !error" class="absolute text-sm text-white/80">Menyalakan kamera…</p>
                <p x-show="error" class="absolute px-8 text-center text-sm text-white" x-text="error"></p>
            </div>

            <div class="flex items-center justify-center gap-4 px-4 pb-[calc(1.25rem+env(safe-area-inset-bottom))] pt-4">
                <template x-if="!shot">
                    <div class="flex w-full items-center justify-between">
                        <button type="button" @click="switchCamera()" x-show="cameras > 1" class="h-12 rounded-full bg-white/10 px-4 text-sm font-semibold text-white">Ganti kamera</button>
                        <span x-show="cameras <= 1" class="w-24"></span>
                        <button type="button" @click="snap()" :disabled="!ready"
                                class="h-20 w-20 rounded-full border-4 border-white bg-brand-500 shadow-lg disabled:opacity-40" aria-label="Jepret"></button>
                        <span class="w-24"></span>
                    </div>
                </template>
                <template x-if="shot">
                    <div class="grid w-full max-w-md grid-cols-2 gap-3">
                        <button type="button" @click="retake()" class="h-14 rounded-xl bg-white/10 text-[15px] font-bold text-white">Ulangi</button>
                        <button type="button" @click="usePhoto()" class="h-14 rounded-xl bg-brand-500 text-[15px] font-bold text-white">Pakai Foto</button>
                    </div>
                </template>
            </div>
            <canvas x-ref="canvas" class="hidden"></canvas>
        </div>
    </template>
</div>

@once
@push('scripts')
<script>
function cameraCapture(target, append, max) {
    return {
        target, append, max,
        isOpen: false, ready: false, error: '', stream: null,
        shot: null, shotUrl: '', facing: 'environment', cameras: 0,

        async open() {
            // Tanpa dukungan kamera langsung (mis. bukan HTTPS) → pakai kamera bawaan HP
            if (!navigator.mediaDevices?.getUserMedia || !window.isSecureContext) {
                this.$refs.nativeCamera.click();
                return;
            }
            this.isOpen = true; this.shot = null; this.error = '';
            await this.start();
        },

        async start() {
            this.ready = false;
            this.stopStream();
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: this.facing }, width: { ideal: 1920 }, height: { ideal: 1440 } },
                    audio: false,
                });
                const video = this.$refs.video;
                video.srcObject = this.stream;
                await video.play();
                this.ready = true;
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.cameras = devices.filter(d => d.kind === 'videoinput').length;
            } catch (e) {
                this.close();
                // Izin ditolak / kamera tidak ada → coba kamera bawaan / pilih file
                this.$refs.nativeCamera.click();
            }
        },

        switchCamera() {
            this.facing = this.facing === 'environment' ? 'user' : 'environment';
            this.start();
        },

        snap() {
            const video = this.$refs.video, canvas = this.$refs.canvas;
            canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(blob => {
                this.shot = blob;
                this.shotUrl = URL.createObjectURL(blob);
            }, 'image/jpeg', 0.9);
        },

        retake() {
            if (this.shotUrl) URL.revokeObjectURL(this.shotUrl);
            this.shot = null; this.shotUrl = '';
        },

        usePhoto() {
            const file = new File([this.shot], 'nota-' + Date.now() + '.jpg', { type: 'image/jpeg' });
            this.putFile(file);
            this.close();
        },

        async useNative(event) {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (file) this.putFile(await this.shrink(file));
        },

        // Foto kamera HP bisa > 10 MB: kecilkan ke sisi terpanjang 2000px
        shrink(file) {
            if (!@json(config('receipt_ai.compress_images', false))) return Promise.resolve(file);
            return new Promise(resolve => {
                if (!file.type.startsWith('image/') || file.size < 2 * 1024 * 1024) return resolve(file);
                const img = new Image();
                img.onload = () => {
                    const scale = Math.min(1, 2000 / Math.max(img.width, img.height));
                    const c = document.createElement('canvas');
                    c.width = Math.round(img.width * scale); c.height = Math.round(img.height * scale);
                    c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
                    c.toBlob(b => resolve(b ? new File([b], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file), 'image/jpeg', 0.88);
                    URL.revokeObjectURL(img.src);
                };
                img.onerror = () => resolve(file);
                img.src = URL.createObjectURL(file);
            });
        },

        putFile(file) {
            const input = document.getElementById(this.target);
            const dt = new DataTransfer();
            if (this.append) {
                Array.from(input.files || []).forEach(f => dt.items.add(f));
                if (dt.files.length >= this.max) {
                    alert('Maksimal ' + this.max + ' nota sekaligus.');
                    return;
                }
            }
            dt.items.add(file);
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },

        stopStream() {
            this.stream?.getTracks().forEach(t => t.stop());
            this.stream = null;
        },

        close() {
            this.stopStream();
            this.isOpen = false; this.ready = false;
            if (this.shotUrl) URL.revokeObjectURL(this.shotUrl);
            this.shot = null; this.shotUrl = '';
        },
    };
}
</script>
@endpush
@endonce
