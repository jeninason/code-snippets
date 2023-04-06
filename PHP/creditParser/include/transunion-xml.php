<?php
/*
REQUIRED VARIABLES:

$isProd: true/false

$uniqueVar
$personArr: ssn, firstName, lastName, address, city, state, zip, dob

TU Variables
-----------------
$systemId
$systemPassword
$subscriberIndustryCode
$subscriberMemberCode
$subscriberPrefixCode
$subscriberPaassword

*/
echo('<?') ?>xml version="1.0" encoding="utf-8" <? echo('?>') ?>
<xmlrequest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:soap="http://schemas.xmlsoap.org/soap/envelope"
        xmlns="******">
        <systemId><?= $systemId ?></systemId>
        <systemPassword><?= $systemPassword ?></systemPassword>
        <productrequest>
                <creditBureau xmlns="******">
                        <document>request</document>
                        <version>2.14</version>
                        <transactionControl>
                                <userRefNumber>Person:_<?= $uniqueVar ?></userRefNumber>
                                <subscriber>
                                        <industryCode><?= $subscriberIndustryCode ?></industryCode>
                                        <memberCode>0<?= $subscriberMemberCode ?></memberCode>
                                        <inquirySubscriberPrefixCode><?= $subscriberPrefixCode ?></inquirySubscriberPrefixCode>
                                        <password><?= $subscriberPassword ?></password>
                                </subscriber>
                                <options>
                                        <processingEnvironment><?= ($isProd) ? 'production' : 'standardTest' ?></processingEnvironment>
                                        <country>us</country>
                                        <language>en</language>
                                        <contractualRelationship>individual</contractualRelationship>
                                        <pointOfSaleIndicator>none</pointOfSaleIndicator>
                                </options>
                        </transactionControl>
                        <product>
                                <code>07000</code>
                                <subject>
                                        <number>1</number>
                                        <subjectRecord>
                                                <indicative>
                                                        <name>
                                                                <person>
                                                                        <alsoReportedAs>false</alsoReportedAs>
                                                                        <first><?= strtoupper($personArr['firstName']) ?></first>
                                                                        <middle />
                                                                        <last><?= strtoupper($personArr['lastName']) ?></last>
                                                                        <generationalSuffix />
                                                                </person>
                                                        </name>
                                                        <address>
                                                                <status>current</status>
                                                                <street>
                                                                        <unparsed><?= strtoupper($personArr['address']) ?></unparsed>
                                                                </street>
                                                                <location>
                                                                        <city><?= strtoupper($personArr['city']) ?></city>
                                                                        <state><?= strtoupper($personArr['state']) ?></state>
                                                                        <zipCode><?= trim($personArr['zip']) ?></zipCode>
                                                                </location>
                                                        </address>
                                                        <socialSecurity>
                                                                <number><?= $personArr['ssn'] ?></number>
                                                        </socialSecurity>
                                                        <dateOfBirth><?= date("Y-m-d", strtotime($personArr['dob'])) ?></dateOfBirth>
                                                        <phone>
                                                                <number>
                                                                        <type>standard</type>
                                                                        <areaCode></areaCode>
                                                                        <exchange></exchange>
                                                                        <suffix></suffix>
                                                                </number>
                                                        </phone>
                                                </indicative>
                                                <custom>
                                                        <credit>
                                                                <creditSummary>
                                                                        <returnAccountRating>true</returnAccountRating>
                                                                </creditSummary>
                                                        </credit>
                                                </custom>
                                       </subjectRecord>
                                </subject>
                                <responseInstructions>
                                        <returnErrorText>true</returnErrorText>
                                </responseInstructions>
                        </product>

                </creditBureau>
        </productrequest>
</xmlrequest>
