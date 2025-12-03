<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppointmentReminder;

class AppointmentReminderController extends Controller
{
    /**
     * ⏰ إنشاء Reminder جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'remind_at' => 'required|date|after:now',
            'channel' => 'in:INAPP,EMAIL,SMS'
        ]);

        $reminder = AppointmentReminder::create([
            'appointment_id' => $request->appointment_id,
            'remind_at' => $request->remind_at,
            'channel' => $request->channel ?? 'INAPP',
        ]);

        return response()->json([
            'message' => 'Reminder created successfully ⏰',
            'reminder' => $reminder
        ], 201);
    }

    /**
     * 📋 عرض كل Reminders
     */
    public function index()
    {
        $reminders = AppointmentReminder::with('appointment')->get();
        return response()->json($reminders);
    }

    /**
     * 🗑️ حذف Reminder
     */
    public function destroy($id)
    {
        $reminder = AppointmentReminder::find($id);
        if (!$reminder) {
            return response()->json(['message' => 'Reminder not found ❌'], 404);
        }
        $reminder->delete();
        return response()->json(['message' => 'Reminder deleted successfully 🗑️']);
    }
}
