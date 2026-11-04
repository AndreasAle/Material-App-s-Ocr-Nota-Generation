<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProjectDocumentController extends Controller
{
    private const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt',
        'jpg', 'jpeg', 'png', 'webp', 'gif', 'dwg', 'dxf', 'zip', 'rar', '7z',
    ];

    // Hanya tipe ini yang aman ditampilkan inline di browser
    private const INLINE_MIMES = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function index(Request $request): View
    {
        $projects = Project::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $query = ProjectDocument::with(['project', 'uploader']);

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($s = $request->search) {
            $query->where(fn($q) => $q->where('title', 'like', "%{$s}%")
                ->orWhere('description', 'like', "%{$s}%"));
        }

        $documents  = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $categories = ['general', 'rab', 'spk', 'laporan', 'gambar', 'invoice'];

        return view('documents.index', compact('documents', 'projects', 'categories'));
    }

    public function create(Request $request): View
    {
        $projects   = Project::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $categories = ['general', 'rab', 'spk', 'laporan', 'gambar', 'invoice'];
        $projectId  = $request->project_id;
        return view('documents.create', compact('projects', 'categories', 'projectId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'title'       => 'required|string|max:255',
            // 20MB max; whitelist ekstensi agar file skrip tidak bisa diupload
            'file'        => 'required|file|max:20480|extensions:' . implode(',', self::ALLOWED_EXTENSIONS),
            'category'    => 'required|string|in:general,rab,spk,laporan,gambar,invoice',
            'description' => 'nullable|string',
        ]);

        $file     = $request->file('file');
        $path     = $file->store('project-documents/' . $request->project_id, 'private');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $fileMime = $file->getMimeType();

        try {
            ProjectDocument::create([
            'project_id'  => $request->project_id,
            'title'       => $request->title,
            'file_path'   => $path,
            'file_name'   => $fileName,
            'file_size'   => $fileSize,
            'file_mime'   => $fileMime,
            'category'    => $request->category,
            'description' => $request->description,
            'uploaded_by' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('private')->delete($path);
            throw $e;
        }

        return redirect()->route('documents.index', ['project_id' => $request->project_id])
            ->with('success', 'Dokumen "' . $request->title . '" berhasil diunggah.');
    }

    public function show(ProjectDocument $document): View
    {
        $document->load(['project', 'uploader']);
        $fileExists = $document->fileExists();

        return view('documents.show', compact('document', 'fileExists'));
    }

    public function download(ProjectDocument $document)
    {
        abort_unless($document->fileExists(), 404, 'File tidak ditemukan.');

        return Storage::disk($document->storageDisk())->download($document->file_path, $document->file_name);
    }

    public function preview(ProjectDocument $document)
    {
        abort_unless($document->fileExists(), 404, 'File tidak ditemukan.');

        if (! in_array($document->file_mime, self::INLINE_MIMES, true)) {
            return $this->download($document);
        }

        return response()->file(Storage::disk($document->storageDisk())->path($document->file_path), [
            'Content-Type'           => $document->file_mime,
            'Content-Disposition'    => 'inline; filename="' . addslashes($document->file_name) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(ProjectDocument $document): RedirectResponse
    {
        $projectId = $document->project_id;
        $disk = $document->storageDisk();
        $document->delete();
        Storage::disk($disk)->delete($document->file_path);

        return redirect()->route('documents.index', ['project_id' => $projectId])
            ->with('success', 'Dokumen dihapus.');
    }
}
