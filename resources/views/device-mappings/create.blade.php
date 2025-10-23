<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Device Mapping') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('device-mappings.store') }}" method="POST">
                        @csrf

                        <!-- User Selection -->
                        <div class="mb-4">
                            <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">
                                User <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" id="user_id" 
                                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('user_id') border-red-500 @enderror"
                                    required>
                                <option value="">Select a user...</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Traccar Device Selection -->
                        <div class="mb-4">
                            <label for="traccar_device_id" class="block text-gray-700 text-sm font-bold mb-2">
                                Traccar Device <span class="text-red-500">*</span>
                            </label>
                            <select name="traccar_device_id" id="traccar_device_id" 
                                    class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('traccar_device_id') border-red-500 @enderror"
                                    required>
                                <option value="">Select a Traccar device...</option>
                                @foreach ($traccarDevices as $device)
                                    <option value="{{ $device['id'] }}" 
                                            data-name="{{ $device['name'] }}"
                                            data-uniqueid="{{ $device['uniqueId'] }}"
                                            {{ old('traccar_device_id') == $device['id'] ? 'selected' : '' }}>
                                        {{ $device['name'] }} (ID: {{ $device['id'] }}, Unique: {{ $device['uniqueId'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('traccar_device_id')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Device Name -->
                        <div class="mb-4">
                            <label for="device_name" class="block text-gray-700 text-sm font-bold mb-2">
                                Device Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="device_name" id="device_name" 
                                   value="{{ old('device_name') }}"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('device_name') border-red-500 @enderror"
                                   placeholder="e.g., iPhone 13, Galaxy S21"
                                   required>
                            @error('device_name')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Unique ID -->
                        <div class="mb-4">
                            <label for="unique_id" class="block text-gray-700 text-sm font-bold mb-2">
                                Unique ID (IMEI) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="unique_id" id="unique_id" 
                                   value="{{ old('unique_id') }}"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('unique_id') border-red-500 @enderror"
                                   placeholder="Device unique identifier"
                                   required>
                            @error('unique_id')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Set as active device</span>
                            </label>
                            <p class="text-xs text-gray-500 ml-6 mt-1">
                                Note: Activating this device will deactivate all other devices for this user.
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Create Device Mapping
                            </button>
                            <a href="{{ route('device-mappings.index') }}" 
                               class="text-gray-600 hover:text-gray-800">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-fill device name and unique ID when Traccar device is selected
        document.getElementById('traccar_device_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const deviceName = selectedOption.getAttribute('data-name');
            const uniqueId = selectedOption.getAttribute('data-uniqueid');
            
            if (deviceName) {
                document.getElementById('device_name').value = deviceName;
            }
            if (uniqueId) {
                document.getElementById('unique_id').value = uniqueId;
            }
        });
    </script>
</x-app-layout>
