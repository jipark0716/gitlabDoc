@extends('layout')

@section('content')
    <div class="content-page">
        @foreach ($result as $row)
            <div class="col-6 float-left">
                <div class="card">
                    <div class="card-body">
                        @if ($row instanceof App\Models\RepositoryFileFunction)
                            <button type="button" class="btn btn-dark btn-rounded waves-effect waves-light">function</button>
                            {{ $row->file->class ?? $row->file->name }}:<a href="{{ route('document', [
                                'repository' => $repository,
                                'file' => $row->file
                            ]) }}#function-{{ $row->id }}">{{ $row->name }}</a>
                        @elseif ($row instanceof App\Models\RepositoryFile)
                            <button type="button" class="btn btn-dark btn-rounded waves-effect waves-light">{{ $row->type ?? 'file' }}</button>
                            <a href="{{ route('document', [
                                'repository' => $repository,
                                'file' => $row
                            ]) }}">{{ $row->class ?? $row->name }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
