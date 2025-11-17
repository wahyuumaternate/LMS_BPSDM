<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Modules\Kursus\Entities\Kursus as EntitiesKursus;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PesertaKursusExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithColumnWidths,
    WithColumnFormatting
{
    protected $kursusId;

    public function __construct($kursusId)
    {
        $this->kursusId = $kursusId;
    }

    /**
     * Get data collection
     */
    public function collection()
    {
        return EntitiesKursus::with(['peserta.opd'])
            ->findOrFail($this->kursusId)
            ->peserta;
    }

    /**
     * Define headers
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'NIP',
            'Email',
            'Jabatan',
            'OPD',
            'Status',
            'Tanggal Daftar',
            'Tanggal Disetujui',
            'Tanggal Selesai',
            'Nilai Akhir',
            'Predikat',
            'Alasan Ditolak',
        ];
    }

    /**
     * Map data to columns
     */
    public function map($peserta): array
    {
        static $no = 0;
        $no++;

        // Format NIP as string with leading apostrophe to preserve leading zeros
        $nip = $peserta->nip ? "'" . $peserta->nip : '-';

        return [
            $no,
            $peserta->nama_lengkap,
            $nip, // NIP sebagai string dengan apostrophe
            $peserta->email,
            $peserta->jabatan ?? '-',
            $peserta->opd->nama_opd ?? '-',
            ucfirst($peserta->pivot->status),
            $peserta->pivot->tanggal_daftar ?
                \Carbon\Carbon::parse($peserta->pivot->tanggal_daftar)->format('d/m/Y') : '-',
            $peserta->pivot->tanggal_disetujui ?
                \Carbon\Carbon::parse($peserta->pivot->tanggal_disetujui)->format('d/m/Y H:i') : '-',
            $peserta->pivot->tanggal_selesai ?
                \Carbon\Carbon::parse($peserta->pivot->tanggal_selesai)->format('d/m/Y H:i') : '-',
            $peserta->pivot->nilai_akhir ?
                number_format($peserta->pivot->nilai_akhir, 2) : '-',
            $peserta->pivot->predikat ?
                ucwords(str_replace('_', ' ', $peserta->pivot->predikat)) : '-',
            $peserta->pivot->alasan_ditolak ?? '-',
        ];
    }

    /**
     * Column formatting to ensure NIP is treated as text
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // NIP column as text
            'D' => NumberFormat::FORMAT_TEXT, // Email column as text
        ];
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Data rows styling
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A2:M{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        // Force NIP column to be text format
        $sheet->getStyle("C2:C{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Auto-height for rows
        foreach (range(1, $lastRow) as $row) {
            $sheet->getRowDimension($row)->setRowHeight(-1);
        }

        // Freeze first row
        $sheet->freezePane('A2');

        return [];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 25,  // Nama
            'C' => 20,  // NIP (increased width for better visibility)
            'D' => 25,  // Email
            'E' => 20,  // Jabatan
            'F' => 30,  // OPD
            'G' => 12,  // Status
            'H' => 15,  // Tanggal Daftar
            'I' => 18,  // Tanggal Disetujui
            'J' => 18,  // Tanggal Selesai
            'K' => 12,  // Nilai
            'L' => 15,  // Predikat
            'M' => 40,  // Alasan Ditolak
        ];
    }

    /**
     * Set sheet title
     */
    public function title(): string
    {
        return 'Daftar Peserta';
    }
}
