@extends('layouts.app')

@section('content')
    <h1>Upload Contacts for {{ $campaign->name }}</h1>
    <form method="POST" action="{{ route('campaigns.storeContacts', $campaign->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="contacts_file" class="form-label">Upload CSV File</label>
            <input type="file" class="form-control" id="contacts_file" name="contacts_file" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
@endsection
