<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notifications
    ) {}

    public function unreadCount(): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $userId = auth()->id();

        return response()->json([
            'count' => $this->notifications->getUnreadCount($companyId, $userId),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $userId = auth()->id();
        $limit = $request->get('limit', 20);

        $notifications = $this->notifications->getNotifications($companyId, $userId, $limit);

        return response()->json([
            'data' => $notifications->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'body'       => $n->body,
                'icon'       => $n->icon,
                'color'      => $n->color,
                'action_url' => $n->action_url,
                'is_read'    => $n->is_read,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
            'unread' => $this->notifications->getUnreadCount($companyId, $userId),
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => 'required|integer']);

        $this->notifications->markAsRead($data['id'], auth()->id());

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $this->notifications->markAllAsRead(auth()->user()->company_id, auth()->id());

        return response()->json(['success' => true]);
    }
}
