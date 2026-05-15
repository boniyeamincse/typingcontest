<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\TypingText;
use App\Services\Admin\AdminContentService;
use Illuminate\Http\Request;

class AdminContentController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminContentService $service)
    {
    }

    public function index(Request $request)
    {
        return $this->success('Action completed successfully', $this->service->list($request->validate([
            'language' => ['nullable', 'string', 'in:english,bangla,programming'],
            'difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ])));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'content' => ['required', 'string', 'min:20'],
            'language' => ['required', 'string', 'in:english,bangla,programming'],
            'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
            'source_label' => ['nullable', 'string', 'max:100'],
        ]);

        return $this->success('Action completed successfully', $this->service->create($payload), 201);
    }

    public function update(Request $request, TypingText $typingText)
    {
        return $this->success('Action completed successfully', $this->service->update($typingText, $request->validate([
            'content' => ['sometimes', 'string', 'min:20'],
            'language' => ['sometimes', 'string', 'in:english,bangla,programming'],
            'difficulty' => ['sometimes', 'string', 'in:easy,medium,hard'],
            'source_label' => ['sometimes', 'nullable', 'string', 'max:100'],
        ])));
    }

    public function destroy(TypingText $typingText)
    {
        $this->service->delete($typingText);

        return $this->success('Action completed successfully', []);
    }
}
