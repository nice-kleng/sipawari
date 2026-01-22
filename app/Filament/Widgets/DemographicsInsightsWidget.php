<?php

namespace App\Filament\Widgets;

use App\Models\Rater;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DemographicsInsightsWidget extends ChartWidget
{
    protected static ?string $heading = 'Demografi Penilai';
    protected static ?int $sort = 11;

    public ?string $filter = 'gender';

    protected function getData(): array
    {
        if ($this->filter === 'gender') {
            return $this->getGenderData();
        } elseif ($this->filter === 'relationship') {
            return $this->getRelationshipData();
        }

        return $this->getGenderData();
    }

    protected function getGenderData(): array
    {
        $genderData = Rater::select('gender', DB::raw('count(*) as count'))
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($genderData as $item) {
            $labels[] = $item->gender === 'L' ? 'Laki-laki' : ($item->gender === 'P' ? 'Perempuan' : 'Lainnya');
            $data[] = $item->count;
            $colors[] = $item->gender === 'L'
                ? 'rgba(59, 130, 246, 0.8)'
                : 'rgba(236, 72, 153, 0.8)';
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getRelationshipData(): array
    {
        $relationshipData = Rater::select('relationship_to_patient', DB::raw('count(*) as count'))
            ->whereNotNull('relationship_to_patient')
            ->groupBy('relationship_to_patient')
            ->orderBy('count', 'desc')
            ->limit(8)
            ->get();

        $labels = [];
        $data = [];

        foreach ($relationshipData as $item) {
            $labels[] = ucfirst($item->relationship_to_patient);
            $data[] = $item->count;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getFilters(): ?array
    {
        return [
            'gender' => 'Gender',
            'relationship' => 'Hubungan dengan Pasien',
        ];
    }
}
