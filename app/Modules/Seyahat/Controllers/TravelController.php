<?php

namespace App\Modules\Seyahat\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Seyahat\Models\TravelRequest;
use App\Modules\Seyahat\Requests\StoreTravelRequest;
use App\Modules\Personel\Models\Personel;
use App\Traits\NotifiesManagers;
use App\Notifications\TravelRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TravelController extends Controller
{
    use NotifiesManagers;

    public function indexView()
    {
        $this->authorize('travel.view');
        $companyId = auth()->user()->company_id;

        $hasData = TravelRequest::forCompany($companyId)->exists();
        if (!$hasData) {
            $this->seedDemoData($companyId);
        }

        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->orderBy('first_name')->get();

        return view('admin.travel.index', compact('personels'));
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('travel.view');
        $companyId = auth()->user()->company_id;

        $query = TravelRequest::with(['personel:id,first_name,last_name'])
            ->forCompany($companyId);

        $user = auth()->user();
        if ($user->hasRole('employee') && !$user->hasAnyRole(['manager','hr_manager','company_admin','super_admin'])) {
            $query->where('personel_id', $user->personel?->id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('destination', 'like', "%{$s}%")
                  ->orWhere('purpose', 'like', "%{$s}%")
                  ->orWhereHas('personel', fn($pq) => $pq->where(DB::raw("concat(first_name,' ',last_name)"), 'like', "%{$s}%"));
            });
        }
        if ($request->filled('personel_id')) {
            $query->where('personel_id', $request->personel_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('departure_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('return_date', '<=', $request->date_to);
        }

        $travels = $query->orderByDesc('created_at')->paginate($request->get('per_page', 15));

        $data = $travels->map(function ($t) {
            return array_merge($t->toArray(), [
                'status_label' => $t->status_label,
                'status_color' => $t->status_color,
            ]);
        });

        return response()->json([
            'data'         => $data,
            'total'        => $travels->total(),
            'pages'        => $travels->lastPage(),
            'current_page' => $travels->currentPage(),
            'last_page'    => $travels->lastPage(),
        ]);
    }

    public function widgetData(): JsonResponse
    {
        $this->authorize('travel.view');
        $companyId = auth()->user()->company_id;
        $user = auth()->user();

        $query = TravelRequest::forCompany($companyId);
        if ($user->hasRole('employee') && !$user->hasAnyRole(['manager','hr_manager','company_admin','super_admin'])) {
            $query->where('personel_id', $user->personel?->id);
        }

        $total    = (clone $query)->count();
        $pending  = (clone $query)->where('status', TravelRequest::STATUS_PENDING)->count();
        $approved = (clone $query)->where('status', TravelRequest::STATUS_APPROVED)->count();
        $completed= (clone $query)->where('status', TravelRequest::STATUS_COMPLETED)->count();

        return response()->json(compact('total', 'pending', 'approved', 'completed'));
    }

    private function seedDemoData(int $companyId): void
    {
        $personelIds = Personel::forCompany($companyId)->active()->pluck('id');
        if ($personelIds->isEmpty()) return;

        $destinations = ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya', 'Adana', 'Trabzon', 'Gaziantep'];
        $modes = ['uçak', 'otobüs', 'tren', 'özel_araç'];
        $purposes = [
            'Müşteri ziyareti',
            'Fuar katılımı',
            'Toplantı',
            'Saha denetimi',
            'Eğitim programı',
            'Proje görüşmesi',
            'Satış görüşmesi',
            'Teknik destek',
        ];

        foreach ($personelIds->take(3) as $pid) {
            $departure = Carbon::today()->addDays(rand(3, 15));
            $return = (clone $departure)->addDays(rand(1, 4));
            TravelRequest::create([
                'company_id'          => $companyId,
                'personel_id'         => $pid,
                'destination'         => $destinations[array_rand($destinations)],
                'departure_date'      => $departure->toDateString(),
                'return_date'         => $return->toDateString(),
                'purpose'             => $purposes[array_rand($purposes)],
                'transportation_mode' => $modes[array_rand($modes)],
                'estimated_cost'      => rand(2000, 15000),
                'currency'            => 'TRY',
                'status'              => TravelRequest::STATUS_PENDING,
                'created_at'          => now()->subDays(rand(1, 5)),
            ]);
        }

        $travelStatuses = ['approved', 'approved', 'rejected', 'completed'];
        foreach ($personelIds->take(rand(3, 5)) as $pid) {
            $departure = Carbon::today()->subDays(rand(10, 60));
            $return = (clone $departure)->addDays(rand(1, 5));
            TravelRequest::create([
                'company_id'          => $companyId,
                'personel_id'         => $pid,
                'destination'         => $destinations[array_rand($destinations)],
                'departure_date'      => $departure->toDateString(),
                'return_date'         => $return->toDateString(),
                'purpose'             => $purposes[array_rand($purposes)],
                'transportation_mode' => $modes[array_rand($modes)],
                'estimated_cost'      => rand(2000, 15000),
                'currency'            => 'TRY',
                'status'              => $travelStatuses[array_rand($travelStatuses)],
                'created_at'          => now()->subDays(rand(10, 60)),
            ]);
        }
    }

    public function create(): JsonResponse
    {
        $this->authorize('travel.request');
        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        return response()->json([
            'html'      => view('admin.travel._form', compact('personels'))->render(),
            'personels' => $personels->map(fn ($p) => ['id' => $p->id, 'name' => $p->first_name . ' ' . $p->last_name]),
        ]);
    }

    public function store(StoreTravelRequest $request): JsonResponse
    {
        $this->authorize('travel.request');

        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['status']     = TravelRequest::STATUS_PENDING;
        $data['created_by'] = auth()->id();
        $data['currency']   = $data['currency'] ?? 'TRY';

        $travel = new TravelRequest($data);
        if ($travel->hasOverlap()) {
            return response()->json(['success' => false, 'message' => 'Bu personelin seçilen tarih aralığında zaten bekleyen/onaylı bir seyahat talebi bulunuyor.'], 422);
        }
        $travel->save();

        $personel = Personel::find($data['personel_id']);
        $this->notifyRoles(
            $data['company_id'],
            ['company_admin', 'hr_manager', 'manager'],
            new TravelRequestNotification(
                travelId:     $travel->id,
                personelName: $personel ? $personel->first_name . ' ' . $personel->last_name : '—',
                destination:  $data['destination'],
                departure:    $data['departure_date'],
                returnDate:   $data['return_date'],
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Seyahat talebi oluşturuldu.',
            'data'    => array_merge($travel->load('personel:id,first_name,last_name')->toArray(), [
                'status_label' => $travel->status_label,
                'status_color' => $travel->status_color,
            ]),
        ], 201);
    }

    public function show(TravelRequest $travel): JsonResponse
    {
        $this->authorize('travel.view');
        return response()->json(['data' => $travel->load(['personel:id,first_name,last_name', 'approver:id,name'])]);
    }

    public function edit(TravelRequest $travel): JsonResponse
    {
        $this->authorize('travel.manage');
        $companyId = auth()->user()->company_id;
        $personels = Personel::forCompany($companyId)->active()
            ->select('id', 'first_name', 'last_name')->get();
        return response()->json([
            'html'          => view('admin.travel._form', compact('personels'))->with('travel', $travel)->render(),
            'personels'     => $personels->map(fn ($p) => ['id' => $p->id, 'name' => $p->first_name . ' ' . $p->last_name]),
            'selected_id'   => $travel->personel_id,
        ]);
    }

    public function update(StoreTravelRequest $request, TravelRequest $travel): JsonResponse
    {
        $this->authorize('travel.manage');
        if (!in_array($travel->status, [TravelRequest::STATUS_PENDING, TravelRequest::STATUS_APPROVED])) {
            return response()->json(['success' => false, 'message' => 'Bu talep artık düzenlenemez.'], 422);
        }
        $data = $request->validated();
        $travel->fill($data);
        if ($travel->hasOverlap($travel->id)) {
            return response()->json(['success' => false, 'message' => 'Bu personelin seçilen tarih aralığında zaten bir seyahat talebi bulunuyor.'], 422);
        }
        $travel->save();
        return response()->json([
            'success' => true,
            'message' => 'Seyahat talebi güncellendi.',
            'data'    => array_merge($travel->fresh()->load('personel:id,first_name,last_name')->toArray(), [
                'status_label' => $travel->status_label,
                'status_color' => $travel->status_color,
            ]),
        ]);
    }

    public function destroy(TravelRequest $travel): JsonResponse
    {
        $this->authorize('travel.manage');
        if ($travel->status === TravelRequest::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => 'Onaylı talep silinemez.'], 422);
        }
        $travel->delete();
        return response()->json(['success' => true, 'message' => 'Talep silindi.']);
    }

    public function approve(Request $request, TravelRequest $travel): JsonResponse
    {
        $this->authorize('travel.approve');
        $result = $travel->approve(auth()->id());
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function reject(Request $request, TravelRequest $travel): JsonResponse
    {
        $this->authorize('travel.approve');
        $request->validate(['reason' => 'required|string|max:500']);
        $result = $travel->reject(auth()->id(), $request->reason);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function cancel(TravelRequest $travel): JsonResponse
    {
        $this->authorize('travel.request');
        $result = $travel->cancel(auth()->id());
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function complete(TravelRequest $travel): JsonResponse
    {
        $this->authorize('travel.manage');
        $result = $travel->complete();
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('travel.view');
        $companyId = auth()->user()->company_id;

        $query = DB::table('travel_requests as tr')
            ->select(DB::raw('
                tr.id, p.first_name, p.last_name,
                tr.destination, tr.departure_date, tr.return_date,
                tr.purpose, tr.transportation_mode, tr.accommodation,
                tr.estimated_cost, tr.currency, tr.status,
                u.name as approver_name, tr.approved_at, tr.created_at
            '))
            ->join('personels as p', 'p.id', '=', 'tr.personel_id')
            ->leftJoin('users as u', 'u.id', '=', 'tr.approved_by')
            ->where('tr.company_id', $companyId);

        if ($request->filled('status')) $query->where('tr.status', $request->status);
        if ($request->filled('personel_id')) $query->where('tr.personel_id', $request->personel_id);
        if ($request->filled('date_from')) $query->where('tr.departure_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('tr.return_date', '<=', $request->date_to);

        $travels = $query->orderByDesc('tr.created_at')->get();

        $filename = 'seyahat_talepleri_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function () use ($travels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Ad', 'Soyad', 'Gidilecek Yer', 'Gidiş', 'Dönüş', 'Amaç', 'Ulaşım', 'Konaklama', 'Tahmini Maliyet', 'Para Birimi', 'Durum', 'Onaylayan', 'Onay Tarihi', 'Oluşturulma'], ';');
            foreach ($travels as $t) {
                fputcsv($handle, [
                    $t->id, $t->first_name, $t->last_name, $t->destination,
                    $t->departure_date, $t->return_date, $t->purpose,
                    $t->transportation_mode, $t->accommodation,
                    $t->estimated_cost, $t->currency, $t->status,
                    $t->approver_name, $t->approved_at, $t->created_at,
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('travel.view');
        $companyId = auth()->user()->company_id;

        $query = TravelRequest::with(['personel', 'approver'])
            ->forCompany($companyId);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('personel_id')) $query->where('personel_id', $request->personel_id);
        if ($request->filled('date_from')) $query->where('departure_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('return_date', '<=', $request->date_to);

        $travels = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('admin.travel.export-pdf', compact('travels'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('seyahat_talepleri_' . now()->format('Y-m-d_His') . '.pdf');
    }
}
