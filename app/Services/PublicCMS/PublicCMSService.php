<?php

namespace App\Services\PublicCMS;

use App\Models\AboutSection;
use App\Models\Category;
use App\Models\ContactSection;
use App\Models\ContactSubmission;
use App\Models\FooterContact;
use App\Models\FooterSocialLink;
use App\Models\HeroSection;
use App\Models\ProcessSection;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServicesSection;
use App\Models\SiteMetadata;
use App\Models\ValuesSection;

class PublicCMSService
{
    public function getHomeData()
    {
        return [
            // Site Metadata with all media info (logo and favicon)
            'site_metadata' => SiteMetadata::with(['logo', 'favicon'])->first() ?? [
                'id' => null,
                'name' => '',
                'description' => '',
                'keywords' => '',
                'logo' => [
                    'id' => null,
                    'path' => '',
                    'url' => '',
                    'alt_text' => '',
                    'title' => '',
                ],
                'favicon' => [
                    'id' => null,
                    'path' => '',
                    'url' => '',
                    'alt_text' => '',
                    'title' => '',
                ],
            ],

            // Footer with all contact and social link data
            'footer' => [
                'contact' => FooterContact::first() ?? [
                    'phone' => '',
                    'whatsapp' => '',
                    'email' => '',
                    'address' => '',
                ],
                'social_links' => FooterSocialLink::all() ?? [],
            ],

            // Hero Section with media details
            'hero' => HeroSection::with('media')->first() ?? [
                'title' => '',
                'subtitle' => '',
                'button' => [
                    'text' => '',
                    'link' => '',
                ],
                'media' => [
                    'id' => null,
                    'path' => '',
                    'url' => '',
                    'alt_text' => '',
                    'title' => '',
                ],
            ],

            // Projects Section (map data for each project)
            'projects_section' => Project::with('category')->get()->map(function ($project) {
                return [
                    'title' => $project->title,
                    'description' => $project->description,
                    'category' => $project->category,
                    'featured' => $project->featured,
                ];
            }),

            // Services Section with image data (all media info)
            'services_section' => ServicesSection::with('image')->first() ?? [
                'title' => '',
                'description' => '',
                'image' => [
                    'id' => null,
                    'path' => '',
                    'url' => '',
                    'alt_text' => '',
                    'title' => '',
                ],
                'button_text' => '',
            ],

            // Values Section with all values included
            'values_section' => ValuesSection::with('values')->first() ?? [
                'id' => null,
                'title' => '',
                'values' => [],
            ],

            // Process Section with image data (all media info)
            'process_section' => ProcessSection::with('image')->first() ?? [
                'title' => '',
                'image' => [
                    'id' => null,
                    'path' => '',
                    'url' => '',
                    'alt_text' => '',
                    'title' => '',
                ],
            ],

            // About Section with image data (all media info)
            'about_section' => AboutSection::with('image')->first() ?? [
                'title' => '',
                'description' => '',
                'image' => [
                    'id' => null,
                    'path' => '',
                    'url' => '',
                    'alt_text' => '',
                    'title' => '',
                ],
            ],

            // Contact Section data
            'contact_section' => ContactSection::first() ?? [
                'title' => '',
                'subtitle' => '',
            ],
        ];
    }

    // Method to get all categories
    public function getCategories()
    {
        return Category::all();
    }

    // Method to get paginated projects
    public function getProjects()
    {
        return Project::paginate(5);
    }

    // Method to get projects by category slug
    public function getProjectsByCategory($slug)
    {
        $projects = Project::whereHas('category', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->get();
        if (! $projects || $projects->isEmpty()) {
            $message = 'No projects found for category: '.$slug.'. Please check the category slug and try again.';
            $code = 404;

            return ['data' => null, 'message' => $message, 'code' => $code];
        }

        return ['data' => $projects, 'message' => 'Projects fetched successfully', 'code' => 200];
    }

    // Method to get a single project by slug
    public function getProjectBySlug($slug)
    {
        $projects = Project::with(['images', 'category'])
            ->where('slug', $slug)
            ->first();
        if (! $projects) {
            $message = 'No projects found for project slug: '.$slug.'. Please check the project slug and try again.';
            $code = 404;

            return ['data' => null, 'message' => $message, 'code' => $code];
        }
        $projectData = [
            'title' => $projects->title,
            'description' => $projects->description,
            'content' => $projects->content,
            'category' => $projects->category, // Return the full category relationship
            'featured' => $projects->featured,
            'published_at' => $projects->published_at,
            'images' => $projects->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'media' => $image->media,  // Return full media details
                    'previous_image_id' => $image->previous_image_id,  // Include previous image relation
                    'sort_order' => $image->sort_order,
                    'caption' => $image->caption,
                ];
            }),
        ];

        return ['data' => $projectData, 'message' => 'Projects fetched successfully', 'code' => 200];
    }

    // Method to get paginated services
    public function getServices()
    {
        $section = ServicesSection::first() ?? [
            'title' => '',
            'description' => '',
            'image' => [],
            'button_text' => '',
        ];

        // Get the paginated list of services
        $services = Service::paginate(5);

        // Return the response with the required structure
        return [
            'success' => true,
            'data' => [
                'section' => $section,
                'services' => $services,
            ],
            'message' => 'Services fetched successfully',
            'code' => 200,
        ];
    }

    // Method to handle contact form submission
    public function submitContactForm($request)
    {
        // Here you can store the data to a ContactSubmission model or send an email
        $ipAddress = $request->ip(); // This will automatically capture the client's IP address
        $userAgent = $request->header('User-Agent'); // Capture the user agent

        // Create a new contact submission
        $contactSubmission = ContactSubmission::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'project_details' => $request->project_details,
            'ip_address' => $ipAddress ?? null,
            'user_agent' => $userAgent ?? null,
        ]);

        return ['data' => $contactSubmission, 'message' => 'Contact form submitted successfully'];
    }
}
