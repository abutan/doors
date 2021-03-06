<?php

namespace app\fond\cart;


use app\fond\entities\manage\shop\Modification;
use app\fond\entities\manage\shop\Product;

class CartItem
{
    private $product;
    private $modificationId;
    private $size;
    private $quantity;

    public function __construct(Product $product, $modificationId, $size, $quantity)
    {
        $this->product = $product;
        $this->modificationId = $modificationId;
        $this->size = $size;
        $this->quantity = $quantity;
    }

    public function getId(): string
    {
        return md5(serialize([$this->product->id, $this->modificationId]));
    }

    public function getProductId(): int
    {
        return $this->product->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getModificationId(): ?int
    {
        return $this->modificationId;
    }

    public function getModification(): ?Modification
    {
        if ($this->modificationId){
            return $this->product->getModification($this->modificationId);
        }
        return null;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPrice(): int
    {
        if ($this->modificationId){
            return $this->product->getModificationPrice($this->modificationId);
        }
        return $this->product->price;
    }

    public function getWeight(): int
    {
        return $this->product->weight * $this->quantity;
    }

    public function getCost(): int
    {
        return $this->getPrice() * $this->quantity;
    }

    public function plus($quantity)
    {
        return new static($this->product, $this->modificationId, $this->size, $this->quantity + $quantity);
    }

    public function changeQuantity($quantity)
    {
        return new static($this->product, $this->modificationId, $this->size, $quantity);
    }
}