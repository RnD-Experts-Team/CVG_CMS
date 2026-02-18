<?php

namespace App\Http\Controllers\AdminCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCMS\AboutSectionRequest;
use App\Http\Requests\AdminCMS\ContactSectionRequest;
use App\Http\Requests\AdminCMS\FooterRequest;
use App\Http\Requests\AdminCMS\HeroRequest;
use App\Http\Requests\AdminCMS\ProcessSectionRequest;
use App\Http\Requests\AdminCMS\ProjectsSectionRequest;
use App\Http\Requests\AdminCMS\ServicesSectionRequest;
use App\Http\Requests\AdminCMS\SiteMetadataRequest;
use App\Http\Requests\AdminCMS\ValuesSectionRequest;
use App\Http\Responses\Response;
use App\Services\AdminAuthCMS\AboutSectionService;
use App\Services\AdminAuthCMS\ContactSectionService;
use App\Services\AdminAuthCMS\ContactSubmissionService;
use App\Services\AdminAuthCMS\FooterService;
use App\Services\AdminAuthCMS\HeroService;
use App\Services\AdminAuthCMS\ProcessSectionService;
use App\Services\AdminAuthCMS\ProjectsSectionService;
use App\Services\AdminAuthCMS\ServicesSectionService;
use App\Services\AdminAuthCMS\SiteMetadataService;
use App\Services\AdminAuthCMS\ValuesSectionService;
use Throwable;

class AdminCMSController extends Controller
{
    public $siteMetadataService;

    public $footerService;

    public $heroService;

    public $projectsSectionService;

    public $servicesSectionService;

    public $valuesSectionService;

    public $processSectionService;

    public $aboutSectionService;

    public $contactSectionService;

    public $contactSubmissionService;

    public function __construct(SiteMetadataService $siteMetadataService, FooterService $footerService, HeroService $heroService, ProjectsSectionService $projectsSectionService, ServicesSectionService $servicesSectionService, ValuesSectionService $valuesSectionService, ProcessSectionService $processSectionService, AboutSectionService $aboutSectionService, ContactSectionService $contactSectionService, ContactSubmissionService $contactSubmissionService)
    {
        $this->siteMetadataService = $siteMetadataService;
        $this->footerService = $footerService;
        $this->heroService = $heroService;
        $this->projectsSectionService = $projectsSectionService;
        $this->servicesSectionService = $servicesSectionService;
        $this->valuesSectionService = $valuesSectionService;
        $this->processSectionService = $processSectionService;
        $this->aboutSectionService = $aboutSectionService;
        $this->contactSectionService = $contactSectionService;
        $this->contactSubmissionService = $contactSubmissionService;
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

    /*
    =================
    hero section
    =================
    */

    // Get Hero Section with media
    public function getHero()
    {
        try {
            $data = $this->heroService->getHero();

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching hero section', $th->getMessage());
        }
    }

    // Update Hero Section with media
    public function updateHero(HeroRequest $request)
    {
        try {
            $data = $this->heroService->updateHero($request);

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating hero section', $th->getMessage());
        }
    }

    /*
    =================
    projects section
    =================
    */

    // Get Projects Section (Singleton)
    public function getProjectsSection()
    {
        try {
            $data = $this->projectsSectionService->getProjectsSection();

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching projects section', $th->getMessage());
        }
    }

    // Update Projects Section (Singleton)
    public function updateProjectsSection(ProjectsSectionRequest $request)
    {
        try {
            $data = $this->projectsSectionService->updateProjectsSection($request);

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating projects section', $th->getMessage());
        }
    }

    /*
    =================
    services section
    =================
    */

    public function getServicesSection()
    {
        try {
            $data = $this->servicesSectionService->getServicesSection();
            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching services section', $th->getMessage(), 500);
        }
    }

    public function updateServicesSection(ServicesSectionRequest $request)
    {
        try {
            $data = $this->servicesSectionService->updateServicesSection($request);
            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating services section', $th->getMessage(), 500);
        }
    }

    /*
    =================
    Values Section
    =================
    */

    public function getValuesSection()
    {
        try {
            $data = $this->valuesSectionService->getValuesSection();
            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching values section', $th->getMessage(), 500);
        }
    }

    public function updateValuesSection(ValuesSectionRequest $request)
    {
        try {
            $data = $this->valuesSectionService->updateValuesSection($request);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating values section', $th->getMessage(), 500);
        }
    }

    /*
    =================
    process Section
    =================
    */

    public function getProcessSection()
    {
        try {
            $data = $this->processSectionService->getProcessSection();

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching process section', $th->getMessage(), 500);
        }
    }

    public function updateProcessSection(ProcessSectionRequest $request)
    {
        try {
            $data = $this->processSectionService->updateProcessSection($request);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating process section', $th->getMessage(), 500);
        }
    }
    /*
    =================
    About Section
    =================
    */

    public function getAboutSection()
    {
        try {
            $data = $this->aboutSectionService->getAboutSection();
            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching about section', $th->getMessage(), 500);
        }
    }

    public function updateAboutSection(AboutSectionRequest $request)
    {
        try {
            $data = $this->aboutSectionService->updateAboutSection($request);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating about section', $th->getMessage(), 500);
        }
    }

    /*
    =================
    contact Section
    =================
    */

    public function getContactSection()
    {
        try {
            $data = $this->contactSectionService->getContactSection();

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching contact section', $th->getMessage(), 500);
        }
    }

    public function updateContactSection(ContactSectionRequest $request)
    {
        try {
            $data = $this->contactSectionService->updateContactSection($request);
            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating contact section', $th->getMessage(), 500);
        }
    }

    /*
    =================
    contact Submitions
    =================
    */

    public function getAll()
    {
        try {
            $data = $this->contactSubmissionService->getAllSubmissions();

            return Response::paginate(
                $data['data'],
                $data['message'],
                $data['code']
            );

        } catch (Throwable $th) {
            return Response::Error('Error fetching contact submissions', $th->getMessage(), 500);
        }
    }

    public function getById($id)
    {
        try {
            $data = $this->contactSubmissionService->getSubmissionById($id);

            if ($data['code'] === 404) {
                return Response::Error($data['message'], null, 404);
            }

            return Response::Success(
                $data['data'],
                $data['message'],
                $data['code']
            );

        } catch (Throwable $th) {
            return Response::Error('Error fetching contact submission', $th->getMessage(), 500);
        }
    }
}
