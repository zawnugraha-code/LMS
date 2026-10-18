<?php

namespace App\Services\Admin;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class CsvImporterService
{
    /**
     * Parse dan validasi file CSV yang diunggah.
     *
     * @param UploadedFile|string $file File yang diunggah atau path absolut file CSV
     * @param array<string> $requiredHeaders Header kolom yang wajib ada (lowercase)
     * @param callable|null $rowValidator Callback verifikasi baris: fn(array $row, int $rowNumber): ?string
     * @return array{
     *     valid: array<int, array<string, mixed>>,
     *     errors: array<int, array{row: int, data: array<string, mixed>, reason: string}>,
     *     total_rows: int,
     *     valid_count: int,
     *     error_count: int
     * }
     */
    public function parseAndValidate(
        UploadedFile|string $file,
        array $requiredHeaders = [],
        ?callable $rowValidator = null
    ): array {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new InvalidArgumentException('File CSV tidak ditemukan atau tidak dapat dibaca.');
        }

        $maxSizeKb = config('arunika.import.max_file_size_kb', 2048);
        if (filesize($filePath) > ($maxSizeKb * 1024)) {
            throw new InvalidArgumentException("Ukuran file melebihi batas maksimum ({$maxSizeKb} KB).");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('Gagal membuka stream file CSV.');
        }

        // Tangani BOM UTF-8 jika ada
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Baca header
        $rawHeader = fgetcsv($handle, 0, ',');
        if ($rawHeader === false || empty($rawHeader)) {
            fclose($handle);
            throw new InvalidArgumentException('File CSV kosong atau tidak memiliki baris header.');
        }

        // Normalisasi header (lowercase, trim, hilangkan spasi ganda)
        $headers = array_map(function ($h) {
            return strtolower(trim((string) $h));
        }, $rawHeader);

        // Validasi keberadaan kolom wajib
        $missingHeaders = array_diff($requiredHeaders, $headers);
        if (!empty($missingHeaders)) {
            fclose($handle);
            throw new InvalidArgumentException(
                'Kolom wajib tidak lengkap: ' . implode(', ', $missingHeaders)
            );
        }

        $maxRows = config('arunika.import.max_rows', 1000);
        $validRows = [];
        $errorRows = [];
        $rowNumber = 1; // Baris 1 adalah header

        while (($rawRow = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;

            if ($rowNumber - 1 > $maxRows) {
                $errorRows[] = [
                    'row' => $rowNumber,
                    'data' => [],
                    'reason' => "Jumlah baris melebihi batas maksimum ({$maxRows} baris).",
                ];
                break;
            }

            // Lewati baris kosong
            if (empty(array_filter($rawRow, fn($val) => trim((string) $val) !== ''))) {
                continue;
            }

            // Petakan ke header
            $rowData = [];
            foreach ($headers as $index => $headerKey) {
                $val = $rawRow[$index] ?? '';
                // Sanitasi potensi formula CSV injection
                $rowData[$headerKey] = self::sanitizeCellValue($val);
            }

            // Jalankan custom validator jika disediakan
            $validationError = null;
            if ($rowValidator !== null) {
                $validationError = $rowValidator($rowData, $rowNumber);
            }

            if ($validationError !== null) {
                $errorRows[] = [
                    'row' => $rowNumber,
                    'data' => $rowData,
                    'reason' => $validationError,
                ];
            } else {
                $validRows[] = $rowData;
            }
        }

        fclose($handle);

        return [
            'valid' => $validRows,
            'errors' => $errorRows,
            'total_rows' => count($validRows) + count($errorRows),
            'valid_count' => count($validRows),
            'error_count' => count($errorRows),
        ];
    }

    /**
     * Sanitasi nilai sel untuk mencegah CSV Formula Injection (=, +, -, @, tab, newline).
     */
    public static function sanitizeCellValue(mixed $value): string
    {
        $str = trim((string) $value);

        if ($str === '') {
            return '';
        }

        $firstChar = $str[0];
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
            // Jika diawali karakter formula, beri prefix tanda kutip tunggal aman
            return "'" . $str;
        }

        return $str;
    }
}
