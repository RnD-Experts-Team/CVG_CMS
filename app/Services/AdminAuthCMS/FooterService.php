<?php

namespace App\Services\AdminAuthCMS;

use App\Models\FooterContact;
use App\Models\FooterSocialLink;

class FooterService
{
    // Get Footer Contact and Social Links
    public function getFooter()
    {
        $footerContact = FooterContact::first();
        if (! $footerContact) {
            $message = 'footer contact not found';
            $data = null;
            $code = 404;

            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
        $footerSocialLinks = FooterSocialLink::all();
        if (! $footerSocialLinks) {
            $message = 'footer social links not found';
            $data = null;
            $code = 404;

            return ['data' => $data, 'message' => $message, 'code' => $code];
        }

        $data = [
            'contact' => FooterContact::first(),
            'social_links' => FooterSocialLink::all(),
        ];

        return ['data' => $data, 'message' => 'Footer fetched successfully', 'code' => 200];
    }

    // Update Footer (Contact and Social Links)
    public function updateFooter($request)
    {
        // Update contact
        $footerContact = FooterContact::first();
        if (! $footerContact) {
            $message = 'footer contact not found';
            $data = null;
            $code = 404;

            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
        $footerContact->update($request->contact);

        // Update social links (replace all)
        FooterSocialLink::truncate(); // Remove all existing social links
        foreach ($request->social_links as $socialLink) {
            FooterSocialLink::create($socialLink);
        }

        $data = [
            'contact' => $footerContact,
            'social_links' => FooterSocialLink::all(),
        ];

        return ['data' => $data, 'message' => 'Footer updated successfully', 'code' => 200];
    }
}
