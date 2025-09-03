<?php

namespace App\Filament\Resources\AssessmentReviewResource\Pages;

use App\Filament\Resources\AssessmentReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentReview extends EditRecord
{
    protected static string $resource = AssessmentReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
