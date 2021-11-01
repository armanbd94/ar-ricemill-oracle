<div class="col-md-12">
    <p><b>Voucher No :</b> {{ $debit_voucher->voucher_no }}</p>
</div>
<div class="col-md-12">
    <p><b>Credit Account :</b> {{ $debit_voucher->name }}</p>
</div>
<div class="col-md-12">
    <p><b>Date :</b> {{ date('d-M-Y',strtotime($debit_voucher->voucher_date)) }}</p>
</div>
<div class="col-md-12">
    <p><b>Warehouse :</b> {{ $debit_voucher->warehouse_name }}</p>
</div>
<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered" id="debit-voucher-table">
            <thead class="bg-primary">
                <th width="60%">Account Name</th>
                <th width="40%" class="text-right">Amount</th>
            </thead>
            <tbody>
                @php
                    $total_credit = 0;
                @endphp     
                @if (!$credit_vouchers->isEmpty())
                    @foreach ($credit_vouchers as $key => $voucher)
                    @php
                        $total_credit += $voucher->credit;
                    @endphp
                    <tr>
                        <td>{{ $voucher->name }}</td>
                        <td class="text-right">{{ number_format($voucher->credit,2,'.','') }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-primary">
                    <th class="text-right">Total</th>
                    <th class="text-right">{{ number_format($total_credit,2,'.','') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<div class="col-md-12">
    <p><b>Remarks :</b> {{ $credit_vouchers[0]->description }}</p>
</div>