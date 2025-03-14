<?php

namespace App\Models;

use App\Helpers\HttpHelper;
use Google\Service\CloudTasks\HttpRequest;
use Google\Service\Docs\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Fedex
{
    //Test: https://apis-sandbox.fedex.com/
    //Production: https://apis.fedex.com/

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


    public static function createShipment(/*string $token,*/ $data) 
    {
      
      if(!empty($data)){
        $url = self::API . '/ship/v1/shipments';

        $token = self::authorize();
        
        $headers = array();

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '. $token["access_token"];
        $headers[] = 'X-locale: en_US';
        
        return HttpHelper::request($url, $data, 'POST', $headers, true);
      }
    }

    public static function trackShipment(/*string $token,*/ $data) 
    {
      $data = json_decode($data);
      //dd($data);
      if(!empty($data)){
      
        $url = self::API . '/track/v1/trackingnumbers';  
        
        // $url = 'https://apis-sandbox.fedex.com/track/v1/449044304137821';
        $token = self::authorize();
        
        $headers = array();

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '. $token["access_token"];
        $headers[] = 'X-locale: en_US';
        
        return HttpHelper::request($url,$data, 'POST', $headers, true);
      }
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
    // {
     
    //      $data = json_decode($data);
      
    //     if(!empty($data)){

    //       // $url = self::API . '/track/v1';
    //       $url = self::API . '/country/v1/postal/validate';
    //       // $url ="https://developer.fedex.com/api/en-us/catalog/postal-code/v1/country/v1/postal/validate";
  
    //       $token = self::authorize();
          
    //       $headers = array();
  
    //       $headers[] = 'Content-Type: application/json';
    //       $headers[] = 'Authorization: Bearer '. $token["access_token"];
    //       $headers[] = 'X-locale: en_US';
      
    //       return HttpHelper::request($url, $data, 'POST', $headers, true);
    //     }
    // }
   
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
        return array(
            'requestedShipment' => [
              'pickupType' => 'USE_SCHEDULED_PICKUP',
              'serviceType' => 'PRIORITY_OVERNIGHT',
              'packagingType' => 'YOUR_PACKAGING',
              'shipper' => [
                'address' => [
                  'streetLines' => [
                    0 => '10 FedEx Parkway',
                    1 => 'Suite 302'
                  ],
                  'city' => 'Beverly Hills',
                  'stateOrProvinceCode' => 'CA',
                  'postalCode' => 90210,
                  'countryCode' => 'US'
                ],
                'contact' => [
                  'personName' => 'SHIPPER NAME',
                  'phoneNumber' => 1234567890,
                  'companyName' => 'Shipper Company Name'
                ],
              ],
              'recipients' => [
                'address' => [
                  'streetLines' => '-10 FedEx Parkway -Suite 302',
                  'city' => 'Beverly Hills',
                  'stateOrProvinceCode' => 'CA',
                  'postalCode' => 90210,
                  'countryCode' => 'US',
                ],
                'contact' => [
                  'personName' => 'SHIPPER NAME',
                  'phoneNumber' => 9612345671,
                  'companyName' => 'Shipper Company Name',
                ],
              ],
              'shippingChargesPayment' => [
                'paymentType' => 'SENDER',
                'payor' => [
                  'responsibleParty' => [
                    'accountNumber' => [
                      'value' => 'Your account number',
                    ],
                  ],
                ],
              ],
              'labelSpecification' => [
                'labelStockType' => 'PAPER_7X475',
                'imageType' => 'PDF',
              ],
              'requestedPackageLineItems' => [
                'weight' => [
                  'units' => 'LB',
                  'value' => 68,
                ],
              ],
            ],
            'accountNumber' => [
              'value' => 'Your account number',
            ],
        );
    }
}
