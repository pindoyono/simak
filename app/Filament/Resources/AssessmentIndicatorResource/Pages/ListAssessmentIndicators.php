<?php

namespace App\Filament\Resources\AssessmentIndicatorResource\Pages;

use App\Filament\Resources\AssessmentIndicatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentIndicators extends ListRecords
{
    protected static string $resource = AssessmentIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
