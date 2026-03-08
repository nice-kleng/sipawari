<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Rating;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class AdminRatingTableWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    // View mode: 'recent' | 'flagged' | 'monitor'
    public string $viewMode = 'recent';

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('recent')
                ->label('Rating Terbaru')
                ->color($this->viewMode === 'recent' ? 'primary' : 'gray')
                ->action(fn() => $this->viewMode = 'recent'),

            \Filament\Actions\Action::make('flagged')
                ->label('Di-Flag')
                ->color($this->viewMode === 'flagged' ? 'danger' : 'gray')
                ->action(fn() => $this->viewMode = 'flagged'),

            \Filament\Actions\Action::make('monitor')
                ->label('Monitor Performa')
                ->color($this->viewMode === 'monitor' ? 'warning' : 'gray')
                ->action(fn() => $this->viewMode = 'monitor'),
        ];
    }

    public function table(Table $table): Table
    {
        return match ($this->viewMode) {
            'flagged' => $this->getFlaggedTable($table),
            'monitor' => $this->getMonitorTable($table),
            default   => $this->getRecentTable($table),
        };
    }

    private function getRecentTable(Table $table): Table
    {
        return $table
            ->heading('Rating Terbaru')
            ->query(
                Rating::query()
                    ->with(['employee.unit', 'employee.jabatan', 'rater'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')->dateTime('d M Y, H:i')->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')->searchable()
                    ->description(fn(Rating $r) => ($r->employee->unit?->name ?? '-') . ' | ' . ($r->employee->jabatan?->name ?? '-')),

                Tables\Columns\TextColumn::make('rater.full_name')
                    ->label('Penilai')->searchable(),

                Tables\Columns\TextColumn::make('overall_satisfaction')
                    ->label('Kepuasan')->badge()
                    ->formatStateUsing(fn($state) => str_repeat('⭐', (int) $state))
                    ->color(fn($state) => match ((int) $state) {
                        5 => 'success', 4 => 'info', 3 => 'warning', default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('average_score')
                    ->label('Rata-rata')->numeric(2)->sortable(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Status')->boolean()
                    ->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-clock')
                    ->trueColor('success')->falseColor('warning'),

                Tables\Columns\IconColumn::make('is_flagged')
                    ->label('Flag')->boolean()
                    ->trueIcon('heroicon-o-flag')->falseIcon('heroicon-o-minus')
                    ->trueColor('danger')->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(fn(Builder $query, array $data) => $query
                        ->when($data['created_from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['created_until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')->color('success')->requiresConfirmation()
                    ->action(fn(Rating $r) => $r->update(['is_approved' => true]))
                    ->visible(fn(Rating $r) => !$r->is_approved),

                Tables\Actions\Action::make('flag')
                    ->icon('heroicon-o-flag')->color('danger')
                    ->form([Textarea::make('flag_reason')->label('Alasan Flag')->required()])
                    ->action(fn(Rating $r, array $data) => $r->update([
                        'is_flagged' => true, 'flag_reason' => $data['flag_reason'],
                    ]))
                    ->visible(fn(Rating $r) => !$r->is_flagged),

                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Rating $r) => route('filament.admin.resources.ratings.view', $r))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function getFlaggedTable(Table $table): Table
    {
        return $table
            ->heading('Rating yang Di-Flag (Perlu Review)')
            ->query(Rating::query()->with(['employee.unit', 'rater'])->where('is_flagged', true)->latest())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')->dateTime('d M Y, H:i')->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->description(fn(Rating $r) => $r->employee->unit?->name ?? '-'),

                Tables\Columns\TextColumn::make('overall_satisfaction')
                    ->label('Rating')
                    ->formatStateUsing(fn($state) => str_repeat('⭐', (int) $state)),

                Tables\Columns\TextColumn::make('flag_reason')
                    ->label('Alasan Flag')->limit(50)
                    ->tooltip(fn(Tables\Columns\TextColumn $col) => strlen($col->getState()) > 50 ? $col->getState() : null),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Komentar')->limit(30)
                    ->tooltip(fn(Tables\Columns\TextColumn $col) => strlen($col->getState()) > 30 ? $col->getState() : null),

                Tables\Columns\IconColumn::make('is_approved')->label('Approved')->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('unflag')
                    ->label('Hapus Flag')->icon('heroicon-o-x-mark')->color('success')
                    ->requiresConfirmation()
                    ->action(fn(Rating $r) => $r->update(['is_flagged' => false, 'flag_reason' => null])),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')->icon('heroicon-o-check')->color('info')
                    ->requiresConfirmation()
                    ->action(fn(Rating $r) => $r->update(['is_approved' => true]))
                    ->visible(fn(Rating $r) => !$r->is_approved),

                Tables\Actions\DeleteAction::make()->label('Hapus')->requiresConfirmation(),
            ])
            ->emptyStateHeading('Tidak ada rating yang di-flag')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    private function getMonitorTable(Table $table): Table
    {
        return $table
            ->heading('Monitor Performa Karyawan')
            ->description('Karyawan dengan rating menurun atau di bawah standar')
            ->query(
                Employee::query()
                    ->where('is_active', true)
                    ->withCount(['ratings as approved_ratings_count' => fn($q) => $q->where('is_approved', true)])
                    ->withAvg(['ratings as avg_rating' => fn($q) => $q->where('is_approved', true)], 'overall_satisfaction')
                    ->withAvg(['ratings as avg_rating_this_month' => fn($q) => $q
                        ->where('is_approved', true)
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)], 'overall_satisfaction')
                    ->withAvg(['ratings as avg_rating_last_month' => fn($q) => $q
                        ->where('is_approved', true)
                        ->whereMonth('created_at', now()->subMonth()->month)
                        ->whereYear('created_at', now()->subMonth()->year)], 'overall_satisfaction')
                    ->having('approved_ratings_count', '>', 0)
                    ->orderBy('avg_rating', 'asc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label('Foto')->circular()
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=NA&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()
                    ->description(fn(Employee $e) => $e->employee_code ?? '-'),

                Tables\Columns\TextColumn::make('unit.name')->label('Unit')->searchable(),

                Tables\Columns\TextColumn::make('jabatan.name')->label('Jabatan'),

                Tables\Columns\TextColumn::make('avg_rating')->label('Rata-rata Total')->numeric(2)->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4.5 => 'success', $state >= 4.0 => 'info',
                        $state >= 3.5 => 'warning', default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('avg_rating_this_month')->label('Bulan Ini')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) : 'N/A')
                    ->color(fn($state) => match (true) {
                        $state >= 4.5 => 'success', $state >= 4.0 => 'info',
                        $state >= 3.5 => 'warning', $state === null => 'gray', default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('avg_rating_last_month')->label('Bulan Lalu')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) : 'N/A'),

                Tables\Columns\IconColumn::make('trend')->label('Tren')
                    ->state(fn(Employee $e) => match (true) {
                        !$e->avg_rating_this_month || !$e->avg_rating_last_month => 'neutral',
                        $e->avg_rating_this_month > $e->avg_rating_last_month    => 'up',
                        $e->avg_rating_this_month < $e->avg_rating_last_month    => 'down',
                        default => 'neutral',
                    })
                    ->icon(fn($state) => match ($state) {
                        'up' => 'heroicon-o-arrow-trending-up', 'down' => 'heroicon-o-arrow-trending-down', default => 'heroicon-o-minus',
                    })
                    ->color(fn($state) => match ($state) {
                        'up' => 'success', 'down' => 'danger', default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('approved_ratings_count')->label('Total Rating')->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_rating')
                    ->label('Rating < 3.5')
                    ->query(fn(Builder $q) => $q->having('avg_rating', '<', 3.5)),

                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')->relationship('unit', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Detail')->icon('heroicon-o-eye')
                    ->url(fn(Employee $e) => route('filament.admin.resources.employees.view', $e)),
            ]);
    }
}
