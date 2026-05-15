<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Services\Admin\AdminContestService;
use Illuminate\Http\Request;

class AdminContestController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminContestService $service)
    {
    }

    public function index(Request $request)
    {
        return $this->success('Action completed successfully', $this->service->list($request->validate([
            'status' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ])));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string'],
            'status' => ['nullable', 'string'],
            'max_participants' => ['nullable', 'integer', 'min:2'],
            'typing_text_id' => ['nullable', 'integer', 'exists:typing_texts,id'],
            'text_content' => ['nullable', 'string'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'allow_late_join' => ['nullable', 'boolean'],
        ]);

        if (! isset($payload['duration_minutes'])) {
            $start = now()->parse($payload['start_time']);
            $end = now()->parse($payload['end_time']);
            $payload['duration_minutes'] = max(1, $start->diffInMinutes($end));
        }

        $payload['created_by'] = $request->user()->id;

        return $this->success('Action completed successfully', $this->service->create($payload), 201);
    }

    public function update(Request $request, Contest $contest)
    {
        $payload = $request->validate([
            'title' => ['sometimes', 'string', 'max:120'],
            'type' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string'],
            'max_participants' => ['sometimes', 'integer', 'min:2'],
            'typing_text_id' => ['sometimes', 'nullable', 'integer', 'exists:typing_texts,id'],
            'text_content' => ['sometimes', 'nullable', 'string'],
            'start_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'date'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1'],
            'allow_late_join' => ['sometimes', 'boolean'],
        ]);

        return $this->success('Action completed successfully', $this->service->update($contest, $payload));
    }

    public function destroy(Contest $contest)
    {
        $this->service->delete($contest);

        return $this->success('Action completed successfully', []);
    }

    public function start(Contest $contest)
    {
        return $this->success('Action completed successfully', $this->service->start($contest));
    }

    public function stop(Contest $contest)
    {
        return $this->success('Action completed successfully', $this->service->stop($contest));
    }

    public function analytics(Contest $contest)
    {
        return $this->success('Action completed successfully', $this->service->analytics($contest));
    }

    public function cloneContest(Contest $contest)
    {
        return $this->success('Action completed successfully', $this->service->cloneContest($contest), 201);
    }
}
