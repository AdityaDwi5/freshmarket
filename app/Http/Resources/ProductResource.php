<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $priceAfterDiscount = $this->price;

        if ($this->relationLoaded('promotions') && $this->promotions->isNotEmpty()) {
            $promotion = $this->promotions->first();

            if ($promotion->type === 'percentage') {
                $priceAfterDiscount -= ($priceAfterDiscount * ($promotion->value / 100));
            } elseif ($promotion->type === 'fixed') {
                $priceAfterDiscount -= $promotion->value;
            }
        }

        return [
            'id' => $this->id,
            'product_name' => $this->name,
            'category_id' => $this->category_id,
            'price' => number_format($this->price, 0, ',', '.'),
            'price_after_discount' => $this->when($this->relationLoaded('promotions') && $this->promotions->isNotEmpty(), number_format($priceAfterDiscount, 0, ',', '.')),
            'promotion' => $this->whenLoaded('promotions', function () {
                return $this->promotions->isNotEmpty() ? new PromotionResource($this->promotions) : null;
            }),
            'description' => $this->description,
            'category' => $this->whenLoaded('category', function () {
                return $this->category ? $this->category->name : null;
            }),
            'image' => $this->image,
            'rating' => $this->average_rating,
            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->isNotEmpty() ? ReviewResource::collection($this->reviews) : null;
            }),
        ];
    }
}
