<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminCmsService;
use Illuminate\Http\Request;

class AdminCmsController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminCmsService $service)
    {
    }

    public function pages()
    {
        return $this->success('Action completed successfully', $this->service->pages());
    }

    public function savePage(Request $request)
    {
        $payload = $request->validate([
            'slug' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:120'],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $payload['updated_by'] = $request->user()->id;

        return $this->success('Action completed successfully', $this->service->savePage($payload));
    }

    public function banners()
    {
        return $this->success('Action completed successfully', $this->service->banners());
    }

    public function saveBanner(Request $request)
    {
        $payload = $request->validate([
            'id' => ['nullable', 'integer', 'exists:cms_banners,id'],
            'title' => ['required', 'string', 'max:120'],
            'image_url' => ['required', 'string', 'max:255'],
            'cta_text' => ['nullable', 'string', 'max:120'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload['updated_by'] = $request->user()->id;

        return $this->success('Action completed successfully', $this->service->saveBanner($payload));
    }
}
