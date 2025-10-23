<?php

namespace App\Http\Controllers;

use App\Models\DeviceMapping;
use App\Models\User;
use App\Services\TraccarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * DeviceMappingController
 * 
 * Manages the linking of SafeRide users to Traccar GPS devices.
 * Admin interface for device management.
 */
class DeviceMappingController extends Controller
{
    protected TraccarService $traccar;

    public function __construct(TraccarService $traccar)
    {
        $this->traccar = $traccar;
    }

    /**
     * Display a listing of device mappings (Admin only).
     */
    public function index()
    {
        $deviceMappings = DeviceMapping::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('device-mappings.index', compact('deviceMappings'));
    }

    /**
     * Show the form for creating a new device mapping.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        
        // Fetch available devices from Traccar
        try {
            $traccarDevices = $this->traccar->getAllDevices();
        } catch (\Exception $e) {
            $traccarDevices = [];
            session()->flash('error', 'Failed to fetch Traccar devices: ' . $e->getMessage());
        }

        return view('device-mappings.create', compact('users', 'traccarDevices'));
    }

    /**
     * Store a newly created device mapping in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'traccar_device_id' => 'required|integer',
            'device_name' => 'required|string|max:255',
            'unique_id' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $deviceMapping = DeviceMapping::create($validated);

        return redirect()
            ->route('device-mappings.index')
            ->with('success', "Device '{$deviceMapping->device_name}' mapped successfully!");
    }

    /**
     * Show the form for editing the specified device mapping.
     */
    public function edit(DeviceMapping $deviceMapping)
    {
        $users = User::orderBy('name')->get();
        
        // Fetch available devices from Traccar
        try {
            $traccarDevices = $this->traccar->getAllDevices();
        } catch (\Exception $e) {
            $traccarDevices = [];
            session()->flash('error', 'Failed to fetch Traccar devices: ' . $e->getMessage());
        }

        return view('device-mappings.edit', compact('deviceMapping', 'users', 'traccarDevices'));
    }

    /**
     * Update the specified device mapping in storage.
     */
    public function update(Request $request, DeviceMapping $deviceMapping)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'traccar_device_id' => 'required|integer',
            'device_name' => 'required|string|max:255',
            'unique_id' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $deviceMapping->update($validated);

        return redirect()
            ->route('device-mappings.index')
            ->with('success', "Device mapping updated successfully!");
    }

    /**
     * Remove the specified device mapping from storage.
     */
    public function destroy(DeviceMapping $deviceMapping)
    {
        $deviceName = $deviceMapping->device_name;
        $deviceMapping->delete();

        return redirect()
            ->route('device-mappings.index')
            ->with('success', "Device '{$deviceName}' removed successfully!");
    }

    /**
     * Toggle active status of a device mapping.
     */
    public function toggleActive(DeviceMapping $deviceMapping)
    {
        $deviceMapping->is_active = !$deviceMapping->is_active;
        $deviceMapping->save();

        $status = $deviceMapping->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Device '{$deviceMapping->device_name}' {$status}!");
    }

    /**
     * Show current user's device mappings (non-admin).
     */
    public function myDevices()
    {
        $deviceMappings = DeviceMapping::where('user_id', Auth::id())
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('device-mappings.my-devices', compact('deviceMappings'));
    }

    /**
     * Set a specific device as active for current user.
     */
    public function setActive(DeviceMapping $deviceMapping)
    {
        // Ensure user owns this device
        if ($deviceMapping->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Deactivate all other devices
        DeviceMapping::where('user_id', Auth::id())
            ->update(['is_active' => false]);

        // Activate this device
        $deviceMapping->is_active = true;
        $deviceMapping->save();

        return redirect()
            ->back()
            ->with('success', "Device '{$deviceMapping->device_name}' is now active!");
    }
}
