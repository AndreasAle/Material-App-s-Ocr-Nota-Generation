<?php

namespace App\Exports\Sheets\Concerns;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait HasProfessionalSheetStyling
{
    protected function titleStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'],
            ],
        ];
    }

    protected function subtitleLabelStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '334155'],
            ],
        ];
    }

    protected function headerStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
    }

    protected function totalLabelStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '334155'],
                ],
            ],
        ];
    }

    protected function totalValueStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '334155'],
                ],
            ],
        ];
    }

    protected function kpiLabelStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '334155'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8FAFC'],
            ],
        ];
    }

    protected function kpiValueStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EFF6FF'],
            ],
        ];
    }
}