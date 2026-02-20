<?php

namespace App\Services\AdminAuthCMS;

use App\Models\ContactSubmission;

class ContactSubmissionService
{
    /*
    |--------------------------------------------------------------------------
    | GET PAGINATED
    |--------------------------------------------------------------------------
    */
    public function getAllSubmissions()
    {
        $submissions = ContactSubmission::latest()->paginate(10);

        return [
            'data' => $submissions,
            'message' => 'Contact submissions retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GET BY ID
    |--------------------------------------------------------------------------
    */
    public function getSubmissionById($id)
    {
        $submission = ContactSubmission::find($id);

        if (! $submission) {
            return [
                'data' => null,
                'message' => 'Contact submission not found',
                'code' => 404,
            ];
        }

        return [
            'data' => $submission,
            'message' => 'Contact submission retrieved successfully',
            'code' => 200,
        ];
    }
}
