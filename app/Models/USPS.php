<?php

namespace App\Models;

use App\Helpers\HttpHelper;
use Google\Service\CloudTasks\HttpRequest;
use Google\Service\Docs\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class USPS
{
    const PRODUCTION_API = 'http://production.shippingapis.com';
    const STAGING_API    = 'https://secure.shippingapis.com';

    public static function validateAddress( string $xmlData = '') 
    {
        $url = self::STAGING_API.'/ShippingAPI.dll?API=Verify';
        $headers = self::headers();
        return HttpHelper::requestXML($url, $xmlData, $headers);
    }

    public static function rateCalculator(string $xmlData = ''){
        $url = self::STAGING_API.'/ShippingAPI.dll?API=RateV4';
        $headers = self::headers();

        return HttpHelper::requestXML($url, $xmlData, $headers);
    }
    public static function cityLookUp(string $xmlData = '') 
    {
        $url = self::STAGING_API.'/ShippingAPI.dll?API=CityStateLookup';
        $headers = self::headers();
        return HttpHelper::requestXML($url, $xmlData, $headers);
    }

    public static function zipCodeLookUp(string $xmlData = '') 
    {
        $url = self::STAGING_API.'/ShippingAPI.dll?API=ZipCodeLookup';
        $headers = self::headers();
        return HttpHelper::requestXML($url, $xmlData, $headers);
    }

    public static function createShipment(string $xmlData = '') 
    {
        $xmlData = self::dummyCreateShipmentPayLoad();
        $url = self::STAGING_API.'/ShippingAPI.dll?API=CarrierPickupSchedule';
        
        $headers = self::headers();
        return HttpHelper::requestXML($url, $xmlData, $headers);
    }
    
    public static function trackshipment(string $xmlData = '') 
    {
        // string $xmlData = ''
        $xmlData = '<TrackRequest USERID="974FLEXM7409">
                    <TrackID ID="EJ123456780US"></TrackID>
                    </TrackRequest>';
        $url = self::STAGING_API.'/ShippingAPI.dll?API=TrackV2';
        
        $headers = self::headers();
        return HttpHelper::requestXML($url, $xmlData, $headers);
    }

    public static function domesticLabelCreation(){

        $xmlData = self::dummyDomesticLabelPayLoad();
        $url = self::STAGING_API.'/ShippingAPI.dll?API=eVS';
        
        $headers = self::headers();
        return HttpHelper::requestXML($url, $xmlData, $headers);
    }
    public static function dummyAddressPayload($revision = 1, $address1 = '', $address2 = '', $city = '', $state = '', $zip5 = '', $zip4 = '') 
    {
        return '<AddressValidateRequest USERID="974FLEXM7409"><Revision>' . $revision . '</Revision><Address><Address1>'  . $address1 . '</Address1><Address2>' . $address2 . '</Address2><City>' . $city . '</City><State>' . $state . '</State><Zip5>' . $zip5 . '</Zip5><Zip4></Zip4></Address></AddressValidateRequest>';
    }

    public static function dummyCityLookUp($zip5 = '') 
    {
        return '<CityStateLookupRequest USERID="974FLEXM7409"><ZipCode><Zip5>' .$zip5. '</Zip5></ZipCode></CityStateLookupRequest>';
    }

    public static function dummyZipCodeLookUp($address1 = '', $address2 = '', $city = '', $state = '') 
    {
        return '<ZipCodeLookupRequest USERID="974FLEXM7409"><Address><Address1>'  . $address1 . '</Address1><Address2>' . $address2 . '</Address2><City>' . $city . '</City><State>' . $state . '</State></Address></ZipCodeLookupRequest>';
    }

    public static function dummyDomesticLabelPayLoad(){
        return '<eVSRequest USERID="974FLEXM7409">
                    <Option></Option>
                    <Revision>1</Revision>
                    <ImageParameters>
                        <LabelSequence>
                            <PackageNumber>1</PackageNumber>
                            <TotalPackages>1</TotalPackages>
                        </LabelSequence>
                    </ImageParameters>
                    <FromName>Lina Smith</FromName>
                    <FromFirm>Horizon</FromFirm>
                    <FromAddress1>Apt 303</FromAddress1>
                    <FromAddress2>1309 S Agnew Avenue</FromAddress2>
                    <FromCity>Oklahoma City</FromCity>
                    <FromState>OK</FromState>
                    <FromZip5>73108</FromZip5>
                    <FromZip4>2427</FromZip4>
                    <FromPhone>1234567890</FromPhone>
                    <POZipCode/>
                    <AllowNonCleansedOriginAddr>false</AllowNonCleansedOriginAddr>
                    <ToName>Tall Tom</ToName>
                    <ToFirm>ABC Corp.</ToFirm>
                    <ToAddress1/>
                    <ToAddress2>1098 N Fraser Street</ToAddress2>
                    <ToCity>Georgetown</ToCity>
                    <ToState>SC</ToState>
                    <ToZip5>29440</ToZip5>
                    <ToZip4>2849</ToZip4>
                    <ToPhone>8005554526</ToPhone>
                    <POBox/>
                    <ToContactPreference>email</ToContactPreference>
                    <ToContactMessaging/>
                    <ToContactEMail>talltom@aol.com</ToContactEMail>
                    <AllowNonCleansedDestAddr>false</AllowNonCleansedDestAddr>
                    <WeightInOunces>32</WeightInOunces>
                    <ServiceType>PRIORITY</ServiceType>
                    <Container>VARIABLE</Container>
                    <Width>5.5</Width>
                    <Length>11</Length>
                    <Height>11</Height>
                    <Machinable>TRUE</Machinable>
                    <ProcessingCategory/>
                    <PriceOptions/>
                    <InsuredAmount>100.00</InsuredAmount>
                    <AddressServiceRequested>true</AddressServiceRequested>
                    <ExpressMailOptions>
                        <DeliveryOption/>
                        <WaiverOfSignature/>
                    </ExpressMailOptions>
                    <ShipDate></ShipDate>
                    <CustomerRefNo>EF789UJK</CustomerRefNo>
                    <CustomerRefNo2>EE66GG87</CustomerRefNo2>
                    <ExtraServices>
                        <ExtraService>120</ExtraService>
                    </ExtraServices>
                    <HoldForPickup/>
                    <OpenDistribute/>
                    <PermitNumber/>
                    <PermitZIPCode/>
                    <PermitHolderName/>
                    <CRID>4569873</CRID>
                    <MID>456789354</MID>
                    <VendorCode>1234</VendorCode>
                    <VendorProductVersionNumber>5.02.1B</VendorProductVersionNumber>
                    <SenderName>Adam Johnson</SenderName>
                    <SenderEMail>Adam1234d@aol.com</SenderEMail>
                    <RecipientName>Robert Jones</RecipientName>
                    <RecipientEMail>bobjones@aol.com</RecipientEMail>
                    <ReceiptOption>SAME PAGE</ReceiptOption>
                    <ImageType>PDF</ImageType>
                    <HoldForManifest>N</HoldForManifest>
                    <NineDigitRoutingZip>false</NineDigitRoutingZip>
                    <ShipInfo>True</ShipInfo>
                    <CarrierRelease>False</CarrierRelease>
                    <DropOffTime/>
                    <ReturnCommitments>True</ReturnCommitments>
                    <PrintCustomerRefNo>False</PrintCustomerRefNo>
                    <PrintCustomerRefNo2>True</PrintCustomerRefNo2>
                    <Content>
                        <ContentType>Perishable</ContentType>
                        <ContentDescription>Other</ContentDescription>
                    </Content>
                    <ActionCode>M0</ActionCode>
                    <OptOutOfSPE>false</OptOutOfSPE>
                    <SortationLevel/>
                    <DestinationEntryFacilityType/>
                </eVSRequest>';
    }

    public static function dummyRateCalculatorPayload()
    {
            return '<RateV4Request USERID="974FLEXM7409">
                    <Revision>2</Revision>
                    <Package ID="0">
                    <Service>PRIORITY</Service>
                    <ZipOrigination>22201</ZipOrigination>
                    <ZipDestination>26301</ZipDestination>
                    <Pounds>8</Pounds>
                    <Ounces>2</Ounces>
                    <Container></Container>
                    <Width></Width>
                    <Length></Length>
                    <Height></Height>
                    <Girth></Girth>
                    <Machinable>TRUE</Machinable>
                    </Package>
                </RateV4Request>';
    }
    public static function dummyCreateShipmentPayLoad()
    {
        return '<CarrierPickupScheduleRequest USERID="974FLEXM7409">
                    <FirstName>John</FirstName>
                    <LastName>Doe</LastName>
                    <FirmName>NotNew</FirmName>
                    <SuiteOrApt>Suite 101</SuiteOrApt>
                    <Address2>9600 colesville road</Address2>
                    <Urbanization></Urbanization>
                    <City>Silver Spring</City>
                    <State>MD</State>
                    <ZIP5>20901</ZIP5>
                    <ZIP4>1000</ZIP4>
                    <Phone>5555551234</Phone>
                    <Extension></Extension>
                    <Package>
                    <ServiceType>PriorityMailExpress</ServiceType>
                    <Count>2</Count>
                    </Package>
                    <Package>
                    <ServiceType>PriorityMail</ServiceType>
                    <Count>1</Count>
                    </Package>
                    <EstimatedWeight>14</EstimatedWeight>
                    <PackageLocation>Front Door</PackageLocation>
                    <SpecialInstructions>Packages are behind the screen door.</SpecialInstructions>
                </CarrierPickupScheduleRequest>';
    }
    public static function dummyvalidateAddressPayload(){
           return '<AddressValidateRequest USERID="974FLEXM7409">
                <Revision>1</Revision>
                <Address ID="0">
                    <Address1>SUITE K</Address1>
                    <Address2>9600 colesville road</Address2>
                    <City>silver spring</City>
                    <State>MD</State>
                    <Zip5>20901</Zip5>
                    <Zip4/>
                </Address>
            </AddressValidateRequest>';
    }
    public static function headers($isGet = true) : array 
    {
        if(! $isGet) {
            // @TODO: implement something that changes header if the request is not get
            return [];
        }

        return [
                'Accept'          => '*/*', 
                'Accept-Encoding' => 'gzip, deflate, br', 
                'Content-Type'    =>'application/x-www-form-urlencoded'
               ];
    }

    public function dummyShipmentAvailabilityPayload(){
        return '<CarrierPickupAvailabilityRequest USERID="974FLEXM7409">
        <FirmName>ABC Corp.</FirmName>
        <SuiteOrApt>Suite 777</SuiteOrApt>
        <Address2>9600 colesville road</Address2>
        <Urbanization></Urbanization>
        <City>silver spring</City>
        <State>MD</State>
        <ZIP4>20901</ZIP4>
        </CarrierPickupAvailabilityRequest>';
    }
}
