<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class MenuService extends CrudService
{
    private ?array $items = null;

    protected function modelClass(): string
    {
        return Menu::class;
    }

    protected function relations(): array
    {
        return ['items.children'];
    }

    protected function prepare(array $data, ?Model $model = null): array
    {
        $this->items = Arr::pull($data, 'items');

        return $data;
    }

    protected function afterSave(Model $model, array $data): void
    {
        if ($this->items === null) {
            return;
        }
        $model->items()->delete();
        $this->createItems($model, $this->items);
    }

    private function createItems(Menu $menu, array $items, ?int $parentId = null): void
    {
        foreach ($items as $position => $item) {
            $children = Arr::pull($item, 'children', []);
            $created = $menu->items()->create($item + [
                'parent_id' => $parentId,
                'sort_order' => $item['sort_order'] ?? $position,
            ]);
            $this->createItems($menu, $children, $created->id);
        }
    }
}
