@foreach ($files as $namespace => $dir)
    @if (is_array($dir))
        <li>
            <a href="javascript: void(0);">
                <span>{{ $namespace }}</span>
                <span class="menu-arrow"></span>
            </a>
            <ul class="nav-second-level mm-collapse">
                @include('partials.sideNamespace', [
                    'files' => $dir
                ])
            </ul>
        </li>
    @else
        <li>
            <a href="{{ route('document', [
                    'repository' => $repository,
                    'file' => $dir->id,
                ]) }}">
                {{ $dir->class ? last(explode('\\', $dir->class)) : last(explode('/', $dir->name)) }}
            </a>
        </li>
    @endif
@endforeach
