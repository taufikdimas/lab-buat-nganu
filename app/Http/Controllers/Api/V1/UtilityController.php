<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function search(Request $request, SearchService $service)
    {
        $data = $request->validate(['q' => ['required', 'string', 'min:2', 'max:200']]);

        return $service->search($request->user(), $data['q']);
    }

    public function notifications(Request $request)
    {
        return $request->user()->workNotifications()->latest()->paginate();
    }

    public function readNotification(Request $request, int $id)
    {
        $notification = $request->user()->workNotifications()->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return $notification;
    }
}
