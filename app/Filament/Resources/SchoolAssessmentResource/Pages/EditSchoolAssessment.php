<?php

namespace App\Filament\Resources\SchoolAssessmentResource\Pages;

use App\Filament\Resources\SchoolAssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolAssessment extends EditRecord
{
    protected static string $resource = SchoolAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
