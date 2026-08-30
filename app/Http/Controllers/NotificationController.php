<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        // ponytail: native Laravel notifications; mark all visible as read on view
        $user = request()->user();
        $notifications = $user->notifications()->latest()->paginate(20);

        $user->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }
}
