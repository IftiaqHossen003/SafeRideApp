<?php

namespace App\Http\Controllers;

use App\Models\TrustedContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * TrustedContactController
 *
 * Handles CRUD operations for trusted contacts.
 */
class TrustedContactController extends Controller
{
    /**
     * Display a listing of the user's trusted contacts.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $contacts = auth()->user()->trustedContacts()->latest()->get();

        return view('trusted-contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new trusted contact.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('trusted-contacts.create');
    }

    /**
     * Store a newly created trusted contact in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'contact_user_id' => 'nullable|exists:users,id',
        ]);

        $validated['user_id'] = auth()->id();

        TrustedContact::create($validated);

        return redirect()->route('trusted-contacts.index')
            ->with('success', 'Trusted contact added successfully.');
    }

    /**
     * Show the form for editing the specified trusted contact.
     *
     * @param  \App\Models\TrustedContact  $trustedContact
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(TrustedContact $trustedContact): View|RedirectResponse
    {
        if ($trustedContact->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('trusted-contacts.edit', compact('trustedContact'));
    }

    /**
     * Update the specified trusted contact in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TrustedContact  $trustedContact
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TrustedContact $trustedContact): RedirectResponse
    {
        if ($trustedContact->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'contact_user_id' => 'nullable|exists:users,id',
        ]);

        $trustedContact->update($validated);

        return redirect()->route('trusted-contacts.index')
            ->with('success', 'Trusted contact updated successfully.');
    }

    /**
     * Remove the specified trusted contact from storage.
     *
     * @param  \App\Models\TrustedContact  $trustedContact
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TrustedContact $trustedContact): RedirectResponse
    {
        if ($trustedContact->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $trustedContact->delete();

        return redirect()->route('trusted-contacts.index')
            ->with('success', 'Trusted contact deleted successfully.');
    }
}
