@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8">
        <h1 class="text-2xl font-semibold mb-6">Create Budget — Step 1</h1>

        <form method="POST" action="{{ route('budgets.wizard.step1') }}" class="space-y-6">
                @csrf
                <div>
                        <label class="block text-sm font-medium">Budget name</label>
                        <input name="name" value="{{ old('name') }}" required
                                class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800" />
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                                <label class="block text-sm font-medium">Period</label>
                                <select name="period" id="period"
                                        class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800">
                                        <option value="monthly">Monthly</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="custom">Custom</option>
                                </select>
                                @error('period') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                                <label class="block text-sm font-medium">Total amount</label>
                                <input name="total_amount" value="{{ old('total_amount') }}" type="number" step="0.01"
                                        min="0" required
                                        class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800" />
                                @error('total_amount') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                </div>

                <div id="custom-dates" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                        <div>
                                <label class="block text-sm font-medium">Start date</label>
                                <input name="start_date" type="date"
                                        class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800" />
                        </div>
                        <div>
                                <label class="block text-sm font-medium">End date</label>
                                <input name="end_date" type="date"
                                        class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-800" />
                        </div>
                </div>

                <div class="flex justify-end gap-2">
                        <a href="{{ route('budgets.index') }}"
                                class="px-4 py-2 text-sm rounded-md bg-gray-100 dark:bg-gray-800">Cancel</a>
                        <button class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white">Continue</button>
                </div>
        </form>
</div>

<script>
        document.getElementById('period').addEventListener('change', function () {
                document.getElementById('custom-dates').classList.toggle('hidden', this.value !== 'custom');
        });
</script>
@endsection