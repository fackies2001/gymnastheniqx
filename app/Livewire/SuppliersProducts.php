<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Suppliers;
use App\Models\SupplierProducts;

class SuppliersProducts extends Component
{
    public $suppliers;
    public $products = [];
    public $selectedSupplier = null;

    public function mount()
    {
        // Gumamit ng 'Suppliers' model base sa iyong files
        $this->suppliers = Suppliers::all();
    }

    public function updatedSelectedSupplier($value)
    {
        if ($value) {
            // Gumamit ng 'SupplierProducts' model at column na 'supplier_id'
            $this->products = SupplierProducts::where('supplier_id', $value)->get();
        } else {
            $this->products = [];
        }
    }

    public function render()
    {
        return view('livewire.suppliers-products');
    }
}
