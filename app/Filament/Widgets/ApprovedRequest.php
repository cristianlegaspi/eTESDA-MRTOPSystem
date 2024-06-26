<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Request;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class ApprovedRequest extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan ='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(Request::query())
            ->query(Request::query()->where('RequestStatus', 'Approved')) 
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                if ($user->role === 'ADMIN') {
                    return;
                }
                $userId = $user->id;
                $query->where('user_id', $userId);
            })
            ->columns([

                Tables\Columns\TextColumn::make('qualification.qualification_name'),
                Tables\Columns\TextColumn::make('NameOfTrainer')
                ->label('Trainer'),
                Tables\Columns\TextColumn::make('targetStart'),
                Tables\Columns\TextColumn::make('targetEnd'),
                Tables\Columns\TextColumn::make('RequestStatus')
                ->badge()
                    ->label('Request Status')
                    ->color(fn (string $state): string => match ($state) {
                        'For Verification' => 'warning',
                        
                        'Approved' => 'success',
                   
                    }),
                Tables\Columns\TextColumn::make('TrainingStatus')
                ->label('Training Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Not Yet Started' => 'danger',
                    'Ongoing' => 'warning',
                    'Completed' => 'success',
                }),
                Tables\Columns\TextColumn::make('created_at')
                ->label('Date Requested')
                ->sortable(), 

                
               
            ])
            ->filters([
                SelectFilter::make('TrainingStatus')
                    ->label('Filter By Training Status')
                    ->preload()
                    ->options([
                        'Not Yet Started' => 'Not Yet Started',
                        'Ongoing' => 'Ongoing',
                        'Completed' => 'Completed',
                    ]),
            ]);
            

            
       
        
    }
    public static function canView(): bool
    {
        return auth()->user()->isEditor();
    }
}
