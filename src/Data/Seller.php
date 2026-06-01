<?php

namespace Cloudbadak\PaymentHub\Data;

class Seller {
    public ?string $id = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $url = null;

    public function __construct(
        ?string $id = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $url = null
    ){
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->url = $url;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}