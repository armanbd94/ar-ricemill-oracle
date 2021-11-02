<div class="col-md-12">
    <p><b>Voucher No :</b> {{ $credit_voucher->voucher_no }}</p>
</div>
<div class="col-md-12">
    <p><b>Credit Account :</b> {{ $credit_voucher->name }}</p>
</div>
<div class="col-md-12">
    <p><b>Date :</b> {{ date('d-M-Y',strtotime($credit_voucher->voucher_date)) }}</p>
</div>
<div class="col-md-12">
    <p><b>Warehouse :</b> {{ $credit_voucher->warehouse_name }}</p>
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
                    $total_debit = 0;
                @endphp     
                @if (!$debit_vouchers->isEmpty())
                    @foreach ($debit_vouchers as $key => $voucher)
                    @php
                        $total_debit += $voucher->debit;
                    @endphp
                    <tr>
                        <td>{{ $voucher->name }}</td>
                        <td class="text-right">{{ number_format($voucher->debit,2,'.','') }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-primary">
                    <th class="text-right">Total</th>
                    <th class="text-right">{{ number_format($total_debit,2,'.','') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<div class="col-md-12">
    <p><b>Remarks :</b> {{ $debit_vouchers[0]->description }}</p>
</div>