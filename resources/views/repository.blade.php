@extends('layout')

@section('content')
@isset ($file)
    <div class="content-page">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        @foreach ($file->path as $path)
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $path }}</a></li>
                        @endforeach
                    </ol>
                </div>
                <h4 class="page-title" style="text-transform: capitalize;">
                    @if ($file->abstract)
                        <button type="button" class="btn btn-dark btn-rounded waves-effect waves-light">abstract</button>
                    @endif
                    @isset ($file->type)
                        <button type="button" class="btn btn-dark btn-rounded waves-effect waves-light">{{ $file->type }}</button>
                    @endisset
                    {{ $file->file_name }}
                    <a href="{{ $file->link }}">(View source)</a>
                </h4>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @isset ($file->extend)
                        <p>
                            extends
                            @isset ($file->extendFile)
                                <a href="{{ route('document', [
                                    'repository' => $repository,
                                    'file' => $file->extendFile->id,
                                ]) }}">
                                    {{ $file->extend }}
                                </a>
                            @else
                                {{ $file->extend }}
                            @endisset
                        </p>
                        <p>
                            @isset ($file->implements)
                                implements
                                @isset ($file->implementFile)
                                    <a href="{{ route('document', [
                                        'repository' => $repository,
                                        'file' => $file->implementFile->id,
                                    ]) }}">
                                        {{ $file->implements }}
                                    </a>
                                @else
                                    {{ $file->implements }}
                                @endisset
                            @endisset
                        </p>
                    @endisset
                    @foreach ($file->function as $function)
                        <p>{{ $function->public }} {{ $function->static ? 'static' : '' }} <a href="#function-{{ $function->id }}">{{ $function->name }}</a>({!! join(', ', $function->param->pluck('text')->toArray()) !!})</p>
                    @endforeach
                </div>
            </div>
        </div>
        @foreach ($file->function as $function)
            <div class="col-12" id="function-{{ $function->id }}">
                <div class="card">
                    <div class="card-body">
                        <h4>{{ $function->public }} {{ $function->static ? 'static' : '' }} {{ $function->name }}({!! join(', ', $function->param->pluck('text')->toArray()) !!})</h4>
                        <p>{!! nl2br($function->comment ?? '-') !!}</p>
                        @if ($function->param->count() > 0)
                            <h5>Parameters</h5>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">name</th>
                                            <th scope="col">type</th>
                                            <th scope="col">comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($function->param as $param)
                                            <tr>
                                                <td>{{ $param->name }}</td>
                                                <td>{!! $param->type_link !!}</td>
                                                <td>{{ $param->comment ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        @if ($function->return_type)
                            <h5>return</h5>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">type</th>
                                            <th scope="col">comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $function->return_type }}</td>
                                            <td>{{ $function->return_comment ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
