<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Repository, RepositoryFile, RepositoryFileFunction};

class DocumentController extends Controller
{
    /**
     * 수집된 프로젝트 목록
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\View\View
     */
    public function __invoke(Request $request)
    {
        return view('home');
    }

    /**
     * 리포지토리 문서
     *
     * @param Illuminate\Http\Request $request
     * @param App\Models\Repository $repository
     * @param App\Models\RepositoryFile $file
     * @return Illuminate\View\View
     */
    public function repository(Request $request, Repository $repository, RepositoryFile $file = null)
    {
        return view('repository', compact('repository', 'file'));
    }

    /**
     * 검색 결과 view
     *
     * @param Illuminate\Http\Request $request
     * @param App\Models\Repository $repository
     * @return Illuminate\View\View
     */
    public function search(Request $request, Repository $repository)
    {
        if (!$request->input('q', false)) {
            return $this->repository($request, $repository);
        }
        $result = RepositoryFileFunction::where('name', 'like', "%$request->q%")->with('file')->get();
        $result = $result->merge(RepositoryFile::where('name', 'like', "%$request->q%")->orWhere('class', 'like', "%$request->q%")->get());
        return view('search', compact('repository', 'result'));
    }
}
