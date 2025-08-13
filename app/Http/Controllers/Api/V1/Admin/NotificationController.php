<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index(){
        $notifications = auth()->user()->notifications()->paginate(request()->perPage??10);
        return [
            'notifications' => $notifications,
            'total_unread' => auth()->user()->notifications()->whereNull('read_at')->count()
        ];
    }

    public function markAsSeen($id){
        $notification = Notification::find($id);
        $notification->read_at = now();
        $notification->save();
        return $notification;
    }
}
