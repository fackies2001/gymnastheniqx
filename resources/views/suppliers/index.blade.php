@extends('layouts.adminlte')

@section('subtitle', 'Suppliers')
@section('content_header_title', 'Suppliers')
@section('content_header_subtitle', 'All Suppliers')

@push('css')
    <style>
        .supplier-header {
            background: #2d3748;
            color: white;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .supplier-count-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 4px 12px;
            border-radius: 15px;
            display: inline-block;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .search-sort-container {
            background: white;
            padding: 18px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }

        .supplier-card {
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            height: 100%;
            overflow: hidden;
            background: white;
        }

        .supplier-card:hover {
            border-color: #cbd5e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .supplier-card-header {
            background: #2d3748;
            color: white;
            padding: 14px;
            text-align: center;
            position: relative;
        }

        .supplier-card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.05rem;
        }

        .supplier-card-header a {
            color: white;
            text-decoration: none;
        }

        .supplier-card-header a:hover {
            opacity: 0.9;
        }

        .supplier-card-body {
            padding: 18px;
            min-height: 135px;
        }

        .supplier-info {
            margin-bottom: 9px;
            display: flex;
            align-items: center;
            color: #4a5568;
            font-size: 0.88rem;
        }

        .supplier-info i {
            width: 18px;
            margin-right: 10px;
            color: #718096;
            font-size: 0.85rem;
        }

        .supplier-footer {
            background: #f8fafc;
            padding: 11px 14px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-count {
            background: #4a5568;
            color: white;
            padding: 4px 11px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.82rem;
        }

        .date-added {
            color: #718096;
            font-size: 0.78rem;
        }

        .search-input,
        .sort-select {
            border-radius: 6px;
            padding: 9px 14px;
            border: 1px solid #cbd5e0;
            font-size: 0.9rem;
        }

        .search-input:focus,
        .sort-select:focus {
            border-color: #4a5568;
            box-shadow: 0 0 0 3px rgba(74, 85, 104, 0.08);
            outline: none;
        }

        .btn-add-supplier {
            background: #2d3748;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 9px 18px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .btn-add-supplier:hover {
            background: #1a202c;
            color: white;
        }

        .no-suppliers {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }

        .no-suppliers i {
            font-size: 3.5rem;
            margin-bottom: 18px;
        }
    </style>
@endpush

@section('content_body')
    <div class="supplier-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1" style="font-size: 1.4rem;">
                <i class="fas fa-truck"></i> All Suppliers
                <span class="supplier-count-badge">{{ $suppliers->count() }}</span>
            </h3>
            <p class="mb-0" style="font-size: 0.9rem; opacity: 0.9;">Manage your supplier network</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-add-supplier">
            <i class="fas fa-plus"></i> Add Supplier
        </a>
    </div>

    <div class="search-sort-container">
        <form method="GET" action="{{ route('suppliers.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-color: #cbd5e0;">
                            <i class="fas fa-search text-muted" style="font-size: 0.85rem;"></i>
                        </span>
                        <input type="text" name="search" class="form-control search-input border-start-0"
                            placeholder="Search suppliers..." value="{{ request('search') }}" id="searchInput">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="sort" class="form-control sort-select" id="sortSelect"
                        onchange="document.getElementById('filterForm').submit();">
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="most_products" {{ request('sort') == 'most_products' ? 'selected' : '' }}>Most
                            Products</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    @if ($suppliers->count() > 0)
        <div class="row" id="suppliersGrid">
            @foreach ($suppliers as $supplier)
                <div class="col-md-3 mb-4 supplier-item" data-name="{{ strtolower($supplier->name) }}"
                    data-email="{{ strtolower($supplier->email ?? '') }}"
                    data-contact="{{ strtolower($supplier->contact_person ?? '') }}"
                    data-phone="{{ strtolower($supplier->contact_number ?? '') }}">
                    <div class="card supplier-card"
                        onclick="window.location='{{ route('suppliers.show', $supplier->id) }}'">
                        <div class="supplier-card-header">
                            <h5>
                                <a href="{{ route('suppliers.show', $supplier->id) }}">
                                    {{ $supplier->name }}
                                </a>
                            </h5>
                        </div>
                        <div class="supplier-card-body">
                            @if (!empty($supplier->contact_person))
                                <div class="supplier-info">
                                    <i class="fas fa-user"></i>
                                    <span>{{ $supplier->contact_person }}</span>
                                </div>
                            @endif
                            @if (!empty($supplier->email))
                                <div class="supplier-info">
                                    <i class="fas fa-envelope"></i>
                                    <span>{{ Str::limit($supplier->email, 30) }}</span>
                                </div>
                            @endif
                            @if (!empty($supplier->contact_number))
                                <div class="supplier-info">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ $supplier->contact_number }}</span>
                                </div>
                            @endif
                            @if (!empty($supplier->address))
                                <div class="supplier-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ Str::limit($supplier->address, 38) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="supplier-footer">
                            <div class="product-count">
                                <i class="fas fa-box"></i> {{ $supplier->supplier_products_count ?? 0 }}
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-warning"
                                    onclick="event.stopPropagation()">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST"
                                    onclick="event.stopPropagation()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this supplier?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-suppliers">
            <i class="fas fa-box-open"></i>
            <h4>No Suppliers Found</h4>
            <p>{{ request('search') ? 'Try a different search term' : 'Start by adding your first supplier' }}</p>
            @if (!request('search'))
                <a href="{{ route('suppliers.create') }}" class="btn btn-add-supplier mt-3">
                    <i class="fas fa-plus"></i> Add Your First Supplier
                </a>
            @endif
        </div>
    @endif
@endsection

@push('js')
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const supplierItems = document.querySelectorAll('.supplier-item');
            supplierItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const email = item.getAttribute('data-email');
                const contact = item.getAttribute('data-contact');
                const phone = item.getAttribute('data-phone');
                if (name.includes(searchTerm) ||
                    email.includes(searchTerm) ||
                    contact.includes(searchTerm) ||
                    phone.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        document.getElementById('filterForm').addEventListener('submit', function(e) {
            if (document.activeElement.id === 'searchInput') {
                e.preventDefault();
            }
        });
    </script>
@endpush
