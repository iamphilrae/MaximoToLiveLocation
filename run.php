<?php

use MaximoToLiveLocation\LiveLocationAsset;
use MaximoToLiveLocation\LiveLocationCategory;
use MaximoToLiveLocation\LiveLocationPing;
use Ramsey\Uuid\Uuid;

require __DIR__.'/vendor/autoload.php';

$config = Dotenv\Dotenv::createImmutable(__DIR__)->load();
$app = new MaximoToLiveLocation\Connector();



$asset_uuids = [];
$category_uuid = (Uuid::uuid4())->toString();


echo "\n\nSTART PINGING\n\n";

for($i=0; $i<36; $i++) {

    $maximo_assets = $app->getMaximoAssets();
    echo "*";

    foreach ($maximo_assets as $id => $maximo_asset) {
        try {

            $uuid = empty($asset_uuids[$id]) ? (Uuid::uuid4())->toString() : $asset_uuids[$id];
            $asset_uuids[$id] = $uuid;

            $livelocation_asset = new LiveLocationAsset(
                $uuid,
                (new LiveLocationCategory($category_uuid)),
                $maximo_asset->assetNum,
                $maximo_asset->siteId,
                $maximo_asset->status
            );

            $app->pingLiveLocation(new LiveLocationPing(
                $livelocation_asset,
                $maximo_asset->latitude,
                $maximo_asset->longitude,
                sha1('floor_level_here'),
                0,
                null
            ));

            echo "-";
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    sleep(3);
}

echo "\n\nSTOPPED\n\n";