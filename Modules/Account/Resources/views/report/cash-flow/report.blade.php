@php
$voucher_data = DB::table('chart_of_accounts as coa')
                ->select('t.voucher_no')
                ->leftjoin('transactions as t','coa.id','=','t.chart_of_account_id')
                ->where('coa.code','like','1020101%')
                ->get();
$vouchers = [];
if(!$voucher_data->isEmpty())
{
    foreach ($voucher_data as $key => $value) {
        array_push($vouchers,$value->voucher_no);
    }
}
@endphp
<div class="row">
    <style>


        @media print {
    
            body,
            html {
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                font-family: sans-serif;
                /* font-size: 12px !important; */
                margin-bottom: 100px !important;
            }
    
            .m-0 {
                margin: 0 !important;
            }
    
            h1,
            h2,
            h3,
            h4,
            h5,
            h6 {
                margin: 0 !important;
            }
    
            .no_screen {
                display: block !important;
            }
    
            .no_print {
                display: none;
            }
    
            a {
                content: none !important;
                text-decoration: none !important;
                color: #036 !important;
            }
    
            .text-center {
                text-align: center !important;
            }
    
            .text-left {
                text-align: left !important;
            }
    
            .text-right {
                text-align: right !important;
            }
    
            .float-left {
                float: left !important;
            }
    
            .float-right {
                float: right !important;
            }
    
            .text-bold {
                font-weight: bold !important;
            }
    
    
            /* .invoice>div:last-child {
                page-break-before: always
            } */
            .hidden-print {
                display: none !important;
            }
            .dashed-border{
                width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
            }
        }
    
        @page {
            /* size: auto; */
            margin: 5mm 5mm;
    
        }
    </style>
    <div class="col-sm-12 col-md-12">
        <div class="panel panel-bd lobidrag">

            <div id="printArea">
                <div class="panel-body">
                    <div>
                      <table width="100%" style="margin:0;padding:0;">
                        <tr>
                            <td width="100%" class="text-center">
                                <h3 style="margin:0;">{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</h3>
                                @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif
                                @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
                                <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
                            </td>
                        </tr>
                      </table>
                        <table width="100%" class="table_boxnew" style="padding: 5px">
                            <tr>
                                <td colspan="3" align="center">
                                    <h4 style="margin: 0;">Cash Flow Statement </h4>
                                </td>
                            </tr>
                            <tr class="table_head">
                                <td colspan="3" align="center" style="padding-left:10px"><b>From {{ $start_date }} To {{ $end_date }}</b></td>
                            </tr>
                            <tr class="table_head">
                                <td width="73%" height="29" align="center"
                                    style="padding-left:10px; border:1px solid #000;">
                                    <b>Particulars</b></td>
                                <td width="2%">&nbsp;</td>
                                <td width="30%" align="right"
                                    style="padding-left:10px; padding-right:10px;  border:1px solid #000;;">
                                    <b>Amount</b></td>
                            </tr>
                            <tr class="table_head">
                                <td colspan="3" style="padding-left:10px">
                                    <strong>Opening Cash and Equivalent:</strong></td>
                            </tr>
                            <?php

                        $oResultAsset = DB::table('chart_of_accounts')
                                      ->where([['transaction',1],['type','A'],['status',1]])
                                      ->where('code', 'like','1020101%')
                                      ->get();
                
                        $TotalOpening=0;
                        for($i=0;$i<count($oResultAsset);$i++)
                        {
                          $oResultAmountPre = DB::table('transactions as t')
                                            ->selectRaw('SUM(t.debit) - SUM(t.credit) AS amount')
                                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                            ->where('coa.code','like',$oResultAsset[$i]->code.'%')
                                            ->where('t.approve',1)
                                            ->whereDate('t.voucher_date','>=',$start_date)
                                            ->whereDate('t.voucher_date','<=',$end_date)
                                            ->first();
                          if($oResultAmountPre->amount!=0)
                          {
                      ?>
                            <tr>
                                <td align="left" style="padding-left:10px"><?php echo $oResultAsset[$i]->name; ?>
                                </td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-left: solid 1px #000; border-right:solid 1px #000;<?php if($TotalOpening==0) echo "border-top: solid 1px #000;"; ?>">
                                    <?php 
                                    $Total=$oResultAmountPre->amount;
                                    echo number_format($Total);
                            
                                    $TotalOpening+=$Total; 
                                ?>
                                </td>
                            </tr>
                            <?php
                          }
                        }
                      ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-top: solid 1px #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px;padding-right:10px"><strong>Total Opening Cash
                                        & Cash Equivalent</strong></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-top: solid 1px #000; border-bottom: solid 1px #000;">
                                    <strong><?php echo number_format($TotalOpening); ?></strong></td>
                            </tr>
                            <tr class="table_head">
                                <td colspan="3" style="padding-left:10px;text-decoration:underline"><b>Cashflow from Operating Activities</b></td>
                            </tr>
                            <?php
                          $TotalCurrAsset=0;
                          $oResultCurrAsset = DB::table('chart_of_accounts')
                                      ->where([['general_ledger',1],['status',1]])
                                      ->where('code', 'like','102%')
                                      ->where('code', 'not like','1020101%')
                                      ->where('code', '<>','102')
                                      ->get();

                          
                          for($s=0;$s<count($oResultCurrAsset);$s++)
                          {

                            
                            $oResultAmount = DB::table('transactions as t')
                                            ->selectRaw('SUM(t.credit) - SUM(t.debit) AS amount')
                                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                            ->where('coa.code','like',$oResultCurrAsset[$s]->code.'%')
                                            ->where('t.approve',1)
                                            ->whereDate('t.voucher_date','>=',$start_date)
                                            ->whereDate('t.voucher_date','<=',$end_date)
                                            ->whereIn('t.voucher_no',$vouchers)
                                            ->first();

                            if($oResultAmount->amount!=0)
                            {
                              ?>
                              <tr>
                                  <td align="left" style="padding-left:10px">
                                      <?php echo $oResultCurrAsset[$s]->name; ?></td>
                                  <td>&nbsp;</td>
                                  <td align="right"
                                      style="padding-right:10px; border-left:solid 1px #000; border-right:solid 1px #000;<?php if($TotalCurrAsset==0) echo "border-top: solid 1px #000;"; ?>">
                                      <?php 
                                            $Total=$oResultAmount->amount;
                                            echo number_format($Total);
                                            $TotalCurrAsset+=$Total; 
                                        ?>
                                  </td>
                              </tr>
                            <?php
                            }
                          }

                      $oResultAmount = DB::table('transactions as t')
                                            ->selectRaw('SUM(t.credit) - SUM(t.debit) AS amount')
                                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                            ->where('coa.code','like','4%')
                                            ->where('t.approve',1)
                                            ->whereDate('t.voucher_date','>=',$start_date)
                                            ->whereDate('t.voucher_date','<=',$end_date)
                                            ->whereIn('t.voucher_no',$vouchers)
                                            ->first();

                      if($oResultAmount->amount!=0)
                      {
                        ?>
                            <tr>
                                <td align="left" style="padding-left:10px">Payment for Other Operating Activities</td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-left:solid 1px #000; border-right:solid 1px #000;">
                                    <?php 
                                  $Total=$oResultAmount->amount;
                                  echo number_format($Total,2);
                                  $TotalCurrAsset+=$Total; 
                              ?>
                                </td>
                            </tr>
                            <?php
                    }
                    ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-top: solid 1px #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px;padding-right:10px"><strong>Cash generated
                                        from Operating Activites before Changing in Opereating Assets &amp;
                                        Liabilities</strong></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-top: solid 1px #000; border-bottom: solid 1px #000;">
                                    <strong><?php echo number_format($TotalCurrAsset); ?></strong></td>
                            </tr>

                            <tr class="table_head">
                                <td colspan="3" style="padding-left:10px;text-decoration:underline"><b>Cashflow from Non
                                        Operating Activities</b></td>
                            </tr>
                            <?php
                        $TotalCurrAssetNon=0;
                        $oResultCurrAsset = DB::table('chart_of_accounts')
                                      ->where([['general_ledger',1],['status',1]])
                                      ->where('code', 'like','3%')
                                      ->get();

                        for($s=0;$s<count($oResultCurrAsset);$s++)
                        {

                        $oResultAmount = DB::table('transactions as t')
                                            ->selectRaw('SUM(t.credit) - SUM(t.debit) AS amount')
                                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                            ->where('coa.code','like',$oResultCurrAsset[$s]>code.'%')
                                            ->where('t.approve',1)
                                            ->whereDate('t.voucher_date','>=',$start_date)
                                            ->whereDate('t.voucher_date','<=',$end_date)
                                            ->whereIn('t.voucher_no',$vouchers)
                                            ->first();

                        if($oResultAmount->amount!=0)
                        {
                      ?>
                            <tr>
                                <td align="left" style="padding-left:10px">
                                    <?php echo $oResultCurrAsset[$s]->name; ?></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-left:solid 1px #000; border-right:solid 1px #000;<?php if($TotalCurrAssetNon==0) echo "border-top: solid 1px #000;"; ?>">
                                    <?php 
                            $Total=$oResultAmount->amount;
                            echo number_format($Total);
                            $TotalCurrAssetNon+=$Total; 
                        ?>
                                </td>
                            </tr>
                            <?php
                          }
                        }
                      ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-top: solid 1px #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px;padding-right:10px"><strong>Cash generated
                                        from Non Operating Activites before Changing in Opereating Assets &amp;
                                        Liabilities</strong></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-top: solid 1px #000; border-bottom: solid 1px #000;">
                                    <strong><?php echo number_format($TotalCurrAssetNon); ?></strong></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class="table_head">
                                <td align="left" style="padding-left:10px;padding-right:10px"><strong>Increase/Decrease
                                        in Operating Assets &amp; Liabilites</strong></td>
                                <td>&nbsp;</td>
                                <td align="right" style="padding-right:10px">&nbsp;</td>
                            </tr>

                            <?php
                    $TotalCurrLiab=0;
                    $oResultLiab = DB::table('chart_of_accounts')
                                      ->where([['general_ledger',1],['status',1]])
                                      ->where('code', 'like','20101%')
                                      ->where('code', '<>','20101')
                                      ->get();

                    for($t=0;$t<count($oResultLiab);$t++)
                    {

                    $oResultAmount =  DB::table('transactions as t')
                                            ->selectRaw('SUM(t.credit) - SUM(t.debit) AS amount')
                                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                            ->where('coa.code','like',$oResultLiab[$t]->code.'%')
                                            ->where('t.approve',1)
                                            ->whereDate('t.voucher_date','>=',$start_date)
                                            ->whereDate('t.voucher_date','<=',$end_date)
                                            ->whereIn('t.voucher_no',$vouchers)
                                            ->first();

                    if($oResultAmount->amount!=0)
                    {
                      ?>
                            <tr>
                                <td align="left" style="padding-left:10px"><?php echo $oResultLiab[$t]->name; ?>
                                </td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-left: solid 1px #000; border-right:solid 1px #000;<?php if($TotalCurrLiab==0) echo "border-top: solid 1px #000;"; ?>">
                                    <?php 
                                  $Total=$oResultAmount->amount;
                                  echo number_format($Total);
                                  $TotalCurrLiab+=$Total;
                              ?>
                                </td>
                            </tr>
                            <?php
                  }
                        }
                      ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-top: solid 1px #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px;padding-right:10px"><strong>Total
                                        Increase/Decrease</strong></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-top: solid 1px #000; border-bottom: solid 1px #000;">
                                    <strong><?php echo number_format($TotalCurrLiab); ?></strong></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px;padding-right:10px"><strong>Net Cash From
                                        Operating/Non Operating Activities</strong></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-top: solid 1px #000; border-bottom: solid 1px #000;">
                                    <strong><?php echo number_format($TotalCurrAsset+$TotalCurrAssetNon+$TotalCurrLiab); ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class="table_head">
                                <td colspan="3" style="padding-left:10px;text-decoration:underline"><b>Cash Flow from
                                        Investing Activities</b></td>
                            </tr>
                            <?php
                      $TotalNonCurrAsset=0;
                      $oResultNonCurrAsset = DB::table('chart_of_accounts')
                                      ->where([['general_ledger',1],['status',1]])
                                      ->where('code', 'like','101%')
                                      ->where('code', '<>','101')
                                      ->get();

                      for($t=0;$t<count($oResultNonCurrAsset);$t++)
                      {
                      $oResultAmount = DB::table('transactions as t')
                                            ->selectRaw('SUM(t.debit) - SUM(t.credit) AS amount')
                                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                            ->where('coa.code','like',$oResultNonCurrAsset[$t]->code.'%')
                                            ->where('t.approve',1)
                                            ->whereDate('t.voucher_date','>=',$start_date)
                                            ->whereDate('t.voucher_date','<=',$end_date)
                                            ->whereIn('t.voucher_no',$vouchers)
                                            ->first();

                      if($oResultAmount->amount!=0)
                      {
                      ?>
                            <tr>
                                <td align="left" style="padding-left:10px">
                                    <?php echo $oResultNonCurrAsset[$t]->name; ?></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-left: solid 1px #000; border-right:solid 1px #000;<?php if($TotalNonCurrAsset==0) echo "border-top: solid 1px #000;"; ?>">
                                    <?php 
                                  $Total=$oResultAmount->amount;
                                  echo number_format($Total,2);
                                  $TotalNonCurrAsset+=$Total;
                              ?>
                                </td>
                            </tr>
                            <?php
                  }
                        }
                      ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-top: solid 1px #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px;padding-right:10px"><strong>Net Cash Used
                                        Investing Activities</strong></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-top:solid 1px #000; border-bottom: solid 1px #000;">
                                    <strong><?php echo number_format($TotalNonCurrAsset); ?></strong></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>

                            <tr class="table_head">
                                <td colspan="3" style="padding-left:10px;text-decoration:underline"><b>Cash Flow from
                                        Financing Activities</b></td>
                            </tr>
                            <?php
                      $TotalNonCurrLiab=0;
                      $oResultNonCurrLiab =  DB::table('chart_of_accounts')
                                      ->where([['general_ledger',1],['status',1]])
                                      ->where('code', 'like','20102%')
                                      ->get();

                      for($t=0;$t<count($oResultNonCurrLiab);$t++)
                      {
                      $oResultAmount = DB::table('transactions as t')
                                            ->selectRaw('SUM(t.credit) - SUM(t.debit) AS amount')
                                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                            ->where('coa.code','like',$oResultNonCurrLiab[$t]->code.'%')
                                            ->where('t.approve',1)
                                            ->whereDate('t.voucher_date','>=',$start_date)
                                            ->whereDate('t.voucher_date','<=',$end_date)
                                            ->whereIn('t.voucher_no',$vouchers)
                                            ->first();

                      if($oResultAmount->amount!=0)
                      {
                          ?>
                            <tr>
                                <td align="left" style="padding-left:10px">
                                    <?php echo $oResultNonCurrLiab[$t]->name; ?></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-left: solid 1px #000; border-right:solid 1px #000;<?php if($TotalNonCurrLiab==0) echo "border-top: solid 1px #000;"; ?>">
                                    <?php 
                                  $Total=$oResultAmount->amount;
                                  echo number_format($Total);
                                  $TotalNonCurrLiab+=$Total;
                              ?>
                                </td>
                            </tr>
                            <?php
                        }
                      }
                      ?>
                            <?php
                      $TotalFund=0;

                      $oResultFund = DB::table('chart_of_accounts')
                                      ->where([['general_ledger',1],['status',1]])
                                      ->where('code', 'like','202%')
                                      ->get();


                      for($t=0;$t<count($oResultFund);$t++)
                      {
                      $oResultAmount = DB::table('transactions as t')
                                            ->selectRaw('SUM(t.credit) - SUM(t.debit) AS amount')
                                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                            ->where('coa.code','like',$oResultFund[$t]->code.'%')
                                            ->where('t.approve',1)
                                            ->whereDate('t.voucher_date','>=',$start_date)
                                            ->whereDate('t.voucher_date','<=',$end_date)
                                            ->whereIn('t.voucher_no',$vouchers)
                                            ->first();

                      if($oResultAmount->amount!=0)
                      {
                      ?>
                            <tr>
                                <td align="left" style="padding-left:10px"><?php echo $oResultFund[$t]->name; ?>
                                </td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-left: solid 1px #000; border-right:solid 1px #000;">
                                    <?php 
                                  $Total=$oResultAmount->amount;
                                  echo number_format($Total,2);
                                  $TotalFund+=$Total;
                              ?>
                                </td>
                            </tr>
                            <?php
                  }
                        }
                      ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-top: solid 1px #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px;padding-right:10px"><strong>Net Cash Used
                                        Financing Activities</strong></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-top:solid 1px #000; border-bottom:solid 1px #000;">
                                    <strong><?php echo number_format($TotalFund+$TotalNonCurrLiab); ?></strong></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px;"><strong>Net Cash Inflow/Outflow (Profit Loss
                                        <?php echo number_format($TotalCurrAsset+$TotalCurrAssetNon+$TotalCurrLiab+$TotalNonCurrAsset+$TotalFund+$TotalNonCurrLiab); ?>)</strong>
                                </td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-top: solid 1px #000; border-bottom: solid 1px #000;">
                                    <strong><?php echo number_format($TotalCurrAsset+$TotalCurrAssetNon+$TotalCurrLiab+$TotalNonCurrAsset+$TotalFund+$TotalNonCurrLiab+$TotalOpening); ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>

                            <tr class="table_head">
                                <td colspan="3" style="padding-left:10px"><strong>Closing Cash & Cash
                                        Equivalent:</strong></td>
                            </tr>
                            <?php

                      $oResultAsset = DB::table('chart_of_accounts')
                                      ->where([['transaction',1],['status',1],['type','A']])
                                      ->where('code', 'like','1020101%')
                                      ->get();

                      $TotalAsset=0;
                      for($i=0;$i<count($oResultAsset);$i++)
                      {
                        $oResultAmount = DB::table('transactions as t')
                                            ->selectRaw('SUM(t.debit) - SUM(t.credit) AS amount')
                                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                            ->where('coa.code','like',$oResultAsset[$i]->code.'%')
                                            ->where('t.approve',1)
                                            ->whereDate('t.voucher_date','>=',$start_date)
                                            ->whereDate('t.voucher_date','<=',$end_date)
                                            ->first();

                        if($oResultAmount->amount!=0)
                        {
                    ?>
                            <tr>
                                <td align="left" style="padding-left:10px"><?php echo $oResultAsset[$i]->name; ?>
                                </td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-left: solid 1px #000; border-right:solid 1px #000;<?php if($TotalAsset==0) echo "border-top: solid 1px #000;"; ?>">
                                    <?php 
                                  $Total=$oResultAmount->amount;
                                  echo number_format($Total);
                                      $TotalAsset+=$Total; 
                              ?>
                                </td>
                            </tr>
                            <?php
                        }
                      }
                    ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-top: solid 1px #000;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="left" style="padding-left:10px;padding-right:10px"><strong>Total Closing Cash
                                        & Cash Equivalent</strong></td>
                                <td>&nbsp;</td>
                                <td align="right"
                                    style="padding-right:10px; border-top: solid 1px #000; border-bottom: solid 1px #000;">
                                    <strong><?php echo number_format($TotalAsset); ?></strong></td>
                            </tr>
                            <tr>
                                <td align="right" colspan="3">&nbsp;</td>
                            </tr>

                            
                        </table>
                        <table style="width: 100%;margin-top:50px;">
                          <tr>
                              <td class="text-center">
                                  <div class="font-size-10" style="width:250px;float:left;">
                                      <p style="margin:0;padding:0;"><b class="text-uppercase">{{ Auth::user()->name }}</b>
                                          <br> {{ date('d-M-Y h:i:s A') }}</p>
                                      <p style="width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;"></p>
                                      <p style="margin:0;padding:0;">Prepared By</p>
                                  </div>
                              </td>
                              <td class="text-center">
                                  <div class="font-size-10" style="width:250px;margin:0 auto;">
                                      <p style="margin:35px 0 0 0;padding:0;"><b class="text-uppercase"></b></p>
                                      <p style="width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;"></p>
                                      <p style="margin:0;padding:0;">Accounts</p>
                                  </div>
                              </td>
                              <td class="text-center">
                                  <div class="font-size-10" style="width:250px;float:right;">
                                      <p style="margin:35px 0 0 0;padding:0;"><b class="text-uppercase"></b></p>
                                      <p style="width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;"></p>
                                      <p style="margin:0;padding:0;">Authorizer</p>
                                  </div>
                              </td>
                          </tr>
                      </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
