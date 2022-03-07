<?php
namespace MaximoToLiveLocation;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use SimpleXMLElement;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException as OAuth2Exception;


class Connector {


	private string $maximoRequestBodyFormat = 
'<?xml version="1.0" encoding="utf-8"?>

<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <QueryLMAPASSET xmlns="http://www.ibm.com/maximo">
      <LMAPASSETQuery>
        <WHERE>%s</WHERE>
      </LMAPASSETQuery>
    </QueryLMAPASSET>
  </soap:Body>
</soap:Envelope>';

	private string $maximoRequestBaseUrl;
	private string $liveLocationPostUrl;


    public function __construct() {
        $this->maximoRequestBaseUrl = $_ENV['MAXIMO_REQUEST_BASE_URL'];
        $this->liveLocationPostUrl = $_ENV['LIVE_LOCATION_POST_URL'];
    }


    /**
     * Returns an array of assets from the Maximo API.
     * @param string|null $asset_id
     * @return array|null
     * @throws GuzzleException
     */
    public function getMaximoAssets(?string $asset_id=null): ?array
	{
		try 
		{
			/*
			* Request the assets from the Maximo API
			*/
			$client = new Client(['base_uri' => $this->maximoRequestBaseUrl]);

			

			$request_headers = [
            	"Content-Type" => "text/xml; charset=utf-8",
            	"SOAPAction" => "urn:processDocument",
            	"apikey" => $_ENV['MAXIMO_API_KEY']
			];

			
			$query = !empty($asset_id) ? "ASSETNUM='" . $asset_id . "'" : "";
			$request_body = sprintf($this->maximoRequestBodyFormat, $query);


			$response = $client->send((new Request('POST', 'LMAPASSET', $request_headers, $request_body)));


			/*
			* Convert the XML response to a PHP array
			*/
			$response_xml = (string)$response->getBody();
			$response_xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response_xml);
			$response_xml = new SimpleXMLElement($response_xml);

			$response_arr = json_decode(json_encode((array)$response_xml->xpath('//soapenvBody')[0]), true); 


			/*
			* Process assets found in array
			*/
			$assets = [];
			$timestamp = $response_arr['QueryLMAPASSETResponse']['@attributes']['creationDateTime'];



			if(!empty($response_arr['QueryLMAPASSETResponse']['LMAPASSETSet']['ASSET'])) {

				if(!isset($response_arr['QueryLMAPASSETResponse']['LMAPASSETSet']['ASSET'][0]))
					$response_arr['QueryLMAPASSETResponse']['LMAPASSETSet']['ASSET'] = [
						$response_arr['QueryLMAPASSETResponse']['LMAPASSETSet']['ASSET']
					];

				foreach($response_arr['QueryLMAPASSETResponse']['LMAPASSETSet']['ASSET'] as $a) 
				{
					$asset = new MaximoAsset(
						$a['ASSETNUM'],
						$a['STATUS'],
						$a['SITEID'],
						$a['DESCRIPTION'],
						$a['ASSETTYPE']
					);

					$asset->setLocation(
						$a['SERVICEADDRESS']['LATITUDEY'],
						$a['SERVICEADDRESS']['LONGITUDEX'],
						$timestamp
					);

					$assets[$a['ASSETNUM']] = $asset;
				}
			}


			return $assets;
		}
		catch(Exception $e)
		{
			print_r($e->getMessage());
			return null;
		}
	}


    /**
     * Gets a single asset from the Maximo API.
     * @param string $asset_id
     * @return MaximoAsset|null
     * @throws GuzzleException
     */
    public function getMaximoAsset(string $asset_id): ?MaximoAsset {
        $assets = $this->getMaximoAssets($asset_id);
        return is_array($assets) ? array_shift($assets) : null;
    }


    /**
     * Sends a ping to the Live Location API.
     * @param LiveLocationPing $ping
     * @return int The HTTP status code
     * @throws GuzzleException
     */
    public function pingLiveLocation(LiveLocationPing $ping): int
    {
        $client = new Client(['base_uri' => sprintf($this->liveLocationPostUrl, 'dev1')]);

        $request_headers = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer " . $this->getLiveLocationApiAccessToken()
        ];

        $request_body = $ping->toApiJson();

        $response = $client->send((new Request('POST', '', $request_headers, $request_body)));
        return $response->getStatusCode();
	}


    /**
     * Gets an Oauth2 access token.
     * @return false|string
     */
    protected function getLiveLocationApiAccessToken(): bool|string
    {
        $provider = new OAuth2Provider([
            'clientId'                => $_ENV['LM_AUTH_CLIENT_ID'],
            'urlAuthorize'            => $_ENV['LM_AUTH_AUTHORIZE_URL'],
            'urlAccessToken'          => $_ENV['LM_AUTH_TOKEN_URL'],
            'urlResourceOwnerDetails' => $_ENV['LM_AUTH_RESOURCE_URL']
        ]);

        try {
            return (string)$provider->getAccessToken('password', [
                'username' => $_ENV['LM_AUTH_USERNAME'],
                'password' => $_ENV['LM_AUTH_PASSWORD']
            ]);
        } catch (OAuth2Exception $e) {
            return false;
        }
    }
}