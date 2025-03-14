<?php

namespace App\Models;

use App\Helpers\HttpHelper;
use Google\Service\CloudTasks\HttpRequest;
use Google\Service\Docs\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EasyPost_
{
    //Test: https://apis-sandbox.fedex.com/
    //Production: https://apis.fedex.com/
    public static function verifyAddress($data){
        try{
          $client = new \EasyPost\EasyPostClient(getenv('EASYPOST_API_KEY'));

          $address = $client->address->create([
              // 'verify_strict'  => true,
              'street1' => $data['street_address'],
              'street2' => '',
              'city'    => $data['city'],
              'state'   => $data['state'],
              'zip'     => $data['zip'],
              'country' => 'US',
              'company' => '',
              'phone'   => '415-123-4567'
          ]);
          $verifiedAddress = $client->address->verify($address->id);
          
          return $verifiedAddress;
  
        }catch(\Exception $e)
        {
          throw $e;
        }
    }
    public static function createShipment($data){
      try{
          $client = new \EasyPost\EasyPostClient(getenv('EASYPOST_API_KEY'));
          //Carrier Accoount id = ca_a806e7e93f354e80bdb09a3bfc4b2009
          
              $shipment = $client->shipment->create($data);
              $shipments = $client->shipment->retrieve($shipment->id);
              $boughtShipment = $client->shipment->buy(
                  $shipments->id,
                  [
                      'rate'      => $shipments->lowestRate(),
                      // 'insurance' => 249.99
                  ]
              );
              $shipmentWithLabel = $client->shipment->label(
                  $shipments->id,
                  ['file_format' => 'PDF']
              );
          return $shipmentWithLabel;
        }catch(\Exception $e)
        {
          throw $e;
        }
    }
    public static function trackShipment($data){
      try{
          $client = new \EasyPost\EasyPostClient(getenv('EASYPOST_API_KEY'));

          $tracker = $client->tracker->create([
              'tracking_code' => $data,
              'carrier' => 'USPS'
          ]);

          return $tracker;
      }catch(\Exception $e)
      {
        throw $e;
      }
    }
    public static function shipmentRates(){
        
//ca_a806e7e93f354e80bdb09a3bfc4b2009
        $client = new \EasyPost\EasyPostClient(getenv('EASYPOST_API_KEY'));

        $shipment = $client->shipment->create([
            'to_address' => [
                'name' => 'Dr. Steve Brule',
                'street1' => '179 N Harbor Dr',
                'city' => 'Redondo Beach',
                'state' => 'CA',
                'zip' => '90277',
                'country' => 'US',
                'phone' => '3331114444',
                'email' => 'dr_steve_brule@gmail.com'
            ],
            'from_address' => [
                'name' => 'EasyPost',
                'street1' => '417 Montgomery Street',
                'street2' => '5th Floor',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zip' => '94104',
                'country' => 'US',
                'phone' => '3331114444',
                'email' => 'support@easypost.com'
            ],
            'parcel' => [
                'length' => 20.2,
                'width' => 10.9,
                'height' => 5,
                'weight' => 65.9
            ]
        ]);

        $rates = $client->betaRate->retrieveStatelessRates($shipment);

        return $rates;
    }
    public static function buyShipment(){

        $client = new \EasyPost\EasyPostClient(getenv('EASYPOST_API_KEY'));

        $shipment = $client->shipment->retrieve('shp_f5aa50445eba481595441de6e650400f');

        $boughtShipment = $client->shipment->buy(
            $shipment->id,
            [
                'rate'      => $shipment->lowestRate(),
                'insurance' => 249.99
            ]
        );

        return $boughtShipment;
    }
    
    const API = 'https://apis-sandbox.fedex.com';
    
    public static function authorize()
    {
        $url = self::API . '/oauth/token';
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => 'l712a1281e2d6d412f8081c5b756c2c390',//env('FEDEX_API_KEY'),
            'client_secret' => 'bc0b0ed468e640c8a26ac3ce3959a93c'//env('FEDEX_SECRET_KEY'),
        ];
        return HttpHelper::request($url, $body, 'POST', $headers);
    }
    public static function calculateRate($data){
      
      // if(!empty($data)){
        $data = json_decode($data);
        $url = 'https://apis-sandbox.fedex.com/rate/v1/rates/quotes';//self::API . '/track/v1/trackingnumbers';  
        
        $token = self::authorize();
        
        $headers = array();

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '. $token["access_token"];
        $headers[] = 'X-locale: en_US';
        
        return HttpHelper::request($url,$data, 'POST', $headers, true);
      // }
    }
    public static function packed(/*string $token,*/ $data) 
    {
      //dd($data);
      if(!empty($data)){
        $url = self::API . '/track/v1';

        $token = self::authorize();
        
        $headers = array();

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '. $token["access_token"];
        $headers[] = 'X-locale: en_US';
        
        return HttpHelper::request($url, $data, 'POST', $headers, true);
      }
    }
  
    
    public static function validateShipment(string $token, array $data = []) 
    {
        $url = self::API . '/ship/v1/shipments/packages/validate';
     
        $headers = array();

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '. $token;
        $headers[] = 'X-locale: en_US';

        return HttpHelper::request($url, $data, 'POST', $headers, true);
    }

    public static function validatePostalCode($data) 
    {
      if(!empty($data)){
     
        $data = ' {
          "carrierCode": "FDXE",
          "countryCode": "US",
          "stateOrProvinceCode": "'. $data['state'] .'",
          "postalCode": "'. $data['zip'] .'",
          "shipDate": "2023-01-05",
          "checkForMismatch": true
          }';

        $data = json_decode($data);
        $url = self::API . '/country/v1/postal/validate';

        $token = self::authorize();
        
        $headers = array();

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '. $token["access_token"];
        $headers[] = 'X-locale: en_US';
      
        return HttpHelper::request($url, $data, 'POST', $headers, true);
      }
      
    }
   
    public static function rateCalculator($data){
        
      // https://apis-sandbox.fedex.com/rate/v1/rates/quotes
      if(!empty($data)){
      
        $recipetZip =$data['recipetZip'];
        $shipperweight =$data['shipperweight'];
        $shipperZip =$data['shipperZip'];
        
        // $recipetZip =$data[0]['shipping_detail']['zip'];
        // $shipperweight =$data[0]['product']['weight'];
        // $shipperZip =$data[0]['product']['zip'];
       
        $data ='{
          "accountNumber": {
            "value": "740561073"
          },
          "requestedShipment": {
            "shipper": {
              "address": {
                "postalCode": '. $shipperZip .',
                "countryCode": "US"
              }
            },
            "recipient": {
              "address": {
                "postalCode": '. $recipetZip .',
                "countryCode": "US"
              }
            },
            "pickupType": "USE_SCHEDULED_PICKUP",
            "rateRequestType": [
              "ACCOUNT",
              "LIST"
            ],
            "requestedPackageLineItems": [
              {
                "weight": {
                  "units": "LB",
                  "value": '. $shipperweight .'
                }
              }
            ]
          }
        }';
        $data = json_decode($data);
        $url = self::API . '/rate/v1/rates/quotes';

        $token = self::authorize();
        
        $headers = array();

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '. $token["access_token"];
        $headers[] = 'X-locale: en_US';
      
        return HttpHelper::request($url, $data, 'POST', $headers, true);
      }
      
    } 
  /**
   * Function for Dummy PosttalCode Validation
   * 
   */
    public static function payLoadPosttalCode(){
      return '{
          "countryCode": "US",
          "stateOrProvinceCode": "MD",
          "postalCode": "2090",
          "checkForMismatch": true
        }';
    }
   /**
   * Function for Address Verifications
   * 
   */
  public static function payLoadVerifyAddress(){
    return '{
        "countryCode": "US",
        "stateOrProvinceCode": "MD",
        "postalCode": "2090",
        "checkForMismatch": true
      }';
  }
  /**
   * Function for Dummy Address Validation
   * 
   */
    public static function payLoadAddressValidate(){
     return ' {
          "inEffectAsOfTimestamp": "2022-01-03",
          "validateAddressControlParameters": {
            "includeResolutionTokens": true
          },
          "addressesToValidate": [
            {
              "address": {
                "streetLines": [
                  "9600 colesville road"
                ],
                "city": "silver spring",
                "stateOrProvinceCode": "MD",
                "postalCode": "2090",
                "countryCode": "US",
                "addressVerificationId": "string"
              }
            }
          ]
        }';
    }
  /**
   * Function for Dummy Track Rate
   * 
   */
    public static function payloadTrackRate($rate) {
      return 
          '{
            "accountNumber": {
              "value": "740561073"
            },
            "requestedShipment": {
              "shipper": {
                "address": {
                  "postalCode": 65247,
                  "countryCode": "US"
                }
              },
              "recipient": {
                "address": {
                  "postalCode": 75063,
                  "countryCode": "US"
                }
              },
              "pickupType": "USE_SCHEDULED_PICKUP",
              "rateRequestType": [
                "ACCOUNT",
                "LIST"
              ],
              "requestedPackageLineItems": [
                {
                  "weight": {
                    "units": "LB",
                    "value": 10
                  }
                }
              ]
            }
          }';
      
  }
   /**
   * Function for Dummy Track Shipment 1
   * 
   */
    public static function trackPayload($trackingNum) {
      return 
          '{
            "trackingInfo": [
              {
                "trackingNumberInfo": {
                  "trackingNumber": "'.$trackingNum.'"
                }
              }
            ],
            "includeDetailedScans": true
          }';
      
  }
  /**
   * Function for Dummy Track Shipment 2
   * 
   */
    public static function dummytrackPayload() {
      return 
          '{
            "trackingInfo": [
              {
                "trackingNumberInfo": {
                  "trackingNumber": "111111111111"
                }
              }
            ],
            "includeDetailedScans": true
          }';
      
  }
  /**
   * Function for Dummy rate Calculator
   * 
   */
  public static function payLoadrateCalculator(){
    return '{
      "accountNumber": {
        "value": "740561073"
      },
      "requestedShipment": {
        "shipper": {
          "address": {
            "postalCode": 65247,
            "countryCode": "US"
          }
        },
        "recipient": {
          "address": {
            "postalCode": 75063,
            "countryCode": "US"
          }
        },
        "pickupType": "USE_SCHEDULED_PICKUP",
        "rateRequestType": [
          "ACCOUNT",
          "LIST"
        ],
        "requestedPackageLineItems": [
          {
            "weight": {
              "units": "LB",
              "value": 10
            }
          }
        ]
      }
    }';
  }
    /**
   * Function for Dummy Create Shipment
   * 
   */

    public static function dummyPayload() {
        return [
                  // 'carrier_account_id '=> 'ca_a806e7e93f354e80bdb09a3bfc4b2009',
                  'to_address' => [
                      'name' => 'Dr. Steve Brule',
                      'street1' => '9600 colesville',
                      'city' => 'Silver Spring',
                      'state' => 'MD',
                      'zip' => '20901',
                      'country' => 'US',
                      'phone' => '3331114444',
                      'email' => 'dr_steve_brule@gmail.com'
                  ],
                  'from_address' => [
                      'name' => 'EasyPost',
                      'street1' => '8040 13th St',
                      'street2' => '5th Floor',
                      'city' => 'Silver Spring',
                      'state' => 'MD',
                      'zip' => '20910',
                      'country' => 'US',
                      'phone' => '3331114444',
                      'email' => 'support@easypost.com'
                  ],
                  'parcel' => [
                      'length' => 20.2,
                      'width' => 10.9,
                      'height' => 5,
                      'weight' => 65.9
                  ]
              ];
    }
}
