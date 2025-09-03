<?php

namespace App\Filament\Resources\AssessmentReviewResource\Pages;

use App\Filament\Resources\AssessmentReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentReviews extends ListRecords
{
    protected static string $resource = AssessmentReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
