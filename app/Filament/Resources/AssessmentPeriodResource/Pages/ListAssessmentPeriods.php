<?php

namespace App\Filament\Resources\AssessmentPeriodResource\Pages;

use App\Filament\Resources\AssessmentPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentPeriods extends ListRecords
{
    protected static string $resource = AssessmentPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
