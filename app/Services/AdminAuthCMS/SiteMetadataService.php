<?php

namespace App\Services\AdminAuthCMS;

use App\Models\SiteMetadata;

class SiteMetadataService
{
    // Get the first site metadata (Singleton)
    public function getSiteMetadata()
    {
        $siteMetadata = SiteMetadata::first();
        if ($siteMetadata) {
            return ['data' => $siteMetadata, 'message' => 'Site Metadata fetched successfully', 'code' => 200];
        }

        return ['data' => null, 'message' => 'site metadata not found', 'code' => 404];

    }

    // Update the site metadata (Singleton)
    public function updateSiteMetadata($request)
    {
        $siteMetadata = SiteMetadata::first();
        if ($siteMetadata) {
            $siteMetadata->update($request->validated());

            return ['data' => $siteMetadata, 'message' => 'site metadata updated successfully', 'code' => 200];
        }

        return ['data' => null, 'message' => 'site metadata not found', 'code' => 404];
    }
}
