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
<table width="100%" style="margin:0;padding:0;">
    <tr>
        <td width="100%" class="text-center">
            <h3 style="margin:0;text-transform:uppercase;">{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</h3>
            @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif
            @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
            <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
        </td>
    </tr>
</table>
<div style="width:100%;height:2px;padding:5px 0;"></div>
<div style="width:100%;height:2px;border-bottom:1px dotted #454d55;"></div>
<div style="width:100%;height:2px;padding-top:5px;"></div>
<h4 class="text-dark text-center py-3"><b> Trail Balance {{ ($with_details == 1) ? ' With Opening' : '' }} (From {{ date('d-M-Y',strtotime($start_date)) }} To {{ date('d-M-Y',strtotime($end_date)) }})</b></h4>

<table class="table table-bordered" style="width:100%;">
    <thead class="bg-primary">
        <tr>
            <th class="text-cnter">Code</th>
            <th class="text-cnter">Account Name</th>
            <th class="text-right">Debit</th>
            <th class="text-right">Credit</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_debit = $total_credit = $k = 0;
        @endphp
        @foreach ($transactional_accounts as $key => $coa)
            @php
                $accounts = DB::table('chart_of_accounts')->where('code','like',$coa->code.'%')->get('id');
                $ids = [];
                if(!$accounts->isEmpty())
                {
                    foreach ($accounts as $key => $value) {
                        array_push($ids,$value->id);
                    }
                }
                $trial_data = DB::table('transactions')->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                            ->where('approve',1)
                            ->whereIn('chart_of_account_id',$ids)
                            ->whereDate('voucher_date','>=',$start_date)
                            ->whereDate('voucher_date','<=',$end_date);

                $trial_data = $trial_data->first();
                $bg=$k&1?"#FFFFFF":"#E7E0EE";
            @endphp

            @if ($trial_data->credit != $trial_data->debit)
                @php  $k++; @endphp
                <tr>
                    <td>{{ $coa->code }}</td>
                    <td>{{ $coa->name }}</td>
                    @if ($trial_data->debit > $trial_data->credit)
                        <td align="right">
                            <?php 
                                $total_debit += $trial_data->debit - $trial_data->credit;
                                echo number_format(($trial_data->debit - $trial_data->credit),2);
                            ?>
                        </td>
                        <td align="right"> {{ number_format(0,2) }}</td>
                    @else 
                        <td align="right"> {{ number_format(0,2) }}</td>
                        <td align="right">
                            <?php 
                                $total_credit += $trial_data->credit - $trial_data->debit;
                                echo number_format(($trial_data->credit - $trial_data->debit),2);
                            ?>
                        </td>
                    @endif
                </tr>
            @endif
            
        @endforeach

        <!--*****-->

        @foreach ($income_expense_accounts as $key => $coa)
            @php
                $accounts = DB::table('chart_of_accounts')->where('code','like',$coa->code.'%')->get('id');
                $ids = [];
                if(!$accounts->isEmpty())
                {
                    foreach ($accounts as $key => $value) {
                        array_push($ids,$value->id);
                    }
                }
                $trial_data = DB::table('transactions')->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                            ->where('approve',1)
                            ->whereIn('chart_of_account_id',$ids)
                            ->whereDate('voucher_date','>=',$start_date)
                            ->whereDate('voucher_date','<=',$end_date);

                $trial_data = $trial_data->first();
                $bg=$k&1?"#FFFFFF":"#E7E0EE";
            @endphp

            @if ($trial_data->credit != $trial_data->debit)
                @php  $k++; @endphp
                <tr>
                    <td>{{ $coa->code }}</td>
                    <td>{{ $coa->name }}</td>
                    @if ($trial_data->debit > $trial_data->credit)
                        <td align="right">
                            <?php 
                                $total_debit += $trial_data->debit - $trial_data->credit;
                                echo number_format(($trial_data->debit - $trial_data->credit),2);
                            ?>
                        </td>
                        <td align="right"> {{ number_format(0,2) }}</td>
                    @else 
                        <td align="right"> {{ number_format(0,2) }}</td>
                        <td align="right">
                            <?php 
                                $total_credit += $trial_data->credit - $trial_data->debit;
                                echo number_format(($trial_data->credit - $trial_data->debit),2);
                            ?>
                        </td>
                    @endif
                </tr>
            @endif
        @endforeach

        @if ($with_details == 1)
            @php
                $profit_loss = $total_debit - $total_credit;
                // dd($profit_loss);
            @endphp
            
            @if($profit_loss != 0)
                <tr class="font-weight-bolder">
                    <td align="left">&nbsp;</td>
                    <td align="left">Profit-Loss</td>
                    @if($profit_loss < 0)
                    
                        <td align="right">
                            <?php 
                                $total_debit += abs($profit_loss);
                                echo number_format( abs($profit_loss),2);
                            ?>
                        </td>
                        <td align="right"><?php echo number_format('0.00',2);?></td>
                    @else

                        <td align="right"><?php echo number_format('0.00',2); ?></td>
                        <td align="right">
                            <?php
                                $total_credit += abs($profit_loss);
                                echo number_format(abs($profit_loss),2);
                            ?>
                        </td>
                    @endif
                </tr>
            @endif
        @endif
    </tbody>
    <tfoot>
        <tr class="bg-primary">
            <td colspan="2" class="text-right text-white font-weight-bolder">Total</td>
            <td class="text-right text-white font-weight-bolder">{{ number_format($total_debit,2)  }}{{ config('settings.currency_symbol') }}</td>
            <td class="text-right text-white font-weight-bolder">{{ number_format($total_credit,2)  }}{{ config('settings.currency_symbol') }}</td>
        </tr>
    </tfoot>
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