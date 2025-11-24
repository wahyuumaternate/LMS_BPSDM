<?php

namespace Modules\Sertifikat\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sertifikat\Entities\Sertifikat;

class SertifikatVerificationController extends Controller
{
    /**
     * Verify sertifikat by nomor (public access)
     */
    public function verify($nomor)
    {
        $sertifikat = Sertifikat::with(['peserta', 'kursus'])
            ->where('nomor_sertifikat', $nomor)
            ->first();

        if (!$sertifikat) {
            return view('sertifikat::verify', [
                'isValid' => false,
                'message' => 'Sertifikat tidak ditemukan atau nomor sertifikat tidak valid.'
            ]);
        }

        if ($sertifikat->isRevoked()) {
            return view('sertifikat::verify', [
                'isValid' => false,
                'message' => 'Sertifikat telah dicabut.',
                'sertifikat' => $sertifikat
            ]);
        }

        return view('sertifikat::verify', [
            'isValid' => true,
            'sertifikat' => $sertifikat,
            'message' => 'Sertifikat valid dan terverifikasi.'
        ]);
    }

    /**
     * Verify sertifikat via search form
     */
    public function search(Request $request)
    {
        $request->validate([
            'nomor_sertifikat' => 'required|string'
        ]);

        return redirect()->route('sertifikat.verify', ['nomor' => $request->nomor_sertifikat]);
    }
}