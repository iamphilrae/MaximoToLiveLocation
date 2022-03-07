<?php
namespace MaximoToLiveLocation;


class LiveLocationAsset {

    public LiveLocationCategory $category;

    public string $uuid;
    public ?string $parameter1;
    public ?string $parameter2;
    public ?string $parameter3;
    public ?array $meta_data;
    public ?string $manufacturer;
    public ?string $model;
    public ?string $sdk_version;
    public ?array $tags;


    public function __construct(
        string $uuid,
        LiveLocationCategory $category,
        ?string $parameter1=null,
        ?string $parameter2=null,
        ?string $parameter3=null,
        ?array $meta_data=null,
        ?string $manufacturer=null,
        ?string $model=null,
        ?string $sdk_version=null,
        ?array $tags=null
    ){
        $this->uuid = $uuid;
        $this->category = $category;
        $this->parameter1 = $parameter1;
        $this->parameter2 = $parameter2;
        $this->parameter3 = $parameter3;
        $this->meta_data = $meta_data;
        $this->manufacturer = $manufacturer;
        $this->model = $model;
        $this->sdk_version = $sdk_version;
        $this->tags = $tags;
    }
}