<?php

namespace App\Services\Suppliers;

interface SupplierApiInterface
{
    public function fetchProducts(): array;

    // public function fetchProductById(int $id): array;

    // public function createProduct(array $data): array;

    // public function updateProduct(int $id, array $data): array;

    // public function deleteProduct(int $id): bool;
}
