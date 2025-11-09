<?php

namespace Modules\Sertifikat\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sertifikat\Entities\TemplateSertifikat;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PDF;

class TemplateSertifikatController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        try {
            $query = TemplateSertifikat::query();

            if ($request->has('search')) {
                $query->where('nama_template', 'like', '%' . $request->search . '%');
            }

            $templates = $query->orderBy('created_at', 'desc')->paginate(10);

            return view('sertifikat::template.index', compact('templates'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error mengambil data template: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('sertifikat::template.create');
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_template' => 'required|string|max:255',
                'design_template' => 'nullable|string',
                'background' => 'nullable|image|max:2048',
                'signature_config' => 'nullable|string',
                'logo_bpsdm' => 'nullable|image|max:2048',
                'logo_pemda' => 'nullable|image|max:2048',
                'footer_text' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $template = new TemplateSertifikat();
            $template->nama_template = $request->nama_template;
            $template->design_template = $request->design_template;
            $template->signature_config = $request->signature_config;
            $template->footer_text = $request->footer_text;

            // Upload background
            if ($request->hasFile('background')) {
                $path = $request->file('background')->store('templates/backgrounds', 'public');
                $template->path_background = $path;
            }

            // Upload logo BPSDM
            if ($request->hasFile('logo_bpsdm')) {
                $path = $request->file('logo_bpsdm')->store('templates/logos', 'public');
                $template->logo_bpsdm_path = $path;
            }

            // Upload logo Pemda
            if ($request->hasFile('logo_pemda')) {
                $path = $request->file('logo_pemda')->store('templates/logos', 'public');
                $template->logo_pemda_path = $path;
            }

            $template->save();

            return redirect()->route('template.sertifikat.index')
                ->with('success', 'Template sertifikat berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error membuat template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        try {
            $template = TemplateSertifikat::findOrFail($id);
            return view('sertifikat::template.show', compact('template'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Template sertifikat tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Error menampilkan template: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        try {
            $template = TemplateSertifikat::findOrFail($id);
            return view('sertifikat::template.edit', compact('template'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Template sertifikat tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Error menampilkan form edit: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $template = TemplateSertifikat::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_template' => 'nullable|string|max:255',
                'design_template' => 'nullable|string',
                'background' => 'nullable|image|max:2048',
                'signature_config' => 'nullable|string',
                'logo_bpsdm' => 'nullable|image|max:2048',
                'logo_pemda' => 'nullable|image|max:2048',
                'footer_text' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Update text fields
            if ($request->has('nama_template')) {
                $template->nama_template = $request->nama_template;
            }

            if ($request->has('design_template')) {
                $template->design_template = $request->design_template;
            }

            if ($request->has('signature_config')) {
                $template->signature_config = $request->signature_config;
            }

            if ($request->has('footer_text')) {
                $template->footer_text = $request->footer_text;
            }

            // Upload background
            if ($request->hasFile('background')) {
                // Delete old file if exists
                if ($template->path_background) {
                    Storage::disk('public')->delete($template->path_background);
                }

                $path = $request->file('background')->store('templates/backgrounds', 'public');
                $template->path_background = $path;
            }

            // Upload logo BPSDM
            if ($request->hasFile('logo_bpsdm')) {
                // Delete old file if exists
                if ($template->logo_bpsdm_path) {
                    Storage::disk('public')->delete($template->logo_bpsdm_path);
                }

                $path = $request->file('logo_bpsdm')->store('templates/logos', 'public');
                $template->logo_bpsdm_path = $path;
            }

            // Upload logo Pemda
            if ($request->hasFile('logo_pemda')) {
                // Delete old file if exists
                if ($template->logo_pemda_path) {
                    Storage::disk('public')->delete($template->logo_pemda_path);
                }

                $path = $request->file('logo_pemda')->store('templates/logos', 'public');
                $template->logo_pemda_path = $path;
            }

            $template->save();

            return redirect()->route('template.sertifikat.show', $template->id)
                ->with('success', 'Template sertifikat berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Template sertifikat tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error memperbarui template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $template = TemplateSertifikat::findOrFail($id);

            // Check if template is being used
            if ($template->sertifikats()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus template karena masih digunakan oleh sertifikat');
            }

            // Delete all files
            if ($template->path_background) {
                Storage::disk('public')->delete($template->path_background);
            }

            if ($template->logo_bpsdm_path) {
                Storage::disk('public')->delete($template->logo_bpsdm_path);
            }

            if ($template->logo_pemda_path) {
                Storage::disk('public')->delete($template->logo_pemda_path);
            }

            $template->delete();

            return redirect()->route('template.sertifikat.index')
                ->with('success', 'Template sertifikat berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Template sertifikat tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Error menghapus template: ' . $e->getMessage());
        }
    }

    /**
     * Generate preview for template.
     * 
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function preview($id)
    {
        try {
            $template = TemplateSertifikat::findOrFail($id);

            // Sample data for preview
            $sampleData = [
                'nama_institusi' => 'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA',
                'nama_acara' => 'PELATIHAN PROFESIONAL 2023',
                'nomor_sertifikat' => 'NO/SERT/BPSDM/2023/001',
                'nama_peserta' => 'Dr. BUDI SANTOSO, S.Pd., M.Kom.',
                'detail_peserta' => 'NIP. 198012252005011002',
                'peringkat_penghargaan' => 'PESERTA TERBAIK',
                'detail_penghargaan' => 'KATEGORI PENGEMBANGAN APLIKASI',
                'nama_jabatan1' => 'Kepala BPSDM',
                'nama_penandatangan1' => 'Dr. Ahmad Wijaya, M.Si.',
                'nip_penandatangan1' => 'NIP. 196705061991031001',
                'tempat_tanggal_terbit' => 'Jakarta, 10 Desember 2023',
                'nama_jabatan2' => 'Ketua Panitia',
                'nama_penandatangan2' => 'Ir. Siti Rahma, M.M.',
                'nip_penandatangan2' => 'NIP. 197503112006041009',
                'tanggal_terbit' => '10 Desember 2023',
                'nama_kursus' => 'Pelatihan Pengembangan Aplikasi',
                'nama_penandatangan' => 'Dr. Ahmad Wijaya, M.Si.',
                'jabatan_penandatangan' => 'Kepala BPSDM'
            ];

            // Replace placeholders with sample data
            $html = $template->design_template;
            foreach ($sampleData as $key => $value) {
                $html = str_replace("{{" . $key . "}}", $value, $html);
            }

            // Jika ada gambar tanda tangan dan logo, tambahkan URL-nya ke HTML
            $html = str_replace(
                "{{url_tanda_tangan1}}",
                $template->signature_path_1 ? Storage::url($template->signature_path_1) : asset('images/placeholder-signature.png'),
                $html
            );

            $html = str_replace(
                "{{url_tanda_tangan2}}",
                $template->signature_path_2 ? Storage::url($template->signature_path_2) : asset('images/placeholder-signature.png'),
                $html
            );

            $html = str_replace(
                "{{url_logo}}",
                $template->logo_bpsdm_path ? Storage::url($template->logo_bpsdm_path) : asset('images/placeholder-logo.png'),
                $html
            );

            // Tampilkan halaman view preview biasa
            return view('sertifikat::template.preview', compact('template', 'html'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Template sertifikat tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Error menampilkan preview: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF preview for template.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function previewPdf($id)
    {
        try {
            $template = TemplateSertifikat::findOrFail($id);

            // Sample data for preview
            $sampleData = [
                'nama_institusi' => 'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA',
                'nama_acara' => 'PELATIHAN PROFESIONAL 2023',
                'nomor_sertifikat' => 'NO/SERT/BPSDM/2023/001',
                'nama_peserta' => 'Dr. BUDI SANTOSO, S.Pd., M.Kom.',
                'detail_peserta' => 'NIP. 198012252005011002',
                'peringkat_penghargaan' => 'PESERTA TERBAIK',
                'detail_penghargaan' => 'KATEGORI PENGEMBANGAN APLIKASI',
                'nama_jabatan1' => 'Kepala BPSDM',
                'nama_penandatangan1' => 'Dr. Ahmad Wijaya, M.Si.',
                'nip_penandatangan1' => 'NIP. 196705061991031001',
                'tempat_tanggal_terbit' => 'Jakarta, 10 Desember 2023',
                'nama_jabatan2' => 'Ketua Panitia',
                'nama_penandatangan2' => 'Ir. Siti Rahma, M.M.',
                'nip_penandatangan2' => 'NIP. 197503112006041009',
                'tanggal_terbit' => '10 Desember 2023',
                'nama_kursus' => 'Pelatihan Pengembangan Aplikasi',
                'nama_penandatangan' => 'Dr. Ahmad Wijaya, M.Si.',
                'jabatan_penandatangan' => 'Kepala BPSDM'
            ];

            // Replace placeholders with sample data
            $html = $template->design_template;
            foreach ($sampleData as $key => $value) {
                $html = str_replace("{{" . $key . "}}", $value, $html);
            }

            // Jika ada gambar tanda tangan dan logo, tambahkan URL-nya ke HTML
            // Convert URL ke absolute path untuk PDF
            $baseUrl = url('/');

            $html = str_replace(
                "{{url_tanda_tangan1}}",
                $template->signature_path_1 ? $baseUrl . Storage::url($template->signature_path_1) : $baseUrl . '/images/placeholder-signature.png',
                $html
            );

            $html = str_replace(
                "{{url_tanda_tangan2}}",
                $template->signature_path_2 ? $baseUrl . Storage::url($template->signature_path_2) : $baseUrl . '/images/placeholder-signature.png',
                $html
            );

            $html = str_replace(
                "{{url_logo}}",
                $template->logo_bpsdm_path ? $baseUrl . Storage::url($template->logo_bpsdm_path) : $baseUrl . '/images/placeholder-logo.png',
                $html
            );

            // Add background image if exists
            $backgroundHtml = '';
            if ($template->path_background) {
                $backgroundUrl = $baseUrl . Storage::url($template->path_background);
                $backgroundHtml = "<style>body { background-image: url('{$backgroundUrl}'); background-size: cover; background-position: center; }</style>";
            }

            // Create complete HTML for PDF
            $pdfHtml = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Preview Sertifikat: {$template->nama_template}</title>
                {$backgroundHtml}
                <style>
                    @page {
                        size: 297mm 210mm landscape;
                        margin: 0;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        width: 297mm;
                        height: 210mm;
                    }
                    .content {
                        position: relative;
                        width: 100%;
                        height: 100%;
                    }
                </style>
            </head>
            <body>
                <div class='content'>
                    {$html}
                </div>
            </body>
            </html>";

            // Generate PDF with DomPDF
            $pdf = FacadePdf::loadHTML($pdfHtml);
            $pdf->setPaper('a4', 'landscape');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true, // Allow loading remote images
                'isHtml5ParserEnabled' => true,
            ]);

            // Stream PDF to browser for preview with filename
            return $pdf->stream("preview-{$template->nama_template}.pdf");
        } catch (ModelNotFoundException $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Template sertifikat tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Error menampilkan preview PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate and download PDF of the template.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf($id)
    {
        try {
            $template = TemplateSertifikat::findOrFail($id);

            // Use the same sample data as in preview
            $sampleData = [
                'nama_institusi' => 'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA',
                'nama_acara' => 'PELATIHAN PROFESIONAL 2023',
                'nomor_sertifikat' => 'NO/SERT/BPSDM/2023/001',
                'nama_peserta' => 'Dr. BUDI SANTOSO, S.Pd., M.Kom.',
                'detail_peserta' => 'NIP. 198012252005011002',
                'peringkat_penghargaan' => 'PESERTA TERBAIK',
                'detail_penghargaan' => 'KATEGORI PENGEMBANGAN APLIKASI',
                'nama_jabatan1' => 'Kepala BPSDM',
                'nama_penandatangan1' => 'Dr. Ahmad Wijaya, M.Si.',
                'nip_penandatangan1' => 'NIP. 196705061991031001',
                'tempat_tanggal_terbit' => 'Jakarta, 10 Desember 2023',
                'nama_jabatan2' => 'Ketua Panitia',
                'nama_penandatangan2' => 'Ir. Siti Rahma, M.M.',
                'nip_penandatangan2' => 'NIP. 197503112006041009',
                'tanggal_terbit' => '10 Desember 2023',
                'nama_kursus' => 'Pelatihan Pengembangan Aplikasi',
                'nama_penandatangan' => 'Dr. Ahmad Wijaya, M.Si.',
                'jabatan_penandatangan' => 'Kepala BPSDM'
            ];

            // Replace placeholders with sample data
            $html = $template->design_template;
            foreach ($sampleData as $key => $value) {
                $html = str_replace("{{" . $key . "}}", $value, $html);
            }

            // Add image URLs with absolute paths
            $baseUrl = url('/');

            $html = str_replace(
                "{{url_tanda_tangan1}}",
                $template->signature_path_1 ? $baseUrl . Storage::url($template->signature_path_1) : $baseUrl . '/images/placeholder-signature.png',
                $html
            );

            $html = str_replace(
                "{{url_tanda_tangan2}}",
                $template->signature_path_2 ? $baseUrl . Storage::url($template->signature_path_2) : $baseUrl . '/images/placeholder-signature.png',
                $html
            );

            $html = str_replace(
                "{{url_logo}}",
                $template->logo_bpsdm_path ? $baseUrl . Storage::url($template->logo_bpsdm_path) : $baseUrl . '/images/placeholder-logo.png',
                $html
            );

            // Add background image if exists
            $backgroundHtml = '';
            if ($template->path_background) {
                $backgroundUrl = $baseUrl . Storage::url($template->path_background);
                $backgroundHtml = "<style>body { background-image: url('{$backgroundUrl}'); background-size: cover; background-position: center; }</style>";
            }

            // Create complete HTML for PDF
            $pdfHtml = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>{$template->nama_template}</title>
                {$backgroundHtml}
                <style>
                    @page {
                        size: 297mm 210mm landscape;
                        margin: 0;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        width: 297mm;
                        height: 210mm;
                    }
                    .content {
                        position: relative;
                        width: 100%;
                        height: 100%;
                    }
                </style>
            </head>
            <body>
                <div class='content'>
                    {$html}
                </div>
            </body>
            </html>";

            // Generate PDF with DomPDF
            $pdf = FacadePdf::loadHTML($pdfHtml);
            $pdf->setPaper('a4', 'landscape');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true, // Allow loading remote images
                'isHtml5ParserEnabled' => true,
            ]);

            // Download the PDF
            return $pdf->download("sertifikat-{$template->nama_template}.pdf");
        } catch (ModelNotFoundException $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Template sertifikat tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('template.sertifikat.index')
                ->with('error', 'Error mengunduh PDF: ' . $e->getMessage());
        }
    }
}
