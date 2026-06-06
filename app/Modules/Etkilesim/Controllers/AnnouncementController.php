<?php

namespace App\Modules\Etkilesim\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Etkilesim\Models\Announcement;
use App\Modules\Etkilesim\Models\Poll;
use App\Modules\Etkilesim\Models\PollResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function indexView()
    {
        $this->authorize('access_admin');
        $companyId = auth()->user()->company_id;
        $announcements = Announcement::forCompany($companyId)->active()->pinned()->get();
        $polls = Poll::forCompany($companyId)->active()->get();
        return view('admin.etkilesim.index', compact('announcements', 'polls'));
    }

    // ─── Duyurular ───────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $this->authorize('access_admin');
        $companyId = auth()->user()->company_id;

        $query = Announcement::forCompany($companyId);

        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->boolean('active_only')) $query->active();
        if ($request->boolean('pinned_only')) $query->pinned();

        $items = $query->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data'  => $items->map(fn ($a) => array_merge($a->toArray(), [
                'type_label'  => $a->type_label,
                'type_color'  => $a->type_color,
                'is_expired'  => $a->isExpired(),
            ])),
            'total' => $items->total(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('access_admin');

        $data = $request->validate([
            'title'       => 'required|string|max:191',
            'content'     => 'required|string',
            'type'        => 'nullable|in:general,urgent,event',
            'visibility'  => 'nullable|in:all,department,position,selected',
            'is_pinned'   => 'nullable|boolean',
            'is_published'=> 'nullable|boolean',
            'publish_at'  => 'nullable|date',
            'expires_at'  => 'nullable|date',
        ]);

        $data['company_id']  = auth()->user()->company_id;
        $data['created_by']  = auth()->id();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('announcements', 'public');
        }

        $announcement = Announcement::create($data);

        return response()->json(['success' => true, 'message' => 'Duyuru oluşturuldu.', 'data' => $announcement], 201);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $this->authorize('access_admin');

        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:191',
            'content'     => 'sometimes|required|string',
            'type'        => 'nullable|in:general,urgent,event',
            'is_pinned'   => 'nullable|boolean',
            'is_published'=> 'nullable|boolean',
            'publish_at'  => 'nullable|date',
            'expires_at'  => 'nullable|date',
        ]);

        $announcement->update($data);

        return response()->json(['success' => true, 'message' => 'Duyuru güncellendi.', 'data' => $announcement->fresh()]);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->authorize('access_admin');
        if ($announcement->attachment) Storage::disk('public')->delete($announcement->attachment);
        $announcement->delete();
        return response()->json(['success' => true, 'message' => 'Duyuru silindi.']);
    }

    // ─── Anketler ─────────────────────────────────────────────────────────────

    public function polls(Request $request): JsonResponse
    {
        $this->authorize('access_admin');
        $companyId = auth()->user()->company_id;

        $polls = Poll::forCompany($companyId)
            ->withCount('responses')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data'  => $polls->map(fn ($p) => array_merge($p->toArray(), ['results' => $p->results])),
            'total' => $polls->total(),
        ]);
    }

    public function storePoll(Request $request): JsonResponse
    {
        $this->authorize('access_admin');

        $data = $request->validate([
            'question'        => 'required|string',
            'options'         => 'required|array|min:2',
            'options.*'       => 'required|string|max:191',
            'multiple_choice' => 'nullable|boolean',
            'anonymous'       => 'nullable|boolean',
            'ends_at'         => 'nullable|date|after:now',
        ]);

        $data['company_id']  = auth()->user()->company_id;
        $data['created_by']  = auth()->id();

        $poll = Poll::create($data);

        return response()->json(['success' => true, 'message' => 'Anket oluşturuldu.', 'data' => $poll], 201);
    }

    public function votePoll(Request $request, Poll $poll): JsonResponse
    {
        if (!$poll->isActive() || $poll->isExpired()) {
            return response()->json(['success' => false, 'message' => 'Bu anket artık aktif değil.'], 422);
        }

        $data = $request->validate([
            'selected_options'   => 'required|array|min:1',
            'selected_options.*' => 'integer|min:0',
        ]);

        PollResponse::create([
            'poll_id'          => $poll->id,
            'personel_id'      => auth()->user()->personel?->id,
            'selected_options' => $data['selected_options'],
        ]);

        return response()->json(['success' => true, 'message' => 'Oyunuz kaydedildi.', 'results' => $poll->fresh()->results]);
    }
}
