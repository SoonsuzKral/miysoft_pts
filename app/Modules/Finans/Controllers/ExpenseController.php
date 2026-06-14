<?php

namespace App\Modules\Finans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finans\Models\ExpenseRequest;
use App\Modules\Finans\Models\ExpenseCategory;
use App\Modules\Finans\Requests\StoreExpenseRequest;
use App\Notifications\ExpenseRequestNotification;
use App\Traits\NotifiesManagers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    use NotifiesManagers;
    public function indexView()
    {
        $this->authorize('expense.view');
        $companyId  = auth()->user()->company_id;
        $personels  = \App\Modules\Personel\Models\Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        $categories = ExpenseCategory::forCompany($companyId)->active()->get();
        return view('admin.finances.expenses.index', compact('personels', 'categories'));
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('expense.view');
        $companyId = auth()->user()->company_id;

        $query = ExpenseRequest::with(['personel', 'category', 'approver'])
            ->forCompany($companyId);

        $user = auth()->user();
        if ($user->hasRole('employee') && !$user->hasAnyRole(['manager','hr_manager','company_admin','super_admin'])) {
            $query->where('personel_id', $user->personel?->id);
        }

        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('personel_id')) $query->where('personel_id', $request->personel_id);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('date_from'))   $query->where('expense_date', '>=', $request->date_from);
        if ($request->filled('date_to'))     $query->where('expense_date', '<=', $request->date_to);

        $expenses = $query->orderByDesc('expense_date')->paginate($request->get('per_page', 15));

        return response()->json([
            'data'  => $expenses->map(fn ($e) => array_merge($e->toArray(), [
                'status_label'    => $e->status_label,
                'status_color'    => $e->status_color,
                'exceeds_limit'   => $e->exceedsLimit(),
            ])),
            'total' => $expenses->total(),
            'pages' => $expenses->lastPage(),
            'meta'  => [
                'avg_amount' => (float) ExpenseRequest::forCompany($companyId)->avg('amount'),
            ],
        ]);
    }

    public function create(): JsonResponse
    {
        $this->authorize('expense.request');
        $companyId  = auth()->user()->company_id;
        $personels  = \App\Modules\Personel\Models\Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        $categories = ExpenseCategory::forCompany($companyId)->active()->get();
        return response()->json([
            'html'       => view('admin.finances.expenses._form', compact('personels', 'categories'))->render(),
            'personels'  => $personels->map(fn ($p) => ['id' => $p->id, 'name' => $p->first_name . ' ' . $p->last_name]),
            'categories' => $categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'limit' => $c->limit_per_item]),
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $this->authorize('expense.request');

        $data               = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['status']     = ExpenseRequest::STATUS_PENDING;
        $data['created_by'] = auth()->id();
        $data['currency']   = $data['currency'] ?? 'TRY';

        // Dosya yükle
        if ($request->hasFile('attachments')) {
            $paths = [];
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store("expenses/{$data['company_id']}", 'public');
            }
            $data['attachments'] = $paths;
        }

        $expense = ExpenseRequest::create($data);

        // Yöneticilere bildirim gönder
        $personel = \App\Modules\Personel\Models\Personel::find($data['personel_id']);
        $this->notifyRoles(
            $data['company_id'],
            ['company_admin', 'hr_manager', 'finance'],
            new ExpenseRequestNotification(
                expenseId:     $expense->id,
                personelName:  $personel ? $personel->first_name . ' ' . $personel->last_name : '—',
                amount:        (float) $data['amount'],
                currency:      $data['currency'],
                categoryName:  $expense->category?->name ?? '—',
                expenseDate:   $data['expense_date'],
            )
        );

        // Limit uyarısı
        $warning = $expense->exceedsLimit()
            ? ' ⚠ Kategori limitini aştığından yönetici onayı gerekebilir.'
            : '';

        return response()->json([
            'success'       => true,
            'message'       => 'Masraf talebi oluşturuldu.' . $warning,
            'exceeds_limit' => $expense->exceedsLimit(),
            'data'          => array_merge($expense->load(['personel', 'category'])->toArray(), [
                'status_label' => $expense->status_label,
            ]),
        ], 201);
    }

    public function destroy(ExpenseRequest $expense): JsonResponse
    {
        $this->authorize('expense.manage');
        if (in_array($expense->status, [ExpenseRequest::STATUS_APPROVED, ExpenseRequest::STATUS_PAID])) {
            return response()->json(['success' => false, 'message' => 'Onaylı veya ödenmiş talep silinemez.'], 422);
        }
        $expense->delete();
        return response()->json(['success' => true, 'message' => 'Talep silindi.']);
    }

    public function approve(Request $request, ExpenseRequest $expense): JsonResponse
    {
        $this->authorize('expense.approve');
        $result = $expense->approve(auth()->id());
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function reject(Request $request, ExpenseRequest $expense): JsonResponse
    {
        $this->authorize('expense.approve');
        $request->validate(['reason' => 'required|string|max:500']);
        $result = $expense->reject(auth()->id(), $request->reason);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function markPaid(ExpenseRequest $expense): JsonResponse
    {
        $this->authorize('expense.manage');
        $result = $expense->markPaid(auth()->id());
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /** Makbuz/fiş görüntüle (PDF/image inline) */
    public function viewAttachment(ExpenseRequest $expense, int $index)
    {
        $this->authorize('expense.view');
        $path = $expense->attachments[$index] ?? null;
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            return response()->file($fullPath, ['Content-Disposition' => 'inline']);
        }

        // PDF — tarayıcıda inline göster
        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    /** Kategori CRUD */
    public function categories(Request $request): JsonResponse
    {
        $this->authorize('expense.manage');
        $companyId  = auth()->user()->company_id;
        $categories = ExpenseCategory::forCompany($companyId)->withCount('expenses')->get();
        return response()->json(['data' => $categories]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $this->authorize('expense.manage');
        $data = $request->validate([
            'name'             => 'required|string|max:191',
            'limit_per_item'   => 'nullable|numeric|min:0',
            'requires_receipt' => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
        ]);
        $data['company_id'] = auth()->user()->company_id;
        $cat = ExpenseCategory::create($data);
        return response()->json(['success' => true, 'message' => 'Kategori oluşturuldu.', 'data' => $cat], 201);
    }
}
