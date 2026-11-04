<?php

namespace App\Services\Rab;

use App\Models\Material;
use App\Models\RabPlan;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RabPlanService
{
    public function store(array $data, int $userId): RabPlan
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = Arr::pull($data, 'items', []);

            $totalBudget = 0;
            $normalizedItems = [];

            foreach ($items as $item) {
                $material = ! empty($item['material_id'])
                    ? Material::with('unit')->find($item['material_id'])
                    : null;

                $materialName = $item['material_name_snapshot'] ?? $material?->name ?? '-';
                $unitName     = $item['unit_name_snapshot'] ?? $material?->unit?->name ?? '-';
                $qty          = (float) ($item['qty_planned'] ?? 0);
                $price        = (float) ($item['price_planned'] ?? 0);
                $subtotal     = $qty * $price;
                $totalBudget += $subtotal;

                $normalizedItems[] = [
                    'material_id'            => $item['material_id'] ?? null,
                    'material_name_snapshot' => $materialName,
                    'unit_name_snapshot'     => $unitName,
                    'qty_planned'            => $qty,
                    'price_planned'          => $price,
                    'subtotal_planned'       => $subtotal,
                    'notes'                  => $item['notes'] ?? null,
                ];
            }

            $plan = RabPlan::create([
                'project_id'   => $data['project_id'],
                'created_by'   => $userId,
                'name'         => $data['name'],
                'period_start' => $data['period_start'],
                'period_end'   => $data['period_end'] ?? null,
                'description'  => $data['description'] ?? null,
                'status'       => 'draft',
                'total_budget' => $totalBudget,
            ]);

            $plan->items()->createMany($normalizedItems);

            return $plan->load(['project', 'creator', 'items.material']);
        });
    }

    public function update(RabPlan $plan, array $data): RabPlan
    {
        return DB::transaction(function () use ($plan, $data) {
            $items = Arr::pull($data, 'items', []);

            $totalBudget = 0;
            $normalizedItems = [];

            foreach ($items as $item) {
                $material = ! empty($item['material_id'])
                    ? Material::with('unit')->find($item['material_id'])
                    : null;

                $materialName = $item['material_name_snapshot'] ?? $material?->name ?? '-';
                $unitName     = $item['unit_name_snapshot'] ?? $material?->unit?->name ?? '-';
                $qty          = (float) ($item['qty_planned'] ?? 0);
                $price        = (float) ($item['price_planned'] ?? 0);
                $subtotal     = $qty * $price;
                $totalBudget += $subtotal;

                $normalizedItems[] = [
                    'material_id'            => $item['material_id'] ?? null,
                    'material_name_snapshot' => $materialName,
                    'unit_name_snapshot'     => $unitName,
                    'qty_planned'            => $qty,
                    'price_planned'          => $price,
                    'subtotal_planned'       => $subtotal,
                    'notes'                  => $item['notes'] ?? null,
                ];
            }

            $plan->update([
                'project_id'   => $data['project_id'],
                'name'         => $data['name'],
                'period_start' => $data['period_start'],
                'period_end'   => $data['period_end'] ?? null,
                'description'  => $data['description'] ?? null,
                'total_budget' => $totalBudget,
            ]);

            $plan->items()->delete();
            $plan->items()->createMany($normalizedItems);

            return $plan->load(['project', 'creator', 'items.material']);
        });
    }

    public function activate(RabPlan $plan): void
    {
        $plan->update(['status' => 'active']);
    }

    public function close(RabPlan $plan): void
    {
        $plan->update(['status' => 'closed']);
    }

    public function delete(RabPlan $plan): void
    {
        DB::transaction(function () use ($plan) {
            $plan->items()->delete();
            $plan->delete();
        });
    }
}
