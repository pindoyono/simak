<?php

namespace App\Filament\Resources\SchoolAssessmentResource\Pages;

use App\Filament\Resources\SchoolAssessmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolAssessments extends ListRecords
{
    protected static string $resource = SchoolAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Create action removed - assessments should only be created via Assessment Wizard
        ];
    }
}
