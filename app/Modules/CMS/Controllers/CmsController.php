<?php

namespace App\Modules\CMS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CMS\Models\Content;
use App\Modules\CMS\Models\Blog;
use App\Modules\CMS\Models\BlogCategory;
use App\Modules\CMS\Models\PartnerLogo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    private function uniqueBlogSlug(string $base, ?int $exceptId = null): string
    {
        $base = $base !== '' ? $base : Str::random(8);
        $slug = $base;
        $n    = 0;
        while (Blog::where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists()) {
            $slug = $base . '-' . strtolower(Str::random(4));
            if (++$n > 30) {
                $slug = $base . '-' . time();
                break;
            }
        }

        return $slug;
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard()
    {
        $this->authorize('settings.manage');
        $sections  = Content::select('section')->distinct()->pluck('section');
        $blogCount = Blog::count();
        $partnerCount = PartnerLogo::count();
        return view('admin.cms.index', compact('sections', 'blogCount', 'partnerCount'));
    }

    // ─── Content (Site İçerikleri) ────────────────────────────────────────────

    public function getContents(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');
        $query = Content::query();
        if ($request->filled('section')) $query->bySection($request->section);
        return response()->json(['data' => $query->orderBy('section')->orderBy('key')->get()]);
    }

    public function upsertContent(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');

        $data = $request->validate([
            'items'         => 'required|array',
            'items.*.key'   => 'required|string|max:100',
            'items.*.value' => 'nullable|string',
            'items.*.label' => 'nullable|string|max:191',
            'items.*.type'  => 'nullable|in:text,html,json,image,url',
        ]);

        foreach ($data['items'] as $item) {
            Content::updateOrCreate(
                ['key' => $item['key']],
                [
                    'value'      => $item['value'] ?? '',
                    'label'      => $item['label'] ?? null,
                    'type'       => $item['type'] ?? 'text',
                    'updated_by' => auth()->id(),
                ]
            );
        }

        Cache::tags(['content'])->flush();

        return response()->json(['success' => true, 'message' => 'İçerik güncellendi.']);
    }

    // ─── Blog CRUD ────────────────────────────────────────────────────────────

    public function blogIndex(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');

        $blogs = Blog::with('category', 'author')
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->filled('is_published'), fn ($q) => $q->where('is_published', $request->boolean('is_published')))
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json(['data' => $blogs->items(), 'total' => $blogs->total()]);
    }

    public function blogCreate(): JsonResponse
    {
        $this->authorize('settings.manage');
        $categories = BlogCategory::where('is_active', true)->get();
        return response()->json(['html' => view('admin.cms.blog._form', compact('categories'))->render()]);
    }

    public function blogStore(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');

        $data = $request->validate([
            'title'            => 'required|string|max:191',
            'slug'             => 'nullable|string|max:191',
            'content'          => 'required|string',
            'summary'          => 'nullable|string|max:500',
            'category_id'      => 'nullable|exists:blog_categories,id',
            'meta_title'       => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'tags_input'       => 'nullable|string',
            'tags'             => 'nullable|array',
            'reading_time'     => 'nullable|integer|min:1|max:60',
            'is_published'     => 'nullable|boolean',
            'published_at'     => 'nullable|date',
            'featured_image'   => 'nullable|image|max:2048',
        ]);

        $baseSlug      = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $data['slug'] = $this->uniqueBlogSlug($baseSlug !== '' ? $baseSlug : Str::slug($data['title']) . '-' . Str::random(4));

        // tags_input → array
        if ($request->filled('tags_input')) {
            $data['tags'] = array_filter(array_map('trim', explode(',', $request->tags_input)));
        }
        unset($data['tags_input']);

        $data['author_id']    = auth()->id();
        $data['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blog/images', 'public');
        }

        $blog = Blog::create($data);

        return response()->json(['success' => true, 'message' => 'Blog yazısı oluşturuldu.', 'data' => $blog], 201);
    }

    public function blogEdit(Blog $blog): JsonResponse
    {
        $this->authorize('settings.manage');
        $categories = BlogCategory::where('is_active', true)->get();
        return response()->json(['html' => view('admin.cms.blog._form', compact('blog', 'categories'))->render()]);
    }

    public function blogUpdate(Request $request, Blog $blog): JsonResponse
    {
        $this->authorize('settings.manage');

        $data = $request->validate([
            'title'            => 'sometimes|required|string|max:191',
            'slug'             => 'nullable|string|max:191',
            'content'          => 'sometimes|required|string',
            'summary'          => 'nullable|string|max:500',
            'category_id'      => 'nullable|exists:blog_categories,id',
            'meta_title'       => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'tags_input'       => 'nullable|string',
            'tags'             => 'nullable|array',
            'reading_time'     => 'nullable|integer|min:1|max:60',
            'is_published'     => 'nullable|boolean',
            'published_at'     => 'nullable|date',
            'featured_image'   => 'nullable|image|max:2048',
        ]);

        // tags_input → array
        if ($request->filled('tags_input')) {
            $data['tags'] = array_filter(array_map('trim', explode(',', $request->tags_input)));
        }
        unset($data['tags_input']);

        $data['is_published'] = $request->boolean('is_published');

        if (array_key_exists('slug', $data) && filled($data['slug'])) {
            $data['slug'] = $this->uniqueBlogSlug(Str::slug($data['slug']), $blog->id);
        }

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) Storage::disk('public')->delete($blog->featured_image);
            $data['featured_image'] = $request->file('featured_image')->store('blog/images', 'public');
        } elseif ($request->input('remove_featured_image') === '1') {
            if ($blog->featured_image) Storage::disk('public')->delete($blog->featured_image);
            $data['featured_image'] = null;
        }

        $blog->update($data);

        return response()->json(['success' => true, 'message' => 'Blog yazısı güncellendi.', 'data' => $blog->fresh()]);
    }

    public function blogDestroy(Blog $blog): JsonResponse
    {
        $this->authorize('settings.manage');
        $blog->delete();
        return response()->json(['success' => true, 'message' => 'Blog yazısı silindi.']);
    }

    // ─── Partner Logos ────────────────────────────────────────────────────────

    public function partners(): JsonResponse
    {
        $this->authorize('settings.manage');
        return response()->json(['data' => PartnerLogo::orderBy('sort_order')->get()]);
    }

    public function storePartner(Request $request): JsonResponse
    {
        $this->authorize('settings.manage');
        $data = $request->validate([
            'name'        => 'required|string|max:191',
            'website_url' => 'nullable|url',
            'alt_text'    => 'nullable|string|max:191',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('partners', 'public');
        }
        $partner = PartnerLogo::create($data);
        return response()->json(['success' => true, 'message' => 'İş ortağı eklendi.', 'data' => $partner], 201);
    }

    public function updatePartner(Request $request, PartnerLogo $partnerLogo): JsonResponse
    {
        $this->authorize('settings.manage');
        $data = $request->validate([
            'name'        => 'sometimes|required|string',
            'website_url' => 'nullable|url',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);
        if ($request->hasFile('logo')) {
            if ($partnerLogo->logo_path) Storage::disk('public')->delete($partnerLogo->logo_path);
            $data['logo_path'] = $request->file('logo')->store('partners', 'public');
        }
        $partnerLogo->update($data);
        return response()->json(['success' => true, 'message' => 'Güncellendi.', 'data' => $partnerLogo->fresh()]);
    }

    public function destroyPartner(PartnerLogo $partnerLogo): JsonResponse
    {
        $this->authorize('settings.manage');
        if ($partnerLogo->logo_path) Storage::disk('public')->delete($partnerLogo->logo_path);
        $partnerLogo->delete();
        return response()->json(['success' => true, 'message' => 'Silindi.']);
    }
}
