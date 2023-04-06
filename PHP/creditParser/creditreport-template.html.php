<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Credit Report</title>
<style type="text/css">
 body{
    background-color:#fff;
    margin-top:0;
    margin-left:0
}
#page_1{
    position:relative;
    margin:18px 0 19px;
    width:800px
}
.halfHeading,.heading{
    margin-top:4px;
    border:1px solid #000;
    background:#d9d9d9
}
.heading{
    width:727px
}
.halfHeading{
    width:363px
}
.p0,.p1{
    margin-top:0
}
.applicant,.employment{
        width: 363px;
    display:inline;
    vertical-align:top
position:relative;
float:left;
}
.dclr{
    clear:both;
    float:none;
    height:1px;
    margin:0;
    padding:0
}
.p0{
    text-align:center;
    margin-bottom:0
}
.p1,.p2{
    text-align:left;
    margin-bottom:0
}
.p1{
    white-space:nowrap
}
.p2{
    margin-top:10px
}
.p15{
    text-align:center;
    margin-top:0;
    margin-bottom:0
}
.t2,table{
    width:727px;
    margin-top:4px
}
.ft0{
    font:11px Helvetica;
    line-height:14px
}
.ft1{
    font:700 16px Helvetica;
    line-height:19px
}
.ft2{
    font:700 11px Helvetica;
    line-height:14px
}
.ft3{
    font:12px 'Lucida Console';
    line-height:2px
}
.ft15,table{
    font:12px 'Lucida Console'
}
.ft15{
    line-height:12px
}

table{
    line-height:12px;
    border-collapse:collapse
}
.t2,.t3,.t4{
    font:12px 'Lucida Console'
}
.tableHeading{
    background:#d9d9d9;
    border:1px solid #000
}
.t3{
    width:651px;
    margin-left:8px;
    margin-top:12px
}
.t4{
    width:734px;
    margin-top:8px
}
.t6{
    width:739px;
    margin-top:3px;
    font:11px Helvetica
}
.tr1{
    vertical-align:bottom
}
.td1,.tr2{
    vertical-align:top
}
.td2{
    vertical-align:bottom;
}
.td3{
    border-right:1px solid #000
}
.td4{
    border-bottom:1px solid #000
}
.tdbg{
    background:#d9d9d9
}

</style>
</head>
<body>
<div>
</div>
<div id="page_1">
	<div class="heading">
		<p class="p0 ft1">CREDIT REPORT</P>
	</div>
	<p class="p2 ft2">DATE RECEIVED: <span class="ft0"><?= $input['cr']['creditReportDate'] ?></span></p>
	<div class="heading">
		<p class="p15 ft2">INPUT DATA</P>
	</div>
	<div class="applicant">
		<P class="p1 ft2">NAME: <span class="ft0"><?= $input['inputDataArr']['firstName'] ?> <?= $input['inputDataArr']['lastName'] ?></span></p>
		<p class="p1 ft2">SSN: <span class="ft0"><?= $input['inputDataArr']['ssn'] ?></span></p>
		<p class="p1 ft2">DOB: <span class="ft0"><?= $input['inputDataArr']['dob'] ?></span></p>

	</div>
    <div class="employment">
		<p class="p2 ft2">ADDRESS:</p>
		<p class="p1 ft0"><?= $input['inputDataArr']['address'] ?><br>
            <?= $input['inputDataArr']['city'] ?> <?= $input['inputDataArr']['state'] ?> <?= $input['inputDataArr']['zip'] ?><br><br>
        </p>
	</div>
	<div style="clear:both;"></div>
	<div class="applicant">
		<div class="halfHeading">
			<P class="p0 ft0">APPLICANT</P>
		</div>
		<P class="p1 ft2">NAME: <span class="ft0"><?= $input['main']['name'] ?></span></p>
		<? if (!empty($input['aka'])):
			foreach ($input['aka'] as $k => $a): ?>
				<p class="p1 ft2">Also known as: <span class="ft0"><?= $a[0]; ?></span></p>
			<? endforeach;
		endif; ?>
		<p class="p1 ft2">SSN: <span class="ft0"><?= $input['cr']['ssn'] ?></span></p>
		<? if ($input['cr']['dob']): ?>
			<p class="p1 ft2">DOB: <span class="ft0"><?= $input['cr']['dob'] ?></span></p>
		<? endif; ?>
			<p class="p2 ft2">CURRENT ADDRESS:</p>
			<p class="p1 ft0"><?= $input['cAdd']['address'] ?><br>
			<?= $input['cAdd']['city'] ?> <?= $input['cAdd']['state'] ?> <?= $input['cAdd']['zip'] ?></p>
		<? foreach ($input['pAdd'] as $address):
			?>
			<p class="p2 ft2"><?= strtoupper(str_replace("Prev", " Prev", $address['status'])) ?> ADDRESS:</p>
			<p class="p1 ft0"><?= $address['address'] ?><br>
				<?= $address['city'] ?> <?= $address['state'] ?> <?= $address['zip'] ?></p>
            <? if (isset($address['dateReported'])): ?>
			<p class="p1 ft2">REPORTED: <span class="ft0"><?= date("m/d/Y", strtotime($address['dateReported'])) ?></span></p>
            <? endif; ?>
		<? endforeach; ?>
	</div>
	<div class="employment">
		<div class="halfHeading">
			<P class="p0 ft0">EMPLOYMENT INFORMATION</P>
		</div>
		<? foreach ($input['eInf'] as $emp):
            
            ?>
			<p class="p2 ft0"><?= (isset($emp['nameUnparsed'][0]) ? $emp['nameUnparsed'][0] : ''); ?></P>
			<? if (isset($emp['occupation'][0])): ?><p class="p1 ft2">Occupation: <span class="ft0"><?= $emp['occupation'][0] ?></span></p><? endif; ?>
			<p class="p1 ft0"><SPAN class="ft2">Since: </SPAN><?= (isset($emp['dateOnFileSince'][0])) ? date("m/d/Y", strtotime($emp['dateOnFileSince'][0])) : ""; ?></P>
			<? if (isset($emp['dateHired'][0])): ?><p class="p1 ft2">Hired: <span class="ft0"><?= date("m/d/Y", strtotime($emp['dateHired'][0])) ?></span></p><? endif; ?>
		<? endforeach; ?>
	</div>
	<div class="dclr"></div>
	<div class="heading">
		<p class="p15 ft2">CREDIT SUMMARY</P>
	</div>
	<? 	$summ = $input['summ'];
		if (!empty($summ)): ?>
		<table class="t2 p1 ft15">
			<tr>
				<td>&nbsp;</td>
				<td>Credit Limit</td>
				<td>Balance</td>
				<td>Est. Spend</td>
				<td>Past Due</td>
				<td>Current Due</td>
				<td>Prior Due</td>
				<td>Recent Payment</td>
			</tr>
			<tr>
				<td>Revolving</td>
				<td><?= $summ['rev_creditLimit'] ?></td>
				<td><?= $summ['rev_currentBalance'] ?></td>
				<td><?= $summ['rev_estimatedSpend'] ?></td>
				<td><?= $summ['rev_pastDue'] ?></td>
				<td><?= $summ['rev_currentPaymentDue'] ?></td>
				<td><?= $summ['rev_priorPaymentDue'] ?></td>
				<td><?= $summ['rev_mostRecentPaymentAmount'] ?></td>
			</tr>
			<tr>
				<td>Installment</td>
				<td>-</td>
				<td><?= $summ['ins_currentBalance'] ?></td>
				<td>-</td>
				<td><?= $summ['ins_pastDue'] ?></td>
				<td><?= $summ['ins_currentPaymentDue'] ?></td>
				<td><?= $summ['ins_priorPaymentDue'] ?></td>
				<td><?= $summ['ins_mostRecentPaymentAmount'] ?></td>
			</tr>
			<tr>
				<td>Mortgage</td>
				<td>-</td>
				<td><?= $summ['mor_currentBalance'] ?></td>
				<td>-</td>
				<td><?= $summ['mor_pastDue'] ?></td>
				<td><?= $summ['mor_currentPaymentDue'] ?></td>
				<td><?= $summ['mor_priorPaymentDue'] ?></td>
				<td><?= $summ['mor_mostRecentPaymentAmount'] ?></td>
			</tr>
			<tr>
				<td>Other</td>
				<td><?= $summ['oth_creditLimit'] ?></td>
				<td><?= $summ['oth_currentBalance'] ?></td>
				<td>-</td>
				<td><?= $summ['oth_pastDue'] ?></td>
				<td><?= $summ['oth_currentPaymentDue'] ?></td>
				<td><?= $summ['oth_priorPaymentDue'] ?></td>
				<td><?= $summ['oth_mostRecentPaymentAmount'] ?></td>
			</tr>
			<tr>
				<td>Total</td>
				<td><?= $summ['tot_creditLimit'] ?></td>
				<td><?= $summ['tot_currentBalance'] ?></td>
				<td><?= $summ['tot_estimatedSpend'] ?></td>
				<td><?= $summ['tot_pastDue'] ?></td>
				<td><?= $summ['tot_currentPaymentDue'] ?></td>
				<td><?= $summ['tot_priorPaymentDue'] ?></td>
				<td><?= $summ['tot_mostRecentPaymentAmount'] ?></td>
			</tr>
		</table>
		<br>
		<!-- Record Counts -->
		<table class="p1 ft15">
			<tr>
				<td>#Inquiries</td>
				<td class=""><?= $summ['totalInquiryCount'] ?></td>
				<td>#Public Records</td>
				<td><?= $summ['publicRecordCount'] ?></td>
				<td>Total Trade Count</td>
				<td><?= $summ['totalTradeCount'] ?></td>
				<td>Open Trade Count</td>
				<td><?= $summ['openTradeCount'] ?></td>
			</tr>
			<tr>
				<td>Open Rev. Trade Count</td>
				<td><?= $summ['openRevolvingTradeCount'] ?></td>
				<td>Open Inst. Trade Count</td>
				<td><?= $summ['openInstallmentTradeCount'] ?></td>
				<td>Open Mort. Trade Count</td>
				<td><?= $summ['openMortgageTradeCount'] ?></td>
				<td>Open Other Trade Count</td>
				<td><?= $summ['openOtherTradeCount'] ?></td>
			</tr>
			<tr>
				<td>Coll. Trade Count</td>
				<td><?= $summ['collectionCount'] ?></td>
				<td>Inst. Trade Count</td>
				<td><?= $summ['installmentTradeCount'] ?></td>
				<td>Rev. Trade Count</td>
				<td><?= $summ['revolvingTradeCount'] ?></td>
				<td>Mort. Trade Count</td>
				<td><?= $summ['mortgageTradeCount'] ?></td>
			</tr>
			<tr>
				<td>Other Trade Count</td>
				<td><?= $summ['otherTradeCount'] ?></td>
   				<td>Negative Trade Count</td>
				<td><?= $summ['negativeTradeCount'] ?></td>
				<td>Historical Negative Trade Count</td>
				<td><?= $summ['historicalNegativeTradeCount'] ?></td>
				<td>Hist. Negative Occurrences Count</td>
				<td><?= $summ['historicalNegativeOccurrencesCount'] ?></td>
			</tr>
			<tr>
				<td>Bankruptcies</td>
				<td><?= $summ['bankruptcyCount'] ?></td>
				<td>In File Since</td>
				<td><?= $summ['inFileSinceDate'] ?></td>
				<td>Satisfactories</td>
				<td><?= $summ['satisfactoryCount'] ?></td>
				<td>Auth. User Count</td>
				<td><?= $summ['authorizedUserCount'] ?></td>
			</tr>
		
		</table>
		<br>

	<? else: ?>
		<p class="ft2">No Credit Summary</p>
	<? endif; ?>
	<!-- Scoring -->
	<div class="heading">
		<p class="p15 ft2">SCORING</P>
	</div>

	<? $score = $input['score'];
		if (!empty($score)):?>
		<p class="p2 ft2">Score: <?= $input['cr']['score'] ?></p>
		
		<? if (strlen($input['cr']['scoreReason'][0]) > 0): ?>
			<p class="p2 ft2"><?= $input['cr']['scoreReason'] ?></p>
		<? endif; ?>
		<? if (isset($score['scoreModel']['score']['derogatoryAlert']) && $score['scoreModel']['score']['derogatoryAlert'] == "true"): ?>
			<p class="ft2">Derogatory Alert</p>
		<? endif; ?>
		<? if (isset($score['scoreModel']['score']['fileInquiriesImpactedScore']) && $score['scoreModel']['score']['fileInquiriesImpactedScore'] == "true"): ?>
			<p class="ft2">File Inquiries Impacted Score</p>
		<? endif; ?>
		<table>
			<? if ($input['cr']['factors']): ?>
					<tr>
                        <td class="ft0"><pre><?= str_replace("Array", "", $input['cr']['factors']) ?></pre></td>
					</tr>
			<? endif; ?>
		</table><br>
	
	
	<? else: ?>
		<p class="ft2">No Score</p>
	<? endif; ?>
    <?php
      if (strlen($input['cr']['decisionText']) > 0):
        $decisionText = explode(",",$input['cr']['decisionText']);
        ?>
	<div class="heading">
		<P class="p15 ft2">MESSAGES</P>
	</div>
	<table>
		<? foreach ($decisionText as $decisionMessages): ?>
			<tr>
				<td><?= $decisionMessages ?></td>
			</tr>
		<? endforeach; ?>
	</table>
	<?
    endif;
	if (in_array("true", $input['creditDataStatus'])): ?>
		<div class="heading">
			<P class="p15 ft2">CREDIT DATA STATUS</P>
		</div>
		<table>
			<? if ($input['creditDataStatus']['suppressed'] == "true"): ?>
				<tr>
					<td>SUPPRESSED:</td>
					<td><?= $input['creditDataStatus']['suppressed'] ?></td>
				</tr>
			<? endif; ?>
			<? if ($input['creditDataStatus']['doNotPromote']['indicator'] == "true"): ?>
				<tr>
					<td>Do Not Promote:</td>
					<td><?= $input['creditDataStatus']['doNotPromote']['indicator'] ?></td>
				</tr>
			<? endif; ?>
			<? if ($input['creditDataStatus']['freeze']['indicator'] == "true"): ?>
				<tr>
					<td>Freeze:</td>
					<td><?= $input['creditDataStatus']['freeze']['indicator'] ?></td>
				</tr>
			<? endif; ?>
			<? if ($input['creditDataStatus']['minor'] == "true"): ?>
				<tr>
					<td>Minor:</td>
					<td><?= $input['creditDataStatus']['minor'] ?></td>
				</tr>
			<? endif ?>
			<? if ($input['creditDataStatus']['disputed'] == "true"): ?>
				<tr>
					<td>Disputed:</td>
					<td><?= $input['creditDataStatus']['disputed'] ?></td>
				</tr>
			<? endif; ?>
		
		</table>
	<? endif; ?>
	
	<?
    if (!empty($input['consumerStatement'])): ?>
		<div class="heading">
			<P class="p15 ft2">CONSUMER STATEMENT</P>
		</div>
		<table>
            <? //print_r($consumerStatement);
                ?>
			<? foreach ($input['consumerStatement']['consumerStatement'] as $k => $v): 
					if (is_array($v)):
						foreach ($v as $k1 => $v1): ?>
				<tr>
					<td><?=ucfirst($k1)?>: <?= $v1 ?></td>
				</tr>
			<?		endforeach;
				else: ?>
				<tr>
					<td><?=ucfirst($k)?>: <?= $v ?></td>
				</tr>
				<? endif;
				endforeach; ?>
		</table>
	<? endif; ?>
	
   <?
   $ofacMessage = $input['ofacMessage']; 
   if (!empty($ofacMessage)) {
       ?>
        <div class="heading">
                <P class="p15 ft2">OFAC NAME SCREEN</P>
        </div>
        <table>
            <tr>
                <td><span class="ft2">
            <?php
            if (!empty($ofacMessage['@attributes']['searchStatus']) && $ofacMessage['@attributes']['searchStatus'] != "clear") {
			$m = (isset($ofacMessage['message'])) ? $ofacMessage['message']['text'] : '_';
				echo "Ofac Name Screen: " . $ofacMessage['@attributes']['searchStatus'] . " - " . $m;
            } else {
				echo "Ofac Name Screen Clear ";
		    }
            ?>
                    </span></td>
            </tr>
        </table>
    <? } //close OFAC ?>

	<?
	$highRiskFraud = $input['highRiskFraud'];
    if (!empty($highRiskFraud)): ?>
		<div class="heading">
			<P class="p15 ft2">HIGH RISK FRAUD MESSAGES</P>
		</div>
		<table>
            <tr>
                <td class="ft2">
            <?php
            if (isset($highRiskFraud['@attributes']['searchStatus'])):
                echo "Search Status: " . $highRiskFraud['@attributes']['searchStatus'];
            endif;
            ?>
                </td>
            </tr>
			<? /* Deceased is possible too, in a seperate set of XML */
			if (!empty($highRiskFraud['decease'])):
			?>
			<tr>
				<td class="ft2">DECEASED</td>
			</tr>
			<tr>
				<td>Date of Death: <?= $highRiskFraud['decease']['dateOfDeath'] ?></td>
			<tr>
			
			<? endif; ?>
           <?
			$fraudToProcess = $input['fraudToProcess'];
			foreach ($fraudToProcess as $fm):
				foreach($fm as $k => $v):
				?><tr><td><?
				if ($k == 'code'):
					echo '<span class="ft2">'.$v . ': </span>' . $fraudCodes[$v]."<br>";
				elseif ($k == "custom"):
					foreach ($v as $k2 => $v2):
						echo $k2 .": ".$v2."<br>";
					endforeach;
				endif;
				?></td></tr><?
				endforeach;
			endforeach;
			?>

		</table>
	<? endif; ?>
	
	<div class="heading">
		<P class="p15 ft2">PUBLIC RECORD INFORMATION</P>
	</div>
	<? if (count($input['pr']) > 0) { ?>
		<table>
			<? foreach ($input['pr'] as $publicRecord) { ?>
				<tr class="tr2">
					<td class="td4"><?= $publicRecord['ecoa'] ?></td>
					<td class="td4"><?= $publicRecord['publicRecordType'] ?><br>
						<?= $publicRecord['industryCode'] ?><br>
						Case: <?= $publicRecord['docketNumber'] ?><br>
						Attorney: <?= $publicRecord['attorney'] ?><br>
						Plaintiff: <?= $publicRecord['plaintiff'] ?><br>
					</td>
					<td class="td4">Filed: <?= $publicRecord['dateFiled'] ?><br>
						Liab.: <?= $publicRecord['liabilities'] ?><br>
						Court: <?= $publicRecord['court'] ?><br>
						Paid: <?= $publicRecord['datePaid'] ?><br>
						Reported: <?= $publicRecord['dateReported'] ?><br>
					</td>
				</tr>
			<? } ?>
		</table>
		<br>
	<? } else { ?>
		<p class="ft2">No Public Records</p>
	<? } ?>
	<!-- Credit History -->
	<div class="heading">
		<P class="p15 ft2">CREDIT HISTORY</p>
	</div>
	<? if (!empty($input['tr'])) { ?>
		<table>
			<tr class="tdbg">
				<td class="td3">&nbsp;</td>
				<td class="td3">&nbsp;</td>
				<td class="td3">&nbsp;</td>
				<td class="td3">&nbsp;</td>
				<td class="td3">&nbsp;</td>
				<td colspan="2" class="td3"><p class="p1">Present Status</p></td>
				<td class="td3">&nbsp;</td>
				<td class="td3">&nbsp;</td>
				<td class="td3">&nbsp;</td>
				<td colspan="4" class="td3" style="text-align:center;border-bottom: 1px solid #000;">Historical Status</td>
			
			</tr>
			<tr class="tr1 tdbg">
				<td class="td3 td4">E<br>C<br>O<br>A</td>
				<td class="td3 td4">Creditor<br>Account No</td>
				<td class="td3 td4">Most<br>Recent</td>
				<td class="td3 td4">Opened</td>
				<td class="td3 td4">Limit<br>or<br>Highest</td>
				<td class="td3 td4" style="border-top: 1px solid #000;">Balance<br>Owing</td>
				<td class="td3 td4" style="border-top: 1px solid #000;">Amount<br>Past<br>Due</td>
				<td class="td3 td4">Count<br>Freq.<br>Amount</td>
				<td class="td3 td4">Update<br>Method</td>
				<td class="td3 td4">Type<br>Rating</td>
				<td class="td3 td4">Hist.</td>
				<td class="td3 td4">30<br>Days</td>
				<td class="td3 td4">60<br>Days</td>
				<td class="td3 td4">90<br>Days</td>
			</tr>
			<? 
			foreach ($input['tr'] as $ch) {
			    $shading = " tdbg";
   				if (($ch['late30Days'] + $ch['late60Days'] + $ch['late90Days']) == 0):
					$shading = '';
				endif;
			    ?>
				<tr class="tr2<?= $shading ?>">
					<td class="td3"><?= substr($ch['ecoaDesignator'], 0, 2) ?></td>
					<td class="td3 td4"><?= $ch['nameUnparsed'] ?><br><?= $ch['accountNumber'] ?><br>
						<?= (isset($ch['industryCode'])) ? wordwrap($industryCodes[$ch['industryCode']], 14, "<br>", true): ''; ?>
                        <br>
						<?= $ch['closedIndicator'] ?><br>
						<br><br>
                        <?
                        $remarkCodes = $ch['remarkCodes'];
                        if (!empty($remarkCodes[0])):
//                            print_r($remarkCodes);
                            foreach ($remarkCodes[0] as $k => $rcodes):
                                echo wordwrap($rcodes, 14, "<br>", true) . "<br>";
                            endforeach;
                        endif;?>
						<br>
                        <?
                        $remarkTypes = $ch['remarkTypes'];
                        if (!empty($remarkTypes[0])):
                            foreach ($remarkTypes[0] as $k => $rtypes):
                                echo wordwrap($rtypes, 14, "<br>", true) . "<br>";
                            endforeach;
                        endif; ?>
					</td>
					<td class="td3"><?= ($ch['mostRecentPaymentDate'] > '1000-01-01') ? date("n/y", strtotime($ch['mostRecentPaymentDate'])) : '' ?></td>
					<td class="td3"><?= ($ch['dateOpened'] > '1000-01-01') ? date("n/y", strtotime($ch['dateOpened'])) : '' ?></td>
					<td class="td3"><?= $ch['highLimit'] ?></td>
					<td class="td3"><?= $ch['currentBalance'] ?></td>
					<td class="td3"><?= $ch['pastDue'] ?></td>
					<td class="td3 td4"><?= $ch['paymentScheduleMonthCount'] ?><br>
						<?= $ch['paymentFrequency'] ?><br>
						<?= $ch['scheduledMonthlyPayment'] ?></td>
					<td class="td3"><?= substr($ch['updateMethod'], 0, 4) ?></td>
					<td class="td3 td4"><?= ucfirst($ch['portfolioType']) ?><br>
						<?= $ch['accountRating'] ?><br>
						<?= wordwrap($ch['accountTypeStr'], 14, "<br>", true); ?></td>
					<td><?= $ch['monthsReviewedCount'] ?></td>
					<td><?= $ch['late30Days'] ?></td>
					<td><?= $ch['late60Days'] ?></td>
					<td class="td3"><?= $ch['late90Days'] ?></td>
				</tr>
				<? //template changes depending on whether it's closed or not
//                (isset($ch->dateClosed)) ? "<br>Date Closed: " . date("n/y", strtotime((string)$ch->dateClosed)) : "0"
                ?>
				<? if ($ch['dateClosed'] <= "1000-01-01") { ?>
					<tr class="tr2<?= $shading ?>">
						<td class="td3 td4">&nbsp;</td>
						<td class="td3 td4">&nbsp;</td>
						<td class="td3 td4">&nbsp;</td>
						<td class="td3 td4">&nbsp;</td>
						<td class="td3 td4">&nbsp;</td>
						<td class="td3 td4">&nbsp;</td>
						<td class="td3 td4">&nbsp;</td>
						<td class="td4">&nbsp;</td>
						<td colspan="4" class="td3 td4"><?= wordwrap($ch['paymentPattern'], 18, "<br>", true); ?></td>
      
					</tr>
				<? } else { ?>
					<tr class="tr2<?= $shading ?>">
						<td class="td3 td4">&nbsp;</td>
						<td colspan="4" class="td3 td4"><br>Date Closed: <?= date("n/y", strtotime($ch['dateClosed'])) ?></td>
						<td class="td3 td4">&nbsp;</td>
						<td class="td3 td4">&nbsp;</td>
						<td class="td4">&nbsp;</td>
						<td colspan="4" class="td3 td4"><?= wordwrap($ch['paymentPattern'], 18, "<br>", true); ?></td>
					</tr>
				<? } //closing closedInfo layout change if ?>
			<? } //closing foreach loop through array ?>
		</table>
	<? } else { ?>
		<p class="ft2">No Credit History</p>
	<? } ?>
	
	
	<br>
	
	<!-- Collections -->
	<div class="heading">
		<p class="p15 ft2">Collections</p>
	</div>
	<?
    if (!empty($input['coll'])) { ?>
		<table>
			<tr class="tr1 tdbg">
				<td class="td3 td4">E<br>C<br>O<br>A</td>
				<td class="td3 td4">Creditor<br>Account No<br>Original</td>
				<td class="td3 td4">Remarks</td>
				<td class="td3 td4">Original<br>Balance</td>
				<td class="td3 td4">Past Due</td>
				<td class="td3 td4">Current Balance</td>
				<td class="td3 td4">Type<br>Amount</td>
				<td class="td3 td4">Rating</td>
				<td class="td3 td4">Date<br>Opened</td>
				<td class="td3 td4">Date<br>Effec</td>
				<td class="td3 td4">Date<br>Closed</td>
				<td class="td3 td4">Paid Out<br>First Delinq<br>Recent</td>
			</tr>
			<? foreach ($input['coll'] as $collection) { ?>
				<tr class="tr2">
					<td class="td3 td4"><?= $collection['ecoa'] ?></td>
					<td class="td3 td4"><?= $collection['creditor'] ?><br><?= $collection['account'] ?><br>
						<?= $collection['closedIndicator'] ?><br>
						<p class="p2 ft2">Original</p>
						<?= $collection['creditGrantor'] ?>  <?= $collection['creditorClass'] ?></td>
					<td class="td3 td4"><?= $collection['remarkCode'] ?>
						<br><?= $collection['remarkType'] ?></td>
					<td class="td3 td4"><?= $collection['originalBalance'] ?></td>
					<td class="td3 td4"><?= $collection['pastDue'] ?></td>
					<td class="td3 td4"><?= $collection['currentBalance'] ?></td>
					<td class="td3 td4"><?= $collection['portfolioType'] ?><br>
						<?= $collection['accountTypeStr'] ?></td>
					<td class="td3 td4"><?= $collection['accountRating'] ?></td>
					<td class="td3 td4"><?= $collection['dateOpened'] ?></td>
					<td class="td3 td4"><?= $collection['dateEffective'] ?></td>
					<td class="td3 td4"><?= $collection['dateClosed'] ?></td>
					<td class="td3 td4"><?= $collection['datePaidOut'] ?><br>
						<?= $collection['dateFirstDelinquent'] ?><br>
						<?= $collection['mostRecentPayment'] ?></td>
				</tr>
			<? } ?>
		</table>
		<br>
	<? } else { ?>
		<p class="ft2">No Collections</p>
	<? } ?>
	
	<!-- Inquiries -->
	<div class="heading">
		<p class="p15 ft2">Inquiries</p>
	</div>
	<? if (!empty($input['inq'])) { ?>
		<table class="t2 p1 ft15">
			<tr class="tdbg">
				<td class="td3 td4">ECOA</td>
				<td class="td3 td4">Name</td>
				<td class="td3 td4">Member Code</td>
				<td class="td3 td4">Industry</td>
				<td class="td3 td4">Date</td>
			</tr>
			
			<? foreach ($input['inq'] as $inquiry) { ?>
				<tr class="tr2">
					<td><?= $inquiry['ecoa'] ?></td>
					<td><?= $inquiry['nameUnparsed'][0] ?></td>
					<td><?= $inquiry['memberCode'][0] ?></td>
					<td><?= $inquiry['industryCode'] ?></td>
					<td><?= (isset($inquiry['dateInquiry'])) ? date("m/d/Y", strtotime($inquiry['dateInquiry'])) : ""; ?></td>
				</tr>
			<? } ?>
		
		</table>
	<? } else { ?>
		<p class="ft2">No Inquiries</p>
	<? } ?>

</div>
</body>
</html>