<?php

namespace Cloudbadak\PaymentHub\Data;

class Item {
    public ?string $id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?int $quantity = null;
    public ?int $price = null;

    public function __construct(
        ?string $id = null,
        ?string $name = null,
        ?string $description = null,
        ?int $quantity = null,
        ?int $price = null,
    ){
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->price = $price;
    }

    public function getId(): ?string {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getQuantity(): ?int {
        return $this->quantity;
    }

    public function getPrice(): ?int {
        return $this->price;
    }
}