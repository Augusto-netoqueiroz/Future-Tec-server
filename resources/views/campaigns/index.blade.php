@extends('layouts.app')

@section('content')
    <h1>Campaigns</h1>
    <a href="{{ route('campaigns.create') }}" class="btn btn-primary">Create Campaign</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->id }}</td>
                    <td>{{ $campaign->name }}</td>
                    <td>{{ $campaign->description }}</td>
                    <td>
                        <a href="{{ route('campaigns.uploadContacts', $campaign->id) }}" class="btn btn-secondary">Upload Contacts</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
