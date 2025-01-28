@extends('day.layout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Campaign Report</h2>
        <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="border border-gray-200 px-4 py-2">ID</th>
                    <th class="border border-gray-200 px-4 py-2">Name</th>
                    <th class="border border-gray-200 px-4 py-2">Status</th>
                    <th class="border border-gray-200 px-4 py-2">Start Date</th>
                    <th class="border border-gray-200 px-4 py-2">End Date</th>
                    <th class="border border-gray-200 px-4 py-2">Total Contacts</th>
                    <th class="border border-gray-200 px-4 py-2">Completed Contacts</th>
                    <th class="border border-gray-200 px-4 py-2">Pending Contacts</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $data)
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-200 px-4 py-2">{{ $data->id }}</td>
                    <td class="border border-gray-200 px-4 py-2">{{ $data->name }}</td>
                    <td class="border border-gray-200 px-4 py-2">{{ $data->status }}</td>
                    <td class="border border-gray-200 px-4 py-2">{{ $data->start_date }}</td>
                    <td class="border border-gray-200 px-4 py-2">{{ $data->end_date }}</td>
                    <td class="border border-gray-200 px-4 py-2">{{ $data->total_contacts }}</td>
                    <td class="border border-gray-200 px-4 py-2">{{ $data->completed_contacts }}</td>
                    <td class="border border-gray-200 px-4 py-2">{{ $data->pending_contacts }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if ($reportData->isEmpty())
        <p class="text-center text-gray-500 py-4">No campaigns found.</p>
        @endif
    </div>
</div>
@endsection
