<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('status', 'All notifications marked as read.');
    }
}
