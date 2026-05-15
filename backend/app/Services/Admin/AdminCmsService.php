<?php

namespace App\Services\Admin;

use App\Models\CmsBanner;
use App\Models\CmsPage;

class AdminCmsService
{
    public function pages()
    {
        return CmsPage::query()->latest('id')->paginate(30);
    }

    public function savePage(array $payload): CmsPage
    {
        return CmsPage::updateOrCreate([
            'slug' => $payload['slug'],
        ], $payload);
    }

    public function banners()
    {
        return CmsBanner::query()->orderBy('sort_order')->paginate(30);
    }

    public function saveBanner(array $payload): CmsBanner
    {
        return CmsBanner::updateOrCreate([
            'id' => $payload['id'] ?? null,
        ], $payload);
    }
}
