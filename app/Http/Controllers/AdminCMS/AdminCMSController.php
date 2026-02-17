<?php

namespace App\Http\Controllers\AdminCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCMS\FooterRequest;
use App\Http\Requests\AdminCMS\SiteMetadataRequest;
use App\Http\Responses\Response;
use App\Services\AdminAuthCMS\FooterService;
use App\Services\AdminAuthCMS\SiteMetadataService;
use Throwable;

class AdminCMSController extends Controller
{
    public $siteMetadataService;

    public $footerService;

    public function __construct(SiteMetadataService $siteMetadataService, FooterService $footerService)
    {
        $this->siteMetadataService = $siteMetadataService;
        $this->footerService = $footerService;
    }

    /*
    =================
    site metadata
    =================
    */

    // Get Site Metadata (Singleton)
    public function getSiteMetadata()
    {
        $data = [];
        try {
            $data = $this->siteMetadataService->getSiteMetadata();

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error Message', $th->getMessage());
        }
    }

    // Update Site Metadata (Singleton)
    public function updateSiteMetadata(SiteMetadataRequest $request)
    {
        $data = [];
        try {
            $data = $this->siteMetadataService->updateSiteMetadata($request);

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error Message', $th->getMessage());
        }
    }

    /*
    =================
    footer
    =================
    */

    // Get Footer (Contact and Social Links)
    public function getFooter()
    {
        $data = [];
        try {
            $data = $this->footerService->getFooter();

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching footer', $th->getMessage());
        }
    }

    // Update Footer (Contact and Social Links)
    public function updateFooter(FooterRequest $request)
    {
        try {
            $data = $this->footerService->updateFooter($request);

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating footer', $th->getMessage());
        }
    }
}
