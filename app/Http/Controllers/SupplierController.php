<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private SupplierService $supplierService)
    {
    }

    public function index(Request $request)
    {
        $supplier = Supplier::query()
            ->when($request->query('q'), fn ($q, $v) => $q->where('nama_supplier', 'like', "%{$v}%"))
            ->orderBy('nama_supplier')
            ->orderBy('id_supplier')
            ->paginate(8)
            ->withQueryString();

        return view('supplier.index', compact('supplier'));
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $supplier = $this->supplierService->tambah($request->validated());

        return redirect()
            ->route('master.supplier.index')
            ->with('status', "Supplier {$supplier->nama_supplier} berhasil ditambahkan.");
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier = $this->supplierService->ubah($supplier, $request->validated());

        return redirect()
            ->back()
            ->with('status', "Supplier {$supplier->nama_supplier} berhasil diubah.");
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $nama = $supplier->nama_supplier;

        if (! $this->supplierService->hapus($supplier)) {
            return redirect()
                ->back()
                ->with('error', "Supplier {$nama} tidak dapat dihapus karena sudah dipakai pada pembelian stok.");
        }

        return redirect()
            ->back()
            ->with('status', "Supplier {$nama} berhasil dihapus.");
    }
}