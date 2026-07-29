<?php

namespace Cloudbadak\PaymentHub\Data;

class Customer {
    public ?string $id = null;
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $email = null;
    public ?string $phone = null;

    public function __construct(
        ?string $id = null,
        ?string $first_name = null,
        ?string $last_name = null,
        ?string $email = null,
        ?string $phone = null,
    ){
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function getFullName(): ?string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        } elseif ($this->first_name) {
            return $this->first_name;
        } elseif ($this->last_name) {
            return $this->last_name;
        }
        return null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getPhoneCode(): ?string
    {
        if(!$this->phone) return null;
        $number = preg_replace('/\D/', '',$this->phone);
        $code1 = (int) substr($number, 0, 1);
        $code2 = (int) substr($number, 0, 2);
        $code3 = (int) substr($number, 0, 3);
        
        if(in_array($code1,[1,7])){
            return "+" . $code1;
        }

        if(in_array($code2, [20,30,31,32,33,34,36,39,40,41,43,44,45,46,47,48,49])){
            return "+" . $code2;
        }

        if(in_array($code2, [51,52,53,54,55,56,57,58])){
            return "+" . $code2;
        }

        if(in_array($code2, [60,61,62,63,64,65,66])){
            return "+" . $code2;
        }

        if(in_array($code2, [81,82,84,86])){
            return "+" . $code2;
        }

        if(in_array($code2, [90,91,92,93,94,95,98])){
            return "+" . $code2;
        }

        return "+" . $code3;
    }

    public function getPhoneNumber(): ?string
    {
        if(!$this->phone) return null;
        $code = $this->getPhoneCode();
        return str_replace($code, '', $this->phone);
    }
}