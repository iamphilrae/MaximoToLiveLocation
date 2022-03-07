<?php 
namespace MaximoToLiveLocation;


class MaximoAsset {
	
	public string $assetNum;
    public string $status;
    public string $siteId;
	public ?string $description;
    public ?array $assetType;

	public float $latitude;
	public float $longitude;
	public \DateTime $last_updated;


	public function __construct(
		string $assetNum, 
		string $status,
		string $siteId,
		?string $description = null,
		?array $assetType = null,
	)
	{
		$this->assetNum = $assetNum;
		$this->assetType = $assetType;

		$this->description = $description;
		$this->siteId = $siteId;
		$this->status = $status;
	}


	public function setLocation(float $latitude, float $longitude, string $timestamp)
	{
		$this->latitude = $latitude;
		$this->longitude = $longitude;	
		$this->last_updated = new \DateTime($timestamp);
	}
}