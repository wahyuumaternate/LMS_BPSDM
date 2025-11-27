<?php

namespace Modules\Quiz\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Modules\Quiz\Entities\QuizResult;
use Illuminate\Support\Facades\DB;

class QuizResultsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $quizId;
    protected $pesertaId;
    protected $isPassed;
    protected $quizTitle;

    public function __construct($quizId = null, $pesertaId = null, $isPassed = null)
    {
        $this->quizId = $quizId;
        $this->pesertaId = $pesertaId;
        $this->isPassed = $isPassed;
        
        // Get quiz title for filename
        if ($quizId) {
            $quiz = \Modules\Quiz\Entities\Quiz::find($quizId);
            $this->quizTitle = $quiz ? $quiz->judul_quiz : 'Semua Quiz';
        } else {
            $this->quizTitle = 'Semua Quiz';
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Subquery to get the best attempt for each peserta per quiz
        $bestAttempts = QuizResult::select('peserta_id', 'quiz_id', DB::raw('MAX(nilai) as max_nilai'))
            ->groupBy('peserta_id', 'quiz_id');

        // Apply filters
        if ($this->quizId) {
            $bestAttempts->where('quiz_id', $this->quizId);
        }

        if ($this->pesertaId) {
            $bestAttempts->where('peserta_id', $this->pesertaId);
        }

        // Join to get the full record of the best attempt
        $query = QuizResult::with(['quiz.modul', 'peserta', 'peserta.opd'])
            ->joinSub($bestAttempts, 'best_attempts', function ($join) {
                $join->on('quiz_results.peserta_id', '=', 'best_attempts.peserta_id')
                    ->on('quiz_results.quiz_id', '=', 'best_attempts.quiz_id')
                    ->on('quiz_results.nilai', '=', 'best_attempts.max_nilai');
            });

        // Filter by is_passed
        if ($this->isPassed !== null && $this->isPassed !== '') {
            $query->where('quiz_results.is_passed', $this->isPassed);
        }

        return $query->orderBy('quiz_results.created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Quiz',
            'Modul',
            'Peserta',
            'NIP',
            'OPD',
            'Email',
            'Nilai Terbaik',
            'Total Attempts',
            'Jawaban Benar',
            'Jawaban Salah',
            'Status',
            'Durasi (Menit)',
            'Tanggal Attempt Terbaik',
            'Waktu Mulai',
            'Waktu Selesai',
        ];
    }

    /**
     * @var QuizResult $result
     */
    public function map($result): array
    {
        static $no = 0;
        $no++;

        // Get total attempts for this peserta and quiz
        $totalAttempts = QuizResult::where('peserta_id', $result->peserta_id)
            ->where('quiz_id', $result->quiz_id)
            ->count();

        return [
            $no,
            $result->quiz->judul_quiz ?? 'N/A',
            $result->quiz->modul->nama_modul ?? 'N/A',
            $result->peserta->nama_lengkap ?? 'N/A',
           "'" . ($result->peserta->nip ?? 'N/A'),
            $result->peserta->opd->nama_opd ?? 'N/A',
            $result->peserta->email ?? 'N/A',
            number_format($result->nilai, 2),
            $totalAttempts,
            $result->jumlah_benar,
            $result->jumlah_salah,
            $result->is_passed ? 'Lulus' : 'Tidak Lulus',
            $result->durasi_pengerjaan_menit,
            $result->created_at->format('d/m/Y H:i:s'),
            $result->waktu_mulai ? \Carbon\Carbon::parse($result->waktu_mulai)->format('d/m/Y H:i:s') : 'N/A',
            $result->waktu_selesai ? \Carbon\Carbon::parse($result->waktu_selesai)->format('d/m/Y H:i:s') : 'N/A',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:P1')->applyFromArray([
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

        // Get the highest row number
        $highestRow = $sheet->getHighestRow();

        // Apply borders to all data
        $sheet->getStyle('A1:P' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Center align specific columns
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H2:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I2:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J2:J' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K2:K' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L2:L' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M2:M' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Conditional formatting for status column (L)
        foreach ($sheet->getRowIterator(2) as $row) {
            $rowIndex = $row->getRowIndex();
            $statusCell = $sheet->getCell('L' . $rowIndex);
            
            if ($statusCell->getValue() == 'Lulus') {
                $sheet->getStyle('L' . $rowIndex)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '006100'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C6EFCE'],
                    ],
                ]);
            } else {
                $sheet->getStyle('L' . $rowIndex)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '9C0006'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC7CE'],
                    ],
                ]);
            }
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Hasil Quiz';
    }
}