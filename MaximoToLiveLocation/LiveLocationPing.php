<?php 
namespace MaximoToLiveLocation;


class LiveLocationPing {

    public LiveLocationAsset $asset;

    public float $latitude;
    public float $longitude;
    public string $floor_level;
    public ?float $accuracy;
    public ?float $heading;


	public function __construct(
        LiveLocationAsset $asset,
		float $latitude,
        float $longitude,
        string $floor_level,
        ?float $accuracy=null,
        ?float $heading=null
	){
        $this->asset = $asset;
		$this->latitude = $latitude;
		$this->longitude = $longitude;
        $this->floor_level = $floor_level;
        $this->accuracy = $accuracy;
        $this->heading = $heading;
	}

    /**
     * Returns the Ping object as an API-compatible JSON object.
     * @return false|string
     */
    public function toApiJson()
    {
        $obj_as_array = [
            'status' => [
                'position' => [
                    'accuracy' => $this->accuracy,
                    'coordinates' => [
                        'latitude' => $this->latitude,
                        'longitude' => $this->longitude
                    ],
                    'floor_level' => $this->floor_level,
                    'heading' => $this->heading,
                ],
                'journey' => null,
            ],

            'asset' => [
                'uuid' =>  $this->asset->uuid,
                'category' =>  $this->asset->category->uuid,
                'manufacturer' =>  $this->asset->manufacturer,
                'meta_data' =>  $this->asset->meta_data,
                'model' =>  $this->asset->model,
                'parameter_1' =>  $this->asset->parameter1,
                'parameter_2' =>  $this->asset->parameter2,
                'parameter_3' =>  $this->asset->parameter3,
                'sdk_version' =>  $this->asset->sdk_version,
                'tags' =>  $this->asset->tags
            ],

            'journey' => null,
        ];

        return json_encode($obj_as_array, JSON_PRETTY_PRINT);
    }
}