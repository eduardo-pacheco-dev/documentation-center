<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DevDocCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\DevDocRequest;
use App\Models\DevDoc;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DevDocController extends Controller
{
    /**
     * List the development documents.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = DevDocCategory::tryFrom((string) $request->query('category'));

        $query = DevDoc::query()->with('author');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($category !== null) {
            $query->where('category', $category);
        }

        $documents = $query
            ->latest('updated_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.dev-docs.index', [
            'documents' => $documents,
            'navigation' => $this->navigation(),
            'search' => $search,
            'category' => $category,
            'categories' => DevDocCategory::cases(),
            'stats' => [
                'total' => DevDoc::query()->count(),
                'published' => DevDoc::query()->where('is_published', true)->count(),
                'drafts' => DevDoc::query()->where('is_published', false)->count(),
                'categories' => DevDoc::query()->distinct()->count('category'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new document.
     */
    public function create(): View
    {
        return view('admin.dev-docs.create', [
            'categories' => DevDocCategory::cases(),
        ]);
    }

    /**
     * Store a newly created document.
     */
    public function store(DevDocRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['title', 'category', 'summary', 'content']);

        $devDoc = DevDoc::query()->create([
            ...$data,
            'user_id' => $request->user()->getKey(),
            'slug' => $this->uniqueSlug($data['title']),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()
            ->route('admin.dev-docs.show', $devDoc)
            ->with('status', 'Documento criado com sucesso.');
    }

    /**
     * Display the given document.
     */
    public function show(DevDoc $devDoc): View
    {
        $devDoc->loadMissing('author');

        return view('admin.dev-docs.show', [
            'document' => $devDoc,
            'navigation' => $this->navigation(),
            'renderedContent' => Str::markdown($devDoc->content, [
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ]),
        ]);
    }

    /**
     * Show the form for editing the given document.
     */
    public function edit(DevDoc $devDoc): View
    {
        return view('admin.dev-docs.edit', [
            'document' => $devDoc,
            'categories' => DevDocCategory::cases(),
        ]);
    }

    /**
     * Update the given document.
     */
    public function update(DevDocRequest $request, DevDoc $devDoc): RedirectResponse
    {
        $devDoc->update([
            ...$request->safe()->only(['title', 'category', 'summary', 'content']),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()
            ->route('admin.dev-docs.show', $devDoc)
            ->with('status', 'Documento atualizado com sucesso.');
    }

    /**
     * Remove the given document.
     */
    public function destroy(DevDoc $devDoc): RedirectResponse
    {
        $devDoc->delete();

        return redirect()
            ->route('admin.dev-docs.index')
            ->with('status', 'Documento excluído com sucesso.');
    }

    /**
     * Build the navigation tree for the wiki sidebar.
     *
     * @return Collection<int, array{category: DevDocCategory, documents: Collection<int, DevDoc>}>
     */
    private function navigation(): Collection
    {
        $documents = DevDoc::query()->orderBy('title')->get();

        return collect(DevDocCategory::cases())
            ->map(fn (DevDocCategory $category): array => [
                'category' => $category,
                'documents' => $documents
                    ->filter(fn (DevDoc $document): bool => $document->category === $category)
                    ->values(),
            ])
            ->filter(fn (array $group): bool => $group['documents']->isNotEmpty())
            ->values();
    }

    /**
     * Build a unique slug for the given title.
     */
    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'documento';

        $slug = $base;
        $suffix = 2;

        while (DevDoc::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
