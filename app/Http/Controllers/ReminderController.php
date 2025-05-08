<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index()
    {
        $reminders = Reminder::latest()->get();
        return view('reminders.index', compact('reminders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'reminder_name' => 'required|string|max:255',
            'purpose' => 'nullable|string',
        ]);

        Reminder::create($request->all());

        return redirect()->back()->with('success', 'Reminder added successfully.');
    }

    public function update(Request $request, Reminder $reminder)
    {
        $request->validate([
            'date' => 'required|date',
            'reminder_name' => 'required|string|max:255',
            'purpose' => 'nullable|string',
        ]);

        $reminder->update($request->all());

        return redirect()->back()->with('success', 'Reminder updated successfully.');
    }

    public function destroy(Reminder $reminder)
    {
        $reminder->delete();

        return redirect()->back()->with('success', 'Reminder deleted successfully.');
    }
}

