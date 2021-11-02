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

        .dashed-border {
            width: 180px;
            height: 2px;
            margin: 0 auto;
            padding: 0;
            border-top: 1px dashed #454d55 !important;
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
            <h3 style="margin:0;text-transform:uppercase;">
                {{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}
            </h3>
            @if (config('settings.contact_no'))
            <p style="font-weight: normal;margin:0;"><b>Contact: </b>{{ config('settings.contact_no') }},
                @if (config('settings.email'))<b>Email:
                </b>{{ config('settings.email') }}@endif
            </p>
            @endif
            @if (config('settings.address'))
            <p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>
            @endif
            <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
        </td>
    </tr>
</table>
<div style="width:100%;height:2px;padding:5px 0;"></div>
<div style="width:100%;height:2px;border-bottom:1px dotted #454d55;"></div>
<div style="width:100%;height:2px;padding-top:5px;"></div>
<h4 class="text-dark text-center py-3"><b> Balance Sheet (From {{ date('d-M-Y', strtotime($start_date)) }} To
        {{ date('d-M-Y', strtotime($end_date)) }})</b></h4>

<table class="table table-bordered" style="width:100%;">
    <thead class="bg-primary">
        <tr>
            <th class="text-cnter">Particulars</th>
            <th class="text-cnter">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($fixed_assets as $assets) {

            $total_assets = 0;
            $head_data = \DB::table('chart_of_accounts')
                ->select('*')
                ->where('parent_name', $assets->name)
                ->groupBy('code')
                ->get();

        ?>
            <tr>
                <td align="left" class="@if($assets->name == 'Current Asset' || $assets->name == 'Non Current Asset') {{ 'font-weight-bolder' }} @endif">{{ $assets->name }}
                </td>

                <td align="right" class="cashflowamnt">

                </td>
            </tr>
            @php
            // dd($head_data);
            @endphp
            <?php foreach ($head_data as $assets_head) {

                $ass_balance = \DB::table('transactions')
                    ->selectRaw('(sum(debit)-sum(credit)) as balance')
                    ->where([['approve', 1], ['chart_of_account_id', $assets_head->id]])
                    ->whereDate('voucher_date', '>=', $start_date)
                    ->whereDate('voucher_date', '<=', $end_date)->first();
                if ($assets_head->parent_name == 'Current Asset') {
                    $child_head_current = \DB::table('chart_of_accounts')
                        ->where('parent_name', $assets_head->name)
                        ->groupBy('code')
                        ->get();
            ?>
                    <tr>
                        <td align="left" class="paddingleft10px">-<?php echo $assets_head->name; ?></td>
                        <td align="right" class="cashflowamnt"> </td>
                    </tr>
                    <?php
                    // dd($child_head_current);
                    foreach ($child_head_current as $cchead) {

                        $cur_ass_balance = \DB::table('transactions')
                            ->selectRaw('(sum(debit)-sum(credit)) as balance')
                            ->where([['approve', 1], ['chart_of_account_id', $cchead->id]])
                            ->whereDate('voucher_date', '>=', $start_date)
                            ->whereDate('voucher_date', '<=', $end_date)->first();

                        $schild_head_current = \DB::table('chart_of_accounts')
                            ->where('parent_name', $cchead->name)
                            ->groupBy('code')
                            ->get();

                            //dd($schild_head_current);

                    ?>
                        <?php if ($cur_ass_balance->balance <> 0) { ?>
                            <tr>
                                <td align="left" class="paddingleft10px">-{{ $cchead->name }}</td>

                                <td align="right" class="cashflowamnt">
                                    <?php
                                    echo $cur_ass_balance->balance;
                                    $total_assets += $cur_ass_balance->balance;
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php
                        if (
                            $cchead->name == 'Cash At Bank' || $cchead->name == 'Cash At Mobile Bank' ||
                            $cchead->name == 'Account Receivable' || $cchead->name == 'Customer Receivable' ||
                            $cchead->name == 'Loan Receivable'
                        ) {
                             //dd($cchead->name);
                            foreach ($schild_head_current as $scchild) {
                                $cur_bank_balance = \DB::table('transactions')
                                    ->selectRaw('(sum(debit)-sum(credit)) as balance')
                                    ->where([['approve', 1], ['chart_of_account_id', $scchild->id]])
                                    ->whereDate('voucher_date', '>=', $start_date)
                                    ->whereDate('voucher_date', '<=', $end_date)->first();
                                // dd($cur_bank_balance);
                                if ($cur_bank_balance->balance <> 0) {
                        ?>
                                    <tr>
                                        <td align="left" class="paddingleft10px">--{{ $scchild->name }}</td>

                                        <td align="right" class="cashflowamnt">
                                            <?php
                                            echo $cur_bank_balance->balance;
                                            $total_assets += $cur_bank_balance->balance;
                                            ?>
                                        </td>
                                    </tr>
                <?php
                                }
                            }
                        }
                    }
                }
                ?>

                <?php if ($assets_head->parent_name == 'Non Current Asset') { ?>
                    <tr>
                        <td align="left" class="paddingleft10px">-<?php echo $assets_head->name; ?></td>

                        <td align="right" class="cashflowamnt">
                            <?php
                            echo $ass_balance->balance;
                            $total_assets += $ass_balance->balance;
                            ?>
                        </td>
                    </tr>
                <?php } ?>

            <?php
            } ?>

            <tr>
                <td class="text-right" style="padding-right: 10px;"><b>Total <?php echo
                                                                                $assets->name; ?></b></td>

                <td align="right" class="cashflowamnt" style="border: solid 2px #000;">
                    <b><?php echo number_format($total_assets, 2); ?></b>
                </td>
            </tr>
        <?php
        } ?>



        <?php foreach ($liabilities as $liability) {

            $total_liab = 0;
            $liab_head_data = \DB::table('chart_of_accounts')
                ->where('parent_name', $liability->name)
                ->get();
        ?>
            <tr>
                <td align="left" class="<?php if ($liability->name == 'Current Liabilities' || $liability->name == 'Non Current Liabilities') {
                                            echo 'font-weight-bolder';
                                        } ?>"><?php echo $liability->name; ?></td>

                <td align="right" class="cashflowamnt">

                </td>
            </tr>
            <?php 
            foreach ($liab_head_data as $liab_head) {
                if ($liab_head->name == 'Tax') {
                    $child_liability = \DB::table('chart_of_accounts')
                        ->where('name', $liab_head->name)
                        ->get();
                } else {
                    $child_liability = \DB::table('chart_of_accounts')
                        ->where('parent_name', $liab_head->name)
                        ->get();
                } ?>
                <?php if ($liab_head->name != 'Tax') { ?>
                    <tr>
                        <td align="left" class="paddingleft10px" style="font-weight:bold;">-<?php echo $liab_head->name; ?></td>

                        <td align="right" class="cashflowamnt">
                            <?php $total_liab += 0; ?>
                        </td>
                    </tr>
                <?php } ?>

                <?php
                //dd($child_liability);
                foreach ($child_liability as $chliab_head) {
                    $total_liab_sub = 0;
                    $liab_balance = \DB::table('transactions')
                        ->selectRaw('(sum(credit)-sum(debit)) as balance')
                        ->where([['approve', 1], ['chart_of_account_id', $chliab_head->id]])
                        ->whereDate('voucher_date', '>=', $start_date)
                        ->whereDate('voucher_date', '<=', $end_date)->first(); ?>
                    <?php //if ($liab_balance->balance != 0) { ?>
                        <tr>
                            <td align="left" class="paddingleft10px ">--<?php echo $chliab_head->name; ?></td>

                            <td align="right" class="cashflowamnt">
                                <?php
                                echo $liab_balance->balance;
                                $total_liab += $liab_balance->balance;
                                ?>
                            </td>
                        </tr>
                        <?php $child_liability_sub = \DB::table('chart_of_accounts')
                        ->where('parent_name', $chliab_head->name)
                        ->get(); 
                        ?>

                        <?php foreach ($child_liability_sub as $chliab_head_sub) {
                            $liab_balance_sub = \DB::table('transactions')
                                ->selectRaw('(sum(credit)-sum(debit)) as balance')
                                ->where([['approve', 1], ['chart_of_account_id', $chliab_head_sub->id]])
                                ->whereDate('voucher_date', '>=', $start_date)
                                ->whereDate('voucher_date', '<=', $end_date)->first(); ?>
                            <?php if ($liab_balance_sub->balance != 0) { ?>
                                <tr>
                                    <td align="left" class="paddingleft10px ">---<?php echo $chliab_head_sub->name; ?></td>

                                    <td align="right" class="cashflowamnt">
                                        <?php
                                        echo $liab_balance_sub->balance;
                                        $total_liab_sub += $liab_balance_sub->balance;
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>

                    <?php //} ?>

                <?php } ?>
            <?php } ?>
            <tr>
                <td class="paddingleft10px text-right" style="padding-right: 10px;"><b>Total <?php echo $liability->name; ?></b></td>

                <td align="right" class="cashflowamnt" style="border: solid 2px #000;">
                    <b><?php echo number_format($total_liab, 2); ?></b>
                </td>
            </tr>
        <?php
        } ?>

        <?php
        $total_expense = 0;
        $total_income = 0;
        // dd($incomes);
        foreach ($incomes as $incomelable) {
            $income_balance = \DB::table('transactions')
                ->selectRaw('(sum(credit)-sum(debit)) as balance')
                ->where([['approve', 1], ['chart_of_account_id', $incomelable->id]])
                ->whereDate('voucher_date', '>=', $start_date)
                ->whereDate('voucher_date', '<=', $end_date)->first(); ?>
            <tr>
                <td align="left" class="paddingleft10px font-weight-bolder"><?php echo
                                                                            $incomelable->name; ?></td>

                <td align="right" class="cashflowamnt">
                    <?php
                    echo $income_balance->balance;
                    $total_income += $income_balance->balance;
                    ?>
                </td>
            </tr>
        <?php
        }
        ?>

        <tr>
            <td class="paddingleft10px text-right" style="padding-right: 10px;"><b>Total Income</b>
            </td>

            <td align="right" class="cashflowamnt" style="border: solid 2px #000;">
                <b><?php echo number_format($total_income, 2); ?></b>
            </td>
        </tr>
        <?php foreach ($expenses as $expense) {
            $expense_balance = \DB::table('transactions')
                ->selectRaw('(sum(debit)-sum(credit)) as balance')
                ->where([['approve', 1], ['chart_of_account_id', $expense->id]])
                ->whereDate('voucher_date', '>=', $start_date)
                ->whereDate('voucher_date', '<=', $end_date)->first(); ?>
            <tr>
                <td align="left" class="paddingleft10px font-weight-bolder"><?php echo $expense->name; ?></td>

                <td align="right" class="cashflowamnt">
                    <?php
                    echo $expense_balance->balance;
                    $total_expense += $expense_balance->balance;
                    ?>
                </td>
            </tr>
        <?php
        } ?>
        <tr>
            <td class="paddingleft10px text-right" style="padding-right: 10px;"><b>Total
                    Expense</b></td>

            <td align="right" class="cashflowamnt" style="border: solid 2px #000;">
                <b><?php echo number_format($total_expense, 2); ?></b>
            </td>
        </tr>
    </tbody>
</table>