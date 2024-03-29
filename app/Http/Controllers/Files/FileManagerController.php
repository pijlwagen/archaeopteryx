<?php


namespace App\Http\Controllers\Files;


use App\Classes\File\Directory;
use App\Classes\FileManager;
use App\Classes\_String;
use App\Http\Controllers\Controller;
use App\Models\Domain;
use Faker\Provider\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FileManagerController extends Controller
{
    public function index($domain, Request $request)
    {
        $domain = Domain::where('domain', $domain)->firstOrFail();

        $path = $request->query('path') ?? '/';

        if ($path && $path !== '/') {
            $files = new Directory($domain->root . "/{$path}");
        } else {
            $files = new Directory($domain->root);
        }

        if ($files->files === null) return abort(404);

        return view('domain.files.index', [
            'domain' => $domain,
            'files' => $files,
            'path' => $path
        ]);
    }

    public function edit($domain, Request $request)
    {
        $domain = Domain::where('domain', $domain)->firstOrFail();
        if (!$request->has('file') || !$request->has('path')) return abort(404);
        $path = $request->query('path');
        $file = $request->query('file');
        $pathToFile = $domain->root . $path . '/' . $file;

        if (!file_exists($pathToFile)) return abort(404);

        return view('domain.files.edit', [
            'domain' => $domain,
            'file' => [
                'name' => $file,
                'content' => file_get_contents($pathToFile)
            ],
            'path' => $path,
        ]);
    }

    public function update($domain, Request $request)
    {
        $request->validate([
            'file' => 'required'
        ]);

        $domain = Domain::where('domain', $domain)->firstOrFail();
        if (!$request->has('file') || !$request->has('path')) return abort(404);

        $path = $request->query('path');
        $file = $request->input('file');
        $pathToFile = $domain->root . $path . '/' . $file;

        file_put_contents($pathToFile, $request->input('content'));

        session()->flash('toast', [
            [
                'content' => 'Successfully saved file ' . $file
            ]
        ]);

        return response()->json([
            'file' => $file,
            'domain' => $domain->only(['domain', 'root', 'id', 'type']),
            'path' => $path,
            'url' => route('domain.files.index', $domain->domain) . '?path=' . $path
        ]);
    }

    public function create($domain, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:directory,file',
            'name' => 'required|string',
            'path' => 'required'
        ]);

        if ($validator->fails()) return response()->json($validator->errors());

        $domain = Domain::where('domain', $domain)->firstOrFail();

        $type = $request->input('type');
        $path = $request->query('path');
        $name = $request->input('name');

        $path = ($path === '/' || _String::endsWith('/', $path)) ? '/' : $path . '/';
        dd($path);

        if ($type === 'file') {
            FileManager::createFile($domain->root . $path, $name);
        } else {
            FileManager::createDirectory($domain->root . $path, $name);
        }

        return response()->json([
            'name' => $name,
            'domain' => $domain->only(['domain', 'root', 'id', 'type']),
            'path' => $path,
            'url' => route('domain.files.index', $domain->domain) . '?path=' . $path
        ]);
    }
}
