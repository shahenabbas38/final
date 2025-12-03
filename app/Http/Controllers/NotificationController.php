<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;


class NotificationController extends Controller
{
    /**
     * ➕ إنشاء إشعار جديد (يمكن استخدامه من قبل الأدمن)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'title'   => 'required|string|max:255',
            'body'    => 'required|string',
            'type'    => 'required|string|in:LAB,APPOINTMENT,SYSTEM,OTHER',
        ]);

        $notification = Notification::create([
            'user_id'    => $request->user_id,
            'title'      => $request->title,
            'body'       => $request->body,
            'type'       => $request->type,
            'is_read'    => 0,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Notification created successfully ✅',
            'data'    => $notification
        ], 201);
    }

    /**
     * 📥 عرض جميع الإشعارات للمستخدم الحالي
     */
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'All notifications fetched ✅',
            'data'    => $notifications
        ]);
    }

    /**
     * ✅ تعليم إشعار كمقروء
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Access denied 🚫'], 403);
        }

        $notification->is_read = 1;
        $notification->save();

        return response()->json([
            'message' => 'Notification marked as read ✅',
            'data'    => $notification
        ]);
    }

    /**
     * 🗑️ حذف إشعار
     */
    public function destroy(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Access denied 🚫'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted ✅']);
    }
}
