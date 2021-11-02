<?php
$GLOBALS['TotalAssertF']   = 0;
$GLOBALS['TotalLiabilityF']= 0;
function AssertCoa($name,$code,$general_ledger,$asset_accounts,$visited,$value,$start_date,$end_date,$check)
{
  if($value==1)
  { 
  ?>
    <tr>
        <td colspan="2" style="font-size: 20px;
        font-weight: bold;
        border-right: solid 1px #000;
        border-left: solid 1px #000;
        border-top: solid 1px #000;
        color: #036;"><?php echo $name;?></td>
    </tr>
  <?php
  }elseif($value>1){

    if($check)
    {
        $oResultAmountPreF = DB::table('transactions as t')
                            ->selectRaw('SUM(t.debit) - SUM(t.credit) AS amount')
                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                            ->where('coa.code','like',$code.'%')
                            ->whereDate('t.voucher_date','>=',$start_date)
                            ->whereDate('t.voucher_date','<=',$end_date)
                            ->first();
    }else{
        $oResultAmountPreF = DB::table('transactions as t')
                            ->selectRaw('SUM(t.credit) - SUM(t.debit) AS amount')
                            ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                            ->where('coa.code','like',$code.'%')
                            ->where('t.approve',1)
                            ->whereDate('t.voucher_date','>=',$start_date)
                            ->whereDate('t.voucher_date','<=',$end_date)
                            ->first();
    }
    if($value==2)
    {
      if($check == 1)
      {
        $GLOBALS['TotalLiabilityF'] = $GLOBALS['TotalLiabilityF'] + $oResultAmountPreF->amount;
      }else
      {
        $GLOBALS['TotalAssertF'] = $GLOBALS['TotalAssertF'] + $oResultAmountPreF->amount;
      }
    }

    if($oResultAmountPreF->amount != 0)
    {
    ?>
      <tr>
        <td align="left" style="border-left: solid 1px #000;" class=" <?php echo  ($value <= 3 ? " font-bold ":" ");?>"  font-size="<?php echo (int)(20-$value*1.5).'px'; ?>"><font size="+1"><?php echo ($value>=3?"&nbsp;&nbsp;":""). $name; ?></font></td>
        <td align="right" style="border-left: solid 1px #000;
        border-right: solid 1px #000;"><?php echo number_format($oResultAmountPreF->amount,2);?></td>
      </tr>
    <?php
    }
  }
  for($i=0;$i<count($asset_accounts);$i++)
  {
        
      if (!$visited[$i] && $general_ledger == 2)
      {
        if ($name == $asset_accounts[$i]->parent_name)
        {
          $visited[$i]=true;
          AssertCoa($asset_accounts[$i]->name,$asset_accounts[$i]->code,$asset_accounts[$i]->general_ledger,$asset_accounts,$visited,$value+1,$start_date,$end_date,$check);
        }
      }
  }
}

?>


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
            <div class="panel-heading">
                <div class="panel-title">
                    <h4></h4>
                </div>
            </div>
            <div id="printArea">
                <div class="panel-body">
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
                  <table class="table" width="100%" class="table_boxnew" cellpadding="5" cellspacing="0">
                    <tr>
                        <td colspan="2" align="center"><h3><b>Statement of Comprehensive Income<br/>From <?php echo $start_date ?> To <?php echo $end_date;?></b></h3></td>
                    </tr>
                    <tr>
                      <td width="85%" align="center"><h4><b>Particulars</b></h4></td>
                      <td width="15%" align="center"><h4><b>Amount</b></h4></td>
                    </tr>
                    <?php
                    for($i=0;$i<count($asset_accounts);$i++)
                    {
                      $visited[$i] = false;
                    }

                    AssertCoa("COA","0",2,$asset_accounts,$visited,0,$start_date,$end_date,0);

                    $TotalAssetF=$GLOBALS['TotalAssertF'];
                    ?>
                    <tr>
                        <td align="right"><strong>Total Income</strong></td>
                        <td align="right" style="border-style: double;border-left: none;border-right: none;"><strong ><?php echo number_format($TotalAssetF,2); ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="right"></td>
                    </tr>
                    <?php
                    for($i=0;$i<count($liability_accounts);$i++)
                    {
                      $visited[$i] = false;
                    }
                    $GLOBALS['TotalLiability']=0;
                    AssertCoa("COA","0",2,$liability_accounts,$visited,0,$start_date,$end_date,1);
                    $TotalLibilityF=$GLOBALS['TotalLiabilityF'];
                    ?>
                    <tr >
                        <td align="right"><strong>Total Expense</strong></td>
                        <td align="right" style="border-style: double;border-left: none;border-right: none;"><strong><?php echo number_format($TotalLibilityF,2); ?></strong></td>
                    </tr>
                    <tr class="profitloss-result">
                        <td align="right" style="background: #036;color:white;"><h4>Profit-Loss <?php echo $TotalAssetF>$TotalLibilityF?"(Profit)":"(Loss)";?></h4></td>
                        <td align="right" style="background: #036;color:white;"><b><?php echo number_format($TotalAssetF-$TotalLibilityF,2); ?></b></td>
                    </tr>
                   
                  </table>
                </div>
            </div>
        </div>
    </div>
</div>
